# Paste this into the next session

> Continue the **Iron Shin VR** WebXR kickboxing game for Meta Quest 3S. It's in `vr-kickboxing/` on branch `claude/zen-sagan-5mj577` of `milesambrosebanks-lab/auction-retriever-backend`. That repo is otherwise a Laravel backend: don't touch anything outside `vr-kickboxing/`, and don't push to `main` (it auto-deploys the backend).
>
> Read `vr-kickboxing/handoff/README.md` first. It covers the architecture, coordinate conventions, the test scripts in `handoff/tests/`, and the priority list.
>
> Main goal for this session: **replace the primitive-shape fighters with realistic, rigged, textured human models and motion that looks like real fighting.** Download openly licensed assets (CC0 preferred: Quaternius, Poly Haven, ambientCG; check redistribution terms for anything else) into `vr-kickboxing/assets/`, with a `LICENSES.md` recording each asset's source and license. Keep the gameplay layer (`OpponentAI`, `MECH` body mechanics, hurtboxes and joint outputs in `Fighter`) working: swap the visual layer, don't rewrite the game. Stay within Quest 3S budgets (about 10–20k triangles per fighter, compressed textures, no real-time shadows).
>
> Before and after, run the Playwright tests in `handoff/tests/` (serve with `python3 -m http.server 8765` from `vr-kickboxing/`) and compare against the screenshots in `handoff/screenshots/`. Give me short progress bullets as you go, and commit and push to the same branch when done.
