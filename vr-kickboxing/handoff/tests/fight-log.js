const { chromium } = require('playwright');
const OUT = process.argv[2];
const OPP = process.argv[3] || 'rico';
const DIFF = process.argv[4] || 'medium';
(async () => {
  const browser = await chromium.launch({ args: ['--use-gl=angle', '--use-angle=swiftshader', '--enable-unsafe-swiftshader'] });
  const page = await browser.newPage({ viewport: { width: 800, height: 500 } });
  const errors = [];
  page.on('console', (m) => { if (m.type() === 'error' || m.type() === 'warning') errors.push(m.type() + ': ' + m.text().slice(0, 800)); });
  page.on('pageerror', (e) => errors.push('pageerror: ' + e.message + '\n' + e.stack));
  await page.goto((process.env.BASE_URL || 'http://localhost:8765') + '/index.html');
  await page.waitForTimeout(1200);
  await page.click('#desktop');
  await page.waitForTimeout(500);
  await page.evaluate(([o, d]) => import('./js/settings.js').then((m) => { m.setSetting('arena', 'octagon'); m.setSetting('opponent', o); m.setSetting('difficulty', d); m.setSetting('roundSeconds', 60); m.setSetting('rounds', 1); window.__game.startFight(); }), [OPP, DIFF]);
  const log = [];
  for (let i = 0; i < 40; i++) {
    // punch when close
    const st = await page.evaluate(() => { const g = window.__game, f = g.fight; if (!f) return null; return { t: g.time.toFixed(1), state: f.state, ai: f.ai.state, mv: f.ai.move && f.ai.move.key, d: +f.ai.distToPlayer().toFixed(2), aiHp: Math.round(f.ai.hp), pHp: Math.round(g.player.hp), thrown: f.ai.stats.thrown, landed: f.ai.stats.landed, pl: g.player.stats.landed, guard: f.ai.guard }; });
    log.push(JSON.stringify(st));
    if (st && st.d < 1.1) { await page.keyboard.press(i % 3 === 0 ? 'KeyF' : i % 3 === 1 ? 'KeyJ' : 'KeyR'); }
    if (i % 7 === 3) await page.keyboard.press('Digit1');
    if (i === 20) await page.screenshot({ path: `${OUT}/mid-${OPP}.png` });
    await page.waitForTimeout(400);
  }
  console.log(log.join('\n'));
  console.log('ERRORS:\n' + [...new Set(errors)].join('\n'));
  await browser.close();
})();
