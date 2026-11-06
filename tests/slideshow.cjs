const { chromium } = require(process.env.PLAYWRIGHT_MODULE || "playwright");
const fs = require("node:fs");
const assert = require("node:assert/strict");
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
    const fixture = JSON.parse(
      fs.readFileSync(".test-artifacts/slideshow-fixture.json", "utf8"),
    );
    const page = await browser.newPage({
      viewport: { width: 1440, height: 1000 },
    });
    const errors = [];
    page.on("pageerror", (e) => errors.push(e.message));
    await page.route("http://**/*", (route) => route.abort());
    const markup = `<style>body{margin:0} ${fs.readFileSync("css/carousel.css", "utf8")}</style>${Object.values(fixture).join("")}`;
    await page.setContent(markup);
    await page.addScriptTag({
      content: fs.readFileSync("js/carousel.js", "utf8"),
    });
    const roots = page.locator("[data-gov-carousel]");
    // Stop automatic playback while measuring deterministic animation frames.
    await roots.evaluateAll((nodes) =>
      nodes.forEach((n) => n.querySelector("[data-rotation]").click()),
    );
    for (const [i, effect] of ["slide", "fade", "slide-up", "none"].entries()) {
      const root = roots.nth(i);
      await root.locator("[data-next]").click();
      const state = await root.evaluate((node) => {
        const old = node.querySelector(".gov-carousel__slide--leaving");
        const animations = node.getAnimations({ subtree: true });
        animations.forEach((a) => {
          a.pause();
          a.currentTime = 300;
        });
        return {
          count: animations.length,
          inert: old?.inert,
          hidden: old?.getAttribute("aria-hidden"),
          frames: animations.map((a) => a.effect.getKeyframes()),
          active: node.querySelectorAll(
            ".gov-carousel__slide:not([hidden]):not([aria-hidden=true])",
          ).length,
        };
      });
      check(state.active === 1, `${effect}: one accessible active slide`);
      if (effect === "none")
        check(state.count === 0, "None switches without animation");
      else {
        check(
          state.count === 2 && state.inert && state.hidden === "true",
          `${effect}: outgoing and incoming animate; outgoing is inert`,
        );
        check(
          effect === "fade"
            ? state.frames.some(
                (f) => f[0].opacity === "0" && f[1].opacity === "1",
              )
            : state.frames.some(
                (f) =>
                  f[0].transform ===
                  `translate${effect === "slide" ? "X" : "Y"}(100%)`,
              ),
          `${effect}: complete transition keyframes`,
        );
        await root.evaluate((n) =>
          n.getAnimations({ subtree: true }).forEach((a) => a.finish()),
        );
      }
      await page.waitForTimeout(30);
      check(
        (await root.locator(".gov-carousel__slide:visible").count()) === 1,
        `${effect}: outgoing slide cleaned up`,
      );
    }
    const first = roots.first();
    await first.locator("[data-previous]").click();
    check(
      await first.evaluate((n) =>
        n
          .getAnimations({ subtree: true })
          .some(
            (a) => a.effect.getKeyframes()[0].transform === "translateX(-100%)",
          ),
      ),
      "Previous slides in the reverse direction",
    );
    await first.locator("[data-next]").click();
    await first.locator("[data-next]").click();
    await page.waitForTimeout(700);
    check(
      (await first.locator(".gov-carousel__slide:visible").count()) === 1 &&
        (await first.locator("[inert]").count()) === 0,
      "Rapid navigation cleans up interrupted transitions",
    );
    for (const width of [375, 768, 1024, 1440]) {
      await page.setViewportSize({ width, height: 1000 });
      await page.waitForTimeout(40);
      check(
        (await first.locator(".gov-carousel__slide:visible").count()) === 1,
        `Single slide at ${width}px`,
      );
      check(
        await page.evaluate(
          () => document.documentElement.scrollWidth <= innerWidth,
        ),
        `No overflow at ${width}px`,
      );
    }
    const before = await first.locator("[data-counter]").textContent();
    await first.locator("[data-rotation]").click();
    await page.waitForTimeout(1750);
    check(
      (await first.locator("[data-counter]").textContent()) !== before,
      "Autoplay advances slides",
    );
    await page.emulateMedia({ reducedMotion: "reduce" });
    await page.waitForTimeout(50);
    check(
      (await first.locator("[data-rotation]").isHidden()) &&
        (await first.evaluate(
          (n) => n.getAnimations({ subtree: true }).length === 0,
        )),
      "Reduced motion stops playback and animation",
    );
    await first.locator("[data-next]").click();
    check(
      (await first.locator(".gov-carousel__slide:visible").count()) === 1,
      "Reduced-motion manual navigation works",
    );
    check(errors.length === 0, `No JavaScript errors: ${errors.join(";")}`);
    const nojs = await browser.newContext({ javaScriptEnabled: false });
    const fallback = await nojs.newPage();
    await fallback.route("http://**/*", (r) => r.abort());
    await fallback.setContent(markup);
    check(
      (await fallback.locator(".gov-carousel__slide:visible").count()) === 4,
      "No-JavaScript first-slide fallback for each instance",
    );
    await nojs.close();
    fs.writeFileSync(
      ".test-artifacts/slideshow-browser.json",
      JSON.stringify(results, null, 2),
    );
    console.log(`${results.length} slideshow assertions passed`);
  } finally {
    await browser.close();
  }
})().catch((e) => {
  console.error(e);
  process.exitCode = 1;
});
