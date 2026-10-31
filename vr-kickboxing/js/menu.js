// In-headset menus: main, fight setup, training setup, settings, how-to,
// pause and results. Laser pointer + trigger to select.

import * as THREE from 'three';
import { OPPONENTS, DIFFICULTIES, ARENAS, DRILLS } from './config.js';
import { settings, setSetting } from './settings.js';
import { Panel, COLORS } from './ui.js';
import { Fighter } from './fighter.js';
import { damp } from './util.js';
import { setVolume, sfxUi } from './audio.js';

const W = 1.5, H = 0.95;

export class Menu {
  constructor(game) {
    this.game = game;
    this.panel = new Panel(W, H);
    this.root = new THREE.Group();
    this.root.add(this.panel.mesh);
    game.scene.add(this.root);
    game.pointers.add(this.panel);
    this.screen = 'main';
    this.visible = false;
    this.root.visible = false;
    this.preview = null;
    this.previewId = null;
    this.result = null;
    this.tresult = null;
  }

  get isOpen() { return this.visible; }

  open(screen = 'main') {
    this.screen = screen;
    this.visible = true;
    this.root.visible = true;
    this.place();
    this.refresh();
  }

  close() {
    this.visible = false;
    this.root.visible = false;
    this.clearPreview();
  }

  place() {
    const head = this.game.input.head;
    const d = 1.45;
    this.root.position.copy(head.pos).addScaledVector(head.fwd, d);
    this.root.position.y = Math.max(1.0, head.pos.y - 0.08);
    this.root.rotation.set(0, Math.atan2(-head.fwd.x, -head.fwd.z), 0);
    // slight tilt back toward the eyes
    this.panel.mesh.rotation.x = -0.06;
  }

  go(screen) { sfxUi('click'); this.screen = screen; this.refresh(); }

  refresh() {
    const b = {
      main: this.drawMain, fight: this.drawFight, training: this.drawTraining, settings: this.drawSettings,
      howto: this.drawHowTo, pause: this.drawPause, results: this.drawResults, tresults: this.drawTrainingResults,
    }[this.screen] || this.drawMain;
    this.panel.setBuilder((ctx, ui) => b.call(this, ctx, ui));
    this.updatePreview();
    // live background preview of the chosen location
    if (this.game.mode === null) {
      if (this.screen === 'fight') this.game.setArena(settings.arena);
      else if (this.screen === 'training') this.game.setArena(settings.trainingArena);
    }
  }

  // ---- shared bits ----------------------------------------------------------
  header(ui, title, sub, back) {
    ui.bg();
    ui.text(title, 60, 92, { size: 60, weight: 900, italic: true });
    if (sub) ui.text(sub, 62, 136, { size: 26, weight: 500, color: COLORS.dim });
    if (back) ui.button('back', ui.w - 230, 42, 180, 72, '← BACK', { size: 28, sound: 'back', onClick: back });
  }

  segmented(ui, idBase, x, y, w, h, options, current, onPick, opts = {}) {
    const n = options.length, gap = 10;
    const bw = (w - gap * (n - 1)) / n;
    options.forEach(([val, label, sub], i) => {
      ui.button(`${idBase}-${val}`, x + i * (bw + gap), y, bw, h, label, {
        selected: current === val, sub, size: opts.size || 28, accent: opts.accent, onClick: () => { onPick(val); this.refresh(); },
      });
    });
  }

  label(ui, text, x, y) { ui.text(text.toUpperCase(), x, y, { size: 22, weight: 800, color: COLORS.dim }); }

  // ---- screens --------------------------------------------------------------
  drawMain(ctx, ui) {
    ui.bg();
    // title block
    const g = ctx.createLinearGradient(0, 0, ui.w, 0);
    g.addColorStop(0, '#ff3b30'); g.addColorStop(1, '#ffc83d');
    ctx.fillStyle = g; ctx.fillRect(60, 70, 10, 120);
    ui.text('IRON SHIN', 92, 150, { size: 104, weight: 900, italic: true });
    ui.text('VR KICKBOXING', 98, 196, { size: 30, weight: 700, color: COLORS.gold });

    ui.button('fight', 60, 250, 640, 150, 'FIGHT', { sub: 'Pick an opponent, arena and difficulty', size: 54, accent: COLORS.accent, align: 'left', onClick: () => this.go('fight') });
    ui.button('train', 60, 420, 640, 150, 'TRAINING', { sub: 'Mitts · Thai pads · Heavy bag · Reflex · Defense', size: 54, align: 'left', onClick: () => this.go('training') });
    ui.button('settings', 60, 590, 310, 110, 'SETTINGS', { size: 32, onClick: () => this.go('settings') });
    ui.button('howto', 390, 590, 310, 110, 'HOW TO PLAY', { size: 32, onClick: () => this.go('howto') });

    // right column: view + quick info
    ui.rect(760, 250, 680, 450, { fill: 'rgba(255,255,255,0.03)', stroke: COLORS.line, radius: 24 });
    this.label(ui, 'View', 800, 300);
    this.segmented(ui, 'view', 800, 320, 600, 110, [['immersive', 'IMMERSIVE', 'Full virtual arena'], ['passthrough', 'PASSTHROUGH', 'Fight in your room']],
      settings.view, (v) => this.game.setView(v), { size: 30 });
    if (!this.game.passthroughSupported && this.game.input.xr) ui.text('Passthrough needs an immersive-ar capable browser (Quest Browser).', 800, 456, { size: 20, color: COLORS.bad });
    this.label(ui, 'Difficulty', 800, 500);
    this.segmented(ui, 'diff', 800, 520, 600, 90, Object.values(DIFFICULTIES).map((d) => [d.id, d.name.toUpperCase()]), settings.difficulty, (v) => setSetting('difficulty', v));
    ui.text(DIFFICULTIES[settings.difficulty].blurb, 800, 650, { size: 24, color: COLORS.dim });

    ui.text('Point a controller at a button and pull the trigger.', 60, 770, { size: 26, color: COLORS.dim });
    ui.text('In a fight: tap the MENU button on your left wrist with your right hand to pause.', 60, 812, { size: 26, color: COLORS.dim });
    if (this.game.input.xr) ui.button('exitvr', ui.w - 300, 750, 240, 80, 'EXIT VR', { size: 26, sound: 'back', onClick: () => this.game.exitXR() });
  }

  drawFight(ctx, ui) {
    this.header(ui, 'FIGHT', 'Choose your opponent', () => this.go('main'));
    // opponent cards
    const cw = 262, ch = 230, gap = 14, x0 = 60, y0 = 170;
    OPPONENTS.forEach((o, i) => {
      const x = x0 + i * (cw + gap), sel = settings.opponent === o.id;
      ui.button('opp-' + o.id, x, y0, cw, ch, '', { selected: sel, accent: '#2a3a52', onClick: () => { setSetting('opponent', o.id); this.refresh(); } });
      ctx.save();
      ctx.fillStyle = '#' + new THREE.Color(o.shorts).getHexString(); ctx.fillRect(x + 18, y0 + 18, 8, 50);
      ctx.restore();
      ui.text(o.name.replace(/ ".*" /, ' '), x + 36, y0 + 46, { size: 26, weight: 900, maxW: cw - 50 });
      ui.text(`"${(o.name.match(/"(.*)"/) || [, o.nick])[1]}"`, x + 36, y0 + 74, { size: 20, weight: 600, color: COLORS.gold, maxW: cw - 50 });
      ui.text(`${o.nick} · ${o.country}`, x + 18, y0 + 106, { size: 19, color: COLORS.dim, maxW: cw - 30 });
      const stats = [['SPD', (o.speed - 0.6) / 0.8], ['PWR', (o.power - 0.6) / 1.0], ['DEF', (o.defense - 0.6) / 1.0], ['HP', (o.hp - 80) / 70]];
      stats.forEach(([k, v], j) => {
        ui.text(k, x + 18, y0 + 142 + j * 22, { size: 16, weight: 800, color: COLORS.dim });
        ui.bar(x + 70, y0 + 131 + j * 22, cw - 92, 10, Math.max(0.08, Math.min(1, v)), sel ? '#ffc83d' : '#4ea8ff');
      });
    });
    const sel = OPPONENTS.find((o) => o.id === settings.opponent) || OPPONENTS[0];
    ui.text(sel.blurb, 60, 446, { size: 26, color: COLORS.text, maxW: 1380 });

    this.label(ui, 'Difficulty', 60, 506);
    this.segmented(ui, 'fd', 60, 520, 660, 80, Object.values(DIFFICULTIES).map((d) => [d.id, d.name.toUpperCase()]), settings.difficulty, (v) => setSetting('difficulty', v));
    this.label(ui, 'Arena', 760, 506);
    this.segmented(ui, 'ar', 760, 520, 680, 80, ARENAS.filter((a) => a.id !== 'gym').map((a) => [a.id, a.name.replace('The ', '').toUpperCase()]), settings.arena, (v) => setSetting('arena', v), { size: 24 });
    this.label(ui, 'Rounds', 60, 646);
    this.segmented(ui, 'rd', 60, 660, 320, 76, [[1, '1'], [3, '3'], [5, '5']], settings.rounds, (v) => setSetting('rounds', v));
    this.label(ui, 'Round length', 400, 646);
    this.segmented(ui, 'rl', 400, 660, 320, 76, [[60, '1:00'], [120, '2:00'], [180, '3:00']], settings.roundSeconds, (v) => setSetting('roundSeconds', v));
    ui.text(DIFFICULTIES[settings.difficulty].blurb, 60, 790, { size: 24, color: COLORS.dim });
    ui.text((ARENAS.find((a) => a.id === settings.arena) || {}).blurb || '', 60, 828, { size: 24, color: COLORS.dim });
    ui.button('start', 940, 660, 500, 176, 'START FIGHT', { selected: true, accent: COLORS.accent, size: 50, sub: `vs ${sel.name}`, onClick: () => this.game.startFight() });
  }

  drawTraining(ctx, ui) {
    this.header(ui, 'TRAINING', 'Sharpen your skills with Coach Ray', () => this.go('main'));
    DRILLS.forEach((d, i) => {
      ui.button('drill-' + d.id, 60, 170 + i * 108, 760, 96, d.name.toUpperCase(), { selected: settings.drill === d.id, sub: d.blurb, align: 'left', size: 32, accent: '#2a3a52', onClick: () => { setSetting('drill', d.id); this.refresh(); } });
    });
    this.label(ui, 'Location', 860, 186);
    const locs = [['gym', 'GYM'], ['octagon', 'OCTAGON'], ['ring', 'RING'], ['stadium', 'STADIUM'], ['street', 'STREET']];
    locs.forEach(([id, l], i) => {
      ui.button('loc-' + id, 860 + (i % 2) * 295, 204 + Math.floor(i / 2) * 84, 285, 72, l, { selected: settings.trainingArena === id, size: 26, onClick: () => { setSetting('trainingArena', id); this.refresh(); } });
    });
    this.label(ui, 'Difficulty', 860, 474);
    this.segmented(ui, 'td', 860, 490, 580, 80, Object.values(DIFFICULTIES).map((d) => [d.id, d.name.toUpperCase()]), settings.difficulty, (v) => setSetting('difficulty', v), { size: 24 });
    ui.button('tstart', 860, 610, 580, 150, 'START DRILL', { selected: true, accent: COLORS.accent, size: 46, sub: (DRILLS.find((d) => d.id === settings.drill) || DRILLS[0]).name, onClick: () => this.game.startTraining() });
    ui.text('Tip: kicks use buttons - A / B right leg, X / Y left leg, triggers for knees.', 60, 735 + 0, { size: 22, color: COLORS.dim });
    ui.text('Elbows: bring your hand to your head and swing the elbow through.', 60, 768, { size: 22, color: COLORS.dim });
  }

  drawSettings(ctx, ui) {
    this.header(ui, 'SETTINGS', 'Saved on this headset', () => this.go(this.backFromSettings || 'main'));
    const rows = [
      ['View', 'view', [['immersive', 'Immersive'], ['passthrough', 'Passthrough']], (v) => this.game.setView(v)],
      ['Haptics', 'haptics', [[0, 'Off'], [0.4, 'Low'], [0.7, 'Medium'], [1, 'High']], (v) => { setSetting('haptics', v); this.game.input.pulse('both', 0.8, 60); }],
      ['Volume', 'volume', [[0, '0'], [0.25, '25'], [0.5, '50'], [0.8, '80'], [1, '100']], (v) => { setSetting('volume', v); setVolume(v); }],
      ['Refresh rate', 'refreshRate', [[72, '72 Hz'], [90, '90 Hz'], [120, '120 Hz']], (v) => { setSetting('refreshRate', v); this.game.applyFrameRate(); }],
      ['Turning', 'turn', [['snap30', 'Snap 30°'], ['snap45', 'Snap 45°'], ['smooth', 'Smooth'], ['off', 'Off']], (v) => setSetting('turn', v)],
      ['Move speed', 'moveSpeed', [[1.0, 'Slow'], [1.6, 'Normal'], [2.4, 'Fast']], (v) => setSetting('moveSpeed', v)],
      ['Comfort vignette', 'vignette', [[true, 'On'], [false, 'Off']], (v) => setSetting('vignette', v)],
      ['Seated mode', 'seated', [[false, 'Standing'], [true, 'Seated']], (v) => { setSetting('seated', v); this.game.applySeated(); }],
      ['Stance', 'stance', [['orthodox', 'Orthodox'], ['southpaw', 'Southpaw']], (v) => setSetting('stance', v)],
      ['Rookie hints', 'hints', [[true, 'On'], [false, 'Off']], (v) => setSetting('hints', v)],
    ];
    const x0 = 60, lw = 330, y0 = 168, rh = 74;
    rows.forEach(([label, key, opts, fn], i) => {
      const col = i < 5 ? 0 : 1;
      const x = x0 + col * 700, y = y0 + (i % 5) * (rh + 14);
      ui.text(label, x, y + 46, { size: 28, weight: 700 });
      this.segmented(ui, 'set-' + key, x + 250, y, 420 - (col ? 0 : 0), rh - 6, opts, settings[key], fn, { size: opts.length > 3 ? 22 : 26 });
      void lw;
    });
    ui.button('fps', 760, 640, 300, 70, settings.showFps ? 'FPS: ON' : 'FPS: OFF', { size: 26, onClick: () => { setSetting('showFps', !settings.showFps); this.refresh(); } });
    ui.button('recenter', 1080, 640, 360, 70, 'RECENTER VIEW', { size: 26, onClick: () => { this.game.recenter(); this.place(); } });
    ui.text('Refresh rate applies instantly where the headset supports it. 120 Hz = smoothest, 72 Hz = coolest/longest battery.', 60, 800, { size: 22, color: COLORS.dim, maxW: 1380 });
    ui.text('Seated mode raises you to standing height. Comfort vignette darkens the edges while you move with the stick.', 60, 836, { size: 22, color: COLORS.dim, maxW: 1380 });
  }

  drawHowTo(ctx, ui) {
    this.header(ui, 'HOW TO PLAY', 'Kickboxing rules · punches, kicks, knees and elbows', () => this.go(this.backFromSettings || 'main'));
    const col = (x, title, lines) => {
      ui.text(title, x, 210, { size: 32, weight: 900, color: COLORS.gold });
      let y = 256;
      for (const l of lines) y = ui.wrap(l, x, y, 420, 34, { size: 24, color: COLORS.text }) + 8;
    };
    col(60, 'STRIKING', [
      'Punch for real — speed = power. Jabs, crosses, hooks and uppercuts are detected automatically.',
      'Elbows: hand near your head, swing the elbow into the target.',
      'Body shots get through a high guard. Mix it up!',
      'Strikes drain stamina. Tired = weaker.',
    ]);
    col(530, 'KICKS & KNEES', [
      'A — right roundhouse (body)', 'B — right high kick (head)', 'X — left teep (push kick)', 'Y — left low kick', 'Triggers — knee (left / right)',
      'Kicks land when the opponent is in range in front of you.',
    ]);
    col(1000, 'DEFENSE & MOVING', [
      'Watch the windup. Slip or duck with your real head, or block with your gloves.',
      'Keep your hands up by your face to cover; elbows tight to cover the body.',
      'Left stick moves, right stick turns (or just step around).',
      'Knocked down? Mash both triggers before the count of 10.',
    ]);
    ui.text('Pause any time: tap the MENU button on your left wrist with your right hand (Esc on desktop).', 60, 850, { size: 24, color: COLORS.dim });
  }

  drawPause(ctx, ui) {
    ui.bg();
    ui.text('PAUSED', 60, 110, { size: 72, weight: 900, italic: true });
    const m = this.game.mode;
    ui.text(m && m.def ? `vs ${m.def.name} · ${m.diff.name}` : m && m.name ? `${m.name} · ${m.diff.name}` : '', 64, 156, { size: 28, color: COLORS.dim });
    ui.button('resume', 60, 210, 620, 120, 'RESUME', { selected: true, accent: COLORS.accent, size: 44, onClick: () => this.game.resume() });
    ui.button('restart', 60, 350, 620, 100, 'RESTART', { size: 34, onClick: () => this.game.restart() });
    ui.button('pview', 60, 470, 620, 100, settings.view === 'immersive' ? 'SWITCH TO PASSTHROUGH' : 'SWITCH TO IMMERSIVE', { size: 32, onClick: () => { this.game.setView(settings.view === 'immersive' ? 'passthrough' : 'immersive'); this.refresh(); } });
    ui.button('psettings', 60, 590, 300, 100, 'SETTINGS', { size: 30, onClick: () => { this.backFromSettings = 'pause'; this.go('settings'); } });
    ui.button('phowto', 380, 590, 300, 100, 'CONTROLS', { size: 30, onClick: () => { this.backFromSettings = 'pause'; this.go('howto'); } });
    ui.button('quit', 60, 720, 620, 100, 'QUIT TO MAIN MENU', { size: 32, sound: 'back', onClick: () => this.game.quitToMenu() });
    ui.rect(740, 210, 700, 610, { fill: 'rgba(255,255,255,0.03)', stroke: COLORS.line, radius: 24 });
    ui.text('CONTROLS', 780, 270, { size: 30, weight: 900, color: COLORS.gold });
    const lines = [['Punch', 'Swing for real'], ['Elbow', 'Hand by head, swing elbow'], ['A / B', 'Right body / head kick'], ['X / Y', 'Left teep / low kick'], ['Triggers', 'Knees'], ['Left stick', 'Move'], ['Right stick', 'Turn'], ['Defend', 'Slip, duck, block']];
    lines.forEach(([k, v], i) => {
      ui.text(k, 780, 330 + i * 58, { size: 28, weight: 800 });
      ui.text(v, 990, 330 + i * 58, { size: 26, color: COLORS.dim });
    });
  }

  showResults(result) {
    this.result = result;
    this.game.paused = true;
    this.open('results');
  }

  showTrainingResults(result) {
    this.tresult = result;
    this.game.paused = true;
    this.open('tresults');
  }

  drawResults(ctx, ui) {
    const r = this.result || {};
    ui.bg();
    const col = r.outcome === 'win' ? COLORS.gold : r.outcome === 'loss' ? COLORS.bad : COLORS.text;
    ui.text(r.outcome === 'win' ? 'VICTORY' : r.outcome === 'loss' ? 'DEFEAT' : 'DRAW', 60, 130, { size: 100, weight: 900, italic: true, color: col });
    ui.text(`${r.method || ''}${r.detail ? '  ·  ' + r.detail : ''}`, 64, 186, { size: 30, weight: 700 });
    ui.text(`vs ${r.opponent} · ${r.difficulty}`, 64, 228, { size: 26, color: COLORS.dim });
    const acc = r.thrown ? Math.round((r.landed / r.thrown) * 100) : 0;
    const stats = [
      ['Strikes landed', `${r.landed}`], ['Accuracy', `${Math.min(100, acc)}%`], ['Damage dealt', `${r.damage}`], ['Kicks landed', r.kicks],
      ['Top hand speed', `${(r.maxSpeed || 0).toFixed(1)} m/s`], ['Shots dodged', `${r.dodged}`], ['Shots blocked', `${r.blocks}`], ['Damage taken', `${r.taken}`],
    ];
    stats.forEach(([k, v], i) => {
      const x = 60 + (i % 4) * 350, y = 280 + Math.floor(i / 4) * 160;
      ui.rect(x, y, 330, 140, { fill: 'rgba(255,255,255,0.04)', stroke: COLORS.line, radius: 20 });
      ui.text(k.toUpperCase(), x + 24, y + 44, { size: 20, weight: 800, color: COLORS.dim });
      ui.text(v, x + 24, y + 108, { size: 52, weight: 900 });
    });
    ui.button('rematch', 60, 640, 440, 130, 'REMATCH', { selected: true, accent: COLORS.accent, size: 44, onClick: () => this.game.restart() });
    ui.button('newfight', 520, 640, 440, 130, 'NEW FIGHT', { size: 38, onClick: () => { this.game.quitToMenu('fight'); } });
    ui.button('rmenu', 980, 640, 460, 130, 'MAIN MENU', { size: 38, sound: 'back', onClick: () => this.game.quitToMenu() });
  }

  drawTrainingResults(ctx, ui) {
    const r = this.tresult || { rows: [] };
    ui.bg();
    ui.text(r.name ? r.name.toUpperCase() : 'DRILL', 60, 110, { size: 64, weight: 900, italic: true });
    ui.text(`${r.difficulty} · Score ${r.score}`, 64, 160, { size: 30, color: COLORS.dim });
    ui.rect(60, 210, 360, 400, { fill: 'rgba(255,255,255,0.04)', stroke: COLORS.line, radius: 24 });
    ui.text('GRADE', 240, 270, { size: 28, weight: 800, align: 'center', color: COLORS.dim });
    ui.text(r.grade || '-', 240, 500, { size: 230, weight: 900, align: 'center', color: r.grade === 'S' || r.grade === 'A' ? COLORS.gold : COLORS.text, italic: true });
    r.rows.forEach(([k, v], i) => {
      const y = 240 + i * 56;
      ui.text(k, 480, y + 30, { size: 30, weight: 600, color: COLORS.dim });
      ui.text(String(v), 1440, y + 30, { size: 34, weight: 900, align: 'right' });
      ctx.fillStyle = COLORS.line; ctx.fillRect(480, y + 46, 960, 2);
    });
    ui.button('again', 60, 680, 440, 130, 'GO AGAIN', { selected: true, accent: COLORS.accent, size: 44, onClick: () => this.game.restart() });
    ui.button('tmenu', 520, 680, 440, 130, 'OTHER DRILL', { size: 38, onClick: () => this.game.quitToMenu('training') });
    ui.button('tmain', 980, 680, 460, 130, 'MAIN MENU', { size: 38, sound: 'back', onClick: () => this.game.quitToMenu() });
  }

  // ---- 3D opponent preview on the fight screen -------------------------------
  updatePreview() {
    if (this.screen !== 'fight' || !this.visible) { this.clearPreview(); return; }
    if (this.previewId === settings.opponent) return;
    this.clearPreview();
    const def = OPPONENTS.find((o) => o.id === settings.opponent) || OPPONENTS[0];
    this.preview = new Fighter(def);
    this.previewId = def.id;
    this.root.add(this.preview.root);
    this.preview.root.position.set(-W / 2 - 0.55, -1.0 + 0.0, -0.25);
  }

  clearPreview() {
    if (this.preview) { this.preview.dispose(); this.preview = null; this.previewId = null; }
  }

  update(dt, t) {
    if (!this.visible) return;
    if (this.preview) {
      // stand on the floor: root is relative to the menu group, so offset by group height
      this.preview.root.position.y = -this.root.position.y;
      this.preview.root.rotation.y = 0.5 + Math.sin(t * 0.6) * 0.35;
      this.preview.setStance(0);
      this.preview.lookTarget = this.game.input.head.pos;
      this.preview.update(dt, t);
    }
    void damp;
  }
}

// ---------------------------------------------------------------------------
// Wrist menu button (left wrist). Tap it with the right hand to pause.

export class WristMenu {
  constructor(game) {
    this.game = game;
    this.panel = new Panel(0.11, 0.07, { pxPerM: 2400 });
    this.panel.interactive = false;
    this.mesh = this.panel.mesh;
    this.mesh.renderOrder = 25;
    game.scene.add(this.mesh);
    this.state = { hp: 1, st: 1, fps: 0, armed: false, showBars: false };
    this.key = '';
    this.cool = 0;
    // grip-space basis for the back of the left wrist (see WebXR grip-space spec)
    this.offset = new THREE.Vector3(-0.048, 0.085, 0.0);
    this.basis = new THREE.Quaternion().setFromRotationMatrix(new THREE.Matrix4().makeBasis(
      new THREE.Vector3(0, -1, 0), new THREE.Vector3(0, 0, 1), new THREE.Vector3(-1, 0, 0)));
    this.panel.setBuilder((ctx, ui) => {
      const s = this.state;
      ui.rect(0, 0, ui.w, ui.h, { fill: s.armed ? 'rgba(255,59,48,0.92)' : 'rgba(10,12,16,0.88)', stroke: s.armed ? '#fff' : 'rgba(255,255,255,0.25)', radius: 26, lw: 6 });
      ui.text('☰ MENU', ui.w / 2, s.showBars || settings.showFps ? 62 : ui.h / 2 + 4, { size: 48, weight: 900, align: 'center', baseline: 'middle' });
      if (s.showBars) {
        ui.bar(18, 98, ui.w - 36, 16, s.hp, s.hp > 0.5 ? '#34d399' : s.hp > 0.25 ? '#ffc83d' : '#ff5a52');
        ui.bar(18, 124, ui.w - 36, 10, s.st, '#4ea8ff');
      }
      if (settings.showFps) ui.text(`${s.fps} FPS`, ui.w / 2, ui.h - 12, { size: 26, weight: 800, align: 'center', color: s.fps >= 85 ? '#34d399' : '#ffc83d' });
    });
  }

  update(dt, { fps, hp, st, showBars, enabled }) {
    const g = this.game, inp = g.input;
    const L = inp.left, R = inp.right;
    this.cool = Math.max(0, this.cool - dt);
    const show = enabled && inp.xr && L.valid;
    this.mesh.visible = show;
    if (!show) return false;
    _o.copy(this.offset).applyQuaternion(L.quat);
    this.mesh.position.copy(L.pos).add(_o);
    this.mesh.quaternion.copy(L.quat).multiply(this.basis);
    // facing the eyes?
    _n.set(0, 0, 1).applyQuaternion(this.mesh.quaternion);
    _d.subVectors(inp.head.pos, this.mesh.position).normalize();
    const facing = _n.dot(_d) > 0.45;
    const near = R.valid && R.pos.distanceTo(this.mesh.position) < 0.075;
    const armed = facing;
    Object.assign(this.state, { hp, st, fps, armed, showBars });
    const key = [Math.round(hp * 40), Math.round(st * 20), fps, armed, showBars, settings.showFps].join('|');
    if (key !== this.key) { this.key = key; this.panel.invalidate(); }
    this.panel.render();
    if (armed && near && R.speed < 2.0 && this.cool <= 0) {
      this.cool = 1.0;
      inp.pulse('right', 0.5, 30); inp.pulse('left', 0.5, 30);
      sfxUi('click');
      return true;
    }
    return false;
  }
}
const _o = new THREE.Vector3(), _n = new THREE.Vector3(), _d = new THREE.Vector3();
