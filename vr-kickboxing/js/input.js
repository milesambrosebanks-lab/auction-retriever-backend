// Unified input: WebXR controllers (Quest Touch Plus) with velocity tracking,
// button edge detection and haptics. Falls back to a keyboard/mouse simulator
// on desktop so the game can be previewed and tested without a headset.

import * as THREE from 'three';
import { settings } from './settings.js';
import { clamp } from './util.js';

const BTN = ['trigger', 'grip', 'stick', 'a', 'b'];
// xr-standard gamepad mapping indices
const IDX = { trigger: 0, grip: 1, stick: 3, a: 4, b: 5 };

function makeButtons() {
  const o = {};
  for (const b of BTN) o[b] = { pressed: false, down: false, up: false, value: 0 };
  return o;
}

class Hand {
  constructor(side) {
    this.side = side;               // 'left' | 'right'
    this.sign = side === 'left' ? -1 : 1;
    this.grip = null;               // THREE.Group (grip space)
    this.ray = null;                // THREE.Group (target ray space)
    this.source = null;             // XRInputSource
    this.connected = false;
    this.pos = new THREE.Vector3();
    this.prevPos = new THREE.Vector3();
    this.quat = new THREE.Quaternion();
    this.local = new THREE.Vector3();
    this.prevLocal = new THREE.Vector3();
    this.vel = new THREE.Vector3();       // world-oriented velocity, rig-relative (locomotion removed)
    this.speed = 0;
    this.peakSpeed = 0;                   // recent peak, decays
    this.elbow = new THREE.Vector3();
    this.elbowLocal = new THREE.Vector3();
    this.prevElbowLocal = new THREE.Vector3();
    this.elbowVel = new THREE.Vector3();
    this.elbowSpeed = 0;
    this.buttons = makeButtons();
    this.uiClick = false;
    this.axes = { x: 0, y: 0 };
    this.rayOrigin = new THREE.Vector3();
    this.rayDir = new THREE.Vector3(0, 0, -1);
    this.valid = false;                   // have a pose this frame
    this.first = true;
    // desktop sim
    this.sim = { t: 1, kind: 'straight', dur: 0.26 };
  }
}

const _v = new THREE.Vector3(), _v2 = new THREE.Vector3(), _q = new THREE.Quaternion();
const _m = new THREE.Matrix4(), _e = new THREE.Euler();

export class Input {
  constructor(renderer, rig, camera, dom) {
    this.renderer = renderer;
    this.rig = rig;
    this.camera = camera;
    this.left = new Hand('left');
    this.right = new Hand('right');
    this.hands = [this.left, this.right];
    this.head = {
      pos: new THREE.Vector3(0, 1.6, 0), prevPos: new THREE.Vector3(0, 1.6, 0),
      fwd: new THREE.Vector3(0, 0, -1), right: new THREE.Vector3(1, 0, 0),
      quat: new THREE.Quaternion(), yaw: 0, vel: new THREE.Vector3(), lookDir: new THREE.Vector3(0, 0, -1),
    };
    this.xr = false;

    // XR controller spaces
    for (let i = 0; i < 2; i++) {
      const grip = renderer.xr.getControllerGrip(i);
      const ray = renderer.xr.getController(i);
      rig.add(grip); rig.add(ray);
      const onConnect = (e) => {
        const src = e.data;
        if (!src || !src.handedness || src.handedness === 'none' || src.hand) return;
        const h = src.handedness === 'left' ? this.left : this.right;
        h.grip = grip; h.ray = ray; h.source = src; h.connected = true; h.first = true;
      };
      const onDisconnect = () => {
        for (const h of this.hands) if (h.grip === grip) { h.connected = false; h.source = null; h.valid = false; }
      };
      grip.addEventListener('connected', onConnect);
      grip.addEventListener('disconnected', onDisconnect);
    }

    // Desktop simulator
    this.keys = new Set();
    this.keyDown = new Set();
    this.mouse = { x: 0, y: 0, down: false, clicked: false, dragging: false, lastX: 0, lastY: 0 };
    this.simYaw = 0; this.simPitch = 0;
    window.addEventListener('keydown', (e) => {
      if (e.repeat) return;
      this.keys.add(e.code); this.keyDown.add(e.code);
    });
    window.addEventListener('keyup', (e) => this.keys.delete(e.code));
    window.addEventListener('blur', () => this.keys.clear());
    if (dom) {
      dom.addEventListener('pointerdown', (e) => {
        this.mouse.down = true; this.mouse.dragging = false;
        this.mouse.lastX = e.clientX; this.mouse.lastY = e.clientY;
      });
      dom.addEventListener('pointermove', (e) => {
        const r = dom.getBoundingClientRect();
        this.mouse.x = ((e.clientX - r.left) / r.width) * 2 - 1;
        this.mouse.y = -((e.clientY - r.top) / r.height) * 2 + 1;
        if (this.mouse.down) {
          const dx = e.clientX - this.mouse.lastX, dy = e.clientY - this.mouse.lastY;
          if (Math.abs(dx) + Math.abs(dy) > 3) this.mouse.dragging = true;
          if (this.mouse.dragging) {
            this.simYaw -= dx * 0.004; this.simPitch = clamp(this.simPitch - dy * 0.004, -1.2, 1.2);
          }
          this.mouse.lastX = e.clientX; this.mouse.lastY = e.clientY;
        }
      });
      window.addEventListener('pointerup', () => {
        if (this.mouse.down && !this.mouse.dragging) this.mouse.clicked = true;
        this.mouse.down = false;
      });
    }
  }

  get presenting() { return this.renderer.xr.isPresenting; }

  update(dt) {
    this.xr = this.presenting;
    const rig = this.rig;
    rig.updateMatrixWorld(true);

    // ---- Head
    const head = this.head;
    head.prevPos.copy(head.pos);
    if (!this.xr) {
      // Desktop: camera driven by sim yaw/pitch at standing height.
      this.camera.position.set(0, settings.seated ? 1.25 : 1.65, 0);
      _e.set(this.simPitch, this.simYaw, 0, 'YXZ');
      this.camera.quaternion.setFromEuler(_e);
      this.camera.updateMatrixWorld(true);
    }
    if (this.xr) this.renderer.xr.updateCamera(this.camera); // current-frame head pose
    const cam = this.xr ? this.renderer.xr.getCamera() : this.camera;
    cam.getWorldPosition(head.pos);
    cam.getWorldQuaternion(head.quat);
    head.lookDir.set(0, 0, -1).applyQuaternion(head.quat);
    head.fwd.set(head.lookDir.x, 0, head.lookDir.z);
    if (head.fwd.lengthSq() < 1e-4) head.fwd.set(0, 0, -1).applyQuaternion(rig.quaternion);
    head.fwd.normalize();
    head.right.set(-head.fwd.z, 0, head.fwd.x);
    head.yaw = Math.atan2(-head.fwd.x, -head.fwd.z);
    if (dt > 0) head.vel.subVectors(head.pos, head.prevPos).divideScalar(dt);

    // ---- Hands
    for (const h of this.hands) {
      h.prevPos.copy(h.pos);
      h.prevLocal.copy(h.local);
      h.prevElbowLocal.copy(h.elbowLocal);
      for (const b of BTN) { h.buttons[b].down = false; h.buttons[b].up = false; }
      h.uiClick = false;

      if (this.xr) this._readXR(h);
      else this._simulate(h, dt);

      if (!h.valid) continue;
      if (h.first) {
        h.prevPos.copy(h.pos); h.prevLocal.copy(h.local); h.first = false;
        this._elbow(h); h.prevElbowLocal.copy(h.elbowLocal);
      }
      // Velocity in rig space (so stick locomotion doesn't count as punch speed),
      // expressed in world orientation.
      if (dt > 0) {
        if (this.xr && h.grip && h.grip.hasLinearVelocity) {
          // runtime-reported controller velocity (reference space = rig space): most accurate
          _v.copy(h.grip.linearVelocity).applyQuaternion(rig.quaternion);
          h.vel.lerp(_v, 0.85);
        } else {
          _v.subVectors(h.local, h.prevLocal).divideScalar(dt);
          _v.applyQuaternion(rig.quaternion);
          // light smoothing to reject tracking jitter, but stay responsive
          h.vel.lerp(_v, 0.7);
        }
        h.speed = h.vel.length();
        h.peakSpeed = Math.max(h.speed, h.peakSpeed - dt * 8);
        this._elbow(h);
        _v2.subVectors(h.elbowLocal, h.prevElbowLocal).divideScalar(dt);
        _v2.applyQuaternion(rig.quaternion);
        h.elbowVel.lerp(_v2, 0.7);
        h.elbowSpeed = h.elbowVel.length();
      }
    }

    this.keyDown.clear();
    this.mouse.clicked = false;
  }

  _readXR(h) {
    if (!h.connected || !h.grip || !h.source) { h.valid = false; return; }
    const grip = h.grip;
    h.valid = grip.visible !== false; // three hides spaces when pose is lost
    grip.getWorldPosition(h.pos);
    grip.getWorldQuaternion(h.quat);
    h.local.copy(grip.position);
    if (h.ray) {
      h.ray.getWorldPosition(h.rayOrigin);
      h.ray.getWorldQuaternion(_q);
      h.rayDir.set(0, 0, -1).applyQuaternion(_q);
    }
    const gp = h.source.gamepad;
    if (gp) {
      for (const b of BTN) {
        const btn = gp.buttons[IDX[b]];
        const st = h.buttons[b];
        const pressed = btn ? (b === 'trigger' || b === 'grip' ? btn.value > 0.65 : btn.pressed) : false;
        st.value = btn ? btn.value : 0;
        st.down = pressed && !st.pressed;
        st.up = !pressed && st.pressed;
        st.pressed = pressed;
      }
      h.uiClick = h.buttons.trigger.down;
      h.axes.x = gp.axes.length > 2 ? gp.axes[2] : 0;
      h.axes.y = gp.axes.length > 3 ? gp.axes[3] : 0;
    }
  }

  _elbow(h) {
    // Shoulder estimate from head pose (yaw only).
    const head = this.head;
    _v.set(h.sign * 0.19, -0.25, 0.04);
    const sx = head.right.x * _v.x - head.fwd.x * _v.z;
    const sz = head.right.z * _v.x - head.fwd.z * _v.z;
    const shoulder = _shoulder.set(head.pos.x + sx, head.pos.y + _v.y, head.pos.z + sz);
    // WebXR grip space: +Y points roughly along the user's forearm (toward the elbow).
    _v2.set(0, 0.27, 0).applyQuaternion(h.quat).add(h.pos);
    _v2.sub(shoulder);
    const len = _v2.length();
    if (len > 0.32) _v2.multiplyScalar(0.32 / len);
    h.elbow.copy(shoulder).add(_v2);
    _m.copy(this.rig.matrixWorld).invert();
    h.elbowLocal.copy(h.elbow).applyMatrix4(_m);
  }

  _simulate(h, dt) {
    // Guard pose in camera space; punches animate forward.
    const k = this.keys, kd = this.keyDown;
    const isL = h.side === 'left';
    const keyMap = isL
      ? { straight: 'KeyF', hook: 'KeyR', uppercut: 'KeyV', elbow: 'KeyT', a: 'Digit3', b: 'Digit4', trigger: 'KeyQ', stick: 'KeyZ' }
      : { straight: 'KeyJ', hook: 'KeyU', uppercut: 'KeyN', elbow: 'KeyY', a: 'Digit1', b: 'Digit2', trigger: 'KeyE', stick: 'KeyM' };
    const sim = h.sim;
    for (const kind of ['straight', 'hook', 'uppercut', 'elbow']) {
      if (kd.has(keyMap[kind]) && sim.t >= 1) { sim.t = 0; sim.kind = kind; sim.dur = kind === 'straight' ? 0.24 : 0.32; }
    }
    sim.t = Math.min(1, sim.t + dt / sim.dur);
    const s = h.sign;
    const guard = isL ? [-0.19, -0.2, -0.42] : [0.17, -0.22, -0.36];
    let p = guard.slice();
    if (sim.t < 1) {
      const ph = sim.t < 0.45 ? sim.t / 0.45 : 1 - (sim.t - 0.45) / 0.55; // out then back
      const e = Math.sin(ph * Math.PI / 2);
      let tgt;
      if (sim.kind === 'straight') tgt = [s * 0.04, -0.08, -0.78];
      else if (sim.kind === 'hook') tgt = [-s * 0.12, -0.08, -0.55];
      else if (sim.kind === 'uppercut') tgt = [s * 0.02, -0.02, -0.55];
      else tgt = [-s * 0.15, -0.05, -0.3];
      if (sim.kind === 'hook') {
        // swing out to the side then across
        p = [lerp3(guard[0], tgt[0], e) + s * 0.28 * Math.sin(e * Math.PI), lerp3(guard[1], tgt[1], e), lerp3(guard[2], tgt[2], e)];
      } else if (sim.kind === 'uppercut') {
        p = [lerp3(guard[0], tgt[0], e), guard[1] - 0.15 * Math.sin(e * Math.PI) + (tgt[1] - guard[1]) * e, lerp3(guard[2], tgt[2], e)];
      } else {
        p = [lerp3(guard[0], tgt[0], e), lerp3(guard[1], tgt[1], e), lerp3(guard[2], tgt[2], e)];
      }
    }
    _v.set(p[0], p[1], p[2]).applyQuaternion(this.head.quat);
    h.pos.copy(this.head.pos).add(_v);
    // grip-space convention: knuckles along -Y, forearm along +Y
    h.quat.copy(this.head.quat).multiply(_qGripSim);
    // local (rig space) position
    _m.copy(this.rig.matrixWorld).invert();
    h.local.copy(h.pos).applyMatrix4(_m);
    h.valid = true; h.connected = true;
    // simulated buttons
    const btnKeys = { a: keyMap.a, b: keyMap.b, trigger: keyMap.trigger, stick: keyMap.stick, grip: null };
    for (const b of BTN) {
      const st = h.buttons[b];
      let pressed = btnKeys[b] ? k.has(btnKeys[b]) : false;
      st.down = pressed && !st.pressed; st.up = !pressed && st.pressed; st.pressed = pressed; st.value = pressed ? 1 : 0;
    }
    h.uiClick = !isL && this.mouse.clicked;
    // WASD -> left stick, arrows -> right stick
    if (isL) { h.axes.x = (k.has('KeyD') ? 1 : 0) - (k.has('KeyA') ? 1 : 0); h.axes.y = (k.has('KeyS') ? 1 : 0) - (k.has('KeyW') ? 1 : 0); }
    else { h.axes.x = (k.has('ArrowRight') ? 1 : 0) - (k.has('ArrowLeft') ? 1 : 0); h.axes.y = 0; }
    // Mouse ray for menus
    _ray.setFromCamera({ x: this.mouse.x, y: this.mouse.y }, this.camera);
    h.rayOrigin.copy(_ray.ray.origin); h.rayDir.copy(_ray.ray.direction);
  }

  // ---- Haptics ---------------------------------------------------------------
  pulse(side, intensity, ms) {
    const scale = settings.haptics;
    if (scale <= 0) return;
    const hands = side === 'both' ? this.hands : [side === 'left' ? this.left : this.right];
    for (const h of hands) {
      const gp = h.source && h.source.gamepad;
      if (!gp) continue;
      const v = clamp(intensity * scale, 0, 1);
      try {
        const act = gp.hapticActuators && gp.hapticActuators[0];
        if (act && act.pulse) { act.pulse(v, ms); continue; }
        if (gp.vibrationActuator && gp.vibrationActuator.playEffect) {
          gp.vibrationActuator.playEffect('dual-rumble', { duration: ms, strongMagnitude: v, weakMagnitude: v * 0.6 });
        }
      } catch (e) { /* haptics unsupported */ }
    }
  }

  /** steps: [[intensity, ms, gapMs], ...] */
  pattern(side, steps) {
    let t = 0;
    for (const [i, ms, gap = 30] of steps) {
      setTimeout(() => this.pulse(side, i, ms), t);
      t += ms + gap;
    }
  }

  handBySide(side) { return side === 'left' ? this.left : this.right; }
}

const _shoulder = new THREE.Vector3();
const _ray = new THREE.Raycaster();
const _qGripSim = new THREE.Quaternion().setFromAxisAngle(new THREE.Vector3(1, 0, 0), Math.PI / 2);
const lerp3 = (a, b, t) => a + (b - a) * t;
