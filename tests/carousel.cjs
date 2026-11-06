const { chromium } = require(process.env.PLAYWRIGHT_MODULE || "playwright");
const assert = require("node:assert/strict");
const fs = require("node:fs");
(async () => {
  const browser = await chromium.launch({
    headless: true,
    executablePath: process.env.CHROME_PATH,
  });
  const results = [];
  const check = (value, label) => {
    assert(value, label);
    results.push(label);
  };
  try {
    const page = await browser.newPage();
    const errors = [];
    page.on("pageerror", (error) => errors.push(error.message));
    await page.goto(process.env.TEST_URL || "http://127.0.0.1:9400", {
      waitUntil: "networkidle",
    });
    const carousel = page.locator("[data-carousel]");
    check((await carousel.count()) === 1, "Homepage Carousel renders");
    check(
      (await page.locator(".site-branding, .header-widget-area").count()) === 1,
      "Header branding or Header widget is restored",
    );
    check(
      (await page
        .locator("main .homepage-carousel-area [data-carousel]")
        .count()) === 1,
      "Carousel is inside homepage main content",
    );
    check(
      (await page.locator(".home-intro, .public-links").count()) === 0,
      "Pictured introduction and information panel removed",
    );
    check(
      (await carousel.getAttribute("data-autoplay")) === "true",
      "Automatic rotation enabled",
    );
    const initial = await carousel
      .locator("[data-slide][aria-pressed=true]")
      .getAttribute("data-slide");
    await page.waitForTimeout(
      Number(await carousel.getAttribute("data-interval")) + 500,
    );
    check(
      initial !==
        (await carousel
          .locator("[data-slide][aria-pressed=true]")
          .getAttribute("data-slide")),
      "Pictures advance automatically without interaction",
    );
    check(
      (await page.locator(".banner-gallery").count()) === 0,
      "Legacy banner gallery is not duplicated",
    );
    const slides = carousel.locator(".dilg-carousel-slide");
    check(
      (await slides.count()) === 3,
      "Three public slides, no protected slide",
    );
    await carousel.getByRole("button", { name: "Show slide 1" }).click();
    check(await slides.nth(0).isVisible(), "Direct slide selection");
    await carousel
      .getByRole("button", { name: "Next slide", exact: true })
      .click();
    check(await slides.nth(1).isVisible(), "Next control advances");
    check(!(await slides.nth(0).isVisible()), "Inactive slide hidden");
    await carousel
      .getByRole("button", { name: "Previous slide", exact: true })
      .click();
    check(await slides.nth(0).isVisible(), "Previous control moves back");
    await carousel
      .getByRole("button", { name: "Previous slide", exact: true })
      .click();
    check(await slides.nth(2).isVisible(), "Previous wraps to last slide");
    await page.waitForTimeout(4500);
    check(await slides.nth(2).isVisible(), "Manual navigation stops autoplay");
    await carousel
      .getByRole("button", { name: "Play slideshow", exact: true })
      .click();
    await page.evaluate(() => window.scrollTo(0, 0));
    await page.mouse.move(0, 0);
    await page.waitForTimeout(4500);
    check(await slides.nth(0).isVisible(), "Explicit Play resumes autoplay");
    await carousel
      .getByRole("button", { name: "Pause slideshow", exact: true })
      .click();
    const selected = await carousel
      .locator("[data-slide][aria-pressed=true]")
      .getAttribute("data-slide");
    await page.evaluate(() => window.scrollTo(0, 0));
    await page.mouse.move(0, 0);
    await page.waitForTimeout(4500);
    check(
      selected ===
        (await carousel
          .locator("[data-slide][aria-pressed=true]")
          .getAttribute("data-slide")),
      "Pause click does not accidentally restart autoplay",
    );
    await page.emulateMedia({ reducedMotion: "reduce" });
    check(
      !(await carousel.locator("[data-rotation]").isVisible()),
      "Reduced motion disables playback control",
    );
    check(
      (await carousel
        .locator(".dilg-carousel-slide:visible")
        .evaluate((el) => getComputedStyle(el).animationName)) === "none",
      "Reduced motion removes slide animation",
    );
    for (const width of [320, 768, 1440]) {
      await page.setViewportSize({ width, height: 900 });
      check(
        await page.evaluate(
          () => document.documentElement.scrollWidth <= innerWidth,
        ),
        `No overflow at ${width}px`,
      );
      const box = await carousel.boundingBox();
      check(Math.abs(box.width - width) < 2, `Full width at ${width}px`);
      await page.screenshot({
        path: `.test-artifacts/carousel-${width}.png`,
        fullPage: false,
      });
    }
    const fallback = await browser.newPage({ javaScriptEnabled: false });
    await fallback.goto(process.env.TEST_URL || "http://127.0.0.1:9400");
    check(
      (await fallback.locator(".dilg-carousel-slide:visible").count()) === 3,
      "All slides readable without JavaScript",
    );
    check(
      !(await fallback.locator(".dilg-carousel-controls").isVisible()),
      "No inert controls without JavaScript",
    );
    check(errors.length === 0, `No JavaScript errors: ${errors.join(", ")}`);
    fs.writeFileSync(
      ".test-artifacts/carousel-browser.json",
      JSON.stringify(results, null, 2),
    );
    console.log(`${results.length} carousel browser checks passed.`);
  } finally {
    await browser.close();
  }
})().catch((error) => {
  console.error(error);
  process.exitCode = 1;
});
