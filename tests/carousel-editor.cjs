const { chromium } = require(process.env.PLAYWRIGHT_MODULE || "playwright");
const fs = require("node:fs");
const assert = require("node:assert/strict");
(async () => {
  const browser = await chromium.launch({
    headless: true,
    executablePath: process.env.CHROME_PATH,
  });
  const context = await browser.newContext({
    storageState: ".test-artifacts/admin-auth.json",
    viewport: { width: 1440, height: 1000 },
  });
  const page = await context.newPage();
  page.setDefaultTimeout(60000);
  const errors = [];
  page.on("pageerror", (error) => errors.push(error.message));
  try {
    await page.goto("http://127.0.0.1:9400/wp-admin/widgets.php", {
      waitUntil: "domcontentloaded",
      timeout: 60000,
    });
    const close = page
      .getByRole("dialog")
      .getByRole("button", { name: "Close", exact: true });
    if (await close.count()) await close.click();
    await page.getByRole("button", { name: "Carousel", exact: true }).click();
    console.log("Carousel area expanded");
    const block = page
      .locator('[data-type="core/legacy-widget"]')
      .filter({ hasText: "Carousel" })
      .first();
    await block.click();
    const editor = page.locator(".gwt-carousel-editor:visible").first();
    await editor.waitFor();
    console.log("Native widget form visible");
    const rows = editor.locator("[data-slide-list] > details");
    while ((await rows.count()) > 4) {
      console.log("Removing extra row", await rows.count());
      await rows.last().locator('[data-slide-action="remove"]').click();
    }
    await page.waitForTimeout(4000);
    assert((await rows.count()) === 4);
    const position =
      (await rows
        .first()
        .locator('select[name$="[image_position]"]')
        .inputValue()) === "bottom"
        ? "top"
        : "bottom";
    await rows
      .first()
      .locator('select[name$="[image_position]"]')
      .selectOption(position);
    await rows.first().locator('[data-slide-action="duplicate"]').click();
    assert((await rows.count()) === 5);
    assert(
      (await rows
        .nth(1)
        .locator('select[name$="[image_position]"]')
        .inputValue()) === position,
    );
    await rows
      .nth(1)
      .locator('input[name$="[title]"]')
      .fill("Editor duplicate test");
    await rows.nth(1).locator('[data-slide-action="up"]').click();
    assert(
      (await rows.first().locator('input[name$="[title]"]').inputValue()) ===
        "Editor duplicate test",
    );
    await rows.first().locator('[data-slide-action="remove"]').click();
    assert((await rows.count()) === 4);
    await editor.locator("[data-add-slide]").click();
    assert((await rows.count()) === 5);
    await rows
      .last()
      .locator('input[name$="[title]"]')
      .fill("Editor added slide");
    await rows.last().locator("[data-choose-image]").first().click();
    await page
      .getByRole("dialog")
      .getByRole("tab", { name: "Media Library" })
      .click();
    await page.locator(".attachments .attachment").first().click();
    await page.getByRole("button", { name: "Use image", exact: true }).click();
    assert(
      Number(await rows.last().locator('input[name$="[image]"]').inputValue()) >
        0,
    );
    await editor.locator("[data-preview-carousel]").click();
    await editor
      .locator("[data-preview-status]")
      .filter({ hasText: "Preview updated" })
      .waitFor();
    const frame = editor.frameLocator("iframe");
    await frame.locator("[data-gov-carousel]").waitFor();
    assert((await frame.locator(".gov-carousel__slide").count()) === 5);
    await page.screenshot({
      path: ".test-artifacts/component-editor-controls.png",
    });
    console.log(
      "Add, duplicate, reorder, remove, Media Library, and rendered preview passed",
    );
    await rows.last().locator('[data-slide-action="remove"]').click();
    await page.waitForTimeout(8000);
    console.log(
      "Before save position:",
      await rows
        .first()
        .locator('select[name$="[image_position]"]')
        .inputValue(),
    );
    await page.waitForFunction(
      () => !wp.data.select("core/edit-widgets").isWidgetSavingLocked(),
    );
    await page.getByRole("button", { name: "Update", exact: true }).click();
    await page.waitForFunction(
      () =>
        !wp.data.select("core/edit-widgets").isSavingWidgetAreas() &&
        wp.data.select("core/edit-widgets").getEditedWidgetAreas().length === 0,
      null,
      { timeout: 120000 },
    );
    await page.reload({ waitUntil: "domcontentloaded" });
    await page.getByRole("button", { name: "Carousel", exact: true }).click();
    await page
      .locator('[data-type="core/legacy-widget"]')
      .filter({ hasText: "Carousel" })
      .first()
      .click();
    const saved = page.locator(".gwt-carousel-editor:visible").first();
    await saved.waitFor();
    assert((await saved.locator("[data-slide-list] > details").count()) === 4);
    assert(
      (await saved
        .locator('[data-slide-list] select[name$="[image_position]"]')
        .first()
        .inputValue()) === position,
    );
    fs.writeFileSync(
      ".test-artifacts/component-editor.log",
      "PASS: Add, duplicate live select values, reorder, remove, Media Library selection, server preview, core widget save and reload.",
    );
    assert(errors.length === 0, errors.join("; "));
    console.log("Widget save and reload passed");
  } catch (e) {
    console.error(e);
    fs.writeFileSync(
      ".test-artifacts/editor-failure.html",
      await page.content(),
    );
    process.exitCode = 1;
  } finally {
    await browser.close();
  }
})();
