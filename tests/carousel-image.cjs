const { chromium } = require(process.env.PLAYWRIGHT_MODULE || 'playwright');
const assert = require('node:assert/strict');
const fs = require('node:fs');
(async () => {
  const browser = await chromium.launch({ headless: true, executablePath: process.env.CHROME_PATH });
  const results = [];
  const check = (value, label) => { assert(value, label); results.push(label); };
  try {
    const page = await browser.newPage({ viewport: { width: 1440, height: 1000 } });
    const errors = [];
    page.on('pageerror', error => errors.push(error.message));
    await page.goto('http://127.0.0.1:9400', { waitUntil: 'networkidle' });
    const root = page.locator('[data-carousel]');
    check(await root.getAttribute('data-interval') === '50', 'Default interval is 50 milliseconds');
    check(await root.locator('.dilg-carousel-slide:visible').count() === 1, 'Only one image visible');
    check(await page.locator('.dilg-carousel-caption, .dilg-carousel-dots').count() === 0, 'No caption panel or numbered controls');
    check(await page.locator('.site-branding, .header-widget-area').count() === 1, 'Header preserved');
    const changes = await root.evaluate(async element => {
      let count = 0;
      const observer = new MutationObserver(records => { count += records.filter(r => r.attributeName === 'hidden').length; });
      observer.observe(element, { subtree: true, attributes: true, attributeFilter: ['hidden'] });
      await new Promise(resolve => setTimeout(resolve, 400));
      observer.disconnect();
      return count;
    });
    check(changes >= 4, 'Automatic rotation advances repeatedly at the short interval');
    await root.getByRole('button', { name: 'Next slide', exact: true }).click();
    const index = await root.locator('.dilg-carousel-slide:visible').getAttribute('aria-label');
    await page.waitForTimeout(200);
    check(index === await root.locator('.dilg-carousel-slide:visible').getAttribute('aria-label'), 'Manual navigation pauses rotation');
    await root.getByRole('button', { name: 'Previous slide', exact: true }).click();
    check(index !== await root.locator('.dilg-carousel-slide:visible').getAttribute('aria-label'), 'Left arrow navigates');
    await root.getByRole('button', { name: 'Next slide', exact: true }).click();
    check(index === await root.locator('.dilg-carousel-slide:visible').getAttribute('aria-label'), 'Right arrow navigates');
    check(await root.locator('.dilg-carousel-slide:visible a img').count() === 1, 'Banner image is the destination link');
    await page.emulateMedia({ reducedMotion: 'reduce' });
    await root.locator('[data-rotation]').waitFor({ state: 'hidden' });
    check(!await root.locator('[data-rotation]').isVisible(), 'Reduced motion disables autoplay control');
    check(await root.locator('.dilg-carousel-slide:visible').evaluate(el => getComputedStyle(el).animationName === 'none'), 'Reduced motion removes animation');
    for (const width of [320, 768, 1440]) {
      await page.setViewportSize({ width, height: 1000 });
      check(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth), `No overflow at ${width}px`);
      const frame = await root.boundingBox();
      const left = await root.locator('[data-previous]').boundingBox();
      const right = await root.locator('[data-next]').boundingBox();
      check(Math.abs(frame.width - width) < 2, `Full width at ${width}px`);
      check(left.x < right.x && left.y >= frame.y && right.y + right.height <= frame.y + frame.height, `Arrows overlay the image at ${width}px`);
      await page.screenshot({ path: `.test-artifacts/carousel-image-${width}.png` });
    }
    check(errors.length === 0, 'No browser JavaScript errors');
    fs.writeFileSync('.test-artifacts/carousel-image-results.json', JSON.stringify(results, null, 2));
    console.log(`${results.length} image carousel checks passed.`);
  } finally { await browser.close(); }
})().catch(error => { console.error(error); process.exitCode = 1; });
