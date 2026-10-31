// Persisted user settings (per device, localStorage with safe fallbacks).

const KEY = 'vr-kickboxing-settings-v1';

export const DEFAULTS = {
  view: 'immersive',        // 'immersive' | 'passthrough'
  difficulty: 'medium',     // easy | medium | hard
  opponent: 'rico',
  arena: 'octagon',
  drill: 'mitts',
  trainingArena: 'gym',
  rounds: 3,
  roundSeconds: 120,
  haptics: 1.0,             // 0 | 0.4 | 0.7 | 1.0
  volume: 0.8,
  refreshRate: 90,          // 72 | 90 | 120 (if supported)
  turn: 'snap45',           // snap30 | snap45 | smooth | off
  moveSpeed: 1.6,           // m/s smooth locomotion
  vignette: true,           // comfort vignette while stick-moving
  seated: false,            // adds height offset
  stance: 'orthodox',       // orthodox (left lead) | southpaw (right lead)
  hints: true,
  showFps: false,
  bloodless: true,          // kept for future; game shows no gore
};

export const settings = { ...DEFAULTS };

export function loadSettings() {
  try {
    const raw = localStorage.getItem(KEY);
    if (raw) {
      const parsed = JSON.parse(raw);
      for (const k of Object.keys(DEFAULTS)) {
        if (k in parsed && typeof parsed[k] === typeof DEFAULTS[k]) settings[k] = parsed[k];
      }
    }
  } catch (e) { /* storage unavailable: defaults are fine */ }
  return settings;
}

export function saveSettings() {
  try { localStorage.setItem(KEY, JSON.stringify(settings)); } catch (e) { /* ignore */ }
}

// Simple pub/sub so systems can react to changes (e.g. passthrough toggle).
const listeners = new Set();
export function onSettingsChange(fn) { listeners.add(fn); return () => listeners.delete(fn); }
export function setSetting(key, value) {
  if (settings[key] === value) return;
  settings[key] = value;
  saveSettings();
  for (const fn of listeners) fn(key, value);
}
