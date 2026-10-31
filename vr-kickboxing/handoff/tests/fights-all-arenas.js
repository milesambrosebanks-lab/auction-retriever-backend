const { chromium } = require('playwright');
const OUT = process.argv[2];
(async () => {
  const browser = await chromium.launch({ args: ['--use-gl=angle', '--use-angle=swiftshader', '--enable-unsafe-swiftshader'] });
  const page = await browser.newPage({ viewport: { width: 1100, height: 700 } });
  const errors = [];
  page.on('console', (m) => { if (m.type() === 'error' || m.type() === 'warning') errors.push(m.type() + ': ' + m.text().slice(0, 600)); });
  page.on('pageerror', (e) => errors.push('pageerror: ' + e.message + '\n' + e.stack));
  await page.goto((process.env.BASE_URL || 'http://localhost:8765') + '/index.html');
  await page.waitForTimeout(1500);
  await page.click('#desktop');
  await page.waitForTimeout(800);
  const arenas = ['octagon', 'ring', 'stadium', 'street'];
  const opps = ['rico', 'nok', 'viktor', 'kai', 'dex'];
  for (let i = 0; i < arenas.length; i++) {
    await page.evaluate(([a, o]) => {
      const g = window.__game;
      const s = g.menu; // settings via module not exposed; set through game.startMode
      return import('./js/settings.js').then((m) => { m.setSetting('arena', a); m.setSetting('opponent', o); m.setSetting('roundSeconds', 60); g.startFight(); });
    }, [arenas[i], opps[i]]);
    await page.waitForTimeout(3500);
    // throw punches
    for (let k = 0; k < 6; k++) { await page.keyboard.press(k % 2 ? 'KeyJ' : 'KeyF'); await page.waitForTimeout(180); }
    await page.keyboard.press('Digit1');
    await page.waitForTimeout(1500);
    const info = await page.evaluate(() => {
      const g = window.__game, r = g.renderer.info.render;
      const f = g.fight;
      return { calls: r.calls, tris: r.triangles, state: f && f.state, aiState: f && f.ai.state, aiHp: f && Math.round(f.ai.hp), pHp: Math.round(g.player.hp), landed: g.player.stats.landed, thrown: g.player.stats.thrown, aiThrown: f && f.ai.stats.thrown, dist: f && f.ai.distToPlayer().toFixed(2) };
    });
    console.log(arenas[i], JSON.stringify(info));
    await page.screenshot({ path: `${OUT}/fight-${arenas[i]}.png` });
  }
  console.log('ERRORS:\n' + [...new Set(errors)].join('\n'));
  await browser.close();
})();
