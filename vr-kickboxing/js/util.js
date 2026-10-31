import * as THREE from 'three';
import { mergeGeometries } from '../vendor/BufferGeometryUtils.js';

export const clamp = (v, a, b) => (v < a ? a : v > b ? b : v);
export const lerp = (a, b, t) => a + (b - a) * t;
export const smoothstep = (a, b, v) => { const t = clamp((v - a) / (b - a), 0, 1); return t * t * (3 - 2 * t); };
export const rand = (a, b) => a + Math.random() * (b - a);
export const randInt = (a, b) => Math.floor(rand(a, b + 1));
export const chance = (p) => Math.random() < p;
/** Frame-rate independent exponential damping factor. */
export const damp = (lambda, dt) => 1 - Math.exp(-lambda * dt);
export const easeOut = (t) => 1 - (1 - t) * (1 - t);
export const easeIn = (t) => t * t;
export const easeInOut = (t) => (t < 0.5 ? 2 * t * t : 1 - Math.pow(-2 * t + 2, 2) / 2);

export function weightedPick(weights) {
  let total = 0;
  for (const k in weights) total += Math.max(0, weights[k]);
  let r = Math.random() * total;
  for (const k in weights) { r -= Math.max(0, weights[k]); if (r <= 0) return k; }
  return Object.keys(weights)[0];
}

const _ab = new THREE.Vector3(), _ap = new THREE.Vector3();
/** Closest distance from point p to segment ab. Writes closest point into out if provided. */
export function distPointSegment(p, a, b, out) {
  _ab.subVectors(b, a);
  _ap.subVectors(p, a);
  const len2 = _ab.lengthSq();
  const t = len2 > 1e-9 ? clamp(_ap.dot(_ab) / len2, 0, 1) : 0;
  const cx = a.x + _ab.x * t, cy = a.y + _ab.y * t, cz = a.z + _ab.z * t;
  if (out) out.set(cx, cy, cz);
  const dx = p.x - cx, dy = p.y - cy, dz = p.z - cz;
  return Math.sqrt(dx * dx + dy * dy + dz * dz);
}

/** Horizontal (XZ) distance. */
export const distXZ = (a, b) => Math.hypot(a.x - b.x, a.z - b.z);

/** Yaw angle (radians) of a direction on XZ, 0 = facing -Z (three.js camera default). */
export const yawOf = (dx, dz) => Math.atan2(-dx, -dz);

export function wrapAngle(a) {
  while (a > Math.PI) a -= Math.PI * 2;
  while (a < -Math.PI) a += Math.PI * 2;
  return a;
}

// ---------------------------------------------------------------------------
// Static geometry batching: build parts with vertex colors, merge into one mesh
// per material => very few draw calls (critical for Quest frame rate).

export class GeoBatch {
  constructor() { this.parts = []; }
  /** Adds a geometry (consumed) with a color and optional transform. */
  add(geo, color, { pos, rot, scale, matrix } = {}) {
    let g = geo;
    if (!g.index) g = indexify(g);
    if (matrix) g.applyMatrix4(matrix);
    else {
      const m = new THREE.Matrix4();
      const q = new THREE.Quaternion();
      if (rot) q.setFromEuler(rot instanceof THREE.Euler ? rot : new THREE.Euler(rot[0] || 0, rot[1] || 0, rot[2] || 0, rot[3] || 'YXZ'));
      const s = scale ? (typeof scale === 'number' ? new THREE.Vector3(scale, scale, scale) : new THREE.Vector3(...scale)) : new THREE.Vector3(1, 1, 1);
      const p = pos ? new THREE.Vector3(...pos) : new THREE.Vector3();
      m.compose(p, q, s);
      g.applyMatrix4(m);
    }
    const n = g.attributes.position.count;
    const c = new THREE.Color(color);
    const arr = new Float32Array(n * 3);
    for (let i = 0; i < n; i++) { arr[i * 3] = c.r; arr[i * 3 + 1] = c.g; arr[i * 3 + 2] = c.b; }
    g.setAttribute('color', new THREE.BufferAttribute(arr, 3));
    if (!g.attributes.uv) {
      g.setAttribute('uv', new THREE.BufferAttribute(new Float32Array(n * 2), 2));
    }
    for (const name of Object.keys(g.attributes)) {
      if (!['position', 'normal', 'color', 'uv'].includes(name)) g.deleteAttribute(name);
    }
    if (!g.attributes.normal) g.computeVertexNormals();
    g.morphAttributes = {};
    this.parts.push(g);
    return this;
  }
  build(material) {
    if (!this.parts.length) return new THREE.Group();
    const merged = mergeGeometries(this.parts, false);
    for (const p of this.parts) p.dispose();
    this.parts = [];
    merged.computeBoundingSphere();
    const mesh = new THREE.Mesh(merged, material);
    mesh.matrixAutoUpdate = false;
    mesh.updateMatrix();
    return mesh;
  }
}

function indexify(g) {
  const n = g.attributes.position.count;
  const idx = new Array(n);
  for (let i = 0; i < n; i++) idx[i] = i;
  g.setIndex(idx);
  return g;
}

// ---------------------------------------------------------------------------
// Canvas helpers

export function roundRect(ctx, x, y, w, h, r) {
  const rr = Math.min(r, w / 2, h / 2);
  ctx.beginPath();
  ctx.moveTo(x + rr, y);
  ctx.arcTo(x + w, y, x + w, y + h, rr);
  ctx.arcTo(x + w, y + h, x, y + h, rr);
  ctx.arcTo(x, y + h, x, y, rr);
  ctx.arcTo(x, y, x + w, y, rr);
  ctx.closePath();
}

export function canvasTexture(w, h, draw, { repeat, srgb = true, mipmaps = true } = {}) {
  const c = document.createElement('canvas');
  c.width = w; c.height = h;
  const ctx = c.getContext('2d');
  draw(ctx, w, h);
  const t = new THREE.CanvasTexture(c);
  if (srgb) t.colorSpace = THREE.SRGBColorSpace;
  if (repeat) { t.wrapS = t.wrapT = THREE.RepeatWrapping; t.repeat.set(repeat[0], repeat[1]); }
  t.anisotropy = 4;
  t.generateMipmaps = mipmaps;
  if (!mipmaps) t.minFilter = THREE.LinearFilter;
  return t;
}

export const FONT = '"Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif';

/** Soft round shadow texture shared by all characters. */
let _shadowTex = null;
export function shadowTexture() {
  if (_shadowTex) return _shadowTex;
  _shadowTex = canvasTexture(128, 128, (ctx, w, h) => {
    const g = ctx.createRadialGradient(w / 2, h / 2, 0, w / 2, h / 2, w / 2);
    g.addColorStop(0, 'rgba(0,0,0,0.55)');
    g.addColorStop(0.55, 'rgba(0,0,0,0.3)');
    g.addColorStop(1, 'rgba(0,0,0,0)');
    ctx.fillStyle = g; ctx.fillRect(0, 0, w, h);
  }, { srgb: false });
  return _shadowTex;
}

export function makeBlobShadow(size = 0.9) {
  const m = new THREE.Mesh(
    new THREE.PlaneGeometry(size, size),
    new THREE.MeshBasicMaterial({ map: shadowTexture(), transparent: true, depthWrite: false })
  );
  m.rotation.x = -Math.PI / 2;
  m.position.y = 0.004;
  m.renderOrder = 1;
  return m;
}
