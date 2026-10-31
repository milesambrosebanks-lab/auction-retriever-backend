// Iron Shin VR — bootstrap, render loop, XR session, locomotion, game flow.

import * as THREE from 'three';
import { settings, loadSettings, setSetting } from './settings.js';
import { Input } from './input.js';
import { Player } from './player.js';
import { getArena, boundsOutline, crowdUniforms } from './arenas.js';
import { Pointers, FloatingText, Announcer, Scoreboard, ScreenFX } from './ui.js';
import { Menu, WristMenu } from './menu.js';
import { FightMode } from './fight.js';
import { TrainingMode } from './training.js';
import { initAudio, setAmbience, updateListener, setVolume } from './audio.js';
import { clamp } from './util.js';

loadSettings();

// ---- Renderer tuned for Quest ------------------------------------------------
const renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true, powerPreference: 'high-performance', stencil: false });
renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
renderer.setSize(window.innerWidth, window.innerHeight);
renderer.setClearColor(0x000000, 0);
renderer.xr.enabled = true;
renderer.xr.setReferenceSpaceType('local-floor');
renderer.xr.setFramebufferScaleFactor(1.0);   // native per-eye resolution
renderer.xr.setFoveation(1.0);                // max fixed foveation: big GPU savings at the lens edges
document.getElementById('app').appendChild(renderer.domElement);

const scene = new THREE.Scene();
const camera = new THREE.PerspectiveCamera(72, window.innerWidth / window.innerHeight, 0.03, 400);
const rig = new THREE.Group();
rig.name = 'rig';
rig.add(camera);
scene.add(rig);

const hemi = new THREE.HemisphereLight(0xffffff, 0x444444, 1.2);
const sun = new THREE.DirectionalLight(0xffffff, 1.5);
sun.position.set(1, 6, 2);
scene.add(hemi, sun, sun.target);

const input = new Input(renderer, rig, camera, renderer.domElement);
const player = new Player(scene, input);
const pointers = new Pointers(scene, input);
const floating = new FloatingText(scene);
const announcer = new Announcer(scene);
const scoreboard = new Scoreboard(scene);
const fx = new ScreenFX(camera);

// ---- Game context shared by modes ----------------------------------------------
const game = {
  scene, camera, rig, renderer, input, player, pointers, floating, announcer, scoreboard, fx,
  arena: null, outline: null, mode: null, paused: false, time: 0, fight: null,
  passthroughSupported: false, session: null, lastModeOpts: null,

  setArena(id) {
    if (this.arena && this.arena.id === id) { this.applyView(); return; }
    if (this.arena) scene.remove(this.arena.group);
    if (this.outline) { scene.remove(this.outline); this.outline.geometry.dispose(); }
    const a = getArena(id);
    this.arena = a;
    scene.add(a.group);
    const L = a.lights;
    // arena values are authored in legacy units; three r155+ lights are physical (x~PI)
    hemi.color.setHex(L.sky); hemi.groundColor.setHex(L.ground); hemi.intensity = L.hemi * LIGHT_SCALE;
    sun.color.setHex(L.dir); sun.intensity = L.dirI * LIGHT_SCALE; sun.position.set(...L.dirPos);
    this.outline = boundsOutline(a.bounds);
    scene.add(this.outline);
    setAmbience(a.ambience);
    this.applyView();
  },

  /** Is passthrough actually being shown right now? */
  get passthroughOn() {
    if (settings.view !== 'passthrough') return false;
    return !input.xr || this.passthroughSupported;
  },

  applyView() {
    const pass = this.passthroughOn;
    if (this.arena) {
      this.arena.group.visible = !pass;
      scene.fog = !pass && this.arena.fog ? new THREE.Fog(this.arena.fog.color, this.arena.fog.near, this.arena.fog.far) : null;
      setAmbience(pass ? (this.arena.ambience === 'crowd' ? 'small' : null) : this.arena.ambience);
    }
    if (this.outline) this.outline.visible = pass;
    document.body.classList.toggle('passthrough', pass);
  },

  setView(v) {
    setSetting('view', v);
    this.applyView();
    menu.refresh();
  },

  /** Puts the player's head at (x, z) facing yaw (0 = -Z), by moving the rig. */
  placePlayer(x, z, yaw) {
    rig.updateMatrixWorld(true);
    input.update(0); // refresh head pose
    const headYaw = input.head.yaw;
    const dYaw = yaw - headYaw;
    rig.rotation.y += dYaw;
    rig.updateMatrixWorld(true);
    input.update(0);
    rig.position.x += x - input.head.pos.x;
    rig.position.z += z - input.head.pos.z;
    rig.updateMatrixWorld(true);
    input.update(0);
  },

  recenter() {
    const p = input.head.pos;
    this.placePlayer(this.mode ? p.x : 0, this.mode ? p.z : 1.2, this.mode ? (this.fight ? Math.atan2(-(this.fight.ai.pos.x - p.x), -(this.fight.ai.pos.z - p.z)) : 0) : 0);
  },

  announce(title, sub, opts = {}) {
    const head = input.head;
    let fwd = head.fwd;
    if (this.fight && this.fight.ai) {
      const a = this.fight.ai.pos;
      _tmp.set(a.x - head.pos.x, 0, a.z - head.pos.z);
      if (_tmp.lengthSq() > 0.01) fwd = _tmp.normalize();
    }
    announcer.show(title, sub, { head: head.pos, fwd, dist: 1.6, height: opts.height ?? 0.25, ...opts });
  },

  startFight() {
    const opts = { opponent: settings.opponent, difficulty: settings.difficulty, arena: settings.arena, rounds: settings.rounds, roundSeconds: settings.roundSeconds };
    this.startMode(new FightMode(this, opts), 'fight');
  },

  startTraining() {
    const opts = { drill: settings.drill, difficulty: settings.difficulty, arena: settings.trainingArena };
    this.startMode(new TrainingMode(this, opts), 'training');
  },

  startMode(mode, kind) {
    initAudio();
    this.endMode();
    menu.close();
    this.paused = false;
    this.mode = mode;
    this.modeKind = kind;
    mode.enter();
  },

  endMode() {
    if (this.mode) { this.mode.exit(); this.mode = null; }
    announcer.hide();
    fx.fade = 0; fx.lowHp = 0;
    player.kicksEnabled = false;
  },

  restart() {
    if (!this.mode) return;
    const kind = this.modeKind, opts = this.mode.opts;
    const m = kind === 'fight' ? new FightMode(this, opts) : new TrainingMode(this, opts);
    this.startMode(m, kind);
  },

  quitToMenu(screen = 'main') {
    this.endMode();
    this.paused = false;
    this.setArena(screen === 'training' ? settings.trainingArena : screen === 'fight' ? settings.arena : 'stadium');
    this.placePlayer(0, 1.2, 0);
    menu.open(screen);
  },

  pause() {
    if (!this.mode || this.paused) return;
    this.paused = true;
    menu.backFromSettings = 'pause';
    menu.open('pause');
  },

  resume() {
    this.paused = false;
    menu.close();
    menu.backFromSettings = null;
  },

  applyFrameRate() {
    const s = this.session;
    if (!s || !s.updateTargetFrameRate) return;
    const rates = s.supportedFrameRates ? Array.from(s.supportedFrameRates) : [];
    if (!rates.length) return;
    // pick the requested rate, or the closest supported one
    let best = rates[0];
    for (const r of rates) if (Math.abs(r - settings.refreshRate) < Math.abs(best - settings.refreshRate)) best = r;
    s.updateTargetFrameRate(best).catch(() => {});
  },

  applySeated() { rig.position.y = settings.seated && input.xr ? 0.5 : 0; },

  exitXR() { if (this.session) this.session.end(); },
};
const _tmp = new THREE.Vector3();
const LIGHT_SCALE = 2.0;
window.__game = game; // handy for debugging in the browser console

const menu = new Menu(game);
const wrist = new WristMenu(game);
game.menu = menu;

// ---- Locomotion -------------------------------------------------------------------
let snapReady = true;
const _mv = new THREE.Vector3(), _hp = new THREE.Vector3();
function locomotion(dt) {
  const L = input.left, R = input.right;
  // move
  const ax = Math.abs(L.axes.x) > 0.15 ? L.axes.x : 0;
  const ay = Math.abs(L.axes.y) > 0.15 ? L.axes.y : 0;
  let moving = 0;
  if (ax || ay) {
    const f = input.head.fwd, r = input.head.right;
    _mv.set(0, 0, 0).addScaledVector(r, ax).addScaledVector(f, -ay);
    const mag = Math.min(1, _mv.length());
    moving = mag;
    if (_mv.lengthSq() > 1) _mv.normalize();
    _mv.multiplyScalar(settings.moveSpeed * dt);
    // keep the head inside the arena when stick-moving
    _hp.copy(input.head.pos).add(_mv);
    if (game.arena) game.arena.clamp(_hp, 0.25);
    rig.position.x += _hp.x - input.head.pos.x;
    rig.position.z += _hp.z - input.head.pos.z;
  }
  // turn
  const tx = R.axes.x;
  if (settings.turn === 'smooth') {
    if (Math.abs(tx) > 0.2) { rotateRigAroundHead(-tx * 2.0 * dt); moving = Math.max(moving, Math.abs(tx) * 0.8); }
  } else if (settings.turn !== 'off') {
    const step = (settings.turn === 'snap30' ? 30 : 45) * Math.PI / 180;
    if (snapReady && Math.abs(tx) > 0.7) { rotateRigAroundHead(-Math.sign(tx) * step); snapReady = false; }
    else if (Math.abs(tx) < 0.3) snapReady = true;
  }
  fx.vignette += ((settings.vignette ? moving * 0.75 : 0) - fx.vignette) * clamp(dt * 10, 0, 1);
}

function rotateRigAroundHead(angle) {
  const h = input.head.pos;
  const dx = rig.position.x - h.x, dz = rig.position.z - h.z;
  const c = Math.cos(angle), s = Math.sin(angle);
  rig.position.x = h.x + dx * c + dz * s;
  rig.position.z = h.z - dx * s + dz * c;
  rig.rotation.y += angle;
}

// ---- XR session ---------------------------------------------------------------------
const ui = {
  overlay: document.getElementById('overlay'),
  enter: document.getElementById('enter-vr'),
  desktop: document.getElementById('desktop'),
  status: document.getElementById('xr-status'),
  viewBtns: document.querySelectorAll('[data-view]'),
  hud: document.getElementById('desktop-hud'),
};

async function detectXR() {
  if (!navigator.xr) {
    ui.status.textContent = 'WebXR not available in this browser. Open this page in the Meta Quest Browser — or try the desktop preview.';
    ui.enter.disabled = true;
    return;
  }
  const [ar, vr] = await Promise.all([
    navigator.xr.isSessionSupported('immersive-ar').catch(() => false),
    navigator.xr.isSessionSupported('immersive-vr').catch(() => false),
  ]);
  game.passthroughSupported = ar;
  game.xrMode = ar ? 'immersive-ar' : vr ? 'immersive-vr' : null;
  if (!game.xrMode) {
    ui.status.textContent = 'No VR headset detected. Open this page in the Meta Quest Browser — or try the desktop preview.';
    ui.enter.disabled = true;
  } else {
    ui.enter.disabled = false;
    ui.status.textContent = ar ? 'Headset ready · immersive and passthrough available' : 'Headset ready · immersive mode (passthrough not supported here)';
  }
}

async function enterXR() {
  initAudio();
  if (!game.xrMode) return;
  try {
    const session = await navigator.xr.requestSession(game.xrMode, {
      requiredFeatures: ['local-floor'],
      optionalFeatures: ['bounded-floor', 'layers'],
    });
    game.session = session;
    session.addEventListener('end', onSessionEnd);
    await renderer.xr.setSession(session);
    renderer.xr.setFoveation(1.0);
    game.applyFrameRate();
    ui.overlay.classList.add('hidden');
    ui.hud.classList.add('hidden');
    game.applySeated();
    game.applyView();
    // start in the menu, a step back from the center
    setTimeout(() => { if (!game.mode) game.quitToMenu(menu.screen === 'main' ? 'main' : menu.screen); }, 50);
  } catch (err) {
    console.error(err);
    ui.status.textContent = 'Could not start VR: ' + (err && err.message ? err.message : err);
  }
}

function onSessionEnd() {
  game.session = null;
  if (game.mode && !game.paused) game.pause();
  ui.overlay.classList.remove('hidden');
  rig.position.y = 0;
  game.applyView();
}

function startDesktop() {
  initAudio();
  ui.overlay.classList.add('hidden');
  ui.hud.classList.remove('hidden');
  game.quitToMenu('main');
}

ui.enter.addEventListener('click', enterXR);
ui.desktop.addEventListener('click', startDesktop);
ui.viewBtns.forEach((b) => b.addEventListener('click', () => {
  setSetting('view', b.dataset.view);
  ui.viewBtns.forEach((x) => x.setAttribute('aria-pressed', String(x.dataset.view === settings.view)));
}));
ui.viewBtns.forEach((x) => x.setAttribute('aria-pressed', String(x.dataset.view === settings.view)));
document.getElementById('show-overlay')?.addEventListener('click', () => {
  if (game.mode && !game.paused) game.pause();
  ui.overlay.classList.remove('hidden');
});
window.addEventListener('keydown', (e) => {
  if (e.code === 'Escape' && !input.xr && ui.overlay.classList.contains('hidden')) {
    if (game.mode && !game.paused) game.pause();
    else if (game.mode && game.paused && menu.screen === 'pause') game.resume();
  }
});
window.addEventListener('resize', () => {
  camera.aspect = window.innerWidth / window.innerHeight;
  camera.updateProjectionMatrix();
  renderer.setSize(window.innerWidth, window.innerHeight);
});
detectXR();
setVolume(settings.volume);

// ---- Main loop ------------------------------------------------------------------
const clock = new THREE.Clock();
let fpsFrames = 0, fpsT = 0, fps = 0;
const _fwd = new THREE.Vector3(), _up = new THREE.Vector3();

game.setArena('stadium');
game.placePlayer(0, 1.2, 0);
menu.open('main');

renderer.setAnimationLoop(() => {
  const dt = Math.min(clock.getDelta(), 0.05);
  game.time += dt;
  fpsFrames++; fpsT += dt;
  if (fpsT >= 0.5) { fps = Math.round(fpsFrames / fpsT); fpsFrames = 0; fpsT = 0; }

  input.update(dt);
  const uiActive = menu.isOpen;
  if (!uiActive || !game.mode) locomotion(dt); else fx.vignette = Math.max(0, fx.vignette - dt * 4);

  if (game.mode && !game.paused) {
    player.update(dt, uiActive);
    game.mode.update(dt);
  } else {
    player.update(dt, true);
  }

  // wrist menu (pause) — only during a mode
  const tapped = wrist.update(dt, {
    fps, hp: player.hpFrac, st: player.stFrac, showBars: !!game.fight, enabled: !!game.mode && !uiActive,
  });
  if (tapped) game.pause();

  pointers.enabled = uiActive;
  pointers.update();
  menu.update(dt, game.time);
  floating.update(dt);
  announcer.update(dt);
  fx.update(dt);
  if (game.arena && game.arena.update && !game.passthroughOn) game.arena.update(dt, game.time);
  crowdUniforms.uTime.value = game.time;

  _fwd.copy(input.head.lookDir);
  _up.set(0, 1, 0).applyQuaternion(input.head.quat);
  updateListener(input.head.pos, _fwd, _up);

  // desktop HUD
  if (!input.xr && settings.showFps) ui.hud.dataset.fps = fps;

  renderer.render(scene, camera);
});
