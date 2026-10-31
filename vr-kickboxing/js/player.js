// The player: gloves, strike detection (punches + elbows), button kicks/knees
// with a visible leg, health/stamina, and defense evaluation against incoming shots.

import * as THREE from 'three';
import { PLAYER } from './config.js';
import { settings } from './settings.js';
import { GeoBatch, clamp, distPointSegment, lerp, damp } from './util.js';
import { sfxWhoosh, sfxHurt, sfxBlock } from './audio.js';

const _v = new THREE.Vector3(), _v2 = new THREE.Vector3(), _v3 = new THREE.Vector3(), _q = new THREE.Quaternion();
const UP = new THREE.Vector3(0, 1, 0);

function gloveMesh(color, trim, side) {
  // Built in WebXR grip space: knuckles toward -Y, forearm toward +Y, thumb toward -Z.
  const g = new GeoBatch();
  g.add(new THREE.SphereGeometry(0.06, 14, 10), color, { pos: [0, -0.035, 0.005], scale: [1.0, 1.28, 1.12] });
  g.add(new THREE.SphereGeometry(0.026, 8, 6), color, { pos: [(side === 'left' ? 1 : -1) * 0.01, -0.022, -0.05], scale: [0.85, 1.5, 0.7] });
  g.add(new THREE.CylinderGeometry(0.047, 0.044, 0.075, 12), trim, { pos: [0, 0.055, 0.005] });
  g.add(new THREE.CylinderGeometry(0.048, 0.048, 0.012, 12), 0x111111, { pos: [0, 0.03, 0.005] });
  const m = g.build(new THREE.MeshLambertMaterial({ vertexColors: true }));
  m.matrixAutoUpdate = true;
  return m;
}

function legMesh() {
  const mk = (r, len, color) => {
    const g = new GeoBatch();
    g.add(new THREE.CapsuleGeometry(r, len - 2 * r, 3, 10), color, { pos: [0, -len / 2, 0] });
    const m = g.build(new THREE.MeshLambertMaterial({ vertexColors: true }));
    m.matrixAutoUpdate = true;
    return m;
  };
  return { thigh: mk(0.075, 0.46, 0x1d2a44), shin: mk(0.055, 0.46, 0xc89a72), foot: mk(0.045, 0.2, 0xb88a64) };
}

// Kick keyframes, in player frame: x = toward kicking side, y = up, z = forward.
// Each entry: [thighDir at chamber, thighDir at extension, knee bend chamber, knee bend extended]
const KICK_POSE = {
  roundhouse: { ch: [0.75, -0.05, 0.65], ex: [-0.15, 0.08, 1], bendC: 1.9, bendE: 0.15 },
  highkick:   { ch: [0.8, 0.25, 0.6],   ex: [-0.12, 0.62, 1], bendC: 2.0, bendE: 0.12 },
  teep:       { ch: [0.08, 0.35, 1],    ex: [0.0, 0.05, 1],   bendC: 2.2, bendE: 0.08 },
  lowkick:    { ch: [0.7, -0.45, 0.55], ex: [-0.2, -0.55, 1], bendC: 1.2, bendE: 0.1 },
  knee:       { ch: [0.05, -0.2, 1],    ex: [0.0, 0.25, 1],   bendC: 1.6, bendE: 2.3 },
};

export class Player {
  constructor(scene, input) {
    this.scene = scene;
    this.input = input;
    this.gloves = {
      left: gloveMesh(0xc81e1e, 0xf2f2f2, 'left'),
      right: gloveMesh(0xc81e1e, 0xf2f2f2, 'right'),
    };
    scene.add(this.gloves.left); scene.add(this.gloves.right);
    this.leg = legMesh();
    this.legGroup = new THREE.Group();
    this.legGroup.add(this.leg.thigh, this.leg.shin, this.leg.foot);
    this.legGroup.visible = false;
    scene.add(this.legGroup);

    this.contacts = new Map();  // `${side}:${targetId}` -> { inside, t }
    this.handHitT = { left: -10, right: -10 }; // one registered strike per punch
    this.time = 0;
    this.reset();
    this.stats = this.freshStats();
    this.kick = null;           // active kick
    this.onKickActive = null;   // callback(kick) set by current mode
    this.hipW = new THREE.Vector3();
    this.footW = new THREE.Vector3();
    this.kneeW = new THREE.Vector3();
    this.strikesEnabled = true;
    this.kicksEnabled = true;
  }

  freshStats() { return { thrown: 0, landed: 0, blocked: 0, kicksThrown: 0, kicksLanded: 0, damage: 0, maxSpeed: 0, dodged: 0, blocks: 0, taken: 0 }; }

  reset() {
    this.maxHp = PLAYER.maxHp;
    this.hp = this.maxHp;
    this.stamina = PLAYER.maxStamina;
    this.lastStrike = -10;
    this.knockdowns = 0;
    this.kick = null;
    this.contacts && this.contacts.clear();
    this.legGroup && (this.legGroup.visible = false);
  }

  get lead() { return settings.stance === 'southpaw' ? 'right' : 'left'; }
  get hpFrac() { return this.hp / this.maxHp; }
  get stFrac() { return this.stamina / PLAYER.maxStamina; }
  get powerMult() { return 0.55 + 0.45 * clamp(this.stamina / 40, 0, 1); }

  setGloveColor(hex) {
    for (const s of ['left', 'right']) {
      const g = this.gloves[s].geometry, col = g.attributes.color;
      // recolor the main glove parts (first two shapes) — cheap one-off rebuild
      const c = new THREE.Color(hex);
      for (let i = 0; i < col.count; i++) {
        const r = col.getX(i), gg = col.getY(i), b = col.getZ(i);
        const isTrim = r > 0.8 && gg > 0.8 && b > 0.8, isBand = r < 0.02 && gg < 0.02 && b < 0.02;
        if (!isTrim && !isBand) col.setXYZ(i, c.r, c.g, c.b);
      }
      col.needsUpdate = true;
    }
  }

  /** Per-frame: glove visuals, stamina regen, kick input + animation. */
  update(dt, uiActive) {
    this.time += dt;
    const inp = this.input;
    for (const side of ['left', 'right']) {
      const h = inp.handBySide(side), g = this.gloves[side];
      g.visible = h.valid;
      if (h.valid) { g.position.copy(h.pos); g.quaternion.copy(h.quat); }
      if (h.speed > this.stats.maxSpeed && h.speed < 25) this.stats.maxSpeed = h.speed;
    }
    // stamina regen
    if (this.time - this.lastStrike > 0.5) this.stamina = Math.min(PLAYER.maxStamina, this.stamina + 16 * dt);

    // kicks: A/B right leg, X/Y left leg, triggers = knees
    if (!uiActive && this.kicksEnabled && !this.kick) {
      const L = inp.left.buttons, R = inp.right.buttons;
      if (R.a.down) this.startKick('roundhouse', 'right');
      else if (R.b.down) this.startKick('highkick', 'right');
      else if (L.a.down) this.startKick('teep', 'left');
      else if (L.b.down) this.startKick('lowkick', 'left');
      else if (R.trigger.down) this.startKick('knee', 'right');
      else if (L.trigger.down) this.startKick('knee', 'left');
    }
    this.updateKick(dt);
  }

  startKick(type, side) {
    const def = PLAYER.kicks[type];
    if (this.stamina < def.stamina * 0.5) {
      // too tired: weak kick
    }
    this.kick = { type, side, def, t: 0, phase: 'windup', resolved: false, power: this.powerMult };
    this.stamina = Math.max(0, this.stamina - def.stamina);
    this.lastStrike = this.time;
    this.stats.kicksThrown++;
    sfxWhoosh(this.input.head.pos, 0.6);
    this.input.pulse(side, 0.2, 20);
  }

  updateKick(dt) {
    const k = this.kick;
    if (!k) { this.legGroup.visible = false; return; }
    k.t += dt;
    const d = k.def, total = d.windup + d.active + d.recover;
    let a; // 0 = rest, 1 = chamber, 2 = extended
    if (k.t < d.windup) { a = k.t / d.windup; k.phase = 'windup'; }
    else if (k.t < d.windup + d.active) { a = 1 + (k.t - d.windup) / d.active; k.phase = 'active'; }
    else { a = 2 - Math.min(1, (k.t - d.windup - d.active) / d.recover) * 2; k.phase = 'recover'; }
    if (k.phase === 'active' && !k.resolved) {
      k.resolved = true;
      this._poseLeg(k, 2); // compute full extension foot position for resolution
      this.onKickActive && this.onKickActive(k, this);
    }
    if (k.t >= total) { this.kick = null; this.legGroup.visible = false; return; }
    this._poseLeg(k, Math.max(0, a));
  }

  _poseLeg(k, a) {
    const head = this.input.head;
    const sgn = k.side === 'left' ? -1 : 1;
    const scale = clamp(head.pos.y / 1.65, 0.75, 1.15);
    const hipY = head.pos.y * 0.55;
    // hip slightly toward kicking side and back from head
    this.hipW.copy(head.pos).addScaledVector(head.right, sgn * 0.1).addScaledVector(head.fwd, -0.05);
    this.hipW.y = hipY;
    const P = KICK_POSE[k.type];
    const rest = _v.set(0, -1, 0.05);
    const toDir = (arr, out) => out.set(0, 0, 0).addScaledVector(head.right, arr[0] * sgn).addScaledVector(UP, arr[1]).addScaledVector(head.fwd, arr[2]).normalize();
    const restW = toDir([rest.x, rest.y, rest.z], _v2.clone());
    const chW = toDir(P.ch, _v2.clone());
    const exW = toDir(P.ex, _v3.clone());
    let thighDir, bend;
    if (a <= 1) { thighDir = restW.lerp(chW, a).normalize(); bend = lerp(0.1, P.bendC, a); }
    else { thighDir = chW.lerp(exW, a - 1).normalize(); bend = lerp(P.bendC, P.bendE, a - 1); }
    const thighLen = 0.46 * scale, shinLen = 0.46 * scale;
    this.kneeW.copy(this.hipW).addScaledVector(thighDir, thighLen);
    // shin: rotate thigh direction by bend around the axis (thighDir x down-ish)
    const axis = _v.crossVectors(thighDir, head.fwd);
    if (axis.lengthSq() < 1e-4) axis.copy(head.right);
    axis.normalize();
    _q.setFromAxisAngle(axis, -bend);
    const shinDir = thighDir.clone().applyQuaternion(_q);
    if (k.type === 'knee') shinDir.set(0, -1, 0).addScaledVector(head.fwd, -0.4).normalize();
    this.footW.copy(this.kneeW).addScaledVector(shinDir, shinLen);
    // place meshes (meshes extend along -Y from their origin)
    const place = (m, from, dir, len) => {
      m.position.copy(from);
      m.quaternion.setFromUnitVectors(_down, dir);
      m.scale.set(1, len / 0.46, 1);
    };
    place(this.leg.thigh, this.hipW, thighDir, thighLen);
    place(this.leg.shin, this.kneeW, shinDir, shinLen);
    this.leg.foot.position.copy(this.footW);
    const footDir = k.type === 'teep' ? UP : shinDir;
    this.leg.foot.quaternion.setFromUnitVectors(_down, footDir);
    this.leg.foot.scale.set(1, 1, 1);
    this.legGroup.visible = true;
  }

  /**
   * Detects glove + elbow contacts with targets.
   * targets: [{ id, pos, r, zone, kind }]
   * returns hit events.
   */
  checkStrikes(targets) {
    const out = [];
    if (!this.strikesEnabled) return out;
    const head = this.input.head;
    for (const side of ['left', 'right']) {
      const h = this.input.handBySide(side);
      if (!h.valid) continue;
      for (const t of targets) {
        if (t.enabled === false) continue;
        // ---- glove
        const key = side + ':' + t.id;
        let c = this.contacts.get(key);
        if (!c) { c = { inside: false, t: -10 }; this.contacts.set(key, c); }
        const reach = t.r + PLAYER.gloveRadius;
        const dist = distPointSegment(t.pos, h.prevPos, h.pos);
        const nowDist = h.pos.distanceTo(t.pos);
        if (!c.inside && dist <= reach) {
          _v.subVectors(t.pos, h.prevPos);
          const approaching = h.vel.dot(_v) > 0;
          if (h.speed >= (t.minSpeed ?? PLAYER.punchMinSpeed) && approaching && this.time - c.t > 0.18 && this.time - this.handHitT[side] > 0.22) {
            out.push(this._makeHit(side, 'punch', h.speed, h.vel, h.pos, t, head));
            c.t = this.time;
            this.handHitT[side] = this.time;
          }
          c.inside = true;
        } else if (c.inside && nowDist > reach + 0.04) c.inside = false;

        // ---- elbow (hand close to own head = elbow strike posture)
        if (t.elbows === false) continue;
        const ekey = side + 'E:' + t.id;
        let ce = this.contacts.get(ekey);
        if (!ce) { ce = { inside: false, t: -10 }; this.contacts.set(ekey, ce); }
        const ereach = t.r + PLAYER.elbowRadius;
        const ed = h.elbow.distanceTo(t.pos);
        const handNearHead = h.pos.distanceTo(head.pos) < 0.36;
        if (!ce.inside && ed <= ereach) {
          if (handNearHead && h.elbowSpeed >= 1.5 && this.time - ce.t > 0.25 && this.time - this.handHitT[side] > 0.22) {
            out.push(this._makeHit(side, 'elbow', h.elbowSpeed, h.elbowVel, h.elbow, t, head));
            ce.t = this.time;
            this.handHitT[side] = this.time;
          }
          ce.inside = true;
        } else if (ce.inside && ed > ereach + 0.05) ce.inside = false;
      }
    }
    return out;
  }

  _makeHit(side, tool, speed, vel, point, target, head) {
    const power = clamp((speed - PLAYER.punchMinSpeed * 0.8) / (PLAYER.punchFullSpeed - PLAYER.punchMinSpeed * 0.8), 0.1, 1);
    let type = 'elbow';
    if (tool === 'punch') {
      _v.subVectors(target.pos, head.pos); _v.y = 0;
      if (_v.lengthSq() < 1e-4) _v.copy(head.fwd); _v.normalize();
      const f = vel.dot(_v), u = vel.y;
      const lat = Math.sqrt(Math.max(0, vel.lengthSq() - f * f - u * u));
      if (u > 0.55 * speed && u > Math.abs(f) * 0.9) type = 'uppercut';
      else if (lat > Math.max(f, 0) * 0.9) type = 'hook';
      else type = side === this.lead ? 'jab' : 'cross';
    }
    const dir = vel.clone();
    if (dir.lengthSq() > 1e-6) dir.normalize(); else dir.copy(head.fwd);
    this.stamina = Math.max(0, this.stamina - (tool === 'elbow' ? 4 : 2 + power * 2));
    this.lastStrike = this.time;
    return { side, tool, type, speed, power: power * this.powerMult, rawPower: power, point: point.clone(), dir, target, zone: target.zone };
  }

  /** Stats hook for whiffed punches (fast glove, no contact) — used for accuracy. */
  countThrows() {
    for (const side of ['left', 'right']) {
      const h = this.input.handBySide(side);
      const st = this['_th' + side] || (this['_th' + side] = { armed: true });
      if (h.speed > 2.4 && st.armed) { st.armed = false; this.stats.thrown++; }
      else if (h.speed < 0.9) st.armed = true;
    }
  }

  /**
   * Resolves an incoming strike against the player's real head/hands.
   * strike: { point, origin, zone }  ->  'hit' | 'block' | 'miss'
   */
  evaluateIncoming(strike) {
    const head = this.input.head.pos;
    const L = this.input.left, R = this.input.right;
    if (strike.zone === 'head') {
      const d = strike.point.distanceTo(head);
      if (d > (strike.radius || 0.25)) return 'miss';
      for (const h of [L, R]) {
        if (!h.valid) continue;
        if (h.pos.distanceTo(strike.point) < 0.15) return 'block';
        if (distPointSegment(strike.point, h.elbow, h.pos) < 0.09) return 'block';
        // glove sitting on the line between the incoming fist and the face
        _v.subVectors(head, strike.origin);
        const len = _v.length();
        if (len > 0.05) {
          _v2.subVectors(h.pos, strike.origin);
          const tt = _v2.dot(_v) / (len * len);
          if (tt > 0.35 && tt < 1.05 && distPointSegment(h.pos, strike.origin, head) < 0.1) return 'block';
        }
      }
      return 'hit';
    }
    if (strike.zone === 'body') {
      _v3.copy(head); _v3.y -= 0.43; _v3.addScaledVector(this.input.head.fwd, -0.04);
      if (strike.point.distanceTo(_v3) > 0.34 && Math.hypot(strike.point.x - head.x, strike.point.z - head.z) > 0.32) return 'miss';
      for (const h of [L, R]) {
        if (!h.valid) continue;
        if (distPointSegment(strike.point, h.elbow, h.pos) < 0.13 || h.pos.distanceTo(strike.point) < 0.15) return 'block';
      }
      return 'hit';
    }
    // leg kicks: only distance matters (step back to avoid)
    if (Math.hypot(strike.point.x - head.x, strike.point.z - head.z) > 0.4) return 'miss';
    return 'hit';
  }

  /** Apply damage. Returns true when hp hits zero. */
  takeHit(dmg, zone, fx) {
    const amount = Math.max(0, dmg);
    this.hp = Math.max(0, this.hp - amount);
    this.stats.taken += amount;
    const power = clamp(amount / 18, 0.25, 1);
    sfxHurt(power);
    if (fx) fx.flash(0.35 + power * 0.6);
    this.input.pulse('both', 0.55 + power * 0.45, 60 + power * 90);
    if (power > 0.6) this.input.pattern('both', [[0, 160, 0], [0.7, 70, 0]]);
    if (zone === 'body') this.stamina = Math.max(0, this.stamina - amount * 0.8);
    return this.hp <= 0;
  }

  blocked(point, power = 0.5) {
    sfxBlock(point, power);
    this.input.pulse('both', 0.35 + power * 0.3, 40);
    this.stats.blocks++;
  }

  regen(dt, rate) { this.hp = Math.min(this.maxHp, this.hp + rate * dt); }
}

const _down = new THREE.Vector3(0, -1, 0);
void damp;
