const { chromium } = require(process.env.PLAYWRIGHT_MODULE || "playwright");
const assert = require("node:assert/strict");
const fs = require("node:fs");
const fixture = JSON.parse(
  fs.readFileSync(".test-artifacts/component-fixture.json", "utf8"),
);
(async () => {
  const browser = await chromium.launch({
    headless: true,
    executablePath: process.env.CHROME_PATH,
  });
  const results = [];
  const check = (value, label) => {
    assert(value, label);
    results.push(label);
    console.log(label);
  };
  try {
    const page = await browser.newPage({
      viewport: { width: 1440, height: 1000 },
      reducedMotion: "reduce",
    });
    page.setDefaultTimeout(60000);
    const errors = [];
    page.on("pageerror", (e) => errors.push(e.message));
    await page.goto(`http://127.0.0.1:9400/?page_id=${fixture.page}`, {
      waitUntil: "networkidle",
      timeout: 120000,
    });
    const roots = page.locator("[data-gov-carousel]");
    check((await roots.count()) === 2, "Two shortcode instances rendered");
    const first = roots.nth(0),
      second = roots.nth(1);
    await page.emulateMedia({ reducedMotion: "no-preference" });
    check(
      JSON.parse(await first.getAttribute("data-gov-carousel")).interval_ms ===
        5000,
      "Default autoplay 5000ms",
    );
    await first.locator("[data-next]").click();
    check(
      (await first.locator("[data-counter]").textContent()).startsWith(
        "Slides 2",
      ),
      "Next arrow advances",
    );
    check(
      (await second.locator("[data-counter]").textContent()).startsWith(
        "Slides 1",
      ),
      "Independent navigation",
    );
    await first.locator("[data-next]").press("ArrowLeft");
    check(
      (await first.locator("[data-counter]").textContent()).startsWith(
        "Slides 1",
      ),
      "Keyboard navigation",
    );
    await first.locator("[data-pagination] button").nth(2).click();
    check(
      (await first
        .locator("[data-pagination] button")
        .nth(2)
        .getAttribute("aria-pressed")) === "true",
      "Accessible pagination",
    );
    check(
      (await first.locator("[data-rotation]").textContent()) ===
        "Play slideshow",
      "Interaction stops autoplay",
    );
    for (const [width, visible] of [
      [375, 2],
      [768, 2],
      [1024, 3],
      [1440, 3],
    ]) {
      await page.setViewportSize({ width, height: 1100 });
      await page.waitForTimeout(100);
      check(
        (await second.locator(".gov-carousel__slide:visible").count()) ===
          visible,
        `Responsive count at ${width}px`,
      );
      check(
        await page.evaluate(
          () => document.documentElement.scrollWidth <= innerWidth,
        ),
        `No horizontal overflow at ${width}px`,
      );
      check(
        (await first.locator(".gov-carousel__description:visible").count()) ===
          1,
        `Descriptions retained at ${width}px`,
      );
      await first.screenshot({
        path: `.test-artifacts/component-${width}.png`,
      });
    }
    check(
      await second.locator("[data-previous]").isDisabled(),
      "Nonloop previous boundary",
    );
    await second.locator("[data-next]").click();
    check(
      await second.locator("[data-next]").isDisabled(),
      "Nonloop next boundary",
    );
    await page.mouse.move(0, 0);
    await page.evaluate(() => document.activeElement.blur());
    await first.locator("[data-rotation]").click();
    await page.mouse.move(0, 0);
    const before = await first.locator("[data-counter]").textContent();
    await page.waitForTimeout(5300);
    check(
      (await first.locator("[data-counter]").textContent()) !== before,
      "Automatic slide after delay",
    );
    await page.emulateMedia({ reducedMotion: "reduce" });
    await first.locator("[data-rotation]").waitFor({ state: "hidden" });
    check(
      await first.locator("[data-rotation]").isHidden(),
      "Reduced motion disables playback",
    );
    const reduced = await first.locator("[data-counter]").textContent();
    await page.waitForTimeout(5200);
    check(
      (await first.locator("[data-counter]").textContent()) === reduced,
      "Reduced motion remains stationary",
    );
    const nojs = await browser.newContext({ javaScriptEnabled: false });
    const fallback = await nojs.newPage();
    await fallback.goto(`http://127.0.0.1:9400/?page_id=${fixture.page}`, {
      timeout: 120000,
    });
    check(
      (await fallback.locator(".gov-carousel__slide:visible").count()) === 2 &&
        (await fallback.locator(".gov-carousel__button:visible").count()) === 4,
      "No-JavaScript first slides and usable links",
    );
    await nojs.close();
    await page.goto("http://127.0.0.1:9400/wp-login.php", { timeout: 120000 });
    await page.locator("#user_login").fill(fixture.username);
    await page.locator("#user_pass").fill("Carousel-Test-Only-2026!");
    await Promise.all([
      page.waitForURL("**/wp-admin/**", { timeout: 120000 }),
      page.locator("#wp-submit").click(),
    ]);
    await page.goto("http://127.0.0.1:9400/wp-admin/widgets.php", {
      waitUntil: "domcontentloaded",
      timeout: 120000,
    });
    await page.waitForFunction(() => !!window.gwtCarouselEditor);
    await page.screenshot({
      path: ".test-artifacts/component-admin.png",
      fullPage: false,
    });
    check(
      await page.evaluate(() => !!window.gwtCarouselEditor),
      "Widget editor and media assets loaded",
    );
    const preview = await page.evaluate(async () => {
      const c = window.gwtCarouselEditor;
      const request = async (nonce) => {
        const response = await fetch(c.url, {
          method: "POST",
          body: new URLSearchParams({
            action: "gwt_carousel_preview",
            nonce,
            settings: JSON.stringify({ source: "manual" }),
          }),
        });
        return { status: response.status, text: await response.text() };
      };
      return { good: await request(c.nonce), bad: await request("invalid") };
    });
    check(
      preview.good.status === 200 &&
        preview.good.text.includes("No carousel slides"),
      "Authorized server preview",
    );
    check(preview.bad.status === 403, "Preview rejects invalid nonce");
    check(errors.length === 0, `No JavaScript errors: ${errors.join("; ")}`);
    fs.writeFileSync(
      ".test-artifacts/component-browser.json",
      JSON.stringify(results, null, 2),
    );
    console.log(results.join("\n"));
  } finally {
    await browser.close();
  }
})().catch((e) => {
  console.error(e);
  process.exitCode = 1;
});
