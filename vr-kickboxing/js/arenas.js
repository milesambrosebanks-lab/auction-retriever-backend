// Fight environments. Everything static is merged per material (GeoBatch) and
// crowds are a single InstancedMesh animated on the GPU, keeping each arena to a
// handful of draw calls for a solid 90/120 Hz on Quest.

import * as THREE from 'three';
import { GeoBatch, canvasTexture, FONT, roundRect, rand, clamp } from './util.js';

const matVC = () => new THREE.MeshLambertMaterial({ vertexColors: true, side: THREE.DoubleSide });
const matGlow = () => new THREE.MeshBasicMaterial({ vertexColors: true, fog: false });

export const crowdUniforms = { uTime: { value: 0 }, uExcite: { value: 0 } };

// ---------------------------------------------------------------------------
// Shared pieces

function skyDome(top, horizon, bottom) {
  const geo = new THREE.SphereGeometry(160, 24, 14);
  const pos = geo.attributes.position;
  const col = new Float32Array(pos.count * 3);
  const ct = new THREE.Color(top), ch = new THREE.Color(horizon), cb = new THREE.Color(bottom), c = new THREE.Color();
  for (let i = 0; i < pos.count; i++) {
    const y = pos.getY(i) / 160;
    if (y >= 0) c.lerpColors(ch, ct, Math.pow(y, 0.6)); else c.lerpColors(ch, cb, Math.min(1, -y * 3));
    col[i * 3] = c.r; col[i * 3 + 1] = c.g; col[i * 3 + 2] = c.b;
  }
  geo.setAttribute('color', new THREE.BufferAttribute(col, 3));
  const m = new THREE.Mesh(geo, new THREE.MeshBasicMaterial({ vertexColors: true, side: THREE.BackSide, depthWrite: false, fog: false }));
  m.renderOrder = -10;
  m.frustumCulled = false;
  return m;
}

function stars(count = 500, radius = 140) {
  const p = new Float32Array(count * 3);
  for (let i = 0; i < count; i++) {
    const th = Math.random() * Math.PI * 2, ph = Math.acos(rand(0.12, 1));
    p[i * 3] = Math.sin(ph) * Math.cos(th) * radius;
    p[i * 3 + 1] = Math.cos(ph) * radius;
    p[i * 3 + 2] = Math.sin(ph) * Math.sin(th) * radius;
  }
  const g = new THREE.BufferGeometry();
  g.setAttribute('position', new THREE.BufferAttribute(p, 3));
  const pts = new THREE.Points(g, new THREE.PointsMaterial({ color: 0xffffff, size: 2, sizeAttenuation: false, fog: false, transparent: true, opacity: 0.8 }));
  pts.renderOrder = -9;
  return pts;
}

/** Instanced crowd. spots: [{x,y,z,face}] ; face = yaw */
function crowd(spots, { scale = 1, palette } = {}) {
  const b = new GeoBatch();
  b.add(new THREE.BoxGeometry(0.42, 0.55, 0.26), 0xffffff, { pos: [0, 0.3, 0] });
  b.add(new THREE.BoxGeometry(0.2, 0.22, 0.2), 0xe0b090, { pos: [0, 0.7, 0] });
  b.add(new THREE.BoxGeometry(0.1, 0.42, 0.1), 0xffffff, { pos: [0.26, 0.42, 0.04], rot: [0.3, 0, 0.2] });
  b.add(new THREE.BoxGeometry(0.1, 0.42, 0.1), 0xffffff, { pos: [-0.26, 0.42, 0.04], rot: [0.3, 0, -0.2] });
  const proto = b.build(null);
  const geo = proto.geometry;
  const mat = new THREE.MeshLambertMaterial({ vertexColors: true });
  mat.onBeforeCompile = (shader) => {
    shader.uniforms.uTime = crowdUniforms.uTime;
    shader.uniforms.uExcite = crowdUniforms.uExcite;
    shader.vertexShader = 'uniform float uTime;\nuniform float uExcite;\n' + shader.vertexShader.replace(
      '#include <color_vertex>',
      `#include <color_vertex>
       // heads (non-white vertex color) get a skin tone instead of the shirt tint
       if (color.b < 0.9) { float sk = fract(sin(float(gl_InstanceID) * 78.233) * 12345.678); vColor.xyz = color.xyz * (0.35 + 0.8 * sk); }`
    ).replace(
      '#include <begin_vertex>',
      `#include <begin_vertex>
       float hid = fract(sin(float(gl_InstanceID) * 12.9898) * 43758.5453);
       float jump = max(0.0, sin(uTime * (4.0 + hid * 4.0) + hid * 6.2831));
       float cheer = step(0.55 - uExcite * 0.6, hid);
       transformed.y += jump * (0.02 + uExcite * 0.16) * cheer;
       if (transformed.y > 0.5 && abs(transformed.x) > 0.2) { transformed.y += (uExcite * 0.25 + 0.02) * jump * cheer; }`
    );
  };
  const pal = palette || [0xd62828, 0x1d3557, 0xf1faee, 0x2a9d8f, 0xe9c46a, 0x264653, 0x6d597a, 0x111111, 0x3a86ff, 0xff8800, 0x8d99ae];
  const skins = [0xf1c27d, 0xe0ac69, 0xc68642, 0x8d5524, 0x5a3a26, 0xffdbac];
  const im = new THREE.InstancedMesh(geo, mat, spots.length);
  const m = new THREE.Matrix4(), q = new THREE.Quaternion(), sc = new THREE.Vector3(), p = new THREE.Vector3(), c = new THREE.Color();
  spots.forEach((s, i) => {
    q.setFromAxisAngle(new THREE.Vector3(0, 1, 0), s.face);
    const k = scale * rand(0.9, 1.1);
    sc.set(k, k, k);
    p.set(s.x, s.y, s.z);
    m.compose(p, q, sc);
    im.setMatrixAt(i, m);
    // mix shirt color with skin tone (head is pre-colored in geometry, tinted by instance color)
    c.setHex(pal[(Math.random() * pal.length) | 0]);
    if (Math.random() < 0.2) c.lerp(new THREE.Color(skins[(Math.random() * skins.length) | 0]), 0.5);
    im.setColorAt(i, c);
  });
  im.instanceMatrix.needsUpdate = true;
  if (im.instanceColor) im.instanceColor.needsUpdate = true;
  im.computeBoundingSphere();
  return im;
}

/** Seats on concentric tier rows (circle or square), facing the center. */
function bowlSpots({ shape = 'circle', r0, rows, rowDepth = 0.9, rowRise = 0.45, y0 = 0, density = 0.85, gapAngles = [], spacing = 0.62 }) {
  const out = [];
  for (let r = 0; r < rows; r++) {
    const rad = r0 + r * rowDepth;
    const y = y0 + r * rowRise;
    if (shape === 'circle') {
      const n = Math.floor((Math.PI * 2 * rad) / spacing);
      for (let i = 0; i < n; i++) {
        const a = (i / n) * Math.PI * 2;
        if (gapAngles.some(([g, w]) => Math.abs(Math.atan2(Math.sin(a - g), Math.cos(a - g))) < w)) continue;
        if (Math.random() > density) continue;
        const x = Math.cos(a) * rad, z = Math.sin(a) * rad;
        out.push({ x, y, z, face: Math.atan2(-x, -z) });
      }
    } else {
      const n = Math.floor((rad * 2) / spacing);
      for (let sIdx = 0; sIdx < 4; sIdx++) {
        for (let i = 0; i < n; i++) {
          if (Math.random() > density) continue;
          const u = -rad + (i + 0.5) * spacing;
          let x, z;
          if (sIdx === 0) { x = u; z = -rad; } else if (sIdx === 1) { x = rad; z = u; } else if (sIdx === 2) { x = -u; z = rad; } else { x = -rad; z = -u; }
          if (gapAngles.length && sIdx === 2 && Math.abs(u) < 1.6) continue;
          const face = sIdx === 0 ? 0 : sIdx === 1 ? Math.PI / 2 : sIdx === 2 ? Math.PI : -Math.PI / 2;
          out.push({ x, y, z, face: face + Math.PI });
        }
      }
    }
  }
  return out;
}

function textTexture(w, h, draw) { return canvasTexture(w, h, draw, {}); }

function logoMat(text, bg, fg, accent) {
  return textTexture(1024, 1024, (ctx, w, h) => {
    ctx.fillStyle = bg; ctx.fillRect(0, 0, w, h);
    // subtle canvas weave
    ctx.globalAlpha = 0.05;
    for (let i = 0; i < 1500; i++) { ctx.fillStyle = Math.random() < 0.5 ? '#000' : '#fff'; ctx.fillRect(Math.random() * w, Math.random() * h, 3, 3); }
    ctx.globalAlpha = 1;
    ctx.strokeStyle = accent; ctx.lineWidth = 14;
    ctx.beginPath(); ctx.arc(w / 2, h / 2, w * 0.18, 0, Math.PI * 2); ctx.stroke();
    ctx.fillStyle = fg; ctx.textAlign = 'center'; ctx.textBaseline = 'middle';
    ctx.font = `900 ${Math.round(w * 0.075)}px ${FONT}`;
    ctx.fillText(text, w / 2, h / 2);
    ctx.font = `700 ${Math.round(w * 0.03)}px ${FONT}`;
    ctx.globalAlpha = 0.7;
    ctx.fillText('KICKBOXING CHAMPIONSHIP', w / 2, h / 2 + w * 0.07);
    ctx.globalAlpha = 0.35;
    ctx.font = `900 ${Math.round(w * 0.05)}px ${FONT}`;
    ctx.fillText('VR', w / 2, h * 0.2);
    ctx.fillText('VR', w / 2, h * 0.8);
  });
}

function lightCone(x, y, z, r, h, color, opacity = 0.06) {
  const g = new THREE.ConeGeometry(r, h, 20, 1, true);
  g.translate(0, -h / 2, 0);
  const m = new THREE.Mesh(g, new THREE.MeshBasicMaterial({ color, transparent: true, opacity, blending: THREE.AdditiveBlending, depthWrite: false, side: THREE.DoubleSide, fog: false }));
  m.position.set(x, y, z);
  m.renderOrder = 5;
  return m;
}

function chainLinkTexture() {
  return canvasTexture(128, 128, (ctx, w, h) => {
    ctx.clearRect(0, 0, w, h);
    ctx.strokeStyle = 'rgba(30,30,30,1)'; ctx.lineWidth = 7;
    ctx.beginPath();
    ctx.moveTo(0, h / 2); ctx.lineTo(w / 2, 0); ctx.lineTo(w, h / 2); ctx.lineTo(w / 2, h); ctx.closePath();
    ctx.stroke();
    ctx.strokeStyle = 'rgba(70,70,70,1)'; ctx.lineWidth = 3; ctx.stroke();
  }, { srgb: true });
}

/** Square ring with posts, ropes, turnbuckles and apron. Returns group. */
function buildRing({ half = 2.8, floorTex, apronColor = 0x111827, ropeColors = [0xd62828, 0xf1f1f1, 0x1d4ed8, 0xd62828], platformDrop = 1.0, logo = 'IRON SHIN' }) {
  const g = new THREE.Group();
  const floor = new THREE.Mesh(new THREE.PlaneGeometry(half * 2 + 0.6, half * 2 + 0.6), new THREE.MeshLambertMaterial({ map: floorTex }));
  floor.rotation.x = -Math.PI / 2;
  g.add(floor);
  const b = new GeoBatch();
  // apron skirt
  const apronTex = textTexture(1024, 128, (ctx, w, h) => {
    ctx.fillStyle = '#' + new THREE.Color(apronColor).getHexString(); ctx.fillRect(0, 0, w, h);
    ctx.fillStyle = '#ffd23f'; ctx.fillRect(0, 0, w, 8); ctx.fillRect(0, h - 8, w, 8);
    ctx.fillStyle = '#fff'; ctx.font = `900 64px ${FONT}`; ctx.textAlign = 'center'; ctx.textBaseline = 'middle';
    ctx.fillText(logo, w / 2, h / 2 + 4);
  });
  const skirt = new THREE.Mesh(new THREE.CylinderGeometry((half + 0.3) * Math.SQRT2, (half + 0.3) * Math.SQRT2, platformDrop, 4, 1, true), new THREE.MeshLambertMaterial({ map: apronTex }));
  skirt.rotation.y = Math.PI / 4;
  skirt.position.y = -platformDrop / 2 - 0.01;
  g.add(skirt);
  const cornerCols = [0xd62828, 0xf1f1f1, 0x1d4ed8, 0xf1f1f1];
  const corners = [[-half, -half], [half, -half], [half, half], [-half, half]];
  corners.forEach(([x, z], i) => {
    b.add(new THREE.CylinderGeometry(0.06, 0.07, 1.6, 10), 0x9ca3af, { pos: [x, 0.8, z] });
    b.add(new THREE.BoxGeometry(0.2, 1.05, 0.2), cornerCols[i], { pos: [x, 0.83, z], rot: [0, Math.PI / 4, 0] });
  });
  const heights = [0.42, 0.75, 1.08, 1.38];
  for (let s = 0; s < 4; s++) {
    const [x0, z0] = corners[s], [x1, z1] = corners[(s + 1) % 4];
    const len = Math.hypot(x1 - x0, z1 - z0);
    const ang = Math.atan2(x1 - x0, z1 - z0);
    heights.forEach((y, k) => {
      b.add(new THREE.CylinderGeometry(0.022, 0.022, len, 6), ropeColors[k % ropeColors.length], { pos: [(x0 + x1) / 2, y, (z0 + z1) / 2], rot: [Math.PI / 2, ang, 0] });
    });
    // rope spacers
    b.add(new THREE.BoxGeometry(0.05, 1.0, 0.05), 0xf1f1f1, { pos: [(x0 + x1) / 2, 0.9, (z0 + z1) / 2] });
  }
  // steps in two corners
  for (const [x, z] of [corners[0], corners[2]]) {
    for (let i = 0; i < 3; i++) b.add(new THREE.BoxGeometry(0.7, 0.3, 0.35), 0x374151, { pos: [x * 1.15, -platformDrop + 0.15 + i * 0.3 - 0.0, z * 1.15 + Math.sign(z) * (0.9 - i * 0.3)] });
  }
  g.add(b.build(matVC()));
  return g;
}

function floorTex(colorA, text, accent = '#ffd23f') {
  return textTexture(1024, 1024, (ctx, w, h) => {
    ctx.fillStyle = colorA; ctx.fillRect(0, 0, w, h);
    ctx.globalAlpha = 0.06;
    for (let i = 0; i < 2000; i++) { ctx.fillStyle = Math.random() < 0.5 ? '#000' : '#fff'; ctx.fillRect(Math.random() * w, Math.random() * h, 3, 3); }
    ctx.globalAlpha = 1;
    ctx.strokeStyle = 'rgba(255,255,255,0.25)'; ctx.lineWidth = 10; ctx.strokeRect(40, 40, w - 80, h - 80);
    ctx.fillStyle = 'rgba(255,255,255,0.85)'; ctx.textAlign = 'center'; ctx.textBaseline = 'middle';
    ctx.font = `900 ${w * 0.09}px ${FONT}`; ctx.fillText(text, w / 2, h / 2);
    ctx.fillStyle = accent; ctx.font = `800 ${w * 0.03}px ${FONT}`; ctx.fillText('★  KICKBOXING  ★', w / 2, h / 2 + w * 0.08);
  });
}

// ---------------------------------------------------------------------------
// Arenas

function octagon() {
  const grp = new THREE.Group();
  const R = 4.1;              // circumradius of the cage
  const off = Math.PI / 8;    // rotate so flat sides face the axes
  const mat = new THREE.Mesh(new THREE.CircleGeometry(R + 0.2, 8, off), new THREE.MeshLambertMaterial({ map: logoMat('IRON SHIN', '#e9e7e2', '#1f2937', '#d62828') }));
  mat.rotation.x = -Math.PI / 2;
  grp.add(mat);

  const b = new GeoBatch();
  const verts = [];
  for (let i = 0; i < 8; i++) { const a = off + (i / 8) * Math.PI * 2; verts.push([Math.cos(a) * R, Math.sin(a) * R]); }
  // posts + top/bottom pads
  for (let i = 0; i < 8; i++) {
    const [x, z] = verts[i];
    b.add(new THREE.CylinderGeometry(0.075, 0.075, 1.95, 10), 0x111111, { pos: [x, 0.975, z] });
    const [x1, z1] = verts[(i + 1) % 8];
    const len = Math.hypot(x1 - x, z1 - z), ang = Math.atan2(x1 - x, z1 - z);
    b.add(new THREE.CylinderGeometry(0.07, 0.07, len, 8), 0x161616, { pos: [(x + x1) / 2, 1.88, (z + z1) / 2], rot: [Math.PI / 2, ang, 0] });
    b.add(new THREE.BoxGeometry(0.08, 0.16, len), 0x0d0d0d, { pos: [(x + x1) / 2, 0.08, (z + z1) / 2], rot: [0, ang, 0] });
  }
  // platform skirt + arena floor
  b.add(new THREE.CylinderGeometry(R + 0.25, R + 0.25, 1.0, 8, 1, true), 0x1a1a1a, { pos: [0, -0.5, 0], rot: [0, off, 0] });
  b.add(new THREE.CylinderGeometry(R + 0.27, R + 0.27, 0.12, 8, 1, true), 0xd62828, { pos: [0, -0.15, 0], rot: [0, off, 0] });
  b.add(new THREE.CircleGeometry(40, 32), 0x0b0d12, { pos: [0, -1, 0], rot: [-Math.PI / 2, 0, 0] });
  // seating tiers (stepped)
  for (let r = 0; r < 16; r++) {
    const rad = 7.5 + r * 0.9;
    b.add(new THREE.CylinderGeometry(rad + 0.45, rad + 0.45, 0.45, 48, 1, true), r % 2 ? 0x161a22 : 0x1b202a, { pos: [0, -1 + r * 0.45 + 0.22, 0] });
    b.add(new THREE.RingGeometry(rad - 0.45, rad + 0.45, 48), 0x12151c, { pos: [0, -1 + (r + 1) * 0.45, 0], rot: [-Math.PI / 2, 0, 0] });
  }
  // light rig
  b.add(new THREE.BoxGeometry(9, 0.25, 0.25), 0x222222, { pos: [0, 7, -4.5] });
  b.add(new THREE.BoxGeometry(9, 0.25, 0.25), 0x222222, { pos: [0, 7, 4.5] });
  b.add(new THREE.BoxGeometry(0.25, 0.25, 9), 0x222222, { pos: [-4.5, 7, 0] });
  b.add(new THREE.BoxGeometry(0.25, 0.25, 9), 0x222222, { pos: [4.5, 7, 0] });
  grp.add(b.build(matVC()));

  const glow = new GeoBatch();
  for (const [x, z] of [[-3, -4.5], [0, -4.5], [3, -4.5], [-3, 4.5], [0, 4.5], [3, 4.5], [-4.5, 0], [4.5, 0]]) {
    glow.add(new THREE.BoxGeometry(1.2, 0.12, 0.6), 0xffffff, { pos: [x, 6.85, z] });
  }
  glow.add(new THREE.RingGeometry(R + 0.27, R + 0.3, 8, 1, off), 0xd62828, { pos: [0, -0.09, 0], rot: [-Math.PI / 2, 0, 0] });
  grp.add(glow.build(matGlow()));

  // chain-link fence (transparent, mipmapped => no shimmer)
  const tex = chainLinkTexture();
  const fenceGeoParts = new GeoBatch();
  for (let i = 0; i < 8; i++) {
    const [x, z] = verts[i], [x1, z1] = verts[(i + 1) % 8];
    const len = Math.hypot(x1 - x, z1 - z), ang = Math.atan2(x1 - x, z1 - z);
    const pg = new THREE.PlaneGeometry(len, 1.7);
    const uv = pg.attributes.uv;
    for (let k = 0; k < uv.count; k++) uv.setXY(k, uv.getX(k) * len / 0.085, uv.getY(k) * 1.7 / 0.085);
    fenceGeoParts.add(pg, 0xffffff, { pos: [(x + x1) / 2, 1.0, (z + z1) / 2], rot: [0, ang + Math.PI / 2, 0] });
  }
  tex.wrapS = tex.wrapT = THREE.RepeatWrapping;
  const fence = fenceGeoParts.build(new THREE.MeshLambertMaterial({ map: tex, transparent: true, side: THREE.DoubleSide, depthWrite: false, alphaTest: 0.02 }));
  fence.renderOrder = 2;
  grp.add(fence);

  grp.add(crowd(bowlSpots({ r0: 7.6, rows: 15, y0: -0.55, rowDepth: 0.9, rowRise: 0.45, density: 0.8, gapAngles: [[Math.PI / 2, 0.12]] })));
  for (const [x, z] of [[-2.5, -2.5], [2.5, 2.5], [2.5, -2.5], [-2.5, 2.5]]) grp.add(lightCone(x * 0.6, 6.8, z * 0.6, 1.6, 6.8, 0xffffff, 0.035));

  return {
    group: grp,
    sky: [0x05070b, 0x0a0d14, 0x05070b],
    lights: { sky: 0xdfe8ff, ground: 0x30302a, hemi: 1.15, dir: 0xffffff, dirI: 1.6, dirPos: [1, 6, 2] },
    fog: { color: 0x05070b, near: 14, far: 40 },
    ambience: 'crowd',
    bounds: { type: 'circle', r: 3.35 },
  };
}

function ring() {
  const grp = new THREE.Group();
  grp.add(buildRing({ half: 2.8, floorTex: floorTex('#24426e', 'IRON SHIN') }));
  const b = new GeoBatch();
  // hall
  b.add(new THREE.PlaneGeometry(40, 40), 0x2a2420, { pos: [0, -1, 0], rot: [-Math.PI / 2, 0, 0] });
  for (const [x, z, ry] of [[0, -16, 0], [0, 16, Math.PI], [-16, 0, Math.PI / 2], [16, 0, -Math.PI / 2]]) {
    b.add(new THREE.PlaneGeometry(32, 14), 0x3a2f2a, { pos: [x, 6, z], rot: [0, ry, 0] });
    b.add(new THREE.PlaneGeometry(32, 1.2), 0x5a1d1d, { pos: [x * 0.999, 2.4, z * 0.999], rot: [0, ry, 0] });
  }
  // ringside chairs and press tables
  for (let i = -3; i <= 3; i++) {
    b.add(new THREE.BoxGeometry(1.4, 0.75, 0.6), 0x1f2937, { pos: [i * 1.6, -0.62, -4.4] });
  }
  // bleachers (square rows)
  for (let r = 0; r < 8; r++) {
    const rad = 6.5 + r * 0.9;
    for (const [x, z, w, d] of [[0, -rad, rad * 2, 0.9], [0, rad, rad * 2, 0.9], [-rad, 0, 0.9, rad * 2], [rad, 0, 0.9, rad * 2]]) {
      b.add(new THREE.BoxGeometry(w, 0.45 * (r + 1), d), r % 2 ? 0x2b2522 : 0x312a26, { pos: [x, -1 + 0.225 * (r + 1), z] });
    }
  }
  // overhead lamp
  b.add(new THREE.BoxGeometry(4, 0.4, 4), 0x151515, { pos: [0, 5.5, 0] });
  for (const x of [-1.8, 1.8]) for (const z of [-1.8, 1.8]) b.add(new THREE.CylinderGeometry(0.02, 0.02, 10, 4), 0x333333, { pos: [x, 10.5, z] });
  grp.add(b.build(matVC()));
  const glow = new GeoBatch();
  glow.add(new THREE.PlaneGeometry(3.6, 3.6), 0xfff4d6, { pos: [0, 5.29, 0], rot: [Math.PI / 2, 0, 0] });
  // banners
  const cols = [0xd62828, 0x1d4ed8, 0xffd23f, 0x1b8a3c];
  for (let i = 0; i < 8; i++) glow.add(new THREE.PlaneGeometry(1.5, 3), cols[i % 4], { pos: [-10.5 + i * 3, 9, -15.9] });
  grp.add(glow.build(matGlow()));
  grp.add(lightCone(0, 5.3, 0, 3.2, 5.3, 0xfff1c9, 0.05));
  grp.add(crowd(bowlSpots({ shape: 'square', r0: 6.5, rows: 8, y0: -0.55, rowDepth: 0.9, rowRise: 0.45, density: 0.55 }), {}));
  return {
    group: grp,
    sky: [0x0a0807, 0x14100d, 0x0a0807],
    lights: { sky: 0xfff1d6, ground: 0x3a2c22, hemi: 1.0, dir: 0xfff1c9, dirI: 1.7, dirPos: [0, 6, 1] },
    fog: { color: 0x120e0b, near: 10, far: 32 },
    ambience: 'small',
    bounds: { type: 'square', h: 2.45 },
  };
}

function stadium() {
  const grp = new THREE.Group();
  grp.add(buildRing({ half: 3.0, floorTex: floorTex('#0f0f12', 'MAIN EVENT', '#ff3b30'), apronColor: 0x7f1d1d, ropeColors: [0xffd23f, 0xffffff, 0xffd23f, 0xffffff], platformDrop: 1.4 }));
  const b = new GeoBatch();
  b.add(new THREE.CircleGeometry(80, 48), 0x0e1a12, { pos: [0, -1.4, 0], rot: [-Math.PI / 2, 0, 0] });
  b.add(new THREE.CircleGeometry(13, 40), 0x14141a, { pos: [0, -1.39, 0], rot: [-Math.PI / 2, 0, 0] });
  // ramp/walkway
  b.add(new THREE.BoxGeometry(2.2, 0.3, 14), 0x222230, { pos: [0, -1.25, 11] });
  // ringside chairs rows
  for (let r = 0; r < 4; r++) for (let i = -8; i <= 8; i++) {
    for (const [x, z] of [[i * 0.7, -(5 + r * 0.9)], [-(5 + r * 0.9), i * 0.7], [(5 + r * 0.9), i * 0.7]]) b.add(new THREE.BoxGeometry(0.5, 0.5, 0.5), 0x1f1f2a, { pos: [x, -1.15, z] });
  }
  // bowl tiers via lathe
  const prof = [new THREE.Vector2(16, -1.4)];
  for (let r = 0; r <= 30; r++) {
    const rad = 16 + r * 1.3, y = -1.4 + r * 0.75;
    prof.push(new THREE.Vector2(rad + 1.3, y), new THREE.Vector2(rad + 1.3, y + 0.75));
  }
  b.add(new THREE.LatheGeometry(prof, 56), 0x23232c);
  // roof ring + light towers
  b.add(new THREE.TorusGeometry(56, 1.2, 6, 56), 0x1a1a22, { pos: [0, 24, 0], rot: [Math.PI / 2, 0, 0] });
  for (let i = 0; i < 4; i++) {
    const a = Math.PI / 4 + i * Math.PI / 2, x = Math.cos(a) * 52, z = Math.sin(a) * 52;
    b.add(new THREE.CylinderGeometry(0.6, 0.9, 40, 8), 0x2a2a35, { pos: [x, 18, z] });
  }
  // jumbotron frame
  b.add(new THREE.BoxGeometry(5.2, 2.6, 5.2), 0x111111, { pos: [0, 9.5, 0] });
  grp.add(b.build(matVC()));

  const glow = new GeoBatch();
  for (let i = 0; i < 4; i++) {
    const a = Math.PI / 4 + i * Math.PI / 2, x = Math.cos(a) * 51, z = Math.sin(a) * 51;
    glow.add(new THREE.BoxGeometry(7, 3, 0.4), 0xffffff, { pos: [x, 38, z], rot: [0, -a + Math.PI / 2, 0] });
  }
  // aisle lights
  for (let i = 0; i < 64; i++) { const a = (i / 64) * Math.PI * 2; glow.add(new THREE.BoxGeometry(0.4, 0.06, 0.4), i % 2 ? 0xff3b30 : 0x3a86ff, { pos: [Math.cos(a) * 15.6, -1.3, Math.sin(a) * 15.6] }); }
  grp.add(glow.build(matGlow()));

  const screenTex = textTexture(1024, 512, (ctx, w, h) => {
    const g = ctx.createLinearGradient(0, 0, 0, h); g.addColorStop(0, '#1a0505'); g.addColorStop(1, '#3d0b0b');
    ctx.fillStyle = g; ctx.fillRect(0, 0, w, h);
    ctx.fillStyle = '#ffd23f'; ctx.font = `900 120px ${FONT}`; ctx.textAlign = 'center'; ctx.textBaseline = 'middle';
    ctx.fillText('MAIN EVENT', w / 2, h * 0.38);
    ctx.fillStyle = '#fff'; ctx.font = `800 64px ${FONT}`; ctx.fillText('IRON SHIN VR', w / 2, h * 0.68);
  });
  const screen = new THREE.Mesh(new THREE.CylinderGeometry(3.75, 3.75, 2.4, 4, 1, true), new THREE.MeshBasicMaterial({ map: screenTex, fog: false }));
  screen.rotation.y = Math.PI / 4; screen.position.y = 9.5;
  grp.add(screen);
  grp.add(stars(600));
  for (let i = 0; i < 4; i++) { const a = Math.PI / 4 + i * Math.PI / 2; grp.add(lightCone(Math.cos(a) * 3, 8, Math.sin(a) * 3, 1.8, 8, 0xffffff, 0.03)); }

  // big crowd on the bowl
  const spots = [];
  for (let r = 0; r < 24; r++) {
    const rad = 16.6 + r * 1.3, y = -1.4 + r * 0.75;
    const n = Math.floor((Math.PI * 2 * rad) / 0.8);
    for (let i = 0; i < n; i++) {
      if (Math.random() > 0.55) continue;
      const a = (i / n) * Math.PI * 2 + r * 0.05;
      if (Math.abs(Math.atan2(Math.sin(a - Math.PI / 2), Math.cos(a - Math.PI / 2))) < 0.07) continue; // tunnel
      const x = Math.cos(a) * rad, z = Math.sin(a) * rad;
      spots.push({ x, y, z, face: Math.atan2(-x, -z) });
    }
  }
  // ringside
  for (let r = 0; r < 4; r++) for (let i = -8; i <= 8; i++) {
    for (const [x, z, f] of [[i * 0.7, -(5 + r * 0.9), 0], [-(5 + r * 0.9), i * 0.7, -Math.PI / 2], [(5 + r * 0.9), i * 0.7, Math.PI / 2]]) {
      if (Math.random() < 0.85) spots.push({ x, y: -1.25, z, face: f === 0 ? 0 : (f > 0 ? -Math.PI / 2 : Math.PI / 2) });
    }
  }
  grp.add(crowd(spots, { scale: 1.05 }));
  return {
    group: grp,
    sky: [0x02030a, 0x0c1330, 0x050608],
    lights: { sky: 0xe6eeff, ground: 0x2a2a35, hemi: 1.2, dir: 0xffffff, dirI: 1.8, dirPos: [2, 8, 3] },
    fog: { color: 0x070a18, near: 30, far: 120 },
    ambience: 'crowd',
    bounds: { type: 'square', h: 2.65 },
  };
}

function street() {
  const grp = new THREE.Group();
  const asphalt = canvasTexture(512, 512, (ctx, w, h) => {
    ctx.fillStyle = '#26272b'; ctx.fillRect(0, 0, w, h);
    for (let i = 0; i < 9000; i++) { const v = 25 + Math.random() * 40; ctx.fillStyle = `rgb(${v},${v},${v + 4})`; ctx.fillRect(Math.random() * w, Math.random() * h, 2, 2); }
    ctx.strokeStyle = 'rgba(10,10,10,0.7)'; ctx.lineWidth = 2;
    for (let i = 0; i < 10; i++) { ctx.beginPath(); let x = Math.random() * w, y = Math.random() * h; ctx.moveTo(x, y); for (let k = 0; k < 6; k++) { x += rand(-40, 40); y += rand(-40, 40); ctx.lineTo(x, y); } ctx.stroke(); }
    for (let i = 0; i < 5; i++) { // puddles
      ctx.fillStyle = 'rgba(90,110,140,0.35)'; ctx.beginPath(); ctx.ellipse(Math.random() * w, Math.random() * h, rand(20, 60), rand(10, 30), Math.random() * 3, 0, Math.PI * 2); ctx.fill();
    }
  }, { repeat: [6, 8] });
  const ground = new THREE.Mesh(new THREE.PlaneGeometry(12, 40), new THREE.MeshLambertMaterial({ map: asphalt }));
  ground.rotation.x = -Math.PI / 2; ground.position.z = -6;
  grp.add(ground);

  const brick = canvasTexture(256, 256, (ctx, w, h) => {
    ctx.fillStyle = '#3b2420'; ctx.fillRect(0, 0, w, h);
    const bh = 32, bw = 64;
    for (let y = 0; y < h; y += bh) for (let x = -(y / bh % 2) * bw / 2; x < w; x += bw) {
      const v = rand(0.75, 1.15);
      ctx.fillStyle = `rgb(${Math.round(110 * v)},${Math.round(52 * v)},${Math.round(42 * v)})`;
      ctx.fillRect(x + 2, y + 2, bw - 4, bh - 4);
    }
    ctx.fillStyle = 'rgba(0,0,0,0.25)'; for (let i = 0; i < 400; i++) ctx.fillRect(Math.random() * w, Math.random() * h, 3, 3);
  }, { repeat: [10, 5] });
  const wallMat = new THREE.MeshLambertMaterial({ map: brick });
  for (const x of [-5, 5]) {
    const wall = new THREE.Mesh(new THREE.PlaneGeometry(40, 14), wallMat);
    wall.position.set(x, 7, -6); wall.rotation.y = x < 0 ? Math.PI / 2 : -Math.PI / 2;
    grp.add(wall);
  }
  const back = new THREE.Mesh(new THREE.PlaneGeometry(10, 14), wallMat);
  back.position.set(0, 7, -26); grp.add(back);

  // graffiti
  const graf = canvasTexture(1024, 384, (ctx, w, h) => {
    ctx.clearRect(0, 0, w, h);
    ctx.font = `900 italic 170px ${FONT}`; ctx.textAlign = 'center'; ctx.textBaseline = 'middle';
    ctx.lineWidth = 18; ctx.strokeStyle = '#111'; ctx.strokeText('IRON SHIN', w / 2, h / 2);
    const g = ctx.createLinearGradient(0, 0, w, 0); g.addColorStop(0, '#ff3b30'); g.addColorStop(0.5, '#ffd23f'); g.addColorStop(1, '#3a86ff');
    ctx.fillStyle = g; ctx.fillText('IRON SHIN', w / 2, h / 2);
    ctx.font = `700 52px ${FONT}`; ctx.fillStyle = '#e5e5e5'; ctx.fillText('NO RULES · NO REFS', w / 2, h * 0.86);
  });
  const gm = new THREE.Mesh(new THREE.PlaneGeometry(6, 2.25), new THREE.MeshLambertMaterial({ map: graf, transparent: true, depthWrite: false }));
  gm.position.set(-4.98, 2.3, -3); gm.rotation.y = Math.PI / 2; grp.add(gm);

  const b = new GeoBatch();
  // dumpster, crates, bags, barrel, pipes, fire escapes, fence
  b.add(new THREE.BoxGeometry(1.0, 1.2, 2.0), 0x1f4d2e, { pos: [4.3, 0.6, -5.5] });
  b.add(new THREE.BoxGeometry(1.06, 0.08, 2.06), 0x163a22, { pos: [4.3, 1.24, -5.5], rot: [0, 0, 0.12] });
  for (const [x, z, s] of [[-4.2, 2.2, 0.8], [-4.3, 1.3, 0.7], [-3.7, 2.0, 0.6]]) b.add(new THREE.BoxGeometry(s, s, s), 0x6b4f2a, { pos: [x, s / 2, z], rot: [0, rand(-0.4, 0.4), 0] });
  for (let i = 0; i < 5; i++) b.add(new THREE.SphereGeometry(rand(0.3, 0.45), 8, 6), 0x0f0f10, { pos: [4.4 + rand(-0.3, 0.3), 0.25, -3.6 + i * 0.5], scale: [1, 0.75, 1] });
  b.add(new THREE.CylinderGeometry(0.32, 0.3, 0.9, 12), 0x5a3a22, { pos: [-3.4, 0.45, -5.8] });
  for (const x of [-4.95, 4.95]) for (let f = 1; f < 4; f++) {
    b.add(new THREE.BoxGeometry(0.9, 0.06, 4), 0x1a1a1a, { pos: [x - Math.sign(x) * 0.45, f * 3.2, -10] });
    b.add(new THREE.BoxGeometry(0.04, 1.0, 4), 0x1a1a1a, { pos: [x - Math.sign(x) * 0.88, f * 3.2 + 0.5, -10] });
  }
  b.add(new THREE.CylinderGeometry(0.06, 0.06, 13, 6), 0x444444, { pos: [-4.9, 6.5, 0.5] });
  // street lamp
  b.add(new THREE.CylinderGeometry(0.07, 0.09, 5.2, 8), 0x202020, { pos: [4.6, 2.6, 0.2] });
  b.add(new THREE.BoxGeometry(1.4, 0.08, 0.08), 0x202020, { pos: [3.95, 5.15, 0.2] });
  b.add(new THREE.BoxGeometry(0.5, 0.18, 0.3), 0x202020, { pos: [3.3, 5.05, 0.2] });
  // back fence posts
  for (let i = -2; i <= 2; i++) b.add(new THREE.CylinderGeometry(0.04, 0.04, 3, 6), 0x777777, { pos: [i * 2.3, 1.5, -14] });
  // skyline beyond the open end
  for (let i = 0; i < 18; i++) {
    const w = rand(4, 9), h = rand(12, 45), x = -40 + i * 5 + rand(-1, 1), z = rand(35, 60);
    b.add(new THREE.BoxGeometry(w, h, w), 0x0b0e17, { pos: [x, h / 2, z] });
  }
  grp.add(b.build(matVC()));

  // chain-link back fence
  const tex = chainLinkTexture();
  tex.wrapS = tex.wrapT = THREE.RepeatWrapping; tex.repeat.set(10 / 0.09, 3 / 0.09);
  const fence = new THREE.Mesh(new THREE.PlaneGeometry(10, 3), new THREE.MeshLambertMaterial({ map: tex, transparent: true, depthWrite: false, side: THREE.DoubleSide }));
  fence.position.set(0, 1.5, -14); fence.renderOrder = 2;
  grp.add(fence);

  // emissive: windows, lamp bulb, fire, chalk circle
  const glow = new GeoBatch();
  for (const x of [-4.97, 4.97]) for (let fl = 0; fl < 4; fl++) for (let k = 0; k < 6; k++) {
    if (Math.random() < 0.5) continue;
    const c = Math.random() < 0.7 ? 0xffc977 : 0x9fc4ff;
    glow.add(new THREE.PlaneGeometry(1.0, 1.3), c, { pos: [x, 4 + fl * 3.2, -22 + k * 5 + 1.2], rot: [0, x < 0 ? Math.PI / 2 : -Math.PI / 2, 0] });
  }
  for (let i = 0; i < 18; i++) for (let k = 0; k < 14; k++) {
    if (Math.random() < 0.72) continue;
    glow.add(new THREE.PlaneGeometry(0.9, 1.2), Math.random() < 0.8 ? 0xffd28a : 0xbfe0ff, { pos: [-40 + i * 5 + rand(-2, 2), 2 + k * 2.8, 34], rot: [0, Math.PI, 0] });
  }
  glow.add(new THREE.BoxGeometry(0.42, 0.06, 0.24), 0xfff2c4, { pos: [3.3, 4.95, 0.2] });
  glow.add(new THREE.RingGeometry(3.45, 3.55, 64), 0xd9d9d9, { pos: [0, 0.006, 0], rot: [-Math.PI / 2, 0, 0] });
  grp.add(glow.build(matGlow()));

  const fireMat = new THREE.MeshBasicMaterial({ color: 0xff7a1a, transparent: true, opacity: 0.9, blending: THREE.AdditiveBlending, depthWrite: false });
  const fire = new THREE.Mesh(new THREE.ConeGeometry(0.26, 0.6, 10, 1, true), fireMat);
  fire.position.set(-3.4, 1.15, -5.8);
  grp.add(fire);
  grp.add(lightCone(3.3, 4.95, 0.2, 2.4, 4.95, 0xfff2c4, 0.045));

  // onlookers around the chalk circle
  const spots = [];
  for (let i = 0; i < 14; i++) {
    const a = -Math.PI * 0.95 + (i / 13) * Math.PI * 1.9;
    if (Math.abs(Math.sin(a)) > 0.95) continue;
    const r = rand(4.2, 4.7); const x = clamp(Math.sin(a) * r, -4.2, 4.2), z = -Math.cos(a) * r - 0.3;
    if (z > 2.2) continue;
    spots.push({ x, y: 0, z, face: Math.atan2(-x, -z) });
  }
  grp.add(crowd(spots, { palette: [0x222222, 0x3a3a3a, 0x5a1d1d, 0x1d2a5a, 0x444444, 0x2b2b2b] }));

  // rain (GPU-animated points)
  const N = 1800, p = new Float32Array(N * 3);
  for (let i = 0; i < N; i++) { p[i * 3] = rand(-5, 5); p[i * 3 + 1] = rand(0, 12); p[i * 3 + 2] = rand(-14, 6); }
  const rg = new THREE.BufferGeometry(); rg.setAttribute('position', new THREE.BufferAttribute(p, 3));
  const rainU = { uTime: { value: 0 } };
  const rain = new THREE.Points(rg, new THREE.ShaderMaterial({
    uniforms: rainU, transparent: true, depthWrite: false,
    vertexShader: `uniform float uTime; void main(){ vec3 p = position; p.y = mod(p.y - uTime * 7.0, 12.0); p.x += sin(uTime + position.z) * 0.05;
      vec4 mv = modelViewMatrix * vec4(p, 1.0); gl_Position = projectionMatrix * mv; gl_PointSize = clamp(40.0 / -mv.z, 1.0, 5.0); }`,
    fragmentShader: `void main(){ vec2 c = gl_PointCoord - 0.5; float a = smoothstep(0.5, 0.0, abs(c.x) * 4.0) * smoothstep(0.5, 0.2, abs(c.y)); gl_FragColor = vec4(0.7, 0.78, 0.9, a * 0.5); }`,
  }));
  rain.frustumCulled = false;
  grp.add(rain);
  grp.add(stars(250));

  return {
    group: grp,
    sky: [0x05060c, 0x1a1626, 0x05060c],
    lights: { sky: 0x8aa0d6, ground: 0x2a1c14, hemi: 1.0, dir: 0xffd9a8, dirI: 1.2, dirPos: [3, 5, 1] },
    fog: { color: 0x0b0a12, near: 8, far: 34 },
    ambience: 'rain',
    bounds: { type: 'circle', r: 3.2 },
    update(dt, t) {
      rainU.uTime.value = t;
      const f = 0.75 + Math.sin(t * 17) * 0.1 + Math.sin(t * 7.3) * 0.1;
      fire.scale.set(1, f * 1.1, 1); fireMat.opacity = 0.6 + f * 0.3;
    },
  };
}

function gym() {
  const grp = new THREE.Group();
  const mats = canvasTexture(256, 256, (ctx, w, h) => {
    const cs = ['#1f1f22', '#7a1414'];
    for (let y = 0; y < 2; y++) for (let x = 0; x < 2; x++) { ctx.fillStyle = cs[(x + y) % 2]; ctx.fillRect(x * 128, y * 128, 128, 128); }
    ctx.strokeStyle = 'rgba(0,0,0,0.45)'; ctx.lineWidth = 3;
    for (let i = 0; i <= 2; i++) { ctx.beginPath(); ctx.moveTo(i * 128, 0); ctx.lineTo(i * 128, h); ctx.moveTo(0, i * 128); ctx.lineTo(w, i * 128); ctx.stroke(); }
    ctx.globalAlpha = 0.06; for (let i = 0; i < 1500; i++) { ctx.fillStyle = '#fff'; ctx.fillRect(Math.random() * w, Math.random() * h, 2, 2); }
  }, { repeat: [8, 8] });
  const floor = new THREE.Mesh(new THREE.PlaneGeometry(16, 16), new THREE.MeshLambertMaterial({ map: mats }));
  floor.rotation.x = -Math.PI / 2; grp.add(floor);

  const b = new GeoBatch();
  for (const [x, z, ry] of [[0, -8, 0], [0, 8, Math.PI], [-8, 0, Math.PI / 2], [8, 0, -Math.PI / 2]]) {
    b.add(new THREE.PlaneGeometry(16, 6), 0xcfcac2, { pos: [x, 3, z], rot: [0, ry, 0] });
    b.add(new THREE.PlaneGeometry(16, 0.6), 0x7a1414, { pos: [x * 0.998, 0.3, z * 0.998], rot: [0, ry, 0] });
  }
  b.add(new THREE.PlaneGeometry(16, 16), 0x2b2b30, { pos: [0, 6, 0], rot: [Math.PI / 2, 0, 0] });
  // hanging bags along the back wall
  for (let i = 0; i < 4; i++) {
    const x = -5 + i * 2.2;
    b.add(new THREE.CylinderGeometry(0.17, 0.17, 1.15, 14), i % 2 ? 0x7a1414 : 0x111111, { pos: [x, 1.35, -6.6] });
    b.add(new THREE.CylinderGeometry(0.01, 0.01, 3.3, 4), 0x888888, { pos: [x, 4.3, -6.6] });
  }
  // weight rack + benches + mirror frame
  b.add(new THREE.BoxGeometry(3, 1.2, 0.5), 0x2b2b2b, { pos: [6.5, 0.6, -2], rot: [0, Math.PI / 2, 0] });
  for (let i = 0; i < 6; i++) b.add(new THREE.CylinderGeometry(0.1, 0.1, 0.18, 10), 0x111111, { pos: [6.45, 1.25, -3.2 + i * 0.45], rot: [0, 0, Math.PI / 2] });
  b.add(new THREE.BoxGeometry(1.4, 0.4, 0.4), 0x1d3557, { pos: [6.0, 0.2, 2.5] });
  b.add(new THREE.BoxGeometry(0.06, 2.3, 7.2), 0x111111, { pos: [-7.96, 1.6, 0] });
  grp.add(b.build(matVC()));
  // corner ring
  const cr = buildRing({ half: 1.8, floorTex: floorTex('#24426e', 'GYM'), platformDrop: 0.5 });
  cr.position.set(4.6, 0.5, 5.6); cr.scale.setScalar(0.9);
  grp.add(cr);

  // mirror wall (fake reflection: glossy gradient) + windows + ceiling lights
  const mirrorTex = canvasTexture(512, 256, (ctx, w, h) => {
    const g = ctx.createLinearGradient(0, 0, w, h); g.addColorStop(0, '#6c7a89'); g.addColorStop(0.45, '#aab6c3'); g.addColorStop(0.55, '#8796a5'); g.addColorStop(1, '#5d6b78');
    ctx.fillStyle = g; ctx.fillRect(0, 0, w, h);
    ctx.fillStyle = 'rgba(255,255,255,0.18)'; for (let i = 0; i < 5; i++) { ctx.save(); ctx.translate(i * 120, 0); ctx.rotate(0.35); ctx.fillRect(0, -50, 22, 400); ctx.restore(); }
  });
  const mirror = new THREE.Mesh(new THREE.PlaneGeometry(7, 2.2), new THREE.MeshBasicMaterial({ map: mirrorTex }));
  mirror.position.set(-7.93, 1.6, 0); mirror.rotation.y = Math.PI / 2; grp.add(mirror);
  const glow = new GeoBatch();
  for (let i = 0; i < 4; i++) glow.add(new THREE.PlaneGeometry(2.2, 1.6), 0xdfefff, { pos: [-5.4 + i * 3.6, 3.6, 7.98], rot: [0, Math.PI, 0] });
  for (const x of [-4, 0, 4]) for (const z of [-4, 0, 4]) glow.add(new THREE.PlaneGeometry(1.6, 0.25), 0xffffff, { pos: [x, 5.98, z], rot: [Math.PI / 2, 0, 0] });
  grp.add(glow.build(matGlow()));

  const poster = canvasTexture(512, 768, (ctx, w, h) => {
    ctx.fillStyle = '#111'; ctx.fillRect(0, 0, w, h);
    ctx.fillStyle = '#d62828'; ctx.fillRect(0, 0, w, 24); ctx.fillRect(0, h - 24, w, 24);
    ctx.fillStyle = '#fff'; ctx.textAlign = 'center'; ctx.font = `900 110px ${FONT}`;
    ctx.fillText('TRAIN', w / 2, 260); ctx.fillStyle = '#ffd23f'; ctx.fillText('HARD', w / 2, 390);
    ctx.fillStyle = '#fff'; ctx.font = `800 60px ${FONT}`; ctx.fillText('FIGHT', w / 2, 520); ctx.fillText('EASY', w / 2, 590);
  });
  for (const x of [-3, 3]) {
    const pm = new THREE.Mesh(new THREE.PlaneGeometry(1.2, 1.8), new THREE.MeshLambertMaterial({ map: poster }));
    pm.position.set(x, 2.6, -7.97); grp.add(pm);
  }
  return {
    group: grp,
    sky: [0x9aa4b0, 0xb9c2cc, 0x5a5f66],
    lights: { sky: 0xffffff, ground: 0x6a5a50, hemi: 1.35, dir: 0xffffff, dirI: 1.1, dirPos: [2, 5, 3] },
    fog: null,
    ambience: 'gym',
    bounds: { type: 'circle', r: 3.4 },
  };
}

const BUILDERS = { octagon, ring, stadium, street, gym };
const cache = new Map();

export function getArena(id) {
  if (!cache.has(id)) {
    const a = (BUILDERS[id] || octagon)();
    a.id = id;
    a.group.name = 'arena-' + id;
    a.skyMesh = skyDome(...a.sky);
    a.group.add(a.skyMesh);
    a.clamp = (v, margin = 0) => clampToBounds(a.bounds, v, margin);
    cache.set(id, a);
  }
  return cache.get(id);
}

export function clampToBounds(bounds, v, margin = 0) {
  if (bounds.type === 'circle') {
    const r = bounds.r - margin, d = Math.hypot(v.x, v.z);
    if (d > r) { v.x *= r / d; v.z *= r / d; return true; }
    return false;
  }
  const h = bounds.h - margin;
  let hit = false;
  if (v.x > h) { v.x = h; hit = true; } else if (v.x < -h) { v.x = -h; hit = true; }
  if (v.z > h) { v.z = h; hit = true; } else if (v.z < -h) { v.z = -h; hit = true; }
  return hit;
}

/** Thin floor outline for passthrough so you can see the fight area on your real floor. */
export function boundsOutline(bounds) {
  let geo;
  if (bounds.type === 'circle') geo = new THREE.RingGeometry(bounds.r - 0.03, bounds.r, 72);
  else {
    const h = bounds.h, w = 0.03;
    const shape = new THREE.Shape();
    shape.moveTo(-h, -h); shape.lineTo(h, -h); shape.lineTo(h, h); shape.lineTo(-h, h); shape.lineTo(-h, -h);
    const hole = new THREE.Path();
    hole.moveTo(-h + w, -h + w); hole.lineTo(-h + w, h - w); hole.lineTo(h - w, h - w); hole.lineTo(h - w, -h + w); hole.lineTo(-h + w, -h + w);
    shape.holes.push(hole);
    geo = new THREE.ShapeGeometry(shape);
  }
  const m = new THREE.Mesh(geo, new THREE.MeshBasicMaterial({ color: 0x7fd3ff, transparent: true, opacity: 0.45, depthWrite: false }));
  m.rotation.x = -Math.PI / 2; m.position.y = 0.005;
  return m;
}

void roundRect;
