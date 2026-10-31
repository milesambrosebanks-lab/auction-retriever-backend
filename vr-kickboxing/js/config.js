// Static game data: opponents, difficulty presets, arenas, training drills.
// All fighters are fictional.

export const DIFFICULTIES = {
  easy: {
    id: 'easy', name: 'Rookie', blurb: 'Slow, telegraphed attacks. Glowing hints.',
    reaction: 0.55,       // seconds before the AI reacts defensively
    blockChance: 0.18,    // chance to block an incoming strike it sees
    attackRate: 0.55,     // multiplier on attack frequency
    windup: 1.6,          // multiplier on telegraph time (bigger = easier)
    damage: 0.6,          // multiplier on damage dealt to player
    tracking: 0.15,       // 0..1 how much strikes track your head after launch
    maxCombo: 2,
    hints: true,
    hpScale: 0.85,
  },
  medium: {
    id: 'medium', name: 'Contender', blurb: 'Real combos, real defense.',
    reaction: 0.32, blockChance: 0.38, attackRate: 1.0, windup: 1.0,
    damage: 1.0, tracking: 0.35, maxCombo: 3, hints: false, hpScale: 1.0,
  },
  hard: {
    id: 'hard', name: 'Champion', blurb: 'Fast, smart, punishes mistakes.',
    reaction: 0.18, blockChance: 0.6, attackRate: 1.45, windup: 0.72,
    damage: 1.35, tracking: 0.55, maxCombo: 5, hints: false, hpScale: 1.25,
  },
};

// Strike vocabulary used by the AI. `hand`: 'lead' | 'rear'; `kind` drives animation.
// target: 'head' | 'body' | 'leg'
export const AI_STRIKES = {
  jab:      { kind: 'straight', hand: 'lead', target: 'head', dmg: 5,  windup: 0.28, travel: 0.13, recover: 0.22 },
  cross:    { kind: 'straight', hand: 'rear', target: 'head', dmg: 9,  windup: 0.36, travel: 0.16, recover: 0.3 },
  hookL:    { kind: 'hook',     hand: 'lead', target: 'head', dmg: 10, windup: 0.42, travel: 0.2,  recover: 0.34 },
  hookR:    { kind: 'hook',     hand: 'rear', target: 'head', dmg: 11, windup: 0.45, travel: 0.2,  recover: 0.36 },
  upper:    { kind: 'uppercut', hand: 'rear', target: 'head', dmg: 12, windup: 0.46, travel: 0.18, recover: 0.36 },
  bodyJab:  { kind: 'straight', hand: 'lead', target: 'body', dmg: 5,  windup: 0.3,  travel: 0.14, recover: 0.24 },
  bodyHook: { kind: 'hook',     hand: 'rear', target: 'body', dmg: 9,  windup: 0.42, travel: 0.2,  recover: 0.34 },
  elbow:    { kind: 'elbow',    hand: 'rear', target: 'head', dmg: 14, windup: 0.42, travel: 0.16, recover: 0.4, range: 0.6 },
  knee:     { kind: 'knee',     leg: 'rear',  target: 'body', dmg: 12, windup: 0.45, travel: 0.2,  recover: 0.45, range: 0.6 },
  teep:     { kind: 'teep',     leg: 'lead',  target: 'body', dmg: 7,  windup: 0.45, travel: 0.2,  recover: 0.45, range: 1.1, push: 0.5 },
  lowKick:  { kind: 'lowkick',  leg: 'rear',  target: 'leg',  dmg: 8,  windup: 0.5,  travel: 0.22, recover: 0.45, range: 1.0 },
  bodyKick: { kind: 'roundhouse', leg: 'rear', target: 'body', dmg: 13, windup: 0.6, travel: 0.25, recover: 0.55, range: 1.1 },
  headKick: { kind: 'roundhouse', leg: 'rear', target: 'head', dmg: 20, windup: 0.75, travel: 0.28, recover: 0.65, range: 1.0 },
};

// Opponents. Colors are hex ints. `style` weights choose strikes; `combos` are favourite sequences.
export const OPPONENTS = [
  {
    id: 'rico', name: 'Rico "Spark" Alvarez', nick: 'Boxer', country: 'Puerto Rico',
    blurb: 'Lightning hands and slick head movement. Rarely kicks.',
    height: 1.75, build: 0.95, skin: 0xb07a52, hair: 0x1a1210, shorts: 0xd42a2a, trim: 0xffd23f, gloves: 0xd42a2a,
    speed: 1.25, power: 0.9, defense: 1.1, aggression: 1.1, hp: 100, footwork: 1.3,
    style: { jab: 5, cross: 4, hookL: 3, hookR: 2, upper: 2, bodyJab: 2, bodyHook: 2, lowKick: 0.3 },
    combos: [['jab', 'cross'], ['jab', 'jab', 'cross'], ['jab', 'cross', 'hookL'], ['bodyJab', 'hookL'], ['cross', 'hookL', 'cross']],
  },
  {
    id: 'nok', name: 'Nok Siriporn', nick: 'Muay Thai', country: 'Thailand',
    blurb: 'Clinch-range menace. Elbows, knees and punishing teeps.',
    height: 1.72, build: 0.9, skin: 0xc49468, hair: 0x0d0d0d, shorts: 0x1f4fd1, trim: 0xf2f2f2, gloves: 0x1f4fd1, wraps: 0xf2f2f2,
    speed: 1.05, power: 1.05, defense: 1.0, aggression: 1.0, hp: 105, footwork: 1.0,
    style: { jab: 2, cross: 2, elbow: 3, knee: 3, teep: 3, lowKick: 3, bodyKick: 3, headKick: 1 },
    combos: [['jab', 'bodyKick'], ['teep'], ['cross', 'elbow'], ['jab', 'cross', 'lowKick'], ['knee', 'elbow']],
  },
  {
    id: 'viktor', name: 'Viktor "Anvil" Petrov', nick: 'Brawler', country: 'Bulgaria',
    blurb: 'Slow to start, but every shot can end the night. Huge gas tank.',
    height: 1.9, build: 1.25, skin: 0xe3b897, hair: 0x7a5a3a, shorts: 0x222222, trim: 0xb31b1b, gloves: 0x222222,
    speed: 0.8, power: 1.45, defense: 0.85, aggression: 1.25, hp: 135, footwork: 0.75,
    style: { jab: 1.5, cross: 3, hookL: 4, hookR: 4, upper: 3, bodyHook: 2, lowKick: 1.5 },
    combos: [['hookR'], ['hookL', 'hookR'], ['cross', 'hookL'], ['upper', 'hookL'], ['bodyHook', 'hookL']],
  },
  {
    id: 'kai', name: 'Kai "Tornado" Nakamura', nick: 'Kicker', country: 'Japan',
    blurb: 'Range control specialist. Watch for the head kick.',
    height: 1.8, build: 0.88, skin: 0xd9ad85, hair: 0x101010, shorts: 0xf2f2f2, trim: 0x1b8a3c, gloves: 0x1b8a3c, wraps: 0x1b8a3c,
    speed: 1.15, power: 1.0, defense: 0.95, aggression: 0.95, hp: 100, footwork: 1.4,
    style: { jab: 3, cross: 1.5, teep: 2, lowKick: 2.5, bodyKick: 3, headKick: 2.2 },
    combos: [['jab', 'headKick'], ['lowKick'], ['jab', 'cross', 'bodyKick'], ['teep', 'lowKick'], ['bodyKick']],
  },
  {
    id: 'dex', name: 'Dex "Iron" Okafor', nick: 'Counter-Striker', country: 'Nigeria',
    blurb: 'Patient, high guard. Waits for you to miss, then makes you pay.',
    height: 1.86, build: 1.1, skin: 0x5a3a26, hair: 0x0a0a0a, shorts: 0x7b2cbf, trim: 0xffb703, gloves: 0x7b2cbf,
    speed: 1.05, power: 1.15, defense: 1.4, aggression: 0.75, hp: 115, footwork: 1.0, counter: true,
    style: { jab: 3, cross: 3, hookL: 2, upper: 2, bodyHook: 1.5, lowKick: 1.5, bodyKick: 1 },
    combos: [['cross', 'hookL'], ['jab', 'cross'], ['upper', 'cross'], ['cross', 'lowKick']],
  },
];

export const ARENAS = [
  { id: 'octagon', name: 'The Octagon', blurb: 'Caged canvas under hard white light.' },
  { id: 'ring', name: 'Boxing Ring', blurb: 'Classic four-post ring in an old fight hall.' },
  { id: 'stadium', name: 'Stadium', blurb: 'Main event. Thousands of fans. Light towers.' },
  { id: 'street', name: 'Street', blurb: 'Rain-slick back alley at night. No ref.' },
  { id: 'gym', name: 'Training Gym', blurb: 'Mats, bags and a mirror wall.' },
];

export const DRILLS = [
  { id: 'mitts', name: 'Mitt Work', blurb: 'Coach calls combos. Hit the lit mitt. Watch for return fire.' },
  { id: 'thai', name: 'Thai Pads', blurb: 'Kicks, knees and elbows on the pads.' },
  { id: 'bag', name: 'Heavy Bag', blurb: 'Free work on the bag. Power + speed readouts.' },
  { id: 'reflex', name: 'Reflex Targets', blurb: 'Smash targets as fast as they appear.' },
  { id: 'defense', name: 'Defense Drill', blurb: 'Slip, duck and block incoming shots.' },
];

// Player strike damage tuning.
export const PLAYER = {
  maxHp: 100,
  maxStamina: 100,
  punchMinSpeed: 1.6,     // m/s glove speed to register a strike
  punchFullSpeed: 6.0,    // m/s for full-power punch
  gloveRadius: 0.075,
  elbowRadius: 0.06,
  kicks: {
    // Button-triggered kicks (no foot tracking on Quest).
    roundhouse: { dmg: 9, stamina: 12, range: 1.35, target: 'body', windup: 0.12, active: 0.14, recover: 0.32 },
    highkick:   { dmg: 14, stamina: 16, range: 1.35, target: 'head', windup: 0.16, active: 0.15, recover: 0.4 },
    teep:       { dmg: 5,  stamina: 8,  range: 1.3,  target: 'body', windup: 0.1,  active: 0.12, recover: 0.28, push: 0.6 },
    lowkick:    { dmg: 6,  stamina: 8,  range: 1.2,  target: 'leg',  windup: 0.1,  active: 0.12, recover: 0.28 },
    knee:       { dmg: 8,  stamina: 9,  range: 0.85, target: 'body', windup: 0.08, active: 0.12, recover: 0.26 },
  },
};
