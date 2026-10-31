const { chromium } = require('playwright');
(async () => {
  const browser = await chromium.launch({ args: ['--use-gl=angle', '--use-angle=swiftshader', '--enable-unsafe-swiftshader'] });
  const page = await browser.newPage({ viewport: { width: 300, height: 200 } });
  await page.goto((process.env.BASE_URL || 'http://localhost:8765') + '/index.html'); await page.waitForTimeout(800); await page.click('#desktop');
  await page.evaluate(() => import('./js/settings.js').then((m) => { m.setSetting('opponent', 'nok'); window.__game.startFight(); }));
  await page.waitForTimeout(3000);
  console.log(await page.evaluate(() => { const f = window.__game.fight.ai.f, j = f.j; const r = (v) => v.toArray().map((x) => x.toFixed(3)).join(','); return { hip: r(j.hip), hpL: r(j.hpL), knL: r(j.knL), anL: r(j.anL), hpR: r(j.hpR), knR: r(j.knR), anR: r(j.anR), footCurL: r(f.footCur.L), footL: r(f.foot.L), thigh: f.dims.thigh, plantL: f.plant.L.init, down: f.downAmt }; }));
  await browser.close();
})();
