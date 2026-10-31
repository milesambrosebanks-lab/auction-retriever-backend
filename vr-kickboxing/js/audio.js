// Procedural audio: every sound is synthesized with WebAudio, so there are no
// asset downloads and latency is minimal. Impacts are spatialized.

import { settings } from './settings.js';

let ctx = null;
let master = null;
let noiseBuf = null;
let ambience = null;   // { gain, src, kind }
let crowdGain = null;
const listenerPos = { x: 0, y: 1.6, z: 0 };

export function initAudio() {
  if (ctx) { if (ctx.state === 'suspended') ctx.resume(); return; }
  const AC = window.AudioContext || window.webkitAudioContext;
  if (!AC) return;
  ctx = new AC({ latencyHint: 'interactive' });
  master = ctx.createGain();
  master.gain.value = settings.volume;
  // Gentle limiter so stacked impacts never clip.
  const comp = ctx.createDynamicsCompressor();
  comp.threshold.value = -10; comp.knee.value = 6; comp.ratio.value = 6;
  comp.attack.value = 0.002; comp.release.value = 0.15;
  master.connect(comp).connect(ctx.destination);

  noiseBuf = ctx.createBuffer(1, ctx.sampleRate * 2, ctx.sampleRate);
  const d = noiseBuf.getChannelData(0);
  for (let i = 0; i < d.length; i++) d[i] = Math.random() * 2 - 1;
}

export function setVolume(v) { if (master) master.gain.setTargetAtTime(v, ctx.currentTime, 0.05); }

export function updateListener(pos, fwd, up) {
  listenerPos.x = pos.x; listenerPos.y = pos.y; listenerPos.z = pos.z;
  if (!ctx) return;
  const l = ctx.listener;
  if (l.positionX) {
    const t = ctx.currentTime;
    l.positionX.setValueAtTime(pos.x, t); l.positionY.setValueAtTime(pos.y, t); l.positionZ.setValueAtTime(pos.z, t);
    l.forwardX.setValueAtTime(fwd.x, t); l.forwardY.setValueAtTime(fwd.y, t); l.forwardZ.setValueAtTime(fwd.z, t);
    l.upX.setValueAtTime(up.x, t); l.upY.setValueAtTime(up.y, t); l.upZ.setValueAtTime(up.z, t);
  } else if (l.setPosition) {
    l.setPosition(pos.x, pos.y, pos.z);
    l.setOrientation(fwd.x, fwd.y, fwd.z, up.x, up.y, up.z);
  }
}

function out(pos) {
  if (!pos) return master;
  const p = ctx.createPanner();
  p.panningModel = 'HRTF';
  p.distanceModel = 'inverse';
  p.refDistance = 0.6; p.rolloffFactor = 1.2;
  if (p.positionX) { p.positionX.value = pos.x; p.positionY.value = pos.y; p.positionZ.value = pos.z; }
  else p.setPosition(pos.x, pos.y, pos.z);
  p.connect(master);
  return p;
}

function noise(dest, t, dur, { type = 'lowpass', freq = 1000, q = 0.7, gain = 1, attack = 0.002, sweepTo = null } = {}) {
  const src = ctx.createBufferSource();
  src.buffer = noiseBuf;
  src.playbackRate.value = 0.8 + Math.random() * 0.4;
  const f = ctx.createBiquadFilter();
  f.type = type; f.frequency.setValueAtTime(freq, t); f.Q.value = q;
  if (sweepTo) f.frequency.exponentialRampToValueAtTime(sweepTo, t + dur);
  const g = ctx.createGain();
  g.gain.setValueAtTime(0.0001, t);
  g.gain.exponentialRampToValueAtTime(gain, t + attack);
  g.gain.exponentialRampToValueAtTime(0.0001, t + dur);
  src.connect(f).connect(g).connect(dest);
  src.start(t, Math.random() * 1.5);
  src.stop(t + dur + 0.05);
}

function tone(dest, t, dur, { freq = 100, to = null, type = 'sine', gain = 1, attack = 0.002 } = {}) {
  const o = ctx.createOscillator();
  o.type = type;
  o.frequency.setValueAtTime(freq, t);
  if (to) o.frequency.exponentialRampToValueAtTime(to, t + dur);
  const g = ctx.createGain();
  g.gain.setValueAtTime(0.0001, t);
  g.gain.exponentialRampToValueAtTime(gain, t + attack);
  g.gain.exponentialRampToValueAtTime(0.0001, t + dur);
  o.connect(g).connect(dest);
  o.start(t); o.stop(t + dur + 0.05);
}

// ---- Public sound effects -------------------------------------------------

/** Glove/shin landing on a body. power 0..1 */
export function sfxImpact(pos, power = 0.6) {
  if (!ctx) return;
  const t = ctx.currentTime, d = out(pos);
  const p = Math.max(0.15, Math.min(1, power));
  noise(d, t, 0.09 + p * 0.06, { freq: 900 + p * 1400, gain: 0.5 + p * 0.6, q: 0.9 });
  tone(d, t, 0.12 + p * 0.1, { freq: 120 + p * 40, to: 45, gain: 0.6 + p * 0.5 });
  noise(d, t, 0.025, { type: 'highpass', freq: 3500, gain: 0.25 * p });
}

/** Strike hitting a guard/glove. */
export function sfxBlock(pos, power = 0.5) {
  if (!ctx) return;
  const t = ctx.currentTime, d = out(pos);
  noise(d, t, 0.06, { type: 'bandpass', freq: 1600, q: 1.4, gain: 0.35 + power * 0.3 });
  tone(d, t, 0.08, { freq: 220, to: 110, gain: 0.25 + power * 0.2 });
}

/** Sharp "pop" of a focus mitt / Thai pad. */
export function sfxPad(pos, power = 0.7) {
  if (!ctx) return;
  const t = ctx.currentTime, d = out(pos);
  noise(d, t, 0.05, { type: 'highpass', freq: 1800, gain: 0.5 + power * 0.5 });
  noise(d, t, 0.08, { type: 'bandpass', freq: 900, q: 1.2, gain: 0.4 + power * 0.3 });
  tone(d, t, 0.07, { freq: 180, to: 80, gain: 0.35 });
}

/** Deep heavy-bag thud. */
export function sfxBag(pos, power = 0.7) {
  if (!ctx) return;
  const t = ctx.currentTime, d = out(pos);
  tone(d, t, 0.22, { freq: 85, to: 40, gain: 0.7 + power * 0.5 });
  noise(d, t, 0.14, { freq: 450, gain: 0.5 + power * 0.5 });
  noise(d, t + 0.01, 0.18, { type: 'bandpass', freq: 2500, q: 3, gain: 0.04 }); // chain rattle
}

export function sfxWhoosh(pos, power = 0.5) {
  if (!ctx) return;
  const t = ctx.currentTime, d = out(pos);
  noise(d, t, 0.22, { type: 'bandpass', freq: 400, sweepTo: 2200, q: 1.5, gain: 0.12 + power * 0.18, attack: 0.06 });
}

/** Player getting hit: muffled thump + ringing. */
export function sfxHurt(power = 0.6) {
  if (!ctx) return;
  const t = ctx.currentTime;
  tone(master, t, 0.25, { freq: 70, to: 35, gain: 0.9 * power + 0.2 });
  noise(master, t, 0.16, { freq: 600, gain: 0.6 * power + 0.2 });
  if (power > 0.7) tone(master, t + 0.05, 1.2, { freq: 3100, gain: 0.03, attack: 0.05 });
}

/** Short grunt for the opponent / coach. */
export function sfxGrunt(pos, pitch = 1) {
  if (!ctx) return;
  const t = ctx.currentTime, d = out(pos);
  const o = ctx.createOscillator(); o.type = 'sawtooth';
  o.frequency.setValueAtTime(140 * pitch, t); o.frequency.exponentialRampToValueAtTime(90 * pitch, t + 0.18);
  const f = ctx.createBiquadFilter(); f.type = 'bandpass'; f.frequency.value = 650 * pitch; f.Q.value = 2.5;
  const g = ctx.createGain(); g.gain.setValueAtTime(0.0001, t);
  g.gain.exponentialRampToValueAtTime(0.18, t + 0.02); g.gain.exponentialRampToValueAtTime(0.0001, t + 0.2);
  o.connect(f).connect(g).connect(d); o.start(t); o.stop(t + 0.25);
}

/** Boxing bell. count = number of dings. */
export function sfxBell(count = 1) {
  if (!ctx) return;
  const partials = [1, 2.76, 5.4, 8.93];
  for (let n = 0; n < count; n++) {
    const t = ctx.currentTime + n * 0.32;
    partials.forEach((r, i) => tone(master, t, 1.6 - i * 0.3, { freq: 620 * r, gain: 0.22 / (i + 1), attack: 0.001 }));
  }
}

export function sfxUi(kind = 'click') {
  if (!ctx) return;
  const t = ctx.currentTime;
  if (kind === 'hover') tone(master, t, 0.03, { freq: 1400, gain: 0.04 });
  else if (kind === 'back') tone(master, t, 0.07, { freq: 700, to: 450, gain: 0.12, type: 'triangle' });
  else tone(master, t, 0.06, { freq: 900, to: 1300, gain: 0.12, type: 'triangle' });
}

export function sfxSuccess() {
  if (!ctx) return;
  const t = ctx.currentTime;
  [523, 659, 784, 1047].forEach((f, i) => tone(master, t + i * 0.09, 0.25, { freq: f, gain: 0.12, type: 'triangle' }));
}

export function sfxFail() {
  if (!ctx) return;
  const t = ctx.currentTime;
  [392, 330, 262].forEach((f, i) => tone(master, t + i * 0.14, 0.3, { freq: f, gain: 0.12, type: 'triangle' }));
}

// ---- Ambience ----------------------------------------------------------------

/** kind: 'crowd' | 'small' | 'rain' | 'gym' | null */
export function setAmbience(kind) {
  if (!ctx) return;
  if (ambience && ambience.kind === kind) return;
  if (ambience) {
    const old = ambience;
    old.gain.gain.setTargetAtTime(0.0001, ctx.currentTime, 0.3);
    setTimeout(() => { try { old.src.stop(); } catch (e) { /* already stopped */ } }, 1500);
    ambience = null; crowdGain = null;
  }
  if (!kind) return;
  const src = ctx.createBufferSource();
  src.buffer = noiseBuf; src.loop = true;
  const f = ctx.createBiquadFilter();
  const g = ctx.createGain(); g.gain.value = 0.0001;
  let level = 0.05;
  if (kind === 'crowd') { f.type = 'bandpass'; f.frequency.value = 700; f.Q.value = 0.6; level = 0.11; }
  else if (kind === 'small') { f.type = 'bandpass'; f.frequency.value = 600; f.Q.value = 0.8; level = 0.05; }
  else if (kind === 'rain') { f.type = 'highpass'; f.frequency.value = 2500; f.Q.value = 0.5; level = 0.06; }
  else { f.type = 'lowpass'; f.frequency.value = 300; level = 0.025; }
  src.connect(f).connect(g).connect(master);
  src.start();
  g.gain.setTargetAtTime(level, ctx.currentTime, 0.8);
  ambience = { src, gain: g, kind, level };
  crowdGain = (kind === 'crowd' || kind === 'small') ? g : null;
}

/** Crowd reacts (big hit, knockdown). amount 0..1 */
export function crowdCheer(amount = 0.5) {
  if (!ctx || !crowdGain || !ambience) return;
  const t = ctx.currentTime;
  const base = ambience.level;
  crowdGain.gain.cancelScheduledValues(t);
  crowdGain.gain.setTargetAtTime(base * (1.6 + amount * 2.5), t, 0.08);
  crowdGain.gain.setTargetAtTime(base, t + 0.6 + amount, 0.8);
}

// ---- Voice (coach/ref callouts) --------------------------------------------
let voice = null;
let voiceChecked = false;
export function say(text, rate = 1.15) {
  try {
    const s = window.speechSynthesis;
    if (!s) return;
    if (!voiceChecked) {
      const vs = s.getVoices();
      if (vs.length) {
        voice = vs.find(v => /en[-_]US/i.test(v.lang) && /male|david|guy/i.test(v.name)) ||
                vs.find(v => /^en/i.test(v.lang)) || null;
        voiceChecked = true;
      }
    }
    s.cancel();
    const u = new SpeechSynthesisUtterance(text);
    if (voice) u.voice = voice;
    u.rate = rate; u.pitch = 0.9; u.volume = Math.min(1, settings.volume + 0.1);
    s.speak(u);
  } catch (e) { /* speech not available: on-screen callouts still show */ }
}
