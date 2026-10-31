// VR UI toolkit: canvas-rendered panels with laser-pointer interaction,
// floating hit text, announcer banners, scoreboard and screen effects.

import * as THREE from 'three';
import { FONT, roundRect, clamp, damp } from './util.js';
import { sfxUi } from './audio.js';

export const COLORS = {
  bg: '#0d1117', bg2: '#151b24', card: '#1b2330', cardHover: '#263244', line: '#2e3a4c',
  text: '#f2f5f9', dim: '#9aa7b8', accent: '#ff3b30', gold: '#ffc83d', good: '#34d399', blue: '#4ea8ff', bad: '#ff5a52',
};

const PX_PER_M = 1000;

export class Panel {
  constructor(wMeters, hMeters, { pxPerM = PX_PER_M, transparent = false } = {}) {
    this.w = Math.round(wMeters * pxPerM); this.h = Math.round(hMeters * pxPerM);
    this.canvas = document.createElement('canvas');
    this.canvas.width = this.w; this.canvas.height = this.h;
    this.ctx = this.canvas.getContext('2d');
    this.tex = new THREE.CanvasTexture(this.canvas);
    this.tex.colorSpace = THREE.SRGBColorSpace;
    this.tex.anisotropy = 4;
    this.mesh = new THREE.Mesh(
      new THREE.PlaneGeometry(wMeters, hMeters),
      new THREE.MeshBasicMaterial({ map: this.tex, transparent: true, fog: false, depthWrite: !transparent, toneMapped: false })
    );
    this.mesh.userData.panel = this;
    this.mesh.renderOrder = 20;
    this.buttons = [];
    this.hover = null;
    this.builder = null;
    this.dirty = true;
    this.interactive = true;
  }

  get visible() { return this.mesh.visible; }
  set visible(v) { this.mesh.visible = v; }

  setBuilder(fn) { this.builder = fn; this.hover = null; this.dirty = true; }
  invalidate() { this.dirty = true; }

  render() {
    if (!this.dirty || !this.builder) return;
    this.dirty = false;
    const ctx = this.ctx;
    ctx.clearRect(0, 0, this.w, this.h);
    this.buttons = [];
    this.builder(ctx, this.api());
    this.tex.needsUpdate = true;
  }

  api() {
    const self = this, ctx = this.ctx;
    return {
      w: this.w, h: this.h,
      bg(radius = 36, alpha = 0.94) {
        ctx.save();
        const g = ctx.createLinearGradient(0, 0, 0, self.h);
        g.addColorStop(0, `rgba(21,27,36,${alpha})`); g.addColorStop(1, `rgba(11,14,19,${alpha})`);
        ctx.fillStyle = g; roundRect(ctx, 0, 0, self.w, self.h, radius); ctx.fill();
        ctx.strokeStyle = 'rgba(255,255,255,0.08)'; ctx.lineWidth = 3; roundRect(ctx, 2, 2, self.w - 4, self.h - 4, radius); ctx.stroke();
        ctx.restore();
      },
      text(str, x, y, { size = 36, weight = 600, color = COLORS.text, align = 'left', baseline = 'alphabetic', maxW, italic = false } = {}) {
        ctx.font = `${italic ? 'italic ' : ''}${weight} ${size}px ${FONT}`;
        ctx.fillStyle = color; ctx.textAlign = align; ctx.textBaseline = baseline;
        if (maxW) ctx.fillText(str, x, y, maxW); else ctx.fillText(str, x, y);
      },
      wrap(str, x, y, maxW, lineH, opts = {}) {
        ctx.font = `${opts.weight || 500} ${opts.size || 30}px ${FONT}`;
        const words = String(str).split(' ');
        let line = '', yy = y;
        ctx.fillStyle = opts.color || COLORS.dim; ctx.textAlign = 'left'; ctx.textBaseline = 'alphabetic';
        for (const w of words) {
          const test = line ? line + ' ' + w : w;
          if (ctx.measureText(test).width > maxW && line) { ctx.fillText(line, x, yy); line = w; yy += lineH; }
          else line = test;
        }
        if (line) ctx.fillText(line, x, yy);
        return yy + lineH;
      },
      rect(x, y, w, h, { fill, stroke, radius = 16, lw = 3 } = {}) {
        roundRect(ctx, x, y, w, h, radius);
        if (fill) { ctx.fillStyle = fill; ctx.fill(); }
        if (stroke) { ctx.strokeStyle = stroke; ctx.lineWidth = lw; ctx.stroke(); }
      },
      bar(x, y, w, h, frac, color, { bg = 'rgba(255,255,255,0.1)', radius } = {}) {
        const r = radius ?? h / 2;
        roundRect(ctx, x, y, w, h, r); ctx.fillStyle = bg; ctx.fill();
        const fw = Math.max(0, Math.min(1, frac)) * w;
        if (fw > 1) { roundRect(ctx, x, y, Math.max(fw, r * 2), h, r); ctx.fillStyle = color; ctx.fill(); }
      },
      /** Interactive button. opts: selected, disabled, sub, accent, size, icon, align */
      button(id, x, y, w, h, label, opts = {}) {
        const hov = self.hover === id && !opts.disabled;
        const sel = !!opts.selected;
        const accent = opts.accent || COLORS.accent;
        ctx.save();
        if (hov) { ctx.shadowColor = 'rgba(0,0,0,0.5)'; ctx.shadowBlur = 18; }
        roundRect(ctx, x, y, w, h, opts.radius ?? 20);
        ctx.fillStyle = opts.disabled ? '#141a22' : sel ? accent : hov ? COLORS.cardHover : (opts.fill || COLORS.card);
        ctx.fill();
        ctx.shadowBlur = 0;
        ctx.lineWidth = hov ? 5 : 2;
        ctx.strokeStyle = hov ? (sel ? '#ffffff' : accent) : sel ? 'rgba(255,255,255,0.35)' : COLORS.line;
        ctx.stroke();
        const tc = opts.disabled ? '#4b5563' : sel ? (opts.selText || '#ffffff') : COLORS.text;
        const size = opts.size || 34;
        ctx.textBaseline = 'middle';
        if (opts.sub) {
          ctx.font = `800 ${size}px ${FONT}`; ctx.fillStyle = tc; ctx.textAlign = opts.align || 'center';
          const tx = opts.align === 'left' ? x + 28 : x + w / 2;
          ctx.fillText(label, tx, y + h * 0.4, w - 40);
          ctx.font = `500 ${Math.round(size * 0.62)}px ${FONT}`;
          ctx.fillStyle = sel ? 'rgba(255,255,255,0.85)' : COLORS.dim;
          ctx.fillText(opts.sub, tx, y + h * 0.72, w - 40);
        } else {
          ctx.font = `800 ${size}px ${FONT}`; ctx.fillStyle = tc; ctx.textAlign = opts.align || 'center';
          ctx.fillText(label, opts.align === 'left' ? x + 28 : x + w / 2, y + h / 2 + 2, w - 30);
        }
        ctx.restore();
        if (!opts.disabled) self.buttons.push({ id, x, y, w, h, onClick: opts.onClick, sound: opts.sound });
      },
    };
  }

  hitTest(uv) {
    const x = uv.x * this.w, y = (1 - uv.y) * this.h;
    for (let i = this.buttons.length - 1; i >= 0; i--) {
      const b = this.buttons[i];
      if (x >= b.x && x <= b.x + b.w && y >= b.y && y <= b.y + b.h) return b;
    }
    return null;
  }

  setHover(id) {
    if (this.hover !== id) {
      this.hover = id; this.dirty = true;
      if (id) sfxUi('hover');
      return true;
    }
    return false;
  }
}

// ---------------------------------------------------------------------------
// Laser pointers

const _ray = new THREE.Raycaster();
export class Pointers {
  constructor(scene, input) {
    this.input = input;
    this.panels = new Set();
    this.enabled = true;
    this.lasers = {};
    this.onHoverHaptic = (side) => input.pulse(side, 0.12, 12);
    for (const side of ['left', 'right']) {
      const geo = new THREE.CylinderGeometry(0.0022, 0.0035, 1, 6, 1, true);
      geo.translate(0, 0.5, 0); geo.rotateX(-Math.PI / 2); // along -Z
      const laser = new THREE.Mesh(geo, new THREE.MeshBasicMaterial({ color: 0x8fd3ff, transparent: true, opacity: 0.7, depthWrite: false, fog: false }));
      const dot = new THREE.Mesh(new THREE.CircleGeometry(0.011, 16), new THREE.MeshBasicMaterial({ color: 0xffffff, depthTest: false, transparent: true, fog: false }));
      dot.renderOrder = 30; laser.renderOrder = 29;
      laser.visible = dot.visible = false;
      scene.add(laser); scene.add(dot);
      this.lasers[side] = { laser, dot, hovered: null, panel: null };
    }
    this.active = 'right';
  }

  add(panel) { this.panels.add(panel); }
  remove(panel) { this.panels.delete(panel); }

  update() {
    const meshes = [];
    for (const p of this.panels) if (p.visible && p.interactive && p.mesh.parent && isVisibleDeep(p.mesh)) meshes.push(p.mesh);
    const any = this.enabled && meshes.length > 0;
    for (const side of ['left', 'right']) {
      const L = this.lasers[side];
      const h = this.input.handBySide(side);
      const show = any && h.valid && (this.input.xr || side === 'right');
      L.laser.visible = show && this.input.xr;
      L.dot.visible = false;
      if (!show) { this._setHover(L, null, null); continue; }
      if (h.uiClick) this.active = side;
      _ray.set(h.rayOrigin, h.rayDir);
      _ray.far = 8;
      const hits = _ray.intersectObjects(meshes, false);
      let dist = 3, btn = null, panel = null;
      if (hits.length) {
        const hit = hits[0];
        dist = hit.distance;
        panel = hit.object.userData.panel;
        btn = panel.hitTest(hit.uv);
        L.dot.visible = true;
        L.dot.position.copy(hit.point).addScaledVector(h.rayDir, -0.004);
        L.dot.quaternion.copy(hit.object.getWorldQuaternion(new THREE.Quaternion()));
        L.dot.material.color.set(btn ? 0xffc83d : 0xffffff);
        L.dot.scale.setScalar(btn ? 1.4 : 1);
      }
      if (L.laser.visible) {
        L.laser.position.copy(h.rayOrigin);
        L.laser.quaternion.setFromUnitVectors(_fwd, h.rayDir);
        L.laser.scale.set(1, 1, dist);
        L.laser.material.opacity = side === this.active ? 0.75 : 0.35;
      }
      this._setHover(L, panel, btn, side);
      if (btn && h.uiClick) {
        sfxUi(btn.sound || 'click');
        this.input.pulse(side, 0.35, 25);
        btn.onClick && btn.onClick();
      }
    }
    for (const p of this.panels) p.render();
  }

  _setHover(L, panel, btn, side) {
    const id = btn ? btn.id : null;
    if (L.panel && L.panel !== panel) L.panel.setHover(null);
    if (panel) { if (panel.setHover(id) && id && side) this.onHoverHaptic(side); }
    L.panel = panel;
  }
}
const _fwd = new THREE.Vector3(0, 0, -1);
function isVisibleDeep(o) { while (o) { if (!o.visible) return false; o = o.parent; } return true; }

// ---------------------------------------------------------------------------
// Floating text (hit markers, "BLOCKED", "SLIP!")

const textTexCache = new Map();
function textTex(text, color) {
  const key = text + '|' + color;
  if (textTexCache.has(key)) return textTexCache.get(key);
  const c = document.createElement('canvas'); c.width = 512; c.height = 128;
  const ctx = c.getContext('2d');
  ctx.font = `900 italic 84px ${FONT}`; ctx.textAlign = 'center'; ctx.textBaseline = 'middle';
  ctx.lineWidth = 12; ctx.strokeStyle = 'rgba(0,0,0,0.85)'; ctx.strokeText(text, 256, 66);
  ctx.fillStyle = color; ctx.fillText(text, 256, 66);
  const t = new THREE.CanvasTexture(c); t.colorSpace = THREE.SRGBColorSpace;
  textTexCache.set(key, t);
  return t;
}

export class FloatingText {
  constructor(scene, n = 10) {
    this.items = [];
    for (let i = 0; i < n; i++) {
      const s = new THREE.Sprite(new THREE.SpriteMaterial({ transparent: true, depthTest: false, fog: false }));
      s.renderOrder = 40; s.visible = false;
      scene.add(s);
      this.items.push({ s, t: 1, life: 1, vy: 0 });
    }
    this.i = 0;
  }
  spawn(text, pos, { color = '#ffffff', size = 0.28, life = 0.9 } = {}) {
    const it = this.items[this.i++ % this.items.length];
    it.s.material.map = textTex(text, color);
    it.s.material.needsUpdate = true;
    it.s.position.copy(pos);
    it.s.scale.set(size * 4, size, 1);
    it.t = 0; it.life = life; it.vy = 0.35; it.base = size;
    it.s.visible = true;
  }
  update(dt) {
    for (const it of this.items) {
      if (!it.s.visible) continue;
      it.t += dt / it.life;
      if (it.t >= 1) { it.s.visible = false; continue; }
      it.s.position.y += it.vy * dt;
      const pop = it.t < 0.12 ? 0.6 + it.t / 0.12 * 0.5 : 1.1 - (it.t - 0.12) * 0.15;
      it.s.scale.set(it.base * 4 * pop, it.base * pop, 1);
      it.s.material.opacity = it.t > 0.65 ? 1 - (it.t - 0.65) / 0.35 : 1;
    }
  }
}

// ---------------------------------------------------------------------------
// Announcer banner (ROUND 1 / FIGHT! / KO)

export class Announcer {
  constructor(scene) {
    this.panel = new Panel(1.6, 0.5, { pxPerM: 800, transparent: true });
    this.panel.interactive = false;
    this.panel.mesh.material.depthTest = false;
    this.panel.mesh.renderOrder = 50;
    this.panel.visible = false;
    scene.add(this.panel.mesh);
    this.t = 0; this.dur = 0;
  }
  show(title, sub = '', { color = '#ffffff', dur = 1.6, head, fwd, dist = 2.0, height = 0.15, accent = COLORS.accent } = {}) {
    this.panel.setBuilder((ctx, ui) => {
      ctx.save();
      const g = ctx.createLinearGradient(0, 0, ui.w, 0);
      g.addColorStop(0, 'rgba(0,0,0,0)'); g.addColorStop(0.2, 'rgba(0,0,0,0.65)'); g.addColorStop(0.8, 'rgba(0,0,0,0.65)'); g.addColorStop(1, 'rgba(0,0,0,0)');
      ctx.fillStyle = g; ctx.fillRect(0, 60, ui.w, ui.h - 120);
      ctx.fillStyle = accent; ctx.fillRect(ui.w * 0.2, 60, ui.w * 0.6, 6); ctx.fillRect(ui.w * 0.2, ui.h - 66, ui.w * 0.6, 6);
      ctx.restore();
      ui.text(title, ui.w / 2, sub ? ui.h * 0.5 : ui.h * 0.56, { size: 128, weight: 900, align: 'center', baseline: 'middle', color, italic: true });
      if (sub) ui.text(sub, ui.w / 2, ui.h * 0.78, { size: 40, weight: 700, align: 'center', baseline: 'middle', color: COLORS.dim });
    });
    this.panel.render();
    if (head && fwd) {
      this.panel.mesh.position.copy(head).addScaledVector(fwd, dist);
      this.panel.mesh.position.y = head.y + height;
      this.panel.mesh.lookAt(head.x, this.panel.mesh.position.y, head.z);
    }
    this.t = 0; this.dur = dur;
    this.panel.visible = true;
  }
  hide() { this.panel.visible = false; }
  update(dt) {
    if (!this.panel.visible) return;
    this.t += dt;
    const m = this.panel.mesh.material;
    const inT = Math.min(1, this.t / 0.15);
    const outT = this.dur > 0 ? clamp((this.dur - this.t) / 0.3, 0, 1) : 1;
    m.opacity = Math.min(inT, outT);
    const s = 0.85 + 0.15 * inT;
    this.panel.mesh.scale.set(s, s, 1);
    if (this.dur > 0 && this.t >= this.dur) this.panel.visible = false;
  }
}

// ---------------------------------------------------------------------------
// Scoreboard (fight HUD) — billboard that floats above the opponent.

export class Scoreboard {
  constructor(scene) {
    this.panel = new Panel(1.0, 0.22, { pxPerM: 1000, transparent: true });
    this.panel.interactive = false;
    this.panel.visible = false;
    scene.add(this.panel.mesh);
    this.state = { pHp: 1, pSt: 1, oHp: 1, oName: '', round: 1, rounds: 3, time: 0, pKd: 0, oKd: 0, label: '' };
    this.last = '';
    this.panel.setBuilder((ctx, ui) => this._draw(ctx, ui));
    this.pos = new THREE.Vector3();
  }
  set(s) {
    Object.assign(this.state, s);
    const st = this.state;
    const key = [Math.round(st.pHp * 100), Math.round(st.pSt * 50), Math.round(st.oHp * 100), st.round, Math.ceil(st.time), st.pKd, st.oKd, st.oName, st.label].join('|');
    if (key !== this.last) { this.last = key; this.panel.invalidate(); }
  }
  _draw(ctx, ui) {
    const s = this.state;
    ui.rect(0, 0, ui.w, ui.h, { fill: 'rgba(8,10,14,0.82)', radius: 28 });
    const hpCol = (f) => (f > 0.5 ? '#34d399' : f > 0.25 ? '#ffc83d' : '#ff5a52');
    // You
    ui.text('YOU', 30, 52, { size: 34, weight: 900 });
    for (let i = 0; i < s.pKd; i++) ui.rect(110 + i * 26, 30, 18, 18, { fill: COLORS.bad, radius: 9 });
    ui.bar(30, 72, 380, 34, s.pHp, hpCol(s.pHp));
    ui.bar(30, 118, 380, 14, s.pSt, '#4ea8ff');
    ui.text('STAMINA', 30, 168, { size: 22, weight: 700, color: COLORS.dim });
    // Opponent
    ui.text(s.oName, ui.w - 30, 52, { size: 30, weight: 900, align: 'right', maxW: 360 });
    for (let i = 0; i < s.oKd; i++) ui.rect(ui.w - 50 - i * 26, 64 + 58, 18, 18, { fill: COLORS.bad, radius: 9 });
    ui.bar(ui.w - 410, 72, 380, 34, s.oHp, hpCol(s.oHp));
    // Round + clock
    const t = Math.max(0, Math.ceil(s.time));
    const mm = Math.floor(t / 60), ss = String(t % 60).padStart(2, '0');
    ui.text(s.label || `ROUND ${s.round}/${s.rounds}`, ui.w / 2, 50, { size: 26, weight: 800, align: 'center', color: COLORS.gold });
    ui.text(`${mm}:${ss}`, ui.w / 2, 130, { size: 76, weight: 900, align: 'center' });
  }
  update(dt, anchor, head) {
    if (!this.panel.visible) return;
    this.pos.lerp(anchor, damp(4, dt));
    this.panel.mesh.position.copy(this.pos);
    this.panel.mesh.lookAt(head.x, this.pos.y, head.z);
    this.panel.render();
  }
}

// ---------------------------------------------------------------------------
// Screen FX: red hit flash, comfort vignette, low-health pulse. A sphere
// attached to the camera, drawn last, alpha from view angle.

export class ScreenFX {
  constructor(camera) {
    this.u = {
      uColor: { value: new THREE.Color(0, 0, 0) }, uAmt: { value: 0 }, uInner: { value: 0.6 },
    };
    const mat = new THREE.ShaderMaterial({
      uniforms: this.u, transparent: true, depthTest: false, depthWrite: false, side: THREE.BackSide, fog: false,
      vertexShader: `varying vec3 vDir; void main(){ vDir = normalize(position); gl_Position = projectionMatrix * modelViewMatrix * vec4(position, 1.0); }`,
      fragmentShader: `uniform vec3 uColor; uniform float uAmt; uniform float uInner; varying vec3 vDir;
        void main(){ float c = dot(normalize(vDir), vec3(0.0, 0.0, -1.0)); float edge = 1.0 - smoothstep(uInner - 0.35, uInner + 0.25, c);
          float a = clamp(edge * uAmt, 0.0, 0.95); gl_FragColor = vec4(uColor, a); }`,
    });
    this.mesh = new THREE.Mesh(new THREE.SphereGeometry(0.5, 24, 12), mat);
    this.mesh.renderOrder = 100;
    this.mesh.frustumCulled = false;
    camera.add(this.mesh);
    this.hit = 0; this.vignette = 0; this.lowHp = 0; this.fade = 0; this.time = 0; this.fadeColor = new THREE.Color(0, 0, 0);
  }
  flash(amount = 0.8) { this.hit = Math.max(this.hit, amount); }
  update(dt) {
    this.time += dt;
    this.hit = Math.max(0, this.hit - dt * 2.2);
    const pulse = this.lowHp > 0 ? this.lowHp * (0.35 + 0.25 * Math.sin(this.time * 5.5)) : 0;
    const red = Math.max(this.hit, pulse);
    if (this.fade > 0.001) {
      this.u.uColor.value.copy(this.fadeColor);
      this.u.uAmt.value = this.fade * 1.2; this.u.uInner.value = 2.0; // full screen
    } else if (red >= this.vignette * 0.8 && red > 0.01) {
      this.u.uColor.value.setRGB(0.55, 0, 0);
      this.u.uAmt.value = red; this.u.uInner.value = 0.75 - red * 0.25;
    } else {
      this.u.uColor.value.setRGB(0, 0, 0);
      this.u.uAmt.value = this.vignette; this.u.uInner.value = 0.82;
    }
    this.mesh.visible = this.u.uAmt.value > 0.005;
  }
}
