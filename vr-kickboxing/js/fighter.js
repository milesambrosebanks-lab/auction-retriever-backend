// Procedural low-poly fighter: primitives + 2-bone IK for arms and legs.
// Cheap to render (~20 draw calls, one shared material) and fully posable by
// the AI / coach controllers. Local frame: feet at origin, facing +Z,
// fighter's LEFT is +X (so the orthodox lead side is +X).

import * as THREE from 'three';
import { GeoBatch, makeBlobShadow, clamp, damp } from './util.js';

const UP = new THREE.Vector3(0, 1, 0);
const FWD = new THREE.Vector3(0, 0, 1);
const _a = new THREE.Vector3(), _b = new THREE.Vector3(), _c = new THREE.Vector3(), _d = new THREE.Vector3();
const _q = new THREE.Quaternion(), _q2 = new THREE.Quaternion();
const _m = new THREE.Matrix4();
const _e1 = new THREE.Euler(), _e2 = new THREE.Euler(), _e3 = new THREE.Euler();
const _ftL = new THREE.Vector3(), _ftR = new THREE.Vector3();
const _pw = new THREE.Vector3(), _mInv = new THREE.Matrix4();
const ssm = (u) => u * u * (3 - 2 * u);
function wrapA(a) { while (a > Math.PI) a -= Math.PI * 2; while (a < -Math.PI) a += Math.PI * 2; return a; }

let sharedMat = null;
function material() {
  if (!sharedMat) sharedMat = new THREE.MeshLambertMaterial({ vertexColors: true });
  return sharedMat;
}

function shade(hex, f) {
  const c = new THREE.Color(hex);
  c.multiplyScalar(f);
  return c.getHex();
}

function capsule(r, len, seg = 8) {
  return new THREE.CapsuleGeometry(r, Math.max(0.001, len - 2 * r), 3, seg);
}

/** Two-bone IK. All vectors in the same space. Writes joint + effective end. */
export function solveIK(A, T, l1, l2, pole, outJoint, outEnd) {
  _a.subVectors(T, A);
  let d = _a.length();
  if (d < 1e-5) { _a.set(0, -1, 0); d = 1e-5; }
  _a.divideScalar(d);
  const dc = clamp(d, Math.abs(l1 - l2) + 0.01, l1 + l2 - 0.002);
  outEnd.copy(A).addScaledVector(_a, dc);
  const a = (l1 * l1 - l2 * l2 + dc * dc) / (2 * dc);
  const h = Math.sqrt(Math.max(0, l1 * l1 - a * a));
  _b.copy(pole).addScaledVector(_a, -pole.dot(_a));
  if (_b.lengthSq() < 1e-8) _b.set(0, -1, 0).addScaledVector(_a, -_a.y);
  _b.normalize();
  outJoint.copy(A).addScaledVector(_a, a).addScaledVector(_b, h);
}

function placeSeg(mesh, a, b) {
  mesh.position.addVectors(a, b).multiplyScalar(0.5);
  _c.subVectors(b, a);
  const len = _c.length();
  if (len > 1e-6) mesh.quaternion.setFromUnitVectors(UP, _c.divideScalar(len));
}

class Spring {
  constructor(k = 120, c = 14) { this.x = new THREE.Vector3(); this.v = new THREE.Vector3(); this.k = k; this.c = c; }
  kick(dir, amt) { this.v.addScaledVector(dir, amt); }
  update(dt, target) {
    // critically-ish damped spring toward target (default zero)
    _d.copy(this.x);
    if (target) _d.sub(target);
    this.v.addScaledVector(_d, -this.k * dt).multiplyScalar(Math.max(0, 1 - this.c * dt));
    this.x.addScaledVector(this.v, dt);
  }
}

export class Fighter {
  /**
   * def: opponent definition (colors, height, build)
   * opts.equipment: 'gloves' | 'mitts' | 'pads'
   */
  constructor(def, opts = {}) {
    this.def = def;
    this.equipment = opts.equipment || 'gloves';
    const s = this.s = def.height / 1.8;
    const b = this.b = def.build || 1;
    this.dims = {
      thigh: 0.425 * s, shin: 0.425 * s, upper: 0.29 * s, fore: 0.27 * s,
      torso: 0.5 * s, shoulderW: 0.19 * s * (0.85 + 0.15 * b), hipW: 0.095 * s,
      headR: 0.105 * s, hipY: 0.975 * s, neck: 0.15 * s,
    };

    this.root = new THREE.Group();     // world placement (x, z, yaw)
    this.pivot = new THREE.Group();    // knockdown pivot at feet
    this.root.add(this.pivot);
    this.shadow = makeBlobShadow(0.95 * s);
    this.root.add(this.shadow);

    this._buildMeshes();

    // ---- Pose inputs (pivot-local) ----
    this.hand = { L: new THREE.Vector3(), R: new THREE.Vector3() };
    this.handCur = { L: new THREE.Vector3(), R: new THREE.Vector3() };
    this.handRate = { L: 18, R: 18 };
    this.armPole = { L: new THREE.Vector3(0.6, -1, -0.5), R: new THREE.Vector3(-0.6, -1, -0.5) };
    this.foot = { L: new THREE.Vector3(), R: new THREE.Vector3() };
    this.footCur = { L: new THREE.Vector3(), R: new THREE.Vector3() };
    this.footRate = { L: 20, R: 20 };
    this.legPole = { L: new THREE.Vector3(0.25, 0, 1), R: new THREE.Vector3(-0.25, 0, 1) };
    this.padFace = { L: new THREE.Vector3(0, 0, 1), R: new THREE.Vector3(0, 0, 1) };
    this.kickAmt = { L: 0, R: 0 };      // 0..1 points the foot like a kick
    this.torsoYaw = -0.3; this.torsoYawCur = -0.3;
    this.hipYaw = -0.35; this.hipYawCur = -0.35;
    this.lean = 0.08; this.leanCur = 0.08;
    this.roll = 0; this.rollCur = 0;
    this.crouch = 0.05; this.crouchCur = 0.05;
    this.hipShift = new THREE.Vector3(); this.hipShiftCur = new THREE.Vector3();
    this.bob = 0; this.bobAmt = 1;
    this.lookTarget = null;             // world-space point to look at
    this.downAmt = 0; this.downTarget = 0;
    this.flash = 0;

    // ---- Body mechanics ----
    this.weight = 0; this.weightCur = 0;          // -1 = on rear foot, +1 = on lead foot
    this.heel = { L: 0.25, R: 0.25 }; this.heelCur = { L: 0.25, R: 0.25 };   // 0 flat .. 1 up on the ball
    this.footYaw = { L: 0, R: 0 }; this.footYawCur = { L: 0, R: 0 };       // pivot on the ball of the foot
    this.footFree = { L: false, R: false };       // leg is striking: not planted
    this.planting = true;
    const mkPlant = () => ({ pos: new THREE.Vector3(), from: new THREE.Vector3(), to: new THREE.Vector3(), t: 1, dur: 0.2, yaw: 0, fromYaw: 0, toYaw: 0, init: false, lift: 0 });
    this.plant = { L: mkPlant(), R: mkPlant() };
    this.lastRoot = new THREE.Vector3(); this.rootVel = new THREE.Vector3(); this.hasLast = false;

    this.headSpring = new Spring(160, 16);
    this.bodySpring = new Spring(110, 13);

    // ---- Outputs (world) ----
    this.headW = new THREE.Vector3();
    this.chestW = new THREE.Vector3();
    this.bellyW = new THREE.Vector3();
    this.glove = { L: new THREE.Vector3(), R: new THREE.Vector3() };
    this.elbowW = { L: new THREE.Vector3(), R: new THREE.Vector3() };
    this.footW = { L: new THREE.Vector3(), R: new THREE.Vector3() };
    this.kneeW = { L: new THREE.Vector3(), R: new THREE.Vector3() };
    this.thighW = { L: new THREE.Vector3(), R: new THREE.Vector3() };
    this.hurt = [
      { zone: 'head', pos: this.headW, r: 0.125 * s },
      { zone: 'body', pos: this.chestW, r: 0.17 * b * s + 0.01 },
      { zone: 'body', pos: this.bellyW, r: 0.16 * b * s },
      { zone: 'leg', pos: this.thighW.L, r: 0.1 * s },
      { zone: 'leg', pos: this.thighW.R, r: 0.1 * s },
    ];

    // internal joints (pivot-local)
    this.j = {
      hip: new THREE.Vector3(), chest: new THREE.Vector3(), neck: new THREE.Vector3(), head: new THREE.Vector3(),
      shL: new THREE.Vector3(), shR: new THREE.Vector3(), elL: new THREE.Vector3(), elR: new THREE.Vector3(),
      haL: new THREE.Vector3(), haR: new THREE.Vector3(), hpL: new THREE.Vector3(), hpR: new THREE.Vector3(),
      knL: new THREE.Vector3(), knR: new THREE.Vector3(), anL: new THREE.Vector3(), anR: new THREE.Vector3(),
    };
    this.setStance();
    this.handCur.L.copy(this.hand.L); this.handCur.R.copy(this.hand.R);
    this.footCur.L.copy(this.foot.L); this.footCur.R.copy(this.foot.R);
  }

  _buildMeshes() {
    const d = this.def, s = this.s, b = this.b, D = this.dims, mat = material();
    const skin = d.skin, skinD = shade(d.skin, 0.82);
    const mk = (batch) => { const m = batch.build(mat); m.matrixAutoUpdate = true; this.pivot.add(m); return m; };

    // Pelvis / shorts
    const pel = new GeoBatch();
    pel.add(new THREE.CylinderGeometry(0.16 * b * s, 0.15 * b * s, 0.22 * s, 12), d.shorts, { pos: [0, -0.04 * s, 0], scale: [1, 1, 0.78] });
    pel.add(new THREE.CylinderGeometry(0.163 * b * s, 0.163 * b * s, 0.05 * s, 12), d.trim, { pos: [0, 0.07 * s, 0], scale: [1, 1, 0.8] });
    this.mPelvis = mk(pel);

    // Torso
    const tor = new GeoBatch();
    const tl = D.torso;
    tor.add(capsule(0.13 * s * b, tl, 10), skin, { scale: [1.22, 1, 0.78] });
    tor.add(new THREE.SphereGeometry(0.105 * s * b, 10, 8), skin, { pos: [0.16 * s * b, 0.17 * s, 0], scale: [1, 0.85, 0.85] }); // delts
    tor.add(new THREE.SphereGeometry(0.105 * s * b, 10, 8), skin, { pos: [-0.16 * s * b, 0.17 * s, 0], scale: [1, 0.85, 0.85] });
    tor.add(new THREE.CylinderGeometry(0.11 * s * b, 0.13 * s * b, 0.16 * s, 10), skinD, { pos: [0, -tl / 2 + 0.1 * s, 0], scale: [1.15, 1, 0.8] }); // waist
    this.mTorso = mk(tor);

    // Neck
    const nk = new GeoBatch();
    nk.add(new THREE.CylinderGeometry(0.05 * s * b, 0.058 * s * b, D.neck, 8), skinD);
    this.mNeck = mk(nk);

    // Head (faces +Z)
    const hd = new GeoBatch();
    const hr = D.headR;
    hd.add(new THREE.SphereGeometry(hr, 14, 10), skin, { scale: [0.88, 1.05, 0.96] });
    hd.add(new THREE.SphereGeometry(hr * 1.04, 14, 8, 0, Math.PI * 2, 0, Math.PI * 0.42), d.hair, { pos: [0, hr * 0.06, -hr * 0.05], scale: [0.9, 1.0, 1.0] });
    hd.add(new THREE.SphereGeometry(hr * 0.13, 6, 4), 0x111111, { pos: [hr * 0.33, hr * 0.12, hr * 0.84] });
    hd.add(new THREE.SphereGeometry(hr * 0.13, 6, 4), 0x111111, { pos: [-hr * 0.33, hr * 0.12, hr * 0.84] });
    hd.add(new THREE.BoxGeometry(hr * 0.42, hr * 0.08, hr * 0.12), d.hair, { pos: [hr * 0.33, hr * 0.32, hr * 0.84] });
    hd.add(new THREE.BoxGeometry(hr * 0.42, hr * 0.08, hr * 0.12), d.hair, { pos: [-hr * 0.33, hr * 0.32, hr * 0.84] });
    hd.add(new THREE.SphereGeometry(hr * 0.16, 6, 5), skinD, { pos: [0, -hr * 0.08, hr * 0.95], scale: [0.8, 1.2, 1] });
    hd.add(new THREE.BoxGeometry(hr * 0.5, hr * 0.07, hr * 0.1), 0x6a2b2b, { pos: [0, -hr * 0.45, hr * 0.82] });
    hd.add(new THREE.SphereGeometry(hr * 0.2, 6, 5), skinD, { pos: [hr * 0.88, 0, 0], scale: [0.5, 1, 0.8] });
    hd.add(new THREE.SphereGeometry(hr * 0.2, 6, 5), skinD, { pos: [-hr * 0.88, 0, 0], scale: [0.5, 1, 0.8] });
    this.mHead = mk(hd);

    // Arms
    const limb = (r, len, color, extra) => {
      const g = new GeoBatch();
      g.add(capsule(r, len, 8), color);
      if (extra) extra(g, len);
      return mk(g);
    };
    this.mUpper = { L: limb(0.062 * s * b, D.upper, skin), R: limb(0.062 * s * b, D.upper, skin) };
    this.mFore = { L: limb(0.051 * s * b, D.fore, skin), R: limb(0.051 * s * b, D.fore, skin) };

    // Hands / equipment
    this.mHand = { L: this._equipMesh('L'), R: this._equipMesh('R') };
    this.pivot.add(this.mHand.L); this.pivot.add(this.mHand.R);

    // Legs (thigh has shorts leg near the hip end = -Y)
    const thighExtra = (g, len) => g.add(new THREE.CylinderGeometry(0.112 * s * b, 0.104 * s * b, 0.22 * s, 10), d.shorts, { pos: [0, -len / 2 + 0.11 * s, 0] });
    const shinExtra = d.wraps ? (g, len) => g.add(new THREE.CylinderGeometry(0.06 * s, 0.06 * s, 0.07 * s, 8), d.wraps, { pos: [0, len / 2 - 0.05 * s, 0] }) : null;
    this.mThigh = { L: limb(0.09 * s * b, D.thigh, skin, thighExtra), R: limb(0.09 * s * b, D.thigh, skin, thighExtra) };
    this.mShin = { L: limb(0.064 * s * b, D.shin, skin, shinExtra), R: limb(0.064 * s * b, D.shin, skin, shinExtra) };
    const foot = () => {
      const g = new GeoBatch();
      g.add(new THREE.BoxGeometry(0.095 * s, 0.065 * s, 0.23 * s), skinD, { pos: [0, -0.035 * s, 0.06 * s] });
      return mk(g);
    };
    this.mFoot = { L: foot(), R: foot() };
  }

  _equipMesh(side) {
    const d = this.def, s = this.s, mat = material();
    const g = new GeoBatch();
    let mesh;
    if (this.equipment === 'mitts') {
      // Focus mitt: face along +Z.
      g.add(new THREE.CylinderGeometry(0.115, 0.12, 0.06, 16), 0x1a1a1a, { rot: [Math.PI / 2, 0, 0] });
      g.add(new THREE.CircleGeometry(0.1, 20), 0xf4f4f4, { pos: [0, 0, 0.0305] });
      g.add(new THREE.CircleGeometry(0.06, 20), 0xd62828, { pos: [0, 0, 0.031] });
      mesh = g.build(mat);
      mesh.userData.faceAxis = FWD;
    } else if (this.equipment === 'pads') {
      g.add(new THREE.BoxGeometry(0.22, 0.38, 0.1), 0x1d3557, {});
      g.add(new THREE.BoxGeometry(0.2, 0.06, 0.005), 0xffd23f, { pos: [0, 0.08, 0.051] });
      g.add(new THREE.BoxGeometry(0.2, 0.06, 0.005), 0xffd23f, { pos: [0, -0.08, 0.051] });
      mesh = g.build(mat);
      mesh.userData.faceAxis = FWD;
    } else {
      // Boxing glove: long axis +Y (knuckles at +Y), cuff toward -Y.
      g.add(new THREE.SphereGeometry(0.058 * s, 10, 8), d.gloves, { scale: [1.05, 1.3, 1.12], pos: [0, 0.015, 0] });
      g.add(new THREE.SphereGeometry(0.026 * s, 6, 5), shade(d.gloves, 0.85), { pos: [(side === 'L' ? -1 : 1) * -0.045 * s, -0.005, 0.03 * s] });
      g.add(new THREE.CylinderGeometry(0.045 * s, 0.042 * s, 0.07 * s, 10), d.trim, { pos: [0, -0.075 * s, 0] });
      mesh = g.build(mat);
    }
    mesh.matrixAutoUpdate = true;
    // Highlight ring for training (hidden by default)
    if (this.equipment !== 'gloves') {
      const ringGeo = this.equipment === 'mitts' ? new THREE.TorusGeometry(0.125, 0.012, 6, 28) : new THREE.TorusGeometry(0.2, 0.014, 6, 28);
      const ring = new THREE.Mesh(ringGeo, new THREE.MeshBasicMaterial({ color: 0xffd23f, transparent: true, opacity: 0.95, depthWrite: false }));
      if (this.equipment === 'pads') ring.scale.set(0.75, 1.15, 1);
      ring.position.z = 0.055;
      ring.visible = false;
      mesh.add(ring);
      mesh.userData.ring = ring;
    }
    return mesh;
  }

  setHighlight(side, on, color = 0xffd23f) {
    const ring = this.mHand[side].userData.ring;
    if (!ring) return;
    ring.visible = on;
    ring.material.color.setHex(color);
  }

  /** Reset pose targets to fighting stance. guard: 0 normal, 1 high block. */
  setStance(guard = 0) {
    const s = this.s, D = this.dims;
    const headY = this.standingHeadY();
    this.hand.L.set(0.11 * s, headY - 0.07 - 0.03 * (1 - guard), 0.27 * s - 0.08 * guard);
    this.hand.R.set(-0.1 * s, headY - 0.09 + 0.02 * guard, 0.16 * s);
    if (guard > 0) { this.hand.L.x = 0.085 * s; this.hand.R.x = -0.085 * s; this.hand.R.z = 0.17 * s; }
    this.foot.L.set(0.11 * s, 0, 0.2 * s);
    this.foot.R.set(-0.14 * s, 0, -0.22 * s);
    this.armPole.L.set(0.5, -1, -0.4); this.armPole.R.set(-0.5, -1, -0.4);
    this.legPole.L.set(0.25, 0, 1); this.legPole.R.set(-0.15, 0, 1);
    this.padFace.L.set(0, 0, 1); this.padFace.R.set(0, 0, 1);
    this.kickAmt.L = 0; this.kickAmt.R = 0;
    this.handRate.L = this.handRate.R = 16;
    this.footRate.L = this.footRate.R = 16;
    this.torsoYaw = -0.3; this.hipYaw = -0.35; this.lean = 0.08 + 0.06 * guard; this.roll = 0;
    this.crouch = 0.035 + 0.02 * guard;
    this.hipShift.set(0, 0, 0);
    this.weight = 0;
    this.heel.L = this.heel.R = 0.25;
    this.footYaw.L = this.footYaw.R = 0;
    this.footFree.L = this.footFree.R = false;
    void D;
  }

  standingHeadY() { return this.dims.hipY + this.dims.torso + this.dims.neck * 0.6 + this.dims.headR * 0.9 - 0.08 * this.s; }

  setWorld(x, z, yaw) {
    this.root.position.set(x, 0, z);
    this.root.rotation.set(0, yaw, 0);
    // teleport: re-plant feet at the new spot instead of stepping across the arena
    this.plant.L.init = this.plant.R.init = false;
    this.hasLast = false; this.rootVel.set(0, 0, 0);
  }

  get pos() { return this.root.position; }
  get yaw() { return this.root.rotation.y; }

  /** World -> pivot-local (useful for aiming strikes). */
  toLocal(world, out) {
    this.pivot.updateWorldMatrix(true, false);
    _m.copy(this.pivot.matrixWorld).invert();
    return out.copy(world).applyMatrix4(_m);
  }

  /** Applies a hit reaction. dirWorld: direction the blow travels. */
  impulse(dirWorld, amount, zone = 'head') {
    _a.copy(dirWorld);
    // convert direction into pivot-local
    _q.copy(this.root.quaternion).invert();
    _a.applyQuaternion(_q);
    _a.y *= 0.4;
    if (_a.lengthSq() > 1e-6) _a.normalize();
    if (zone === 'head') { this.headSpring.kick(_a, amount * 1.4); this.bodySpring.kick(_a, amount * 0.35); }
    else { this.bodySpring.kick(_a, amount * 0.9); this.headSpring.kick(_a, amount * 0.3); }
    this.flash = 1;
  }

  update(dt, t) {
    const D = this.dims, s = this.s, j = this.j;

    // Knockdown (pivot pitches back around the feet).
    this.downAmt += (this.downTarget - this.downAmt) * damp(this.downTarget > this.downAmt ? 6 : 2.5, dt);
    this.pivot.rotation.x = -this.downAmt * 1.42;
    this.pivot.position.z = -this.downAmt * 0.1;
    this.shadow.scale.set(1, 1 + this.downAmt * 1.6, 1);
    this.shadow.position.z = -this.downAmt * 0.75;

    // Smooth scalars
    const k = damp(10, dt);
    // hips lead, shoulders follow (kinetic chain)
    this.torsoYawCur += (this.torsoYaw - this.torsoYawCur) * damp(11, dt);
    this.hipYawCur += (this.hipYaw - this.hipYawCur) * damp(17, dt);
    this.weightCur += (this.weight - this.weightCur) * damp(9, dt);
    for (const sd of ['L', 'R']) {
      this.heelCur[sd] += (this.heel[sd] - this.heelCur[sd]) * damp(14, dt);
      this.footYawCur[sd] += (this.footYaw[sd] - this.footYawCur[sd]) * damp(14, dt);
    }
    if (dt > 0) {
      if (this.hasLast) { _pw.subVectors(this.root.position, this.lastRoot).divideScalar(dt); this.rootVel.lerp(_pw, damp(10, dt)); }
      this.lastRoot.copy(this.root.position); this.hasLast = true;
    }
    this.leanCur += (this.lean - this.leanCur) * k;
    this.rollCur += (this.roll - this.rollCur) * damp(12, dt);
    this.crouchCur += (this.crouch - this.crouchCur) * k;
    this.hipShiftCur.lerp(this.hipShift, damp(10, dt));
    this.headSpring.update(dt);
    this.bodySpring.update(dt);

    // Hands approach targets; feet are planted and step (see _feet)
    for (const sd of ['L', 'R']) this.handCur[sd].lerp(this.hand[sd], damp(this.handRate[sd], dt));
    this._feet(dt);

    // ---- Spine
    const bob = Math.sin(t * 5.2) * 0.012 * this.bobAmt;
    // weight transfer: hips travel over the loaded foot
    const wx = (0.11 + 0.14) * s * this.weightCur * 0.32, wz = (0.2 + 0.22) * s * this.weightCur * 0.32;
    // up on the balls of the feet raises the hips slightly
    const heelUp = (this.heelCur.L + this.heelCur.R) * 0.012 * s;
    j.hip.set(wx - 0.015 * s, D.hipY - this.crouchCur + bob + heelUp, wz - 0.01 * s).add(this.hipShiftCur).addScaledVector(this.bodySpring.x, 0.25);
    _q.setFromEuler(_e1.set(this.leanCur, this.torsoYawCur, this.rollCur, 'YXZ'));
    // body spring tilts the torso
    const bs = this.bodySpring.x;
    _q2.setFromEuler(_e2.set(bs.z * 1.2, 0, -bs.x * 1.2));
    _q.multiply(_q2);
    j.chest.set(0, D.torso, 0).applyQuaternion(_q).add(j.hip);
    j.neck.set(0, D.torso + D.neck * 0.5, 0.01).applyQuaternion(_q).add(j.hip);
    j.head.set(0, D.torso + D.neck + D.headR * 0.75, 0.02 * s).applyQuaternion(_q).add(j.hip).add(this.headSpring.x);
    j.shL.set(D.shoulderW, D.torso - 0.05 * s, 0).applyQuaternion(_q).add(j.hip);
    j.shR.set(-D.shoulderW, D.torso - 0.05 * s, 0).applyQuaternion(_q).add(j.hip);
    const hq = _q2.setFromAxisAngle(UP, this.hipYawCur);
    j.hpL.set(D.hipW, -0.04 * s, 0).applyQuaternion(hq).add(j.hip);
    j.hpR.set(-D.hipW, -0.04 * s, 0).applyQuaternion(hq).add(j.hip);

    // ---- Arms
    // when down, arms flop out
    if (this.downAmt > 0.3) {
      this.hand.L.set(0.45 * s, j.hip.y + 0.3, 0.1); this.hand.R.set(-0.45 * s, j.hip.y + 0.3, 0.1);
    }
    solveIK(j.shL, this.handCur.L, D.upper, D.fore, this.armPole.L, j.elL, j.haL);
    solveIK(j.shR, this.handCur.R, D.upper, D.fore, this.armPole.R, j.elR, j.haR);
    // ---- Legs
    _ftL.copy(this.footCur.L); _ftL.y += (0.08 + this.heelCur.L * 0.045) * s;
    _ftR.copy(this.footCur.R); _ftR.y += (0.08 + this.heelCur.R * 0.045) * s;
    solveIK(j.hpL, _ftL, D.thigh, D.shin, this.legPole.L, j.knL, j.anL);
    solveIK(j.hpR, _ftR, D.thigh, D.shin, this.legPole.R, j.knR, j.anR);

    // ---- Place meshes
    this.mPelvis.position.copy(j.hip);
    this.mPelvis.quaternion.copy(hq);
    placeSeg(this.mTorso, j.hip, j.chest);
    this.mTorso.position.lerpVectors(j.hip, j.chest, 0.55);
    this.mTorso.quaternion.premultiply(_q2.setFromAxisAngle(_c.subVectors(j.chest, j.hip).normalize(), this.torsoYawCur));
    placeSeg(this.mNeck, j.chest, j.head);
    this.mNeck.position.lerpVectors(j.chest, j.head, 0.42);
    this.mHead.position.copy(j.head);
    this._aimHead(dt);
    for (const sd of ['L', 'R']) {
      const sh = sd === 'L' ? j.shL : j.shR, el = sd === 'L' ? j.elL : j.elR, ha = sd === 'L' ? j.haL : j.haR;
      placeSeg(this.mUpper[sd], sh, el);
      placeSeg(this.mFore[sd], el, ha);
      const hm = this.mHand[sd];
      if (this.equipment === 'gloves') {
        _c.subVectors(ha, el).normalize();
        hm.quaternion.setFromUnitVectors(UP, _c);
        hm.position.copy(ha).addScaledVector(_c, 0.035 * s);
      } else {
        hm.position.copy(ha);
        _c.copy(this.padFace[sd]).normalize();
        hm.quaternion.setFromUnitVectors(FWD, _c);
      }
      const hp = sd === 'L' ? j.hpL : j.hpR, kn = sd === 'L' ? j.knL : j.knR, an = sd === 'L' ? j.anL : j.anR;
      placeSeg(this.mThigh[sd], hp, kn);
      placeSeg(this.mShin[sd], kn, an);
      const fm = this.mFoot[sd];
      fm.position.copy(an);
      // foot: planted yaw (doesn't spin with the body) + pivot, heel raised = toes down
      const P = this.plant[sd];
      const baseYaw = (sd === 'L' ? 0.25 : 0.55) + this.footYawCur[sd];
      const yawL = P.init ? wrapA(P.yaw - this.root.rotation.y) + baseYaw : this.hipYawCur * 0.6 + baseYaw;
      _q.setFromEuler(_e1.set(this.heelCur[sd] * 0.45, yawL, 0, 'YXZ'));
      if (this.kickAmt[sd] > 0.01) {
        _c.subVectors(an, kn).normalize();
        _q2.setFromUnitVectors(FWD, _c);
        _q.slerp(_q2, this.kickAmt[sd]);
      }
      fm.quaternion.copy(_q);
    }

    // ---- World outputs
    this.pivot.updateWorldMatrix(true, false);
    const mw = this.pivot.matrixWorld;
    this.headW.copy(j.head).applyMatrix4(mw);
    this.chestW.lerpVectors(j.hip, j.chest, 0.78).applyMatrix4(mw);
    this.bellyW.lerpVectors(j.hip, j.chest, 0.32).applyMatrix4(mw);
    for (const sd of ['L', 'R']) {
      this.glove[sd].copy(this.mHand[sd].position).applyMatrix4(mw);
      this.elbowW[sd].copy(sd === 'L' ? j.elL : j.elR).applyMatrix4(mw);
      this.footW[sd].copy(sd === 'L' ? j.anL : j.anR).applyMatrix4(mw);
      this.kneeW[sd].copy(sd === 'L' ? j.knL : j.knR).applyMatrix4(mw);
      this.thighW[sd].lerpVectors(sd === 'L' ? j.hpL : j.hpR, sd === 'L' ? j.knL : j.knR, 0.55).applyMatrix4(mw);
    }
  }

  /** Planted-foot stepping: feet stay put on the canvas and step when the body leaves them behind. */
  _feet(dt) {
    const sides = ['L', 'R'];
    const usePlant = this.planting && this.downAmt < 0.05;
    this.pivot.updateWorldMatrix(true, false);
    const mw = this.pivot.matrixWorld;
    _mInv.copy(mw).invert();
    const rootYaw = this.root.rotation.y;
    const want = {};
    for (const sd of sides) {
      const P = this.plant[sd];
      if (!usePlant || this.footFree[sd]) {
        this.footCur[sd].lerp(this.foot[sd], damp(this.footRate[sd], dt));
        P.init = false;
        continue;
      }
      if (!P.init) {
        // (re)plant where the foot currently is, e.g. after a kick lands back down
        P.pos.copy(this.footCur[sd]).setY(0).applyMatrix4(mw); P.pos.y = 0;
        P.yaw = rootYaw; P.t = 1; P.init = true;
      }
      _pw.copy(this.foot[sd]).setY(0).applyMatrix4(mw); _pw.y = 0;
      want[sd] = { pos: _pw.clone(), err: Math.hypot(_pw.x - P.pos.x, _pw.z - P.pos.z), yawErr: Math.abs(wrapA(rootYaw - P.yaw)) };
    }
    // start at most one step at a time; the foot that is furthest out of place goes first
    const busy = sides.some((sd) => want[sd] && this.plant[sd].t < 1);
    if (!busy) {
      let pick = null, best = 0;
      for (const sd of sides) {
        const w = want[sd];
        if (!w) continue;
        const need = w.err / 0.085 + w.yawErr / 0.45;
        if (need > 1 && need > best) { best = need; pick = sd; }
      }
      if (pick) {
        const P = this.plant[pick], w = want[pick];
        P.from.copy(P.pos); P.fromYaw = P.yaw;
        // anticipate body motion so the foot lands under where the hips are going
        _pw.copy(this.rootVel).multiplyScalar(0.12); _pw.y = 0;
        if (_pw.length() > 0.12) _pw.setLength(0.12);
        P.to.copy(w.pos).add(_pw); P.to.y = 0;
        P.toYaw = rootYaw;
        P.dur = clamp(0.14 + w.err * 0.3, 0.14, 0.26);
        P.lift = clamp(0.03 + w.err * 0.12, 0.03, 0.08) * this.s;
        P.t = 0;
      }
    }
    for (const sd of sides) {
      const P = this.plant[sd];
      if (!want[sd]) continue;
      let lift = 0;
      if (P.t < 1) {
        P.t = Math.min(1, P.t + dt / P.dur);
        const e = ssm(P.t);
        P.pos.lerpVectors(P.from, P.to, e);
        P.yaw = P.fromYaw + wrapA(P.toYaw - P.fromYaw) * e;
        lift = Math.sin(P.t * Math.PI) * P.lift;
      }
      this.footCur[sd].copy(P.pos).applyMatrix4(_mInv);
      this.footCur[sd].y = this.foot[sd].y + lift;
      if (P.t < 1) this.heelCur[sd] = Math.max(this.heelCur[sd], 0.5);
    }
  }

  /** Is a foot currently mid-step? */
  stepping() { return this.plant.L.t < 1 || this.plant.R.t < 1; }

  _aimHead(dt) {
    const head = this.mHead;
    const target = this.lookTarget;
    let yaw = this.torsoYawCur * 0.3, pitch = 0;
    if (target && this.downAmt < 0.2) {
      this.toLocal(target, _d);
      _d.sub(this.j.head);
      yaw = clamp(Math.atan2(_d.x, _d.z), -1.1, 1.1);
      pitch = clamp(-Math.atan2(_d.y, Math.hypot(_d.x, _d.z)), -0.6, 0.6);
    }
    const hs = this.headSpring.x;
    _q.setFromEuler(_e3.set(pitch + hs.z * 2.2 - hs.y, yaw + hs.x * 1.5, -hs.x * 2.0, 'YXZ'));
    head.quaternion.slerp(_q, damp(14, dt));
  }

  /** Places local-space target (pivot frame) from a world point. */
  worldToLocalTarget(world, out) { return this.toLocal(world, out); }

  dispose() {
    this.root.traverse((o) => { if (o.geometry) o.geometry.dispose(); });
    if (this.root.parent) this.root.parent.remove(this.root);
  }
}
