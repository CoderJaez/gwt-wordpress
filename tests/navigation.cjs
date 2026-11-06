/* Focused verification for the enhanced desktop/touch submenu and widget layout. */
const { chromium } = require(process.env.PLAYWRIGHT_MODULE || 'playwright');
const assert = require('node:assert/strict');
const fs = require('node:fs');
(async () => {
  const browser = await chromium.launch({ headless: true, executablePath: process.env.CHROME_PATH });
  try {
    const page = await browser.newPage({ viewport: { width: 1200, height: 900 }, hasTouch: true });
    await page.goto(process.env.TEST_URL || 'http://127.0.0.1:9400', { waitUntil: 'networkidle' });
    const submenu = page.getByRole('button', { name: 'Toggle submenu for About us' });
    await submenu.tap();
    assert.equal(await submenu.getAttribute('aria-expanded'), 'true');
    assert(await page.getByRole('link', { name: 'Mandate and vision' }).isVisible());
    await page.keyboard.press('Escape');
    assert.equal(await submenu.getAttribute('aria-expanded'), 'false');
    assert(await submenu.evaluate(el => el === document.activeElement));
    assert(!await page.getByRole('link', { name: 'Mandate and vision' }).isVisible());
    await page.getByRole('link', { name: 'About us', exact: true }).focus();
    assert(await page.getByRole('link', { name: 'Mandate and vision' }).isVisible());
    await page.locator('h1').tap();
    assert.equal(await submenu.getAttribute('aria-expanded'), 'false');
    await page.setViewportSize({ width: 375, height: 900 });
    await page.getByRole('button', { name: 'Menu', exact: true }).tap();
    assert(await page.getByRole('link', { name: 'Mandate and vision' }).isVisible());
    assert(!await submenu.isVisible());
    assert(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth));
    await page.screenshot({ path: '.test-artifacts/mobile-final.png', fullPage: true });
    await page.setViewportSize({ width: 1440, height: 1000 });
    await page.screenshot({ path: '.test-artifacts/desktop-final.png', fullPage: true });
    await page.setViewportSize({ width: 1200, height: 900 });
    await page.screenshot({ path: 'screenshot.png' });
    fs.writeFileSync('.test-artifacts/navigation-results.txt', '11 focused touch, keyboard, resize, and overflow checks passed.\n');
    console.log('11 focused navigation checks passed.');
  } finally { await browser.close(); }
})().catch(error => { console.error(error); process.exitCode = 1; });
