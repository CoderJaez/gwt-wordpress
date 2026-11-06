const { chromium } = require(process.env.PLAYWRIGHT_MODULE || "playwright");
const fs = require("node:fs");
const assert = require("node:assert/strict");
(async () => {
  const browser = await chromium.launch({
    headless: true,
    executablePath: process.env.CHROME_PATH,
  });
  const page = await browser.newPage({
    viewport: { width: 1440, height: 1000 },
  });
  page.setDefaultTimeout(20000);
  try {
    console.log("Loading login");
    await page.goto("http://127.0.0.1:9400/wp-login.php", {
      waitUntil: "domcontentloaded",
      timeout: 60000,
    });
    await page.locator("#user_login").fill("admin");
    await page.locator("#user_pass").fill("Carousel-Test-Only-2026!");
    console.log("Submitting test login");
    await page.locator("#wp-submit").click({ noWaitAfter: true });
    await page.waitForURL("**/wp-admin/**", {
      waitUntil: "domcontentloaded",
      timeout: 60000,
    });
    console.log("Opening widgets");
    await page.goto("http://127.0.0.1:9400/wp-admin/widgets.php", {
      waitUntil: "domcontentloaded",
      timeout: 60000,
    });
    await page.waitForFunction(() => !!window.gwtCarouselEditor);
    console.log("Widget assets ready");
    await page.screenshot({
      path: ".test-artifacts/component-admin.png",
      timeout: 15000,
    });
    fs.writeFileSync(".test-artifacts/admin-dom.html", await page.content());
    console.log("Editors:", await page.locator(".gwt-carousel-editor").count());
    const response = await page.evaluate(async () => {
      const s = window.gwtCarouselEditor;
      const call = async (nonce) => {
        const r = await fetch(s.url, {
          method: "POST",
          body: new URLSearchParams({
            action: "gwt_carousel_preview",
            nonce,
            settings: JSON.stringify({ source: "manual" }),
          }),
        });
        return { status: r.status, text: await r.text() };
      };
      return { good: await call(s.nonce), bad: await call("invalid") };
    });
    assert(
      response.good.status === 200 &&
        response.good.text.includes("No carousel slides"),
    );
    assert(response.bad.status === 403);
    console.log("Authorized preview and nonce rejection passed");
    await page
      .context()
      .storageState({ path: ".test-artifacts/admin-auth.json" });
  } catch (error) {
    console.error(error);
    process.exitCode = 1;
  } finally {
    await browser.close();
  }
})();
