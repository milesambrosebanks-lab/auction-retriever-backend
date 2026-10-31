// Training mode: mitt work, Thai pads, heavy bag, reflex targets, defense drill.

import * as THREE from 'three';
import { DIFFICULTIES, DRILLS } from './config.js';
import { settings } from './settings.js';
import { Fighter } from './fighter.js';
import { OpponentAI } from './fight.js';
import { Panel, COLORS } from './ui.js';
import { clamp, rand, randInt, chance, damp, distPointSegment, GeoBatch, lerp, easeOut } from './util.js';
import { sfxPad, sfxBag, sfxBell, sfxSuccess, say, sfxUi, sfxWhoosh, sfxHurt, sfxBlock } from './audio.js';

const _v = new THREE.Vector3(), _v2 = new THREE.Vector3(), _v3 = new THREE.Vector3(), _q = new THREE.Quaternion();
const UP = new THREE.Vector3(0, 1, 0);

const COACH = {
  id: 'coach', name: 'Coach Ray', height: 1.78, build: 1.05, skin: 0x8d5524, hair: 0x2b2b2b,
  shorts: 0x2d2d2d, trim: 0xffc83d, gloves: 0x2d2d2d, speed: 0.9, power: 0, defense: 1, aggression: 1, hp: 999, footwork: 0.8,
  style: { jab: 3, cross: 2, hookL: 2, hookR: 2, bodyJab: 1, bodyHook: 1, upper: 1 },
  combos: [['jab'], ['jab', 'cross'], ['hookL'], ['cross', 'hookL'], ['hookR'], ['jab', 'hookR']],
};

// Strike vocabulary for callouts. x: + = your rear-hand side... resolved per stance below.
// pos: [side(+ = lead side), up (rel. to head), forward], face: [side, up, toward-you]
const MITT = {
  '1':  { name: 'Jab', hand: 'lead', want: ['jab'], pos: [0.1, -0.03, 0.5], face: [0, 0, 1] },
  '2':  { name: 'Cross', hand: 'rear', want: ['cross'], pos: [-0.04, -0.03, 0.5], face: [0, 0, 1] },
  '3':  { name: 'Hook', hand: 'lead', want: ['hook'], pos: [0.02, -0.03, 0.38], face: [1, 0, 0.35] },
  '4':  { name: 'Rear hook', hand: 'rear', want: ['hook'], pos: [-0.02, -0.03, 0.38], face: [-1, 0, 0.35] },
  '5':  { name: 'Lead upper', hand: 'lead', want: ['uppercut'], pos: [0.06, -0.24, 0.38], face: [0, -1, 0.4] },
  '6':  { name: 'Rear upper', hand: 'rear', want: ['uppercut'], pos: [-0.06, -0.24, 0.38], face: [0, -1, 0.4] },
  '3b': { name: 'Body hook', hand: 'lead', want: ['hook'], pos: [0.04, -0.5, 0.36], face: [1, 0, 0.35] },
  '2b': { name: 'Body cross', hand: 'rear', want: ['cross'], pos: [-0.02, -0.48, 0.45], face: [0, 0, 1] },
  'el': { name: 'Elbow', hand: 'rear', want: ['elbow'], pos: [-0.02, -0.02, 0.3], face: [-1, 0, 0.5], tool: 'elbow' },
  'slip': { name: 'Slip!', slip: true },
};
const MITT_COMBOS = {
  easy: [['1'], ['1', '2'], ['1', '1'], ['2'], ['1', '2', '3'], ['3'], ['2', '3']],
  medium: [['1', '2'], ['1', '2', '3'], ['1', '2', '3', '2'], ['1', '6', '3', '2'], ['3b', '3'], ['1', '2', 'slip', '2'], ['2', '3', '2'], ['1', '1', '2', 'slip'], ['5', '3', '2']],
  hard: [['1', '2', '3', '2'], ['1', '2', 'slip', '2', '3'], ['1', '6', '3', '2', 'slip'], ['3b', '3', '2', '3'], ['1', '2', '5', '4', '3'], ['slip', '2', '3', '2'], ['1', '2', 'el'], ['2b', '3', '2', 'slip', '2']],
};

const THAI = {
  '1':  { name: 'Jab', hand: 'lead', want: ['jab'], pos: [0.12, -0.03, 0.52], face: [0, 0, 1] },
  '2':  { name: 'Cross', hand: 'rear', want: ['cross'], pos: [-0.04, -0.03, 0.52], face: [0, 0, 1] },
  'el': { name: 'Elbow', hand: 'rear', want: ['elbow'], pos: [-0.02, -0.02, 0.33], face: [-1, 0, 0.6], tool: 'elbow' },
  'rk': { name: 'Right kick', kick: ['roundhouse'], leg: 'right', pos: [0.35, -0.55, 0.75], abs: 'right', face: [1, 0, 0.3], btn: 'A' },
  'rh': { name: 'Right high kick', kick: ['highkick'], leg: 'right', pos: [0.3, -0.1, 0.75], abs: 'right', face: [1, 0, 0.3], btn: 'B' },
  'tp': { name: 'Teep', kick: ['teep'], leg: 'left', pos: [0, -0.62, 0.7], face: [0, 0, 1], btn: 'X' },
  'lk': { name: 'Low kick', kick: ['lowkick'], leg: 'left', pos: [0.3, -1.05, 0.7], abs: 'left', face: [1, 0, 0.3], btn: 'Y' },
  'kn': { name: 'Knee', kick: ['knee'], leg: 'any', pos: [0, -0.65, 0.45], face: [0, -0.6, 1], btn: 'TRIGGER' },
};
const THAI_COMBOS = {
  easy: [['rk'], ['tp'], ['1', '2'], ['kn'], ['lk'], ['1', 'rk']],
  medium: [['1', '2', 'rk'], ['tp', 'rk'], ['kn', 'kn'], ['1', 'el'], ['rh'], ['lk', 'rk'], ['2', 'kn']],
  hard: [['1', '2', 'rh'], ['tp', '1', '2', 'rk'], ['kn', 'el', 'kn'], ['lk', '2', 'rk'], ['1', '2', 'el', 'kn'], ['rh', 'tp']],
};

// ---------------------------------------------------------------------------

class DrillBoard {
  constructor(scene) {
    this.panel = new Panel(0.9, 0.32, { transparent: true });
    this.panel.interactive = false;
    scene.add(this.panel.mesh);
    this.s = { title: '', time: 0, score: 0, streak: 0, call: '', sub: '', extra: '' };
    this.key = '';
    this.panel.setBuilder((ctx, ui) => {
      const s = this.s;
      ui.rect(0, 0, ui.w, ui.h, { fill: 'rgba(8,10,14,0.85)', radius: 26 });
      ui.text(s.title.toUpperCase(), 28, 50, { size: 28, weight: 900, color: COLORS.gold });
      const t = Math.max(0, Math.ceil(s.time));
      ui.text(`${Math.floor(t / 60)}:${String(t % 60).padStart(2, '0')}`, ui.w - 28, 52, { size: 40, weight: 900, align: 'right' });
      ui.text(s.call, ui.w / 2, 168, { size: s.call.length > 14 ? 58 : 76, weight: 900, align: 'center', italic: true, maxW: ui.w - 40 });
      ui.text(s.sub, ui.w / 2, 222, { size: 28, weight: 600, align: 'center', color: COLORS.dim, maxW: ui.w - 40 });
      ui.text(`SCORE ${s.score}`, 28, 296, { size: 30, weight: 800 });
      ui.text(s.extra || (s.streak > 1 ? `STREAK x${s.streak}` : ''), ui.w - 28, 296, { size: 30, weight: 800, align: 'right', color: COLORS.good });
    });
  }
  set(v) {
    Object.assign(this.s, v);
    const k = JSON.stringify([this.s.title, Math.ceil(this.s.time), this.s.score, this.s.streak, this.s.call, this.s.sub, this.s.extra]);
    if (k !== this.key) { this.key = k; this.panel.invalidate(); }
  }
  place(pos, head, dt) {
    if (dt === undefined) this.panel.mesh.position.copy(pos);
    else this.panel.mesh.position.lerp(pos, damp(5, dt));
    this.panel.mesh.lookAt(head.x, this.panel.mesh.position.y, head.z);
    this.panel.render();
  }
  dispose() { this.panel.mesh.parent && this.panel.mesh.parent.remove(this.panel.mesh); }
}

// ---------------------------------------------------------------------------

export class TrainingMode {
  constructor(game, opts) {
    this.game = game;
    this.opts = opts;
    this.drill = opts.drill;
    this.diff = DIFFICULTIES[opts.difficulty] || DIFFICULTIES.medium;
    this.duration = this.drill === 'bag' ? 120 : this.drill === 'reflex' || this.drill === 'defense' ? 60 : 90;
    this.objects = [];
  }

  enter() {
    const g = this.game;
    g.setArena(this.opts.arena);
    g.player.reset();
    g.player.stats = g.player.freshStats();
    g.player.strikesEnabled = true;
    g.player.kicksEnabled = true;
    g.player.onKickActive = (k) => this.onKick(k);
    g.placePlayer(0, 0.9, 0);
    this.time = this.duration;
    this.score = 0; this.streak = 0; this.bestStreak = 0;
    this.hits = 0; this.misses = 0; this.perfect = 0;
    this.reaction = []; this.state = 'countdown'; this.stateT = 0;
    this.board = new DrillBoard(g.scene);
    this.objects.push(this.board);
    const name = DRILLS.find((d) => d.id === this.drill)?.name || 'Training';
    this.name = name;
    this.board.set({ title: name, time: this.time, call: 'GET READY', sub: '' });
    if (this.drill === 'mitts' || this.drill === 'thai') this.setupCoach(this.drill === 'mitts' ? 'mitts' : 'pads');
    if (this.drill === 'bag') this.setupBag();
    if (this.drill === 'reflex') this.setupReflex();
    if (this.drill === 'defense') this.setupDefense();
    g.announce(name.toUpperCase(), this.hint(), { dur: 2.6, height: 0.35 });
  }

  hint() {
    return {
      mitts: 'Hit the glowing mitt with the called punch',
      thai: 'Punch the pads; A/B right kicks, X teep, Y low kick, triggers knee',
      bag: 'Free work: punches, elbows, kicks and knees',
      reflex: 'Red = left glove, Blue = right, Gold = either, Green = kick. Duck the bars!',
      defense: 'Slip, duck or block. Counter when he misses!',
    }[this.drill];
  }

  exit() {
    const g = this.game;
    for (const o of this.objects) {
      if (o.dispose) { o.dispose(); continue; }
      if (o.parent) o.parent.remove(o);
      o.traverse((x) => { if (x.geometry) x.geometry.dispose(); });
    }
    this.objects = [];
    if (this.coach) { this.coach.dispose(); this.coach = null; }
    if (this.ai) { this.ai.dispose(); this.ai = null; }
    g.player.onKickActive = null;
    g.fx.lowHp = 0;
  }

  frame() {
    // Drill frame: from the player's head toward the coach / forward.
    const head = this.game.input.head;
    const f = this._fwd || (this._fwd = new THREE.Vector3(0, 0, -1));
    const target = this.coach ? this.coach.root.position : this.ai ? this.ai.pos : null;
    if (target) { f.set(target.x - head.pos.x, 0, target.z - head.pos.z); if (f.lengthSq() < 0.01) f.copy(head.fwd); f.normalize(); }
    else f.copy(head.fwd);
    const r = this._right || (this._right = new THREE.Vector3());
    r.set(-f.z, 0, f.x);
    return { o: head.pos, f, r };
  }
  /** [side(+lead), up, fwd] -> world. leadSign: +1 when lead hand is right. */
  toWorld(arr, out, abs) {
    const { o, f, r } = this.frame();
    const lead = settings.stance === 'southpaw' ? 1 : -1; // lead side in "right" units
    const sx = abs === 'right' ? 1 : abs === 'left' ? -1 : lead;
    return out.copy(o).addScaledVector(r, arr[0] * sx).addScaledVector(UP, arr[1]).addScaledVector(f, arr[2]);
  }
  dirToWorld(arr, out, abs) {
    const { f, r } = this.frame();
    const lead = settings.stance === 'southpaw' ? 1 : -1;
    const sx = abs === 'right' ? 1 : abs === 'left' ? -1 : lead;
    return out.set(0, 0, 0).addScaledVector(r, arr[0] * sx).addScaledVector(UP, arr[1]).addScaledVector(f, -arr[2]).normalize();
  }

  // ---- Coach (mitts / pads) -------------------------------------------------
  setupCoach(equipment) {
    const g = this.game;
    this.coach = new Fighter(COACH, { equipment });
    g.scene.add(this.coach.root);
    this.coach.setWorld(0, -0.15, 0);
    this.coach.update(0.016, 0);
    this.seq = []; this.seqI = 0; this.tokenT = 0; this.active = null; this.comboStart = 0;
    this.pause = 1.2;
    this.slipMove = null;
  }

  nextCombo() {
    const book = this.drill === 'mitts' ? MITT_COMBOS : THAI_COMBOS;
    const list = book[this.diff.id];
    let c;
    do { c = list[randInt(0, list.length - 1)]; } while (list.length > 1 && c === this.lastCombo);
    this.lastCombo = c;
    this.seq = c.slice(); this.seqI = 0;
    const dict = this.drill === 'mitts' ? MITT : THAI;
    const label = this.seq.map((k) => (dict[k].slip ? 'SLIP' : this.drill === 'mitts' && /^[1-6]$/.test(k) ? k : dict[k].name.toUpperCase())).join(' - ');
    const spoken = this.seq.map((k) => dict[k].name).join(', ');
    this.board.set({ call: label, sub: this.seq.map((k) => dict[k].btn ? `${dict[k].name} (${dict[k].btn})` : '').filter(Boolean).join('  ') || spoken });
    say(spoken, 1.25);
    this.comboStart = this.game.time;
    this.comboClean = true;
    this.startToken();
  }

  startToken() {
    const dict = this.drill === 'mitts' ? MITT : THAI;
    const key = this.seq[this.seqI];
    this.active = { key, def: dict[key], t: 0 };
    const window = { easy: 2.6, medium: 1.7, hard: 1.2 }[this.diff.id] + (dict[key].kick ? 0.6 : 0);
    this.active.window = window;
    if (dict[key].slip) this.startCoachSlip();
  }

  startCoachSlip() {
    // Coach swings the mitt at your head: slip, duck or block.
    const sideHand = chance(0.5) ? 'L' : 'R';
    this.slipMove = { side: sideHand, t: 0, windup: { easy: 0.85, medium: 0.6, hard: 0.42 }[this.diff.id], travel: 0.2, target: this.game.input.head.pos.clone(), origin: new THREE.Vector3(), resolved: false };
    this.coach.setHighlight(sideHand, true, 0xff4040);
  }

  updateCoach(dt) {
    const g = this.game, c = this.coach, head = g.input.head;
    // stay ~0.95 m in front of the player, facing them
    const toCoach = _v.subVectors(c.root.position, head.pos); toCoach.y = 0;
    const d = toCoach.length() || 1;
    const want = _v2.copy(head.pos).addScaledVector(toCoach, 0.95 / d); want.y = 0;
    c.root.position.lerp(want, damp(1.5, dt));
    if (g.arena) g.arena.clamp(c.root.position, 0.3);
    c.root.rotation.y = Math.atan2(head.pos.x - c.root.position.x, head.pos.z - c.root.position.z);
    c.lookTarget = head.pos;
    c.setStance(0);
    c.bobAmt = 0.6;
    const chestY = c.dims.hipY + c.dims.torso * 0.75;
    // idle pad positions
    c.hand.L.set(0.17, chestY, 0.28); c.hand.R.set(-0.17, chestY, 0.28);
    c.padFace.L.set(0, 0.6, 1); c.padFace.R.set(0, 0.6, 1);
    c.handRate.L = c.handRate.R = 14;
    c.setHighlight('L', false); c.setHighlight('R', false);

    const a = this.active;
    if (a && !a.def.slip && this.state === 'run') {
      const p = this.toWorld(a.def.pos, _v3, a.def.abs);
      if (a.def.kick && a.def.abs === undefined) { /* centered */ }
      const local = c.toLocal(p, new THREE.Vector3());
      const side = local.x >= 0 ? 'L' : 'R';
      a.side = side;
      c.hand[side].copy(local);
      c.handRate[side] = 22;
      // low targets (low kick / knee / body): coach bends down to hold the pad there
      const low = clamp((1.15 - p.y) * 0.55, 0, 0.32);
      c.crouch += low; c.lean += low * 0.9;
      // face direction (world) -> coach local
      this.dirToWorld(a.def.face, _v2, a.def.abs);
      _q.copy(c.root.quaternion).invert();
      c.padFace[side].copy(_v2).applyQuaternion(_q);
      // pads for body kicks: hold both pads together
      if (a.def.kick && (a.def.kick[0] === 'roundhouse' || a.def.kick[0] === 'teep' || a.def.kick[0] === 'knee')) {
        const other = side === 'L' ? 'R' : 'L';
        c.hand[other].copy(local).addScaledVector(_v.set(0, 1, 0), 0.22);
        c.padFace[other].copy(c.padFace[side]);
        c.setHighlight(other, true);
      }
      c.setHighlight(side, true);
      a.worldPos = p.clone();
    }
    // coach slip swing animation
    const sm = this.slipMove;
    if (sm) {
      sm.t += dt;
      const sd = sm.side, sgn = sd === 'L' ? 1 : -1;
      if (sm.t < sm.windup * 0.7) sm.target.copy(head.pos);
      const tl = c.toLocal(sm.target, new THREE.Vector3());
      const headY = c.standingHeadY();
      const chamber = _v.set(sgn * 0.4, headY - 0.05, 0.1);
      if (sm.t < sm.windup) { c.hand[sd].lerp(chamber, clamp(sm.t / sm.windup * 2, 0, 1)); c.torsoYaw = sgn > 0 ? -0.6 : 0.5; }
      else if (sm.t < sm.windup + sm.travel) {
        const u = (sm.t - sm.windup) / sm.travel;
        c.hand[sd].lerpVectors(chamber, tl.addScaledVector(_v2.set(0, 0, -1), 0.04), easeOut(u)); c.handRate[sd] = 40;
        c.torsoYaw = sgn > 0 ? lerp(-0.6, 0.4, u) : lerp(0.5, -0.5, u);
        if (sm.origin.lengthSq() === 0) { sm.origin.copy(c.glove[sd]); sfxWhoosh(sm.origin, 0.4); }
      } else if (!sm.resolved) {
        sm.resolved = true;
        const res = g.player.evaluateIncoming({ point: c.glove[sd].clone(), origin: sm.origin, zone: 'head' });
        this.scoreDefense(res, c.glove[sd]);
      } else if (sm.t > sm.windup + sm.travel + 0.35) {
        c.setHighlight(sd, false);
        this.slipMove = null;
        this.advance(true);
      }
      c.padFace[sd].set(-sgn, 0, 0.3);
      c.setHighlight(sd, true, 0xff4040);
    }
    c.update(dt, g.time);
  }

  scoreDefense(res, point) {
    const g = this.game;
    if (res === 'miss') { this.addScore(80, 'NICE SLIP!', point, '#4ea8ff'); g.player.stats.dodged++; g.input.pulse('both', 0.15, 15); }
    else if (res === 'block') { this.addScore(40, 'BLOCKED', point, '#ffffff'); sfxBlock(point, 0.4); g.input.pulse('both', 0.35, 35); g.player.stats.blocks++; }
    else { this.streak = 0; this.comboClean = false; this.misses++; g.floating.spawn('TAGGED', point, { color: '#ff5a52', size: 0.1 }); sfxHurt(0.3); g.fx.flash(0.35); g.input.pulse('both', 0.6, 60); }
  }

  addScore(pts, label, pos, color = '#ffffff') {
    this.streak++; this.bestStreak = Math.max(this.bestStreak, this.streak);
    const mult = 1 + Math.min(4, Math.floor(this.streak / 5)) * 0.25;
    this.score += Math.round(pts * mult);
    this.hits++;
    if (label) this.game.floating.spawn(label, _v.copy(pos).setY(pos.y + 0.12), { color, size: 0.1 });
  }

  advance(success) {
    if (!success) { this.comboClean = false; }
    this.seqI++;
    if (this.seqI >= this.seq.length) {
      if (this.comboClean) { this.score += 100; this.game.floating.spawn('COMBO +100', this.toWorld([0, 0.2, 0.7], new THREE.Vector3()), { color: '#ffc83d', size: 0.12 }); sfxSuccess(); }
      this.active = null;
      this.pause = { easy: 1.3, medium: 0.9, hard: 0.6 }[this.diff.id];
    } else this.startToken();
  }

  handlePadHits() {
    const g = this.game, a = this.active;
    if (!a || a.def.slip || a.def.kick || !a.worldPos) return;
    const t = { id: 'pad' + this.seqI + a.key, pos: a.worldPos, r: this.drill === 'thai' ? 0.15 : 0.12, zone: 'pad', minSpeed: 1.3 };
    const hits = g.player.checkStrikes([t]);
    for (const h of hits) {
      const leadSide = g.player.lead;
      const wantSide = a.def.hand === 'lead' ? leadSide : (leadSide === 'left' ? 'right' : 'left');
      const toolOk = a.def.tool ? h.tool === a.def.tool : h.tool === 'punch';
      if (h.side !== wantSide || !toolOk) {
        g.floating.spawn('WRONG HAND', h.point, { color: '#ff5a52', size: 0.09 });
        sfxUi('back');
        continue;
      }
      const perfect = a.def.want.includes(h.type);
      const pts = (perfect ? 100 : 60) + Math.round(h.rawPower * 50);
      if (perfect) this.perfect++;
      this.reaction.push(a.t);
      this.addScore(pts, perfect ? 'PERFECT' : 'GOOD', h.point, perfect ? '#ffc83d' : '#ffffff');
      sfxPad(h.point, h.rawPower);
      g.input.pulse(h.side, 0.5 + h.rawPower * 0.5, 35 + h.rawPower * 40);
      this.coach.impulse(h.dir, 0.25 + h.rawPower * 0.4, 'body');
      this.advance(true);
      return;
    }
  }

  onKick(k) {
    const g = this.game;
    if (this.drill === 'bag') return this.kickBag(k);
    if (this.drill === 'reflex') return this.kickReflex(k);
    if (this.drill !== 'thai' || this.state !== 'run') return;
    const a = this.active;
    if (!a || !a.def.kick) { g.floating.spawn('WAIT FOR THE CALL', g.player.footW, { color: '#9aa7b8', size: 0.08 }); return; }
    const okType = a.def.kick.includes(k.type);
    const okLeg = a.def.leg === 'any' || a.def.leg === k.side;
    if (!okType || !okLeg) { g.floating.spawn('WRONG KICK', g.player.footW, { color: '#ff5a52', size: 0.09 }); sfxUi('back'); return; }
    const pos = a.worldPos || g.player.footW;
    this.reaction.push(a.t);
    this.perfect++;
    this.addScore(120 + Math.round(k.power * 40), k.type === 'knee' ? 'KNEE!' : 'KICK!', pos, '#ffc83d');
    sfxPad(pos, 1);
    g.input.pulse(k.side, 1, 90);
    g.input.pulse(k.side === 'left' ? 'right' : 'left', 0.35, 40);
    this.coach.impulse(g.input.head.fwd, 0.8, 'body');
    this.advance(true);
  }

  updatePads(dt) {
    if (this.state !== 'run') return;
    if (!this.active) {
      this.pause -= dt;
      if (this.pause <= 0) this.nextCombo();
      return;
    }
    const a = this.active;
    a.t += dt;
    if (!a.def.slip) {
      this.handlePadHits();
      if (this.active === a && a.t > a.window) {
        this.misses++; this.streak = 0;
        this.game.floating.spawn('TOO SLOW', a.worldPos || this.toWorld([0, 0, 0.6], new THREE.Vector3()), { color: '#ff5a52', size: 0.09 });
        this.advance(false);
      }
    }
  }

  // ---- Heavy bag ------------------------------------------------------------
  setupBag() {
    const g = this.game;
    const grp = new THREE.Group();
    const pivot = new THREE.Vector3(0, 2.75, 0.9 - 0.74); // ~0.74 m in front of the start spot
    this.bag = {
      pivot, tilt: new THREE.Vector2(), tiltV: new THREE.Vector2(), twist: 0, twistV: 0,
      chain: 0.75, len: 1.15, r: 0.19, top: new THREE.Vector3(), bottom: new THREE.Vector3(), axis: new THREE.Vector3(0, -1, 0),
      group: grp, lastHit: -10, combo: 0, bestCombo: 0, strikes: 0, topSpeed: 0, lastForce: 0, maxForce: 0,
      contacts: {},
    };
    const b = new GeoBatch();
    const L = this.bag.len, R = this.bag.r, C = this.bag.chain;
    b.add(new THREE.CylinderGeometry(R, R, L, 20), 0x6b1010, { pos: [0, -C - L / 2, 0] });
    b.add(new THREE.CylinderGeometry(R * 1.01, R * 1.01, 0.06, 20), 0x111111, { pos: [0, -C - 0.12, 0] });
    b.add(new THREE.CylinderGeometry(R * 1.01, R * 1.01, 0.06, 20), 0x111111, { pos: [0, -C - L + 0.12, 0] });
    b.add(new THREE.CylinderGeometry(R * 1.012, R * 1.012, 0.22, 20, 1, true, -0.5, 1.0), 0xf2f2f2, { pos: [0, -C - L * 0.45, 0] });
    b.add(new THREE.SphereGeometry(R, 16, 6, 0, Math.PI * 2, Math.PI / 2, Math.PI / 2), 0x5a0d0d, { pos: [0, -C - L, 0], scale: [1, 0.25, 1] });
    for (let i = 0; i < 4; i++) {
      const a = i * Math.PI / 2 + Math.PI / 4;
      const x = Math.cos(a) * R * 0.8, z = Math.sin(a) * R * 0.8;
      const dir = new THREE.Vector3(x, -C, z);
      const len = dir.length();
      b.add(new THREE.CylinderGeometry(0.008, 0.008, len, 4), 0x9ca3af, { matrix: new THREE.Matrix4().compose(dir.clone().multiplyScalar(0.5), new THREE.Quaternion().setFromUnitVectors(UP, dir.clone().normalize()), new THREE.Vector3(1, 1, 1)) });
    }
    b.add(new THREE.CylinderGeometry(0.025, 0.025, 0.08, 8), 0x9ca3af, { pos: [0, 0, 0] });
    const mesh = b.build(new THREE.MeshLambertMaterial({ vertexColors: true }));
    mesh.matrixAutoUpdate = true;
    grp.add(mesh);
    this.bag.mesh = mesh;
    grp.position.copy(pivot);
    // free-standing bag stand so it works in every arena
    const st = new GeoBatch();
    st.add(new THREE.BoxGeometry(2.0, 0.08, 0.08), 0x1f2937, { pos: [0, 0.05, 0] });
    for (const x of [-0.95, 0.95]) {
      st.add(new THREE.BoxGeometry(0.07, 2.85, 0.07), 0x1f2937, { pos: [x, -1.38, 0] });
      st.add(new THREE.BoxGeometry(0.1, 0.06, 0.9), 0x111827, { pos: [x, -2.78, 0] });
    }
    const mount = st.build(new THREE.MeshLambertMaterial({ vertexColors: true }));
    mount.matrixAutoUpdate = true;
    mount.position.copy(pivot);
    g.scene.add(grp); g.scene.add(mount);
    const shadow = new THREE.Mesh(new THREE.CircleGeometry(0.35, 24), new THREE.MeshBasicMaterial({ color: 0x000000, transparent: true, opacity: 0.35, depthWrite: false }));
    shadow.rotation.x = -Math.PI / 2; shadow.position.set(pivot.x, 0.005, pivot.z);
    g.scene.add(shadow);
    this.bag.shadow = shadow;
    this.objects.push(grp, mount, shadow);
    this.updateBagGeom();
  }

  updateBagGeom() {
    const B = this.bag;
    // tilt = horizontal displacement direction of the bag per unit length (small angles)
    B.axis.set(B.tilt.x, -1, B.tilt.y).normalize();
    B.top.copy(B.pivot).addScaledVector(B.axis, B.chain);
    B.bottom.copy(B.pivot).addScaledVector(B.axis, B.chain + B.len);
    _q.setFromUnitVectors(_v.set(0, -1, 0), B.axis);
    B.group.quaternion.copy(_q).multiply(new THREE.Quaternion().setFromAxisAngle(UP, B.twist));
    B.shadow.position.set(B.bottom.x, 0.005, B.bottom.z);
  }

  hitBag(point, velIn, power, side, label) {
    const g = this.game, B = this.bag;
    const vel = velIn.clone();
    const horiz = new THREE.Vector3(vel.x, 0, vel.z);
    const lever = clamp((point.y - B.pivot.y) / -(B.chain + B.len), 0.3, 1);
    B.tiltV.x += horiz.x * 0.09 * (0.4 + power) * lever;
    B.tiltV.y += horiz.z * 0.09 * (0.4 + power) * lever;
    // twist from off-center hits
    _v2.subVectors(point, B.top); _v2.y = 0;
    B.twistV += (_v2.x * horiz.z - _v2.z * horiz.x) * 2.0;
    const speed = vel.length();
    const force = Math.round((speed * 38 + power * 120) * (label === 'KICK' || label === 'KNEE' ? 2.4 : label === 'ELBOW' ? 1.4 : 1));
    B.strikes++; this.hits++;
    B.lastForce = force; B.maxForce = Math.max(B.maxForce, force);
    B.topSpeed = Math.max(B.topSpeed, speed);
    if (g.time - B.lastHit < 1.2) B.combo++; else B.combo = 1;
    B.bestCombo = Math.max(B.bestCombo, B.combo);
    B.lastHit = g.time;
    this.score += Math.round(10 + power * 40) * (label === 'KICK' ? 2 : 1);
    sfxBag(point, power);
    g.input.pulse(side, 0.6 + power * 0.4, 50 + power * 50);
    g.floating.spawn(`${force}`, _v3.copy(point).setY(point.y + 0.12), { color: force > 300 ? '#ffc83d' : '#ffffff', size: 0.09 });
  }

  updateBag(dt) {
    const g = this.game, B = this.bag;
    // pendulum dynamics: tilt'' = -w^2 tilt - c tilt'
    const w2 = 9.81 / (B.chain + B.len * 0.6);
    B.tiltV.x += (-w2 * B.tilt.x - 1.2 * B.tiltV.x) * dt;
    B.tiltV.y += (-w2 * B.tilt.y - 1.2 * B.tiltV.y) * dt;
    B.tilt.x = clamp(B.tilt.x + B.tiltV.x * dt, -0.6, 0.6);
    B.tilt.y = clamp(B.tilt.y + B.tiltV.y * dt, -0.6, 0.6);
    B.twistV += (-4 * B.twist - 1.5 * B.twistV) * dt;
    B.twist += B.twistV * dt;
    this.updateBagGeom();
    if (this.state !== 'run') return;
    for (const side of ['left', 'right']) {
      const h = g.input.handBySide(side);
      if (!h.valid) continue;
      for (const [part, pos, prev, vel, spd, rad] of [['g', h.pos, h.prevPos, h.vel, h.speed, 0.075], ['e', h.elbow, null, h.elbowVel, h.elbowSpeed, 0.06]]) {
        const key = side + part;
        const c = B.contacts[key] || (B.contacts[key] = { inside: false, t: -10 });
        const dNow = distPointSegment(pos, B.top, B.bottom, _v2);
        const dPrev = prev ? distPointSegment(prev, B.top, B.bottom) : dNow;
        const d = Math.min(dNow, dPrev);
        const reach = B.r + rad;
        if (!c.inside && d < reach) {
          c.inside = true;
          _v3.subVectors(_v2, pos);
          const approaching = vel.dot(_v3) > 0 || d < B.r * 0.8;
          const handNearHead = h.pos.distanceTo(g.input.head.pos) < 0.36;
          if (approaching && spd > 1.3 && g.time - c.t > 0.18 && (part === 'g' || handNearHead)) {
            c.t = g.time;
            const power = clamp((spd - 1.2) / 5, 0.1, 1) * g.player.powerMult;
            this.hitBag(pos.clone(), vel, power, side, part === 'e' ? 'ELBOW' : 'PUNCH');
            if (part === 'g') {
              g.player.stamina = Math.max(0, g.player.stamina - (2 + power * 2));
              g.player.lastStrike = g.player.time;
            }
          }
        } else if (c.inside && d > reach + 0.04) c.inside = false;
      }
    }
  }

  kickBag(k) {
    const g = this.game, B = this.bag;
    if (this.state !== 'run') return;
    const head = g.input.head;
    const hy = { roundhouse: 1.0, highkick: 1.45, teep: 1.0, lowkick: 0.8, knee: 1.0 }[k.type];
    const axisPt = _v.copy(B.top).lerp(B.bottom, clamp((B.top.y - hy) / (B.top.y - B.bottom.y), 0, 1));
    const dx = axisPt.x - head.pos.x, dz = axisPt.z - head.pos.z;
    const dist = Math.hypot(dx, dz);
    const ang = Math.acos(clamp((dx * head.fwd.x + dz * head.fwd.z) / (dist || 1), -1, 1));
    if (dist > k.def.range + 0.25 || ang > 0.9) { g.floating.spawn('MISS', g.player.footW, { color: '#9aa7b8', size: 0.08 }); return; }
    const dir = _v2.copy(head.fwd);
    if (k.type === 'roundhouse' || k.type === 'highkick' || k.type === 'lowkick') dir.addScaledVector(head.right, k.side === 'right' ? -0.9 : 0.9).normalize();
    const vel = dir.multiplyScalar(6.5 * k.power + 1.5);
    this.hitBag(axisPt.clone(), vel, Math.min(1, k.power + 0.2), k.side, k.type === 'knee' ? 'KNEE' : 'KICK');
    g.input.pulse(k.side, 1, 90);
  }

  // ---- Reflex targets ---------------------------------------------------------
  setupReflex() {
    const g = this.game;
    this.targets = [];
    const geo = new THREE.SphereGeometry(1, 16, 12);
    const ringGeo = new THREE.TorusGeometry(1.35, 0.12, 6, 32);
    for (let i = 0; i < 6; i++) {
      const m = new THREE.Mesh(geo, new THREE.MeshBasicMaterial({ color: 0xffffff }));
      const ring = new THREE.Mesh(ringGeo, new THREE.MeshBasicMaterial({ color: 0xffffff, transparent: true, opacity: 0.8 }));
      m.add(ring); m.visible = false;
      g.scene.add(m); this.objects.push(m);
      this.targets.push({ m, ring, active: false, t: 0, life: 1, kind: 'any', id: i, pos: new THREE.Vector3(), r: 0.08 });
    }
    // duck bar
    const bar = new THREE.Mesh(new THREE.BoxGeometry(2.2, 0.08, 0.08), new THREE.MeshBasicMaterial({ color: 0xff3b30 }));
    bar.visible = false; g.scene.add(bar); this.objects.push(bar);
    this.bar = { m: bar, active: false, t: 0, y: 0, dist: 3 };
    this.spawnT = 1.2;
  }

  spawnTarget() {
    const t = this.targets.find((x) => !x.active);
    if (!t) return;
    const r = Math.random();
    const kind = r < 0.32 ? 'left' : r < 0.64 ? 'right' : r < 0.86 ? 'any' : 'kick';
    t.kind = kind; t.active = true; t.t = 0;
    const speedUp = 1 - (1 - this.time / this.duration) * 0.35;
    t.life = { easy: 2.2, medium: 1.5, hard: 1.05 }[this.diff.id] * speedUp * (kind === 'kick' ? 1.4 : 1);
    if (kind === 'kick') {
      t.side = chance(0.5) ? 'left' : 'right';
      this.toWorld([0.3, -0.85, 0.75], t.pos, t.side);
      t.r = 0.13;
    } else {
      const x = kind === 'left' ? rand(-0.45, 0.1) : kind === 'right' ? rand(-0.1, 0.45) : rand(-0.35, 0.35);
      this.toWorld([x, rand(-0.38, 0.1), rand(0.38, 0.6)], t.pos, 'right');
      t.r = 0.075;
    }
    const col = { left: 0xff3b30, right: 0x3a86ff, any: 0xffc83d, kick: 0x34d399 }[kind];
    t.m.material.color.setHex(col); t.ring.material.color.setHex(col);
    t.m.position.copy(t.pos); t.m.scale.setScalar(0.001); t.m.visible = true;
    t.m.lookAt(this.game.input.head.pos);
  }

  updateReflex(dt) {
    const g = this.game;
    if (this.state !== 'run') { for (const t of this.targets) t.m.visible = t.active; return; }
    this.spawnT -= dt;
    const maxActive = { easy: 2, medium: 3, hard: 4 }[this.diff.id];
    if (this.spawnT <= 0 && this.targets.filter((t) => t.active).length < maxActive) {
      this.spawnTarget();
      this.spawnT = rand(0.35, 0.9) * (this.diff.id === 'easy' ? 1.5 : 1);
    }
    const strikeTargets = [];
    for (const t of this.targets) {
      if (!t.active) continue;
      t.t += dt;
      const u = t.t / t.life;
      t.m.scale.setScalar(t.r * Math.min(1, t.t / 0.12));
      t.ring.scale.setScalar(1 + (1 - u) * 1.2);
      t.ring.material.opacity = 0.3 + u * 0.6;
      if (u >= 1) {
        t.active = false; t.m.visible = false; this.misses++; this.streak = 0;
        g.floating.spawn('MISSED', t.pos, { color: '#ff5a52', size: 0.07 });
        continue;
      }
      if (t.kind !== 'kick') strikeTargets.push({ id: 'rt' + t.id + '_' + Math.floor(t.t === 0 ? 0 : 1), pos: t.pos, r: t.r, zone: 'target', ref: t, minSpeed: 1.2 });
    }
    const hits = g.player.checkStrikes(strikeTargets);
    for (const h of hits) {
      const t = h.target.ref;
      if (!t.active) continue;
      if (t.kind !== 'any' && t.kind !== h.side) { g.floating.spawn('WRONG HAND', t.pos, { color: '#ff5a52', size: 0.07 }); sfxUi('back'); continue; }
      this.popTarget(t, h.side, h.rawPower);
    }
    // duck bar
    const B = this.bar;
    if (!B.active && this.diff.id !== 'easy' ? chance(dt * 0.12) : (!B.active && chance(dt * 0.06))) {
      B.active = true; B.t = 0; B.dist = 3.0; B.y = g.input.head.pos.y - 0.05; B.judged = false;
      say('Duck!', 1.3);
    }
    if (B.active) {
      const speed = { easy: 1.8, medium: 2.4, hard: 3.0 }[this.diff.id];
      B.dist -= speed * dt;
      const { o, f } = this.frame();
      B.m.visible = true;
      B.m.position.copy(o).addScaledVector(f, B.dist); B.m.position.y = B.y;
      B.m.rotation.y = Math.atan2(f.x, f.z);
      if (!B.judged && B.dist <= 0) {
        B.judged = true;
        if (g.input.head.pos.y < B.y - 0.12) { this.addScore(80, 'DUCKED!', _v.copy(o).addScaledVector(f, 0.6), '#4ea8ff'); g.player.stats.dodged++; }
        else { this.streak = 0; this.misses++; g.fx.flash(0.4); sfxHurt(0.3); g.input.pulse('both', 0.6, 60); g.floating.spawn('DUCK LOWER!', _v.copy(o).addScaledVector(f, 0.6), { color: '#ff5a52', size: 0.09 }); }
      }
      if (B.dist < -1.0) { B.active = false; B.m.visible = false; }
    }
  }

  popTarget(t, side, power) {
    const g = this.game;
    t.active = false; t.m.visible = false;
    this.reaction.push(t.t);
    const fast = t.t < t.life * 0.4;
    this.addScore(fast ? 120 : 80, fast ? 'FAST!' : 'HIT', t.pos, fast ? '#ffc83d' : '#ffffff');
    sfxPad(t.pos, power);
    g.input.pulse(side, 0.5 + power * 0.5, 40);
  }

  kickReflex(k) {
    const t = this.targets && this.targets.find((x) => x.active && x.kind === 'kick');
    if (!t) return;
    if (t.side !== k.side) { this.game.floating.spawn('OTHER LEG', t.pos, { color: '#ff5a52', size: 0.07 }); return; }
    this.popTarget(t, k.side, 1);
  }

  // ---- Defense drill -----------------------------------------------------------
  setupDefense() {
    const g = this.game;
    this.ai = new OpponentAI(g, COACH, this.diff);
    this.ai.f.setWorld(0, -0.2, 0);
    g.scene.add(this.ai.f.root);
    this.ai.state = 'idle';
    this.ai.pref = 0.95;
    this.ai.diff = { ...this.diff, blockChance: 0, attackRate: this.diff.attackRate * 1.4 };
    this.ai.onResolve = (res, m, point) => {
      this.scoreDefense(res, point);
      if (res === 'miss') this.counterT = 0.8;
    };
    this.counterT = 0;
  }

  updateDefense(dt) {
    const g = this.game, ai = this.ai;
    ai.update(dt, g.time, this.state === 'run');
    if (this.state !== 'run') return;
    this.counterT = Math.max(0, this.counterT - dt);
    if (this.counterT > 0) {
      const hits = g.player.checkStrikes([{ id: 'cHead', pos: ai.f.headW, r: 0.13, zone: 'head' }]);
      for (const h of hits) {
        this.addScore(60, 'COUNTER!', h.point, '#ffc83d');
        sfxPad(h.point, h.rawPower); g.input.pulse(h.side, 0.7, 50);
        ai.f.impulse(h.dir, 0.6, 'head');
        this.counterT = 0;
      }
    }
  }

  // ---- Main update --------------------------------------------------------------
  update(dt) {
    const g = this.game, head = g.input.head;
    this.stateT += dt;
    if (this.state === 'countdown') {
      const n = 3 - Math.floor(this.stateT);
      if (n !== this._cd && n > 0) { this._cd = n; this.board.set({ call: String(n), sub: this.hint() }); sfxUi('click'); }
      if (this.stateT >= 3) {
        this.state = 'run'; this.stateT = 0; sfxBell(1);
        this.board.set({ call: this.drill === 'bag' ? 'GO!' : this.drill === 'reflex' ? 'GO!' : 'READY', sub: '' });
        if (this.ai) this.ai.toNeutral(1.0);
      }
    } else if (this.state === 'run') {
      this.time -= dt;
      g.player.countThrows();
      if (this.time <= 0) this.finish();
    }

    if (this.coach) { this.updateCoach(dt); this.updatePads(dt); }
    if (this.bag) this.updateBag(dt);
    if (this.targets) this.updateReflex(dt);
    if (this.ai) this.updateDefense(dt);

    // board placement
    let anchor;
    if (this.bag) {
      const { o, f, r } = this.frame();
      anchor = _v.copy(o).addScaledVector(f, 1.1).addScaledVector(r, -0.75); anchor.y = 1.65;
      const B = this.bag;
      const elapsed = Math.max(1, this.duration - this.time);
      this.board.set({
        time: this.time, score: this.score,
        call: B.lastForce ? `${B.lastForce}` : 'HIT THE BAG',
        sub: B.lastForce ? `power  ·  top ${B.maxForce}  ·  ${B.topSpeed.toFixed(1)} m/s` : 'Punches, elbows, A/B/X/Y kicks, triggers knee',
        extra: `${B.strikes} hits · ${Math.round(B.strikes / elapsed * 60)}/min · combo ${B.combo}`, streak: 0,
      });
    } else {
      const target = this.coach ? this.coach.root.position : this.ai ? this.ai.pos : null;
      const { o, f } = this.frame();
      anchor = target ? _v.copy(target).addScaledVector(f, 0.7) : _v.copy(o).addScaledVector(f, 1.6);
      anchor.y = 2.2;
      this.board.set({ time: this.time, score: this.score, streak: this.streak });
    }
    this.board.place(anchor, head.pos, this._placed ? dt : undefined);
    this._placed = true;
  }

  finish() {
    const g = this.game;
    if (this.state === 'over') return;
    this.state = 'over';
    sfxBell(2);
    if (this.ai) this.ai.state = 'idle';
    if (this.coach) this.active = null;
    const total = this.hits + this.misses;
    const acc = total ? this.hits / total : 0;
    const avgReact = this.reaction.length ? this.reaction.reduce((a, b) => a + b, 0) / this.reaction.length : 0;
    let grade;
    if (this.drill === 'bag') {
      const B = this.bag, perMin = B.strikes / (this.duration / 60);
      grade = perMin > 110 ? 'S' : perMin > 85 ? 'A' : perMin > 60 ? 'B' : perMin > 35 ? 'C' : 'D';
    } else grade = acc > 0.93 ? 'S' : acc > 0.82 ? 'A' : acc > 0.68 ? 'B' : acc > 0.5 ? 'C' : 'D';
    g.announce('TIME!', `Grade ${grade}`, { dur: 2.0, color: '#ffc83d' });
    sfxSuccess();
    const B = this.bag;
    const result = {
      drill: this.drill, name: this.name, difficulty: this.diff.name, score: this.score, grade,
      rows: this.drill === 'bag'
        ? [['Strikes', B.strikes], ['Strikes / min', Math.round(B.strikes / (this.duration / 60))], ['Hardest hit', B.maxForce], ['Top hand speed', `${B.topSpeed.toFixed(1)} m/s`], ['Best combo', B.bestCombo]]
        : [['Accuracy', `${Math.round(acc * 100)}%`], ['Hits', this.hits], ['Missed', this.misses], ['Best streak', this.bestStreak], ['Avg reaction', avgReact ? `${avgReact.toFixed(2)} s` : '—'],
          ...(this.drill === 'mitts' || this.drill === 'thai' ? [['Perfect technique', this.perfect]] : []),
          ...(g.player.stats.dodged ? [['Dodged', g.player.stats.dodged]] : [])],
    };
    setTimeout(() => { if (g.mode === this) g.menu.showTrainingResults(result); }, 2200);
  }
}

void DIFFICULTIES; void sfxHurt; void lerp;
