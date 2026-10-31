// Fight mode: opponent AI, rounds, knockdowns, scoring.

import * as THREE from 'three';
import { OPPONENTS, DIFFICULTIES, AI_STRIKES, PLAYER } from './config.js';
import { settings } from './settings.js';
import { Fighter } from './fighter.js';
import { clamp, lerp, rand, randInt, chance, damp, weightedPick, distPointSegment, smoothstep, wrapAngle, canvasTexture } from './util.js';
import { sfxImpact, sfxBlock, sfxWhoosh, sfxBell, sfxGrunt, crowdCheer, say, sfxSuccess, sfxFail } from './audio.js';
import { crowdUniforms } from './arenas.js';

const _v = new THREE.Vector3(), _v2 = new THREE.Vector3(), _v3 = new THREE.Vector3();
const _tl = new THREE.Vector3(), _p = new THREE.Vector3();

function bezier(a, c, b, t, out) {
  const u = 1 - t;
  return out.set(
    u * u * a.x + 2 * u * t * c.x + t * t * b.x,
    u * u * a.y + 2 * u * t * c.y + t * t * b.y,
    u * u * a.z + 2 * u * t * c.z + t * t * b.z
  );
}
const ss = (u) => u * u * (3 - 2 * u);

/** Guard position of a hand (fighter-local), matching Fighter.setStance(0). */
function guardOf(f, sd, out) {
  const s = f.s, headY = f.standingHeadY();
  return sd === 'L' ? out.set(0.11 * s, headY - 0.1, 0.27 * s) : out.set(-0.1 * s, headY - 0.09, 0.16 * s);
}

// Whole-body mechanics per strike: values at end of windup (W) and at impact (T).
// hip/torso = yaw (+ turns the rear side forward), weight (+ onto lead foot), heelL/R (up on the ball),
// fyL/fyR = foot pivot, crouch = level change, lean (+ forward), roll (+ tilts toward fighter's right), push = hips forward.
const BASE = { hip: -0.35, torso: -0.3, weight: 0, lean: 0.08, crouch: 0.035, roll: 0, heelL: 0.25, heelR: 0.25, fyL: 0, fyR: 0, push: 0 };
const MECH = {
  jab:      { W: { hip: -0.38, torso: -0.38, weight: 0.05 },              T: { hip: -0.5, torso: -0.7, weight: 0.35, lean: 0.12, heelR: 0.45 } },
  cross:    { W: { hip: -0.42, torso: -0.42, weight: -0.15, crouch: 0.07 }, T: { hip: 0.3, torso: 0.52, weight: 0.45, lean: 0.16, heelR: 1, fyR: 0.75 } },
  hookL:    { W: { hip: -0.45, torso: -0.72, weight: 0.18, crouch: 0.07 }, T: { hip: 0.25, torso: 0.45, weight: -0.05, heelL: 0.95, fyL: -0.85, heelR: 0.2 } },
  hookR:    { W: { hip: -0.5, torso: -0.58, weight: -0.22, crouch: 0.07 }, T: { hip: 0.45, torso: 0.58, weight: 0.3, heelR: 1, fyR: 0.85 } },
  upper:    { W: { hip: -0.42, torso: -0.22, weight: -0.12, crouch: 0.17, roll: -0.1 }, T: { hip: 0.3, torso: 0.45, weight: 0.3, crouch: 0.0, lean: 0.02, heelR: 1, fyR: 0.6 } },
  bodyJab:  { W: { hip: -0.38, torso: -0.38, crouch: 0.15, roll: 0.06 },   T: { hip: -0.5, torso: -0.68, weight: 0.4, crouch: 0.2, lean: 0.2, roll: 0.08, heelR: 0.5 } },
  bodyHook: { W: { hip: -0.5, torso: -0.55, weight: -0.2, crouch: 0.17, roll: -0.08 }, T: { hip: 0.45, torso: 0.55, weight: 0.25, crouch: 0.2, lean: 0.18, roll: -0.12, heelR: 1, fyR: 0.8 } },
  elbow:    { W: { hip: -0.45, torso: -0.42, weight: 0.2 },              T: { hip: 0.45, torso: 0.68, weight: 0.4, lean: 0.18, heelR: 1, fyR: 0.8 } },
  knee:     { W: { weight: 0.5, lean: 0.05, heelL: 0.4, hip: -0.2 },     T: { weight: 0.6, lean: -0.2, hip: 0.1, heelL: 0.85, push: 0.13 } },
  teep:     { W: { weight: -0.5, lean: -0.14, heelR: 0.1 },              T: { weight: -0.35, lean: -0.26, push: 0.09, heelR: 0.4, hip: -0.15 } },
  lowKick:  { W: { weight: 0.5, hip: 0.1, torso: -0.1, heelL: 0.45, fyL: -0.45, crouch: 0.07 }, T: { weight: 0.55, hip: 0.85, torso: 0.6, lean: -0.1, roll: -0.12, heelL: 0.75, fyL: -0.95, crouch: 0.07 } },
  bodyKick: { W: { weight: 0.55, hip: 0.2, torso: 0.0, lean: -0.08, heelL: 0.6, fyL: -0.6 }, T: { weight: 0.6, hip: 1.15, torso: 0.75, lean: -0.22, roll: -0.2, heelL: 0.95, fyL: -1.3 } },
  headKick: { W: { weight: 0.6, hip: 0.25, torso: 0.0, lean: -0.12, heelL: 0.65, fyL: -0.7, crouch: -0.02 }, T: { weight: 0.65, hip: 1.25, torso: 0.8, lean: -0.36, roll: -0.26, heelL: 1, fyL: -1.4, crouch: -0.06 } },
};

function applyMechanics(f, m, inW, inT, uW, uT) {
  const M = MECH[m.key];
  if (!M || (!inW && !inT)) return; // recovery: stance values ease back via the fighter's smoothing
  const v = {};
  for (const k in BASE) {
    const w = M.W[k] ?? BASE[k], tt = M.T[k] ?? w;
    // hips fire first, shoulders follow, weight arrives with the shot
    const u = k === 'hip' || k === 'fyL' || k === 'fyR' || k === 'heelL' || k === 'heelR' ? Math.pow(uT, 0.6) : k === 'torso' ? Math.pow(uT, 1.15) : uT;
    v[k] = inW ? BASE[k] + (w - BASE[k]) * ss(uW) : w + (tt - w) * ss(u);
  }
  f.hipYaw = v.hip; f.torsoYaw = v.torso; f.weight = v.weight; f.lean = v.lean; f.crouch = v.crouch; f.roll = v.roll;
  f.heel.L = v.heelL; f.heel.R = v.heelR; f.footYaw.L = v.fyL; f.footYaw.R = v.fyR;
  f.hipShift.z = v.push;
}

let glowTex = null;
function telegraphSprite() {
  if (!glowTex) glowTex = canvasTexture(64, 64, (ctx, w, h) => {
    const g = ctx.createRadialGradient(w / 2, h / 2, 0, w / 2, h / 2, w / 2);
    g.addColorStop(0, 'rgba(255,80,60,1)'); g.addColorStop(0.4, 'rgba(255,40,30,0.5)'); g.addColorStop(1, 'rgba(255,0,0,0)');
    ctx.fillStyle = g; ctx.fillRect(0, 0, w, h);
  }, { srgb: false });
  const s = new THREE.Sprite(new THREE.SpriteMaterial({ map: glowTex, blending: THREE.AdditiveBlending, depthWrite: false, transparent: true, fog: false }));
  s.scale.setScalar(0.3); s.visible = false; s.renderOrder = 35;
  return s;
}

// ---------------------------------------------------------------------------

export class OpponentAI {
  constructor(game, def, diff) {
    this.game = game;
    this.def = def;
    this.diff = diff;
    this.f = new Fighter(def, { equipment: 'gloves' });
    this.maxHp = Math.round(def.hp * diff.hpScale);
    this.hp = this.maxHp;
    this.stun = 0;
    this.knockdowns = 0;
    this.state = 'idle';
    this.stateT = 0;
    this.cooldown = rand(1.0, 2.0);
    this.queue = [];
    this.move = null;
    this.guard = 0; this.guardT = 0;
    this.slip = new THREE.Vector3(); this.slipT = 0;
    this.strafe = 1; this.strafeT = 2;
    this.pref = { rico: 0.95, nok: 0.9, viktor: 0.92, kai: 1.2, dex: 1.02 }[def.id] || 1.0;
    this.stepPhase = 0;
    this.velLocal = new THREE.Vector3();
    this.watch = { left: true, right: true };
    this.counterReady = false;
    this.glow = telegraphSprite();
    game.scene.add(this.glow);
    this.vel = new THREE.Vector3();
    this.knock = new THREE.Vector3();
    this.celebrate = false;
    this.lastHitT = -10;
    this.stats = { thrown: 0, landed: 0, blockedByYou: 0, dodgedByYou: 0, damage: 0 };
  }

  get pos() { return this.f.root.position; }

  dispose() {
    this.f.dispose();
    this.game.scene.remove(this.glow);
  }

  /** Point on the player the AI aims at, for a target zone. */
  zonePoint(zone, out) {
    const head = this.game.input.head;
    out.copy(head.pos);
    if (zone === 'body') { out.y -= 0.42; out.addScaledVector(head.fwd, -0.03); }
    else if (zone === 'leg') { out.y = clamp(head.pos.y * 0.3, 0.3, 0.6); out.addScaledVector(head.right, settings.stance === 'southpaw' ? 0.12 : -0.12); }
    return out;
  }

  distToPlayer() {
    const h = this.game.input.head.pos;
    return Math.hypot(h.x - this.pos.x, h.z - this.pos.z);
  }

  // ---- Decision making ----------------------------------------------------
  pickCombo() {
    const d = this.def, diff = this.diff;
    const dist = this.distToPlayer();
    let combo;
    if (chance(0.55)) combo = d.combos[randInt(0, d.combos.length - 1)].slice();
    else {
      const n = randInt(1, diff.maxCombo);
      combo = [];
      for (let i = 0; i < n; i++) combo.push(weightedPick(d.style));
    }
    combo = combo.slice(0, diff.maxCombo + (chance(0.3) ? 1 : 0));
    // if far, start with something that has reach
    if (dist > 1.3 && !combo.some((k) => (AI_STRIKES[k].range || 1) > 1.15)) {
      if (d.style.teep || d.style.bodyKick) combo.unshift(chance(0.5) && d.style.teep ? 'teep' : 'bodyKick');
    }
    return combo;
  }

  startMove(key) {
    const def = AI_STRIKES[key];
    const sp = this.def.speed;
    const wm = this.diff.windup;
    const isLeg = !!def.leg;
    const side = isLeg ? (def.leg === 'lead' ? 'L' : 'R') : (def.hand === 'lead' ? 'L' : 'R');
    const m = {
      key, def, side, sign: side === 'L' ? 1 : -1, isLeg, t: 0,
      durW: (def.windup * wm) / sp, durT: (def.travel * Math.sqrt(wm)) / sp, durR: def.recover / sp,
      targetW: this.zonePoint(def.target, new THREE.Vector3()),
      chamber: new THREE.Vector3(), start: new THREE.Vector3(), originW: new THREE.Vector3(),
      resolved: false, lunge: 0,
    };
    const range = def.range || 0.98;
    const dist = this.distToPlayer();
    m.lunge = clamp(dist - range * 0.85, 0, 0.6);
    this.move = m;
    this.stats.thrown++;
    this.state = 'strike';
  }

  // ---- Per-frame -----------------------------------------------------------
  update(dt, t, fightActive) {
    const f = this.f, g = this.game, head = g.input.head;
    this.stateT += dt;
    this.stun = Math.max(0, this.stun - dt * 0.12);

    // Face the player
    const dx = head.pos.x - this.pos.x, dz = head.pos.z - this.pos.z;
    const dist = Math.hypot(dx, dz);
    if (this.state !== 'down' && this.state !== 'ko') {
      const want = Math.atan2(dx, dz);
      const cur = f.root.rotation.y;
      f.root.rotation.y = cur + wrapAngle(want - cur) * damp(this.state === 'strike' ? 4 : 7, dt);
    }
    f.lookTarget = head.pos;

    // Base pose
    f.setStance(this.guard > 0.5 ? 1 : 0);
    f.bobAmt = 1;
    let moveSpeed = 0;
    _v.set(0, 0, 0);

    switch (this.state) {
      case 'idle': {
        f.bobAmt = 0.6;
        break;
      }
      case 'neutral': {
        if (!fightActive) break;
        // footwork: maintain preferred range, circle
        this.strafeT -= dt;
        if (this.strafeT <= 0) { this.strafe = chance(0.2) ? 0 : (chance(0.5) ? 1 : -1); this.strafeT = rand(1.2, 3.2); }
        const pref = this.pref + (this.game.player.hpFrac < 0.3 ? -0.1 : 0);
        let radial = clamp((dist - pref) * 2.5, -1, 1) * 0.85 * this.def.footwork;
        if (radial < 0) radial *= 0.45; // hold ground rather than run away
        const nx = dx / (dist || 1), nz = dz / (dist || 1);
        _v.set(nx * radial + -nz * this.strafe * 0.32 * this.def.footwork, 0, nz * radial + nx * this.strafe * 0.32 * this.def.footwork);
        moveSpeed = _v.length();
        // defensive reactions
        this.watchPunches(dt, dist);
        // attack timer
        this.cooldown -= dt;
        if (this.cooldown <= 0 && dist < 2.0) {
          this.queue = this.pickCombo();
          this.startMove(this.queue.shift());
        }
        break;
      }
      case 'strike': this.updateStrike(dt, dist); break;
      case 'hit': {
        f.bobAmt = 0.2;
        f.weight = -0.55; f.heel.L = 0.6; f.heel.R = 0; f.lean = 0.02; f.crouch = 0.08;
        if (this.stateT > this.hitDur) {
          this.toNeutral(rand(0.3, 0.8));
          // shell up after eating shots
          if (chance(this.diff.blockChance * this.def.defense * 1.2)) { this.guard = 1; this.guardT = rand(0.6, 1.2); }
        }
        break;
      }
      case 'stunned': {
        f.bobAmt = 0.1; f.crouch = 0.12;
        f.hand.L.y -= 0.18; f.hand.R.y -= 0.2; f.hand.L.z -= 0.05;
        f.roll = Math.sin(t * 2.4) * 0.12; f.lean = 0.12 + Math.sin(t * 1.7) * 0.06;
        f.weight = Math.sin(t * 2.4) * 0.5; f.heel.L = f.heel.R = 0; f.crouch = 0.14 + Math.sin(t * 3.1) * 0.03;
        // drift backward
        const nx = dx / (dist || 1), nz = dz / (dist || 1);
        if (dist < 1.4) _v.set(-nx * 0.25, 0, -nz * 0.25);
        moveSpeed = _v.length();
        if (this.stateT > 1.6) this.toNeutral(rand(0.6, 1.2));
        break;
      }
      case 'down': {
        f.downTarget = 1; f.bobAmt = 0;
        break;
      }
      case 'ko': { f.downTarget = 1; f.bobAmt = 0; break; }
      case 'rising': {
        f.downTarget = 0; f.bobAmt = 0.2;
        if (this.stateT > 1.3) { f.downAmt = 0; this.toNeutral(1.4); }
        break;
      }
      case 'corner': {
        // walk to own corner, then wait
        _v2.copy(this.corner || this.game.fight?.oppCorner || this.pos).sub(this.pos); _v2.y = 0;
        const d = _v2.length();
        if (d > 0.15) { _v.copy(_v2).normalize().multiplyScalar(Math.min(0.9, d * 1.5)); moveSpeed = _v.length(); }
        f.bobAmt = 0.3; f.hand.L.y -= 0.25; f.hand.R.y -= 0.25;
        break;
      }
      case 'celebrate': {
        f.bobAmt = 1.5;
        const hy = f.standingHeadY() + 0.35;
        f.hand.L.set(0.25, hy + Math.sin(t * 6) * 0.04, 0.05); f.hand.R.set(-0.25, hy + Math.cos(t * 6) * 0.04, 0.05);
        f.armPole.L.set(1, 0, -0.3); f.armPole.R.set(-1, 0, -0.3);
        f.lean = -0.1;
        break;
      }
      default: break;
    }

    // guard decay / slips
    this.guardT -= dt; if (this.guardT <= 0) this.guard = 0;
    if (this.slipT > 0) {
      this.slipT -= dt;
      const k = Math.sin(clamp(1 - this.slipT / 0.45, 0, 1) * Math.PI);
      f.hipShift.set(this.slip.x * k, -0.09 * k, this.slip.z * k);
      f.roll = -this.slip.x * 2.2 * k;
      f.lean += 0.15 * k;
      f.crouch += 0.06 * k;
    }

    // Movement integration (world)
    if (moveSpeed > 0 || this.move?.lunge) {
      this.vel.lerp(_v, damp(8, dt));
    } else this.vel.multiplyScalar(Math.max(0, 1 - dt * 10));
    this.pos.addScaledVector(this.vel, dt);
    // keep space from the player and inside the arena
    const dx2 = this.pos.x - head.pos.x, dz2 = this.pos.z - head.pos.z;
    const d2 = Math.hypot(dx2, dz2);
    const close = this.move && (this.move.def.kind === 'elbow' || this.move.def.kind === 'knee');
    const minD = close ? 0.42 : 0.55;
    if (d2 < minD && d2 > 1e-4) { this.pos.x = head.pos.x + dx2 / d2 * minD; this.pos.z = head.pos.z + dz2 / d2 * minD; }
    if (this.game.arena && this.game.arena.clamp(this.pos, 0.35)) this.strafe = -this.strafe || 1;

    // knockback from big shots: the body is pushed, planted feet then stumble-step to catch it
    if (this.knock.lengthSq() > 1e-5) {
      this.pos.addScaledVector(this.knock, dt);
      this.knock.multiplyScalar(Math.exp(-5 * dt));
    }
    // footwork: move in the direction of travel, lean slightly into it
    const sp = this.vel.length();
    if (sp > 0.05 && this.state === 'neutral') {
      _v2.copy(this.vel).applyQuaternion(_qInv.copy(f.root.quaternion).invert());
      f.weight += clamp(_v2.z * 0.35, -0.3, 0.3);
      f.bobAmt += Math.min(1, sp / 0.5) * 0.3;
    }
    if (this.state === 'neutral' || this.state === 'idle') {
      // rhythm: light weight rocking, bouncing on the balls of the feet
      f.weight += Math.sin(t * 1.6 + this.def.height * 10) * 0.12;
      f.heel.L = f.heel.R = 0.3 + 0.1 * Math.sin(t * 5.2);
    }

    // telegraph glow (Rookie hints)
    if (this.glow.visible && !(this.move && this.move.t < this.move.durW + this.move.durT)) this.glow.visible = false;

    f.update(dt, t);
  }

  toNeutral(cd = 0.8) {
    this.state = 'neutral'; this.stateT = 0;
    this.move = null; this.queue = [];
    this.cooldown = cd / (this.diff.attackRate * this.def.aggression);
  }

  watchPunches(dt, dist) {
    const inp = this.game.input;
    const f = this.f;
    for (const side of ['left', 'right']) {
      const h = inp.handBySide(side);
      if (!h.valid) continue;
      if (h.speed < 1.0) { this.watch[side] = true; continue; }
      if (!this.watch[side]) continue;
      const dGlove = h.pos.distanceTo(f.headW);
      _v3.subVectors(f.headW, h.pos);
      const toward = h.vel.dot(_v3) / (dGlove || 1);
      if (h.speed > 2.0 && toward > 1.4 && dGlove < 0.8) {
        this.watch[side] = false;
        let p = this.diff.blockChance * this.def.defense;
        if (this.stun > 0.5) p *= 0.4;
        if (chance(p)) {
          if (chance(0.62)) { this.guard = 1; this.guardT = rand(0.45, 0.8); }
          else {
            // slip away from the punching glove
            const local = f.toLocal(h.pos, _tl);
            const dir = local.x > 0 ? -1 : 1;
            this.slip.set(dir * 0.16, 0, -0.04); this.slipT = 0.45;
            if (this.def.counter || chance(0.3 * this.diff.attackRate)) { this.counterReady = true; this.cooldown = Math.min(this.cooldown, 0.25); }
          }
        }
      }
    }
    // Keep a high guard more often on harder levels when close
    if (dist < 1.1 && this.guardT <= 0 && chance(dt * 0.35 * this.diff.blockChance * this.def.defense)) { this.guard = 1; this.guardT = rand(0.6, 1.4); }
  }

  updateStrike(dt, dist) {
    const m = this.move, f = this.f, def = m.def, sgn = m.sign;
    const prevT = m.t;
    m.t += dt;
    const D = f.dims, s = f.s;
    const headY = f.standingHeadY();
    // Re-aim during the first 70% of the windup, then commit (slip window!)
    if (m.t < m.durW * 0.7) this.zonePoint(def.target, m.targetW);
    else if (m.t < m.durW + m.durT) { this.zonePoint(def.target, _v3); m.targetW.lerp(_v3, clamp(this.diff.tracking * dt * 5, 0, 1)); }
    f.toLocal(m.targetW, _tl);
    // lunge in during windup/travel
    if (m.lunge > 0 && m.t < m.durW + m.durT) {
      const fwd = _v.set(Math.sin(f.root.rotation.y), 0, Math.cos(f.root.rotation.y));
      this.pos.addScaledVector(fwd, (m.lunge / (m.durW + m.durT)) * dt);
    }

    const inW = m.t < m.durW, inT = !inW && m.t < m.durW + m.durT;
    const uW = clamp(m.t / m.durW, 0, 1), uT = clamp((m.t - m.durW) / m.durT, 0, 1);
    const sd = m.side;
    // hints
    if (this.diff.hints && settings.hints && (inW || inT)) {
      this.glow.visible = true;
      this.glow.position.copy(m.isLeg ? f.footW[sd] : f.glove[sd]);
      this.glow.scale.setScalar(0.18 + 0.2 * uW);
    }

    if (!m.isLeg) {
      const guardP = _v2.copy(f.hand[sd]);
      f.handRate[sd] = 34;
      // end point: stop just short of the target (no glove through your face)
      _p.copy(_tl);
      const sh = sd === 'L' ? f.j.shL : f.j.shR;
      _v3.subVectors(_p, sh); const len = _v3.length();
      if (len > 0.01) _p.addScaledVector(_v3, -0.05 / len);
      if (def.kind === 'straight') {
        m.chamber.copy(guardP); m.chamber.z -= 0.06; m.chamber.y -= 0.02;
        f.torsoYaw = sd === 'L' ? lerp(-0.3, -0.62, inW ? uW : 1) : lerp(-0.3, 0.42, inW ? uW * 0.4 : 1);
        if (def.target === 'body') { f.crouch = 0.05 + 0.18 * (inW ? uW : 1); f.lean = 0.2; }
        if (inW) f.hand[sd].lerpVectors(guardP, m.chamber, ss(uW));
        else if (inT) f.hand[sd].lerpVectors(m.chamber, _p, ss(uT));
      } else if (def.kind === 'hook') {
        m.chamber.set(sgn * 0.36 * s, (def.target === 'body' ? D.hipY + 0.25 : headY - 0.08), 0.1 * s);
        f.armPole[sd].set(sgn * 1, 0.25, -0.3);
        f.torsoYaw = sd === 'L' ? (inW ? lerp(-0.3, -0.75, uW) : lerp(-0.75, 0.45, uT)) : (inW ? lerp(-0.3, 0.55, uW) : lerp(0.55, -0.55, uT));
        if (def.target === 'body') { f.crouch = 0.16; f.lean = 0.18; }
        if (inW) f.hand[sd].lerpVectors(guardP, m.chamber, ss(uW));
        else if (inT) { _v3.set(m.chamber.x * 0.9, _p.y, _p.z * 0.85); bezier(m.chamber, _v3, _p, ss(uT), f.hand[sd]); }
      } else if (def.kind === 'uppercut') {
        m.chamber.set(sgn * 0.12 * s, D.hipY + 0.3 * s, 0.14 * s);
        f.torsoYaw = sd === 'L' ? -0.5 : (inW ? lerp(-0.3, 0.1, uW) : 0.5);
        f.crouch = inW ? lerp(0.05, 0.16, uW) : lerp(0.16, 0.0, uT);
        if (inW) f.hand[sd].lerpVectors(guardP, m.chamber, ss(uW));
        else if (inT) { _v3.set(_p.x, _p.y - 0.3, _p.z - 0.08); bezier(m.chamber, _v3, _p, ss(uT), f.hand[sd]); }
      } else if (def.kind === 'elbow') {
        m.chamber.set(sgn * 0.12 * s, headY + 0.04, 0.04 * s);
        f.armPole[sd].set(sgn * 0.9, 0.9, 0.1);
        const dy = sd === 'R' ? 1 : -1;
        f.torsoYaw = inW ? lerp(-0.3, -0.35 * dy, uW) : lerp(-0.35 * dy, 0.6 * dy, uT);
        f.lean = 0.14;
        if (inW) f.hand[sd].lerpVectors(guardP, m.chamber, ss(uW));
        else if (inT) f.hand[sd].set(lerp(m.chamber.x, -sgn * 0.12 * s, ss(uT)), headY - 0.04, lerp(m.chamber.z, 0.14 * s, ss(uT)));
      }
      if (!inW && !inT) { f.handRate[sd] = 12; }
    } else {
      // ---- legs
      f.footRate[sd] = 30;
      const footRest = _v2.copy(f.foot[sd]);
      if (def.kind === 'knee') {
        f.hand.L.set(0.15, headY - 0.05, 0.45); f.hand.R.set(-0.15, headY - 0.05, 0.45); // clinch reach
        m.chamber.set(sgn * 0.08, 0.15, -0.25);
        _v3.set(sgn * 0.04, 0.45, 0.12);
        f.legPole[sd].set(0, 0.5, 1);
        f.lean = inW ? 0.05 : -0.15;
        f.hipShift.z = inT ? 0.12 * ss(uT) : 0;
        if (inW) f.foot[sd].lerpVectors(footRest, m.chamber, ss(uW));
        else if (inT) f.foot[sd].lerpVectors(m.chamber, _v3, ss(uT));
      } else if (def.kind === 'teep') {
        m.chamber.set(sgn * 0.05, 0.55, 0.2);
        f.legPole[sd].set(0, 0.6, 1);
        f.lean = -0.18; f.hipYaw = -0.1;
        _p.copy(_tl); _p.y = Math.min(_p.y, 1.05 * s);
        if (inW) f.foot[sd].lerpVectors(footRest, m.chamber, ss(uW));
        else if (inT) f.foot[sd].lerpVectors(m.chamber, _p, ss(uT));
        f.kickAmt[sd] = inT ? 0.3 : 0;
      } else {
        // roundhouse / low kick (rear leg)
        const high = def.target === 'head', low = def.kind === 'lowkick';
        m.chamber.set(sgn * 0.42, low ? 0.25 : 0.55, -0.1);
        f.legPole[sd].set(sgn * 1, 0.6, 0.7);
        f.hipYaw = inW ? lerp(-0.35, 0.7, uW) : 1.0;
        f.torsoYaw = inW ? lerp(-0.3, 0.4, uW) : 0.75;
        f.lean = high ? -0.32 : -0.18; f.crouch = high ? -0.06 : 0;
        f.hand[sd === 'R' ? 'R' : 'L'].set(sgn * 0.35, headY - 0.25, -0.15);   // swinging arm
        _p.copy(_tl);
        if (low) _p.y = Math.min(_p.y, 0.6);
        _v3.set(sgn * 0.85, _p.y, _p.z * 0.45);
        if (inW) f.foot[sd].lerpVectors(footRest, m.chamber, ss(uW));
        else if (inT) bezier(m.chamber, _v3, _p, ss(uT), f.foot[sd]);
        f.kickAmt[sd] = inT ? 1 : inW ? uW : 0;
      }
      if (!inW && !inT) { f.footRate[sd] = 10; f.kickAmt[sd] = 0; }
    }

    // ---- whole-body mechanics (overrides the simple torso values above)
    const uR = clamp((m.t - m.durW - m.durT) / m.durR, 0, 1);
    applyMechanics(f, m, inW, inT, uW, uT);
    if (!m.isLeg) {
      if (!inW && !inT) {
        // retract along the same line it went out on, back to guard
        if (!m.endL) m.endL = f.handCur[sd].clone();
        f.hand[sd].copy(m.endL).lerp(guardOf(f, sd, _v3), ss(uR));
        f.handRate[sd] = 30;
      }
    } else {
      // kicking leg is off the floor; after impact it re-chambers, then sets down and re-plants
      f.footFree[sd] = inW || inT || uR < 0.55;
      if (!inW && !inT) {
        if (!m.endF) m.endF = f.footCur[sd].clone();
        if (uR < 0.55) { f.foot[sd].lerpVectors(m.endF, m.chamber, ss(uR / 0.55)); f.footRate[sd] = 28; f.kickAmt[sd] = 1 - uR / 0.55; }
      }
    }

    // capture origin when travel starts
    if (prevT < m.durW && m.t >= m.durW) {
      m.originW.copy(m.isLeg ? f.footW[sd] : f.glove[sd]);
      sfxWhoosh(m.originW, def.dmg / 20);
    }
    // resolve at end of travel
    if (!m.resolved && m.t >= m.durW + m.durT) {
      m.resolved = true;
      this.resolveStrike(m);
    }
    if (m.t >= m.durW + m.durT + m.durR) {
      if (this.queue.length && this.state === 'strike') this.startMove(this.queue.shift());
      else this.toNeutral(rand(0.9, 2.2) * (this.counterReady ? 0.5 : 1));
      this.counterReady = false;
    }
  }

  resolveStrike(m) {
    const f = this.f, def = m.def, g = this.game;
    const zp = this.zonePoint(def.target, _v3);
    const point = new THREE.Vector3();
    if (!m.isLeg) {
      if (def.kind === 'elbow') point.copy(f.elbowW[m.side]); else point.copy(f.glove[m.side]);
      // pull the evaluation point to the actual fist surface toward target
    } else if (def.kind === 'knee') {
      point.copy(f.kneeW[m.side]); point.y += 0.3;
    } else {
      distPointSegment(zp, f.kneeW[m.side], f.footW[m.side], point);
    }
    const radius = def.kind === 'elbow' ? 0.3 : (m.isLeg && def.target === 'head') ? 0.34 : 0.25;
    const res = g.player.evaluateIncoming({ point, origin: m.originW, zone: def.target, radius });
    if (this.onResolve) { this.onResolve(res, m, point, zp); return; }
    const power = this.def.power * this.diff.damage;
    if (res === 'miss') {
      if (point.distanceTo(zp) < 0.6) {
        g.player.stats.dodged++;
        this.stats.dodgedByYou++;
        g.floating.spawn(def.target === 'leg' ? 'EVADED' : 'SLIPPED!', _v.copy(point).setY(point.y + 0.15), { color: '#4ea8ff', size: 0.12 });
        g.input.pulse('both', 0.12, 15);
        // Counter window for the player: AI is open
        this.openT = 0.5;
      }
      return;
    }
    if (res === 'block') {
      const chip = def.dmg * power * (m.isLeg ? 0.3 : 0.18);
      g.player.blocked(point, def.dmg / 18);
      g.player.hp = Math.max(1, g.player.hp - chip);
      this.stats.blockedByYou++;
      g.floating.spawn('BLOCKED', _v.copy(point).setY(point.y + 0.12), { color: '#ffffff', size: 0.11 });
      return;
    }
    const dmg = def.dmg * power * rand(0.85, 1.15);
    this.stats.landed++; this.stats.damage += dmg;
    sfxImpact(point, clamp(dmg / 18, 0.3, 1));
    const down = g.player.takeHit(dmg, def.target, g.fx);
    if (def.target === 'head' && dmg > 14) crowdCheer(0.6);
    g.fight.onPlayerHit(dmg, down);
  }

  /** Player landed a strike on us. Returns { blocked, dmg }. */
  receiveHit(hit, power) {
    const f = this.f, g = this.game;
    const zone = hit.zone;
    // high guard blocks head shots from the front; tight guard can catch body shots
    let blocked = false;
    if (this.state !== 'stunned' && this.state !== 'down') {
      if (zone === 'head' && this.guard > 0.5) blocked = chance(0.88);
      else if (zone === 'body' && this.guard > 0.5) blocked = chance(0.12 * this.def.defense);
      else if (zone === 'head' && this.state === 'neutral' && hit.type === 'jab') blocked = chance(0.12 * this.diff.blockChance * this.def.defense);
    }
    const base = {
      jab: 3, cross: 5, hook: 5.5, uppercut: 6, elbow: 8,
      roundhouse: PLAYER.kicks.roundhouse.dmg, highkick: PLAYER.kicks.highkick.dmg, teep: PLAYER.kicks.teep.dmg,
      lowkick: PLAYER.kicks.lowkick.dmg, knee: PLAYER.kicks.knee.dmg,
    }[hit.type] || 6;
    let dmg = base * (0.45 + power * 0.85);
    if (zone === 'head') dmg *= 1.15;
    if (this.state === 'strike' && this.move && this.move.t < this.move.durW + this.move.durT) { dmg *= 1.3; hit.counter = true; }
    if (this.openT > 0) { dmg *= 1.2; hit.counter = true; }
    if (this.state === 'stunned') dmg *= 1.2;
    if (blocked) dmg *= 0.15;
    dmg /= Math.max(0.6, this.def.defense * 0.4 + 0.6);
    this.hp = Math.max(0, this.hp - dmg);
    this.lastHitT = g.time;

    // reaction
    const imp = blocked ? 0.25 : clamp(dmg / 10, 0.25, 1.8);
    f.impulse(hit.dir, imp, zone === 'leg' ? 'body' : zone);
    _v.copy(hit.dir); _v.y = 0;
    if (_v.lengthSq() > 1e-4) this.knock.addScaledVector(_v.normalize(), (blocked ? 0.25 : 0.35 + imp * 0.45) * (this.def.id === 'viktor' ? 0.6 : 1));
    if (!blocked) {
      this.stun += zone === 'head' ? dmg * 0.022 : dmg * 0.01;
      if (this.hp <= 0) return { blocked, dmg, down: true };
      const armor = this.def.id === 'viktor' && dmg < 9;
      if (this.stun > 1.0) { this.state = 'stunned'; this.stateT = 0; this.move = null; this.queue = []; this.stun = 0.35; crowdCheer(0.7); say('He’s hurt!', 1.2); }
      else if (!armor && (dmg > 6 || (this.state === 'strike' && dmg > 4))) {
        this.state = 'hit'; this.stateT = 0; this.hitDur = clamp(0.12 + dmg * 0.02, 0.15, 0.5); this.move = null; this.queue = [];
        if (chance(0.4)) sfxGrunt(f.headW, 1 / f.s);
      }
      if (this.def.counter && chance(0.3)) this.cooldown = Math.min(this.cooldown, 0.4);
    } else {
      if (this.def.counter || chance(0.25)) { this.counterReady = true; this.cooldown = Math.min(this.cooldown, 0.2); }
    }
    return { blocked, dmg, down: false };
  }
}
const _qInv = new THREE.Quaternion();

// ---------------------------------------------------------------------------

export class FightMode {
  constructor(game, opts) {
    this.game = game;
    this.opts = opts;
    this.def = OPPONENTS.find((o) => o.id === opts.opponent) || OPPONENTS[0];
    this.diff = DIFFICULTIES[opts.difficulty] || DIFFICULTIES.medium;
    this.oppCorner = new THREE.Vector3(0, 0, -2.0);
    this.state = 'init';
  }

  enter() {
    const g = this.game;
    g.setArena(this.opts.arena);
    g.fight = this;
    g.player.reset();
    g.player.stats = g.player.freshStats();
    g.player.onKickActive = (k, p) => this.onPlayerKick(k, p);
    g.player.strikesEnabled = true; g.player.kicksEnabled = false;
    this.ai = new OpponentAI(g, this.def, this.diff);
    g.scene.add(this.ai.f.root);
    // place fighters
    const start = g.arena.bounds.type === 'circle' ? Math.min(1.3, g.arena.bounds.r - 1.5) : Math.min(1.3, g.arena.bounds.h - 1.0);
    this.oppCorner.set(0, 0, -start - 0.6);
    g.placePlayer(0, start, 0);
    this.ai.f.setWorld(0, -start - 0.4, 0);
    this.ai.f.update(0.016, 0);
    this.round = 1;
    this.roundTime = this.opts.roundSeconds;
    this.roundLog = [];
    this.curRound = { pDmg: 0, oDmg: 0, pKd: 0, oKd: 0 };
    this.result = null;
    g.scoreboard.panel.visible = true;
    g.scoreboard.pos.set(0, 2.2, -start);
    this.beginRound();
  }

  exit() {
    const g = this.game;
    if (this.ai) { this.ai.dispose(); this.ai = null; }
    g.scoreboard.panel.visible = false;
    g.player.onKickActive = null;
    g.fx.lowHp = 0; g.fx.fade = 0;
    crowdUniforms.uExcite.value = 0;
    if (g.fight === this) g.fight = null;
  }

  beginRound() {
    const g = this.game;
    this.state = 'intro'; this.stateT = 0;
    this.ai.state = 'idle';
    this.ai.f.downTarget = 0;
    g.player.kicksEnabled = false;
    g.announce(`ROUND ${this.round}`, this.round === this.opts.rounds ? 'FINAL ROUND' : `${this.def.name}`, { dur: 1.8 });
    say(`Round ${this.round}`);
  }

  get isActive() { return this.state === 'fight'; }

  update(dt) {
    const g = this.game, ai = this.ai, p = g.player;
    this.stateT += dt;
    ai.openT = Math.max(0, (ai.openT || 0) - dt);

    switch (this.state) {
      case 'intro':
        if (this.stateT > 2.0) {
          this.state = 'fight'; this.stateT = 0;
          sfxBell(1); g.announce('FIGHT!', '', { dur: 0.9, color: '#ffc83d' });
          ai.toNeutral(1.2); p.kicksEnabled = true;
          crowdCheer(0.5);
        }
        break;
      case 'fight': {
        this.roundTime -= dt;
        // strikes
        p.countThrows();
        const hits = p.checkStrikes(this.targets());
        for (const h of hits) this.onPlayerStrike(h);
        if (this.roundTime <= 0) this.endRound();
        // gentle regen
        p.regen(dt, 0.08);
        ai.hp = Math.min(ai.maxHp, ai.hp + dt * 0.06);
        break;
      }
      case 'oppDown': this.updateOppDown(dt); break;
      case 'playerDown': this.updatePlayerDown(dt); break;
      case 'rest':
        if (this.stateT > 1.0 && (g.input.right.buttons.a.down || g.input.left.buttons.a.down || this.stateT > 15)) {
          this.round++; this.roundTime = this.opts.roundSeconds;
          this.curRound = { pDmg: 0, oDmg: 0, pKd: 0, oKd: 0 };
          g.announcer.hide();
          this.beginRound();
        } else if (Math.floor(this.stateT) !== Math.floor(this.stateT - dt)) {
          g.announce('REST', `Round ${this.round + 1} in ${Math.max(0, Math.ceil(15 - this.stateT))}s — press A to skip`, { dur: 0 , height: 0.35});
        }
        p.regen(dt, 2.0); ai.hp = Math.min(ai.maxHp, ai.hp + dt * 1.6);
        break;
      case 'over':
        break;
      default: break;
    }

    ai.update(dt, g.time, this.state === 'fight');

    // HUD
    const head = g.input.head.pos;
    const ap = ai.pos;
    _v.set(ap.x - head.x, 0, ap.z - head.z);
    const l = _v.length() || 1;
    _v.multiplyScalar(1 / l);
    const anchor = _v2.set(ap.x + _v.x * 0.9, 2.15, ap.z + _v.z * 0.9);
    g.scoreboard.set({
      pHp: p.hpFrac, pSt: p.stFrac, oHp: ai.hp / ai.maxHp, oName: this.def.name.toUpperCase(),
      round: this.round, rounds: this.opts.rounds, time: this.roundTime, pKd: p.knockdowns, oKd: ai.knockdowns,
      label: this.state === 'rest' ? 'REST' : '',
    });
    g.scoreboard.update(dt, anchor, head);
    g.fx.lowHp = p.hpFrac < 0.25 && this.state === 'fight' ? (0.25 - p.hpFrac) / 0.25 * 0.8 + 0.2 : 0;
    // heartbeat haptic when low
    if (g.fx.lowHp > 0) {
      this._hb = (this._hb || 0) - dt;
      if (this._hb <= 0) { this._hb = 1.1; g.input.pattern('both', [[0.25, 35, 90], [0.4, 45, 0]]); }
    }
    crowdUniforms.uExcite.value = Math.max(0, crowdUniforms.uExcite.value - dt * 0.4);
  }

  targets() {
    const f = this.ai.f;
    if (this.ai.state === 'down' || this.ai.state === 'ko' || this.ai.state === 'rising') return [];
    return [
      { id: 'oG_L', pos: f.glove.L, r: 0.07, zone: 'guard', elbows: false, enabled: this.ai.guard > 0.5 },
      { id: 'oG_R', pos: f.glove.R, r: 0.07, zone: 'guard', elbows: false, enabled: this.ai.guard > 0.5 },
      ...f.hurt.filter((h) => h.zone !== 'leg').map((h, i) => ({ id: 'oH' + i, pos: h.pos, r: h.r, zone: h.zone })),
    ];
  }

  onPlayerStrike(hit) {
    const g = this.game, ai = this.ai, p = g.player;
    if (hit.zone === 'guard') {
      // glove-on-glove
      sfxBlock(hit.point, hit.power);
      g.input.pulse(hit.side, 0.35, 30);
      p.contacts.set(hit.side + ':oH0', { inside: true, t: p.time });
      ai.f.impulse(hit.dir, 0.15, 'head');
      p.stats.blocked++;
      return;
    }
    const res = ai.receiveHit(hit, hit.power);
    this.applyLanded(hit, res);
  }

  applyLanded(hit, res) {
    const g = this.game, p = g.player, ai = this.ai;
    p.stats.landed++;
    if (res.blocked) {
      p.stats.blocked++;
      sfxBlock(hit.point, hit.power);
      g.input.pulse(hit.side, 0.35, 30);
      g.floating.spawn('BLOCKED', _v.copy(hit.point).setY(hit.point.y + 0.15), { color: '#9aa7b8', size: 0.1 });
      return;
    }
    p.stats.damage += res.dmg;
    this.curRound.pDmg += res.dmg;
    const pw = clamp(res.dmg / 14, 0.2, 1);
    sfxImpact(hit.point, pw);
    g.input.pulse(hit.side, 0.45 + pw * 0.55, 30 + pw * 60);
    const big = res.dmg >= 11;
    const label = hit.counter ? 'COUNTER!' : big ? (hit.type === 'elbow' ? 'ELBOW!' : hit.tool === 'kick' ? 'KICK!' : 'CRITICAL!') : hit.type.toUpperCase();
    g.floating.spawn(label, _v.copy(hit.point).setY(hit.point.y + 0.15), { color: big || hit.counter ? '#ffc83d' : '#ffffff', size: big ? 0.14 : 0.1 });
    if (big) { crowdCheer(0.5); crowdUniforms.uExcite.value = Math.min(1, crowdUniforms.uExcite.value + 0.4); }
    if (res.down) this.opponentDown();
  }

  onPlayerKick(k, p) {
    const g = this.game, ai = this.ai;
    if (this.state !== 'fight' || !ai || ['down', 'ko', 'rising'].includes(ai.state)) return;
    const head = g.input.head;
    const d = k.def;
    _v.subVectors(ai.pos, head.pos); _v.y = 0;
    const dist = _v.length();
    _v.normalize();
    const ang = Math.acos(clamp(_v.dot(head.fwd), -1, 1));
    if (dist > d.range + 0.2 || ang > 0.75) {
      g.floating.spawn('MISS', _v2.copy(p.footW), { color: '#9aa7b8', size: 0.09 });
      return;
    }
    // AI may check/catch/avoid the kick
    let avoid = this.diff.blockChance * this.def.defense * (ai.state === 'neutral' ? 0.45 : 0.1);
    if (k.type === 'lowkick' || k.type === 'knee') avoid *= 0.6;
    if (chance(avoid) && ai.state !== 'stunned') {
      const isCheck = k.type === 'lowkick' || k.type === 'roundhouse';
      const res = { blocked: true, dmg: 0 };
      const point = k.type === 'highkick' ? ai.f.headW : ai.f.chestW;
      g.floating.spawn(isCheck ? 'CHECKED' : 'BLOCKED', _v2.copy(point).setY(point.y + 0.2), { color: '#9aa7b8', size: 0.1 });
      sfxBlock(point, 0.7);
      g.input.pulse(k.side, 0.5, 50);
      p.stats.landed++; p.stats.blocked++;
      ai.guard = 1; ai.guardT = 0.5;
      void res;
      return;
    }
    const zone = d.target === 'head' ? 'head' : d.target === 'leg' ? 'leg' : 'body';
    const point = zone === 'head' ? ai.f.headW.clone() : zone === 'leg' ? ai.f.thighW.L.clone() : ai.f.chestW.clone();
    const dir = _v3.copy(head.right).multiplyScalar(k.side === 'right' ? -1 : 1).add(head.fwd).normalize();
    if (k.type === 'teep' || k.type === 'knee') dir.copy(head.fwd);
    const hit = { side: k.side, tool: 'kick', type: k.type, power: k.power, point, dir: dir.clone(), zone };
    const res = ai.receiveHit(hit, 0.75 * k.power + 0.25);
    p.stats.kicksLanded++;
    // teep pushes them back
    if (k.type === 'teep' && !res.blocked) ai.pos.addScaledVector(head.fwd, 0.45);
    g.input.pulse(k.side, 0.9, 80);
    g.input.pulse(k.side === 'left' ? 'right' : 'left', 0.3, 40);
    this.applyLanded(hit, res);
  }

  onPlayerHit(dmg, down) {
    this.curRound.oDmg += dmg;
    if (down) this.playerDown();
  }

  // ---- Knockdowns ---------------------------------------------------------
  opponentDown() {
    const g = this.game, ai = this.ai;
    ai.knockdowns++; this.curRound.oKd++;
    ai.state = 'down'; ai.stateT = 0; ai.move = null; ai.queue = [];
    this.state = 'oppDown'; this.stateT = 0; this.count = 0;
    g.player.kicksEnabled = false;
    crowdCheer(1); crowdUniforms.uExcite.value = 1;
    sfxSuccess();
    g.input.pattern('both', [[1, 90, 40], [0.6, 60, 0]]);
    // will they beat the count?
    const kd = ai.knockdowns;
    const tko = kd >= 3;
    const getUpP = clamp(0.95 - (kd - 1) * 0.3 + (this.diff.hpScale - 1) * 0.6, 0.15, 0.97);
    this.oppGetsUp = !tko && chance(getUpP);
    this.oppGetUpAt = this.oppGetsUp ? randInt(3, 8) : 99;
    this.tko = tko;
    g.announce(tko ? 'TKO!' : 'KNOCKDOWN!', tko ? 'Three knockdowns' : '', { dur: 1.6, color: '#ffc83d' });
    if (tko) say('T K O!'); else say('Knockdown!');
  }

  updateOppDown(dt) {
    const g = this.game, ai = this.ai;
    if (this.tko) {
      if (this.stateT > 2.2) this.finish('win', 'TKO', `Round ${this.round}`);
      return;
    }
    const c = Math.floor((this.stateT - 1.4) / 0.95) + 1;
    if (this.stateT > 1.4 && c !== this.count && c <= 10) {
      this.count = c;
      g.announce(String(c), '', { dur: 0.8, color: '#ffffff' });
      say(String(c), 1.3);
      if (c >= this.oppGetUpAt) {
        ai.state = 'rising'; ai.stateT = 0;
        ai.hp = ai.maxHp * clamp(0.55 - 0.12 * ai.knockdowns, 0.2, 0.6);
        this.state = 'fight'; this.stateT = 0;
        g.player.kicksEnabled = true;
        g.announce('FIGHT!', '', { dur: 0.8, color: '#ffc83d' });
        return;
      }
      if (c === 10) { this.finish('win', 'KO', `Round ${this.round}`); ai.state = 'ko'; }
    }
  }

  playerDown() {
    const g = this.game, p = g.player;
    p.knockdowns++; this.curRound.pKd++;
    this.state = 'playerDown'; this.stateT = 0; this.count = 0; this.mash = 0;
    this.mashNeed = 8 + p.knockdowns * 5 + (this.diff.id === 'hard' ? 4 : 0);
    this.ai.state = 'corner'; this.ai.move = null; this.ai.queue = [];
    p.kicksEnabled = false; p.strikesEnabled = false;
    g.fx.fadeColor.setRGB(0.12, 0.02, 0.02);
    sfxFail();
    crowdCheer(0.9);
    if (p.knockdowns >= 3) { this.tko = true; g.announce('TKO', 'Three knockdowns', { dur: 2.2, color: '#ff5a52' }); }
    else { this.tko = false; g.announce('YOU’RE DOWN!', 'Mash both TRIGGERS to get up!', { dur: 1.5, color: '#ff5a52' }); }
  }

  updatePlayerDown(dt) {
    const g = this.game, p = g.player;
    g.fx.fade = Math.min(0.75, this.stateT * 1.5) * (1 - this.mash / this.mashNeed * 0.6);
    if (this.tko) {
      if (this.stateT > 2.4) { g.fx.fade = 0; p.strikesEnabled = true; this.finish('loss', 'TKO', `Round ${this.round}`); }
      return;
    }
    const L = g.input.left.buttons, R = g.input.right.buttons;
    if (this.stateT > 0.8 && (L.trigger.down || R.trigger.down || L.grip.down || R.grip.down)) {
      this.mash++;
      g.input.pulse(L.trigger.down || L.grip.down ? 'left' : 'right', 0.3, 20);
    }
    const c = Math.floor((this.stateT - 1.0) / 1.0) + 1;
    if (this.stateT > 1.0 && c !== this.count && c <= 10) {
      this.count = c;
      g.announce(String(c), `Get up! ${Math.min(this.mash, this.mashNeed)}/${this.mashNeed}`, { dur: 0.9, color: '#ffffff', height: 0.0 });
      say(String(c), 1.3);
      if (c === 10 && this.mash < this.mashNeed) {
        g.fx.fade = 0; p.strikesEnabled = true;
        this.finish('loss', 'KO', `Round ${this.round}`);
        return;
      }
    }
    if (this.mash >= this.mashNeed) {
      p.hp = p.maxHp * clamp(0.5 - 0.12 * p.knockdowns, 0.2, 0.5);
      p.stamina = 60;
      g.fx.fade = 0;
      this.state = 'fight'; this.stateT = 0;
      p.kicksEnabled = true; p.strikesEnabled = true;
      this.ai.toNeutral(2.0);
      g.announce('FIGHT!', '', { dur: 0.8, color: '#ffc83d' });
    }
  }

  // ---- Rounds & result -----------------------------------------------------
  endRound() {
    const g = this.game;
    sfxBell(3);
    this.roundLog.push({ ...this.curRound });
    if (this.round >= this.opts.rounds) {
      this.decision();
      return;
    }
    this.state = 'rest'; this.stateT = 0;
    this.ai.state = 'corner'; this.ai.move = null; this.ai.queue = [];
    g.player.kicksEnabled = false;
    g.announce(`END OF ROUND ${this.round}`, 'Rest up. Press A to skip.', { dur: 2.0 });
  }

  decision() {
    // Three judges, 10-point must system with a little subjectivity.
    const cards = [0, 0, 0].map(() => {
      let you = 0, opp = 0;
      for (const r of this.roundLog) {
        const pScore = r.pDmg * rand(0.85, 1.15) + r.oKd * 20;
        const oScore = r.oDmg * rand(0.85, 1.15) + r.pKd * 20;
        let y = 10, o = 10;
        if (Math.abs(pScore - oScore) < 3) { /* even */ } else if (pScore > oScore) o = 9; else y = 9;
        y -= r.pKd; o -= r.oKd;
        you += y; opp += o;
      }
      return [you, opp];
    });
    const wins = cards.filter(([a, b]) => a > b).length, losses = cards.filter(([a, b]) => b > a).length;
    let outcome, method;
    if (wins >= 2) { outcome = 'win'; method = wins === 3 ? 'Unanimous Decision' : 'Split Decision'; }
    else if (losses >= 2) { outcome = 'loss'; method = losses === 3 ? 'Unanimous Decision' : 'Split Decision'; }
    else { outcome = 'draw'; method = 'Draw'; }
    this.finish(outcome, method, cards.map(([a, b]) => `${a}-${b}`).join('   '));
  }

  finish(outcome, method, detail) {
    const g = this.game;
    if (this.state === 'over') return;
    this.state = 'over'; this.stateT = 0;
    g.player.kicksEnabled = false;
    if (outcome === 'win') { this.ai.state = this.ai.state === 'ko' || this.ai.state === 'down' ? 'ko' : 'corner'; sfxSuccess(); crowdCheer(1); }
    else if (outcome === 'loss') { this.ai.state = 'celebrate'; this.ai.stateT = 0; }
    else this.ai.state = 'corner';
    sfxBell(outcome === 'draw' ? 2 : 3);
    const title = outcome === 'win' ? 'YOU WIN!' : outcome === 'loss' ? 'YOU LOSE' : 'DRAW';
    g.announce(title, method, { dur: 2.4, color: outcome === 'win' ? '#ffc83d' : outcome === 'loss' ? '#ff5a52' : '#ffffff' });
    say(outcome === 'win' ? 'Winner!' : outcome === 'loss' ? 'You lose.' : 'Draw.');
    const s = g.player.stats;
    this.result = {
      outcome, method, detail, opponent: this.def.name, difficulty: this.diff.name, round: this.round,
      landed: s.landed, thrown: Math.max(s.thrown, s.landed), damage: Math.round(s.damage), maxSpeed: s.maxSpeed,
      kicks: `${s.kicksLanded}/${s.kicksThrown}`, dodged: s.dodged, blocks: s.blocks, taken: Math.round(s.taken),
      oppLanded: this.ai.stats.landed, oppThrown: this.ai.stats.thrown,
    };
    setTimeout(() => { if (g.mode === this) g.menu.showResults(this.result); }, 2600);
  }
}

void smoothstep;
