const { chromium } = require('playwright');
const OUT = process.argv[2];
const MOVES = (process.argv[3] || 'cross,hookL,bodyKick,headKick,teep,knee').split(',');
(async () => {
  const browser = await chromium.launch({ args: ['--use-gl=angle', '--use-angle=swiftshader', '--enable-unsafe-swiftshader'] });
  const page = await browser.newPage({ viewport: { width: 520, height: 520 } });
  const errors = [];
  page.on('pageerror', (e) => errors.push('pageerror: ' + e.message + '\n' + e.stack));
  await page.goto((process.env.BASE_URL || 'http://localhost:8765') + '/index.html');
  await page.waitForTimeout(1000);
  await page.click('#desktop');
  await page.evaluate(() => import('./js/settings.js').then((m) => { m.setSetting('arena', 'gym'); m.setSetting('opponent', 'nok'); m.setSetting('difficulty', 'easy'); m.setSetting('roundSeconds', 180); window.__game.startFight(); }));
  await page.evaluate(async () => {
    const THREE = await import('three');
    const g = window.__game;
    const cam = new THREE.PerspectiveCamera(50, 1, 0.05, 100);
    window.__side = cam;
    const orig = g.renderer.render.bind(g.renderer);
    g.renderer.render = (scene) => {
      const f = g.fight && g.fight.ai && g.fight.ai.f;
      if (f) { const p = f.root.position, h = g.input.head.pos; const mx = (p.x + h.x) / 2, mz = (p.z + h.z) / 2; cam.position.set(mx + 3.0, 1.2, mz + 0.4); cam.lookAt(mx, 1.0, mz); }
      orig(scene, cam);
    };
  });
  await page.waitForTimeout(6000);
  for (const mv of MOVES) {
    await page.evaluate((k) => { const ai = window.__game.fight.ai; ai.pos.set(window.__game.input.head.pos.x, 0, window.__game.input.head.pos.z - 0.85); ai.state = 'neutral'; ai.queue = []; ai.startMove(k); }, mv);
    const fr = [0.5, 0.95, 1.0, 1.5];
    for (let i = 0; i < 4; i++) {
      // advance game time to the wanted phase: frac 1.0 = impact
      await page.evaluate((fr) => { const g = window.__game, m = g.fight.ai.move; g.timeScale = 0.25; window.__goal = (m.durW + m.durT) * fr; }, fr[i]);
      await page.waitForFunction(() => { const m = window.__game.fight.ai.move; return !m || m.t >= window.__goal; }, null, { timeout: 20000 });
      await page.evaluate(() => { window.__game.timeScale = 0; });
      await page.waitForTimeout(250);
      await page.screenshot({ path: `${OUT}/mech-${mv}-${i}.png` });
    }
    await page.evaluate(() => { window.__game.timeScale = 1; });
    await page.waitForTimeout(1800);
  }
  console.log('ERRORS:', errors.join('\n'));
  await browser.close();
})();
