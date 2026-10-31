const { chromium } = require('playwright');
const OUT = process.argv[2];
(async () => {
  const browser = await chromium.launch({ args: ['--use-gl=angle', '--use-angle=swiftshader', '--enable-unsafe-swiftshader'] });
  const page = await browser.newPage({ viewport: { width: 1280, height: 800 } });
  const errors = [];
  page.on('console', (m) => { if (m.type() === 'error' || m.type() === 'warning') errors.push(m.type() + ': ' + m.text()); });
  page.on('pageerror', (e) => errors.push('pageerror: ' + e.message + '\n' + e.stack));
  await page.goto((process.env.BASE_URL || 'http://localhost:8765') + '/index.html');
  await page.waitForTimeout(2500);
  await page.screenshot({ path: OUT + '/01-landing.png' });
  await page.click('#desktop');
  await page.waitForTimeout(2000);
  await page.screenshot({ path: OUT + '/02-menu.png' });
  console.log('ERRORS:\n' + errors.join('\n'));
  await browser.close();
})();
