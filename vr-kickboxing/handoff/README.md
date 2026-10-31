# Iron Shin VR — Handoff

WebXR kickboxing game for **Meta Quest 3 / 3S**, running in the Quest Browser. No build step. Everything lives in `vr-kickboxing/` on branch `claude/zen-sagan-5mj577` of `milesambrosebanks-lab/auction-retriever-backend`. That repo is an unrelated Laravel backend: the game is fully self-contained, and deploys only run from `main`.

## Status at handoff

| Area | State |
|---|---|
| Fight mode (5 opponents × 3 difficulties × 4 arenas, rounds, knockdowns, 10-count, TKO, 3-judge decision) | Working; automated in headless Chromium |
| Training (mitts, Thai pads, heavy bag, reflex targets, defense drill) | Working; automated |
| Immersive ⇄ passthrough toggle inside one `immersive-ar` session | Implemented; **not yet tested on a headset** |
| Haptics (impact-scaled, blocks, getting hit, low-HP heartbeat) | Implemented; **not yet felt on a headset** |
| Procedural body mechanics (planted-foot stepping, weight transfer, heel lift, foot pivots, hip→shoulder sequencing, retraction, re-chamber, knockback stumble) | Working; checked with side-view slow-motion captures (`screenshots/strike-mechanics-grid.png`) |
| Realistic fighter models/textures | **Not started.** The previous session's network policy blocked every asset host |
| On-device performance | Not measured. Desktop numbers: 30–37 draw calls per arena; triangles 7k (street) to 178k (stadium) |

**Nothing has been run on a real Quest yet.** That's the first job for the next session (checklist below).

## Run it

```bash
cd vr-kickboxing && python3 -m http.server 8765
```
Open `http://localhost:8765` → **Desktop preview** (drag to look, WASD; punches F/J, hooks R/U, uppercuts V/N, elbows T/Y; kicks 1/2 right and 3/4 left; knees Q/E; Esc pauses).

**On the Quest:** WebXR needs HTTPS (or `localhost`). Options:
1. Enable GitHub Pages on a repo containing `vr-kickboxing/` (simplest; Pages on a private repo requires a paid plan), or move the folder into its own public repo.
2. Run `adb reverse tcp:8765 tcp:8765` with the Quest plugged in over USB, then open `http://localhost:8765` in the Quest Browser.
3. Any static HTTPS host (Netlify, Cloudflare Pages, Vercel). Upload the folder as-is.

## Architecture (`vr-kickboxing/js/`)

| File | Role |
|---|---|
| `main.js` | Renderer (foveation 1.0, framebuffer scale 1.0, MSAA), XR session (`immersive-ar` if supported, otherwise `immersive-vr`; `local-floor`; optional `layers`), refresh-rate select, main loop, locomotion, `game` context object (exposed as `window.__game`; `game.timeScale` gives slow motion) |
| `input.js` | Controllers → `hand.pos/vel/speed/elbow/buttons/axes`. Uses runtime `linearVelocity` when the browser provides it. Keyboard/mouse simulator for desktop. Haptics: `pulse(side, intensity, ms)`, `pattern()` |
| `player.js` | Player gloves, swept glove/elbow contact detection (one hit per punch), punch classification (jab/cross/hook/uppercut), button kicks/knees with a visible leg, stamina, `evaluateIncoming()` (slip/block/hit against your real head and hands) |
| `fighter.js` | Procedural humanoid: primitives + 2-bone IK, planted-foot stepping (`_feet`), weight/heel/foot-pivot, hit springs, knockdown pivot, gloves/mitts/pads, hurtboxes |
| `fight.js` | `OpponentAI` (footwork, combos, telegraph→commit window, lunges, guard/slip/counter, stun, knockback), `MECH` per-strike body-mechanics keyframes, `FightMode` (rounds, knockdowns, scoring) |
| `training.js` | `TrainingMode`: coach mitts/pads (`MITT`/`THAI` callouts), pendulum heavy bag on a stand, reflex targets plus duck bars, defense drill (reuses `OpponentAI` via `onResolve`) |
| `arenas.js` | Octagon, ring, stadium, street, gym. Static geometry merged via `GeoBatch`; crowd is one `InstancedMesh` animated in the vertex shader; sky dome (needed because three.js clears to transparent in AR) |
| `ui.js` | Canvas panels with laser pointer, floating text, announcer, scoreboard, `ScreenFX` (hit flash, vignette, fade) |
| `menu.js` | All in-headset menus, 3D opponent preview, wrist MENU button (arms only when the wrist faces your eyes) |
| `audio.js` | All sounds are synthesized in WebAudio (HRTF-spatial impacts), plus ambience and `speechSynthesis` callouts |
| `config.js` / `settings.js` | Opponents, difficulties, strike tables, player tuning; persisted settings |

### Conventions you must know
- **Fighter local frame:** feet at the origin, facing **+Z**. The fighter's **left is +X**; orthodox lead side = left = `'L'`. `root.rotation.y = atan2(dx, dz)` faces a target.
- **WebXR grip space:** knuckles point along −Y, the forearm along **+Y**, the thumb along −Z. The player glove mesh and elbow estimate depend on this.
- **Player yaw:** 0 = facing −Z. `game.placePlayer(x, z, yaw)` moves the rig so your head lands at (x, z).
- **Light intensities** in `arenas.js` are legacy units, multiplied by `LIGHT_SCALE` in `main.js`.
- **GLSL:** don't name variables `active` (reserved word; it already broke a shader once).

## Tests (`handoff/tests/`, Playwright, headless Chromium + SwiftShader)
Run from any directory that has `playwright` available. The scripts take `BASE_URL` (default `http://localhost:8765`), and most take an output folder for screenshots.
- `smoke-landing-menu.js OUT` — landing page and menu, console errors
- `fights-all-arenas.js OUT` — fight in each arena; draw calls and triangles
- `fight-log.js OUT [opp] [diff]`, `ai-vs-passive-player.js [opp] [diff] [secs]` — AI behavior logs
- `training-drills.js OUT` — all 5 drills plus the results screen
- `strike-mechanics-sideview.js OUT [moves]` — slow-motion side camera at windup/impact/recover
- `leg-geometry-dump.js` — joint positions (used to fix the leg proportions)

Software rendering runs at a few FPS, so game time runs slower than wall-clock time. Judge logic and pose, not speed.

## Next steps (priority order)
1. **Test on a headset** (checklist below). Fix whatever device-only issues come up, especially the grip-space orientation of the gloves and elbows, the wrist button placement, haptic strength, and passthrough compositing.
2. **Realistic fighters.** The user explicitly asked for this. Plan:
   - Rigged, textured humanoid GLB per fighter. Licenses must allow redistribution. Candidates: Quaternius (CC0), Poly Haven / ambientCG (CC0 textures), Mixamo (free to use in projects, but check redistribution terms before committing assets to a repo).
   - Keep the gameplay layer and swap only the visual layer. `Fighter` already exposes every joint target (`j.*`, `hand`, `foot`, poles, `torsoYaw`/`hipYaw`/`weight`...). Either drive the GLB skeleton from these with bone retargeting and IK, or play motion-capture clips through `AnimationMixer` and keep IK on top for strike aiming. Hurtboxes and `glove/elbowW/footW/kneeW` outputs must stay correct; `fight.js` relies on them.
   - Budget on Quest 3S: about 10–20k triangles per fighter, one skinned mesh, 1–2 1K–2K textures (KTX2/Basis if possible), no real-time shadows.
3. **Hosting:** set up GitHub Pages or another HTTPS host so the user can just open a URL.
4. Nice to have: hand-tracking fallback, more fight styles (MMA clinch and takedowns were mentioned as future scope), a referee, saved career/stats.

## On-device test checklist
- [ ] Enter VR shows the menu about 1.45 m in front of you; the laser pointer selects with the trigger; hover gives a tick on the controller.
- [ ] Immersive ⇄ passthrough switches live from the main menu, pause menu and settings.
- [ ] Gloves sit on your fists (knuckles forward). If they're rotated, fix the grip-space assumptions in `player.js` `gloveMesh()`, the elbow estimate in `input.js` `_elbow()`, and the basis in `menu.js` `WristMenu`.
- [ ] Punch speed feels right (`PLAYER.punchMinSpeed` and `punchFullSpeed` in `config.js`); jab, cross, hook and uppercut classify correctly; elbows register.
- [ ] A/B/X/Y kicks and trigger knees fire, the leg is visible, and kicks land in range.
- [ ] Slipping and blocking real shots works at Rookie/Contender/Champion; telegraph glow shows on Rookie.
- [ ] Wrist MENU tap pauses, and doesn't trigger by accident in a high guard.
- [ ] Haptic strength feels good at all 4 settings.
- [ ] Frame rate holds at 90 and 120 Hz in the stadium (worst case). Turn on Settings → FPS.
- [ ] Seated mode, snap/smooth turn and the comfort vignette all work.
- [ ] All 5 drills complete and show results; the bag swings believably.

## Network allowlist needed for the asset work
`ambientcg.com`, `polyhaven.com`, `dl.polyhaven.org`, `api.polyhaven.com`, `quaternius.com`, `opengameart.org`, `sketchfab.com`, plus whatever hosts those redirect downloads to. GitHub and npm were already reachable. Network policy changes only take effect in a **new** session.
