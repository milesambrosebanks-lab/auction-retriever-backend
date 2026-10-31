const { chromium } = require('playwright');
const OUT = process.argv[2];
(async () => {
  const browser = await chromium.launch({ args: ['--use-gl=angle', '--use-angle=swiftshader', '--enable-unsafe-swiftshader'] });
  const page = await browser.newPage({ viewport: { width: 900, height: 560 } });
  const errors = [];
  page.on('pageerror', (e) => errors.push('pageerror: ' + e.message + '\n' + e.stack));
  page.on('console', (m) => { if (m.type() === 'error' || m.type() === 'warning') errors.push(m.text().slice(0, 500)); });
  await page.goto((process.env.BASE_URL || 'http://localhost:8765') + '/index.html');
  await page.waitForTimeout(1200);
  await page.click('#desktop');
  for (const drill of ['mitts', 'thai', 'bag', 'reflex', 'defense']) {
    await page.evaluate((d) => import('./js/settings.js').then((m) => { m.setSetting('drill', d); m.setSetting('trainingArena', 'gym'); m.setSetting('difficulty', 'easy'); window.__game.startTraining(); }), drill);
    await page.waitForTimeout(4500);
    for (let i = 0; i < 10; i++) { await page.keyboard.press(['KeyF', 'KeyJ', 'KeyR', 'Digit1', 'Digit3'][i % 5]); await page.waitForTimeout(250); }
    const st = await page.evaluate(() => { const m = window.__game.mode; return { state: m.state, score: m.score, hits: m.hits, misses: m.misses, time: m.time.toFixed(1), active: m.active && m.active.key, bag: m.bag && { strikes: m.bag.strikes, tilt: [m.bag.tilt.x.toFixed(3), m.bag.tilt.y.toFixed(3)] } }; });
    console.log(drill, JSON.stringify(st));
    await page.screenshot({ path: `${OUT}/train-${drill}.png` });
  }
  // finish a drill to see the results panel
  await page.evaluate(() => { window.__game.mode.time = 0.01; });
  await page.waitForTimeout(4000);
  await page.screenshot({ path: `${OUT}/train-results.png` });
  console.log('ERRORS:', [...new Set(errors)].join('\n'));
  await browser.close();
})();
