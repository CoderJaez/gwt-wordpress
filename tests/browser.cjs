/* Run against the disposable fixture site from tests/blueprint.json. */
const { chromium } = require(process.env.PLAYWRIGHT_MODULE || "playwright");
const fs = require("node:fs");
const path = require("node:path");
const assert = require("node:assert/strict");

(async () => {
  const browser = await chromium.launch({
    headless: true,
    ...(process.env.CHROME_PATH
      ? { executablePath: process.env.CHROME_PATH }
      : {}),
  });
  const out = path.resolve(".test-artifacts");
  fs.mkdirSync(out, { recursive: true });
  const base = process.env.TEST_URL || "http://127.0.0.1:9400";
  const context = await browser.newContext();
  const page = await context.newPage();
  const errors = [];
  page.on("pageerror", (error) => errors.push(error.message));
  const results = [];
  let protectedURL;
  const check = (condition, message) => {
    assert(condition, message);
    results.push(message);
  };
  try {
    for (const width of [320, 375, 768, 1024, 1440]) {
      await page.setViewportSize({ width, height: 1000 });
      const response = await page.goto(base, { waitUntil: "networkidle" });
      check(response.status() === 200, `Homepage returns 200 at ${width}px`);
      check(
        (await page.locator("main h1").count()) === 1,
        `One primary heading at ${width}px`,
      );
      check(
        await page.evaluate(
          () => document.documentElement.scrollWidth <= window.innerWidth,
        ),
        `No horizontal overflow at ${width}px`,
      );
      check(
        !/Fatal error|Warning:|Deprecated:/.test(
          await page.locator("body").innerText(),
        ),
        `No PHP errors at ${width}px`,
      );
      protectedURL = await page
        .locator(".entry-title a")
        .filter({ hasText: "Protected test document" })
        .first()
        .getAttribute("href");
      if (width <= 760) {
        const toggle = page.getByRole("button", { name: "Menu" });
        await toggle.click();
        check(
          await page.locator("#primary-navigation").isVisible(),
          `Mobile menu opens at ${width}px`,
        );
        check(
          (await toggle.getAttribute("aria-expanded")) === "true",
          "Menu exposes expanded state",
        );
        await page.keyboard.press("Escape");
        check(
          !(await page.locator("#primary-navigation").isVisible()),
          "Escape closes mobile navigation",
        );
        check(
          await toggle.evaluate((el) => el === document.activeElement),
          "Escape returns focus to toggle",
        );
      }
      await page.screenshot({
        path: path.join(out, `homepage-${width}.png`),
        fullPage: true,
      });
    }
    check(
      (await page.request.get(base)).headers()["x-content-type-options"] ===
        "nosniff",
      "nosniff response header",
    );
    check(
      (await page.request.get(base)).headers()["x-frame-options"] ===
        "SAMEORIGIN",
      "Same-origin frame response header",
    );
    await page.keyboard.press("Control+Home");
    await page.goto(base);
    await page.keyboard.press("Tab");
    check(
      await page
        .locator(".skip-link")
        .evaluate((el) => el === document.activeElement),
      "Skip link is the first keyboard stop",
    );
    await page.keyboard.press("Enter");
    check(
      await page
        .locator("main")
        .evaluate((el) => el === document.activeElement),
      "Skip link focuses main content",
    );
    const dropdown = page.getByRole("link", { name: "About us", exact: true });
    await dropdown.focus();
    check(
      await page.getByRole("link", { name: "Mandate and vision" }).isVisible(),
      "Keyboard focus reveals desktop submenu",
    );

    const article = await page
      .locator(".entry-title a")
      .filter({ hasText: "Local partnerships" })
      .getAttribute("href");
    await page.goto(article);
    check(
      (await page.locator(".entry-content").count()) === 1,
      "Single post content renders",
    );
    check(
      (await page.locator("#commentform").count()) === 1,
      "Comments follow site settings",
    );
    await page.screenshot({
      path: path.join(out, "single-post.png"),
      fullPage: true,
    });

    await page.goto(`${base}/?s=communities`);
    check(
      (await page.locator("main h1").innerText()).includes("communities"),
      "Search query appears in heading",
    );
    check(
      (await page.locator(".post-card").count()) > 0,
      "Search returns matching posts",
    );
    const payload = '\"><script>window.GWT_XSS=true</script>';
    await page.goto(`${base}/?s=${encodeURIComponent(payload)}`);
    check(
      !(await page.evaluate(() => window.GWT_XSS)),
      "Search payload does not execute",
    );
    check(
      (await page.locator(".empty-state").count()) === 1,
      "No-results view renders",
    );
    await page.goto(`${base}/?paged=2`);
    check(
      (await page.locator(".post-card").count()) > 0,
      "Pagination renders older posts",
    );
    await page.goto(`${base}/?cat=1`);
    check(
      (await page.locator("main h1").count()) === 1,
      "Category archive renders",
    );
    await page.goto(`${base}/?page_id=2`);
    check(
      (await page.locator(".entry-content").count()) === 1,
      "Page template renders",
    );
    await page.goto(protectedURL);
    check(
      (await page.locator(".post-password-form").count()) === 1,
      "Password form renders",
    );
    check(
      !(await page.content()).includes("PRIVATE_TEST_CONTENT_DO_NOT_EXPOSE"),
      "Password-protected body is not exposed",
    );
    const missing = await page.goto(`${base}/?p=999999`);
    check(missing.status() === 404, "Missing post returns 404");
    check(
      (await page.locator("main .search-form").count()) === 1,
      "404 offers a working search form",
    );

    const noJS = await browser.newContext({
      javaScriptEnabled: false,
      viewport: { width: 375, height: 900 },
    });
    const fallback = await noJS.newPage();
    await fallback.goto(base);
    check(
      await fallback.locator("#primary-navigation").isVisible(),
      "Navigation remains visible without JavaScript",
    );
    check(
      await fallback
        .getByRole("link", { name: "Mandate and vision" })
        .isVisible(),
      "Nested links remain accessible without JavaScript",
    );
    await noJS.close();
    check(
      errors.length === 0,
      `No browser JavaScript errors: ${errors.join(", ")}`,
    );
    fs.writeFileSync(
      path.join(out, "browser-results.json"),
      JSON.stringify(results, null, 2),
    );
    console.log(`${results.length} browser checks passed.`);
  } finally {
    await browser.close();
  }
})().catch((error) => {
  console.error(error);
  process.exitCode = 1;
});
