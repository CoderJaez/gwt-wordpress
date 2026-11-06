# Verification for GWT DILG

## 29.2.0 GAD palette — November 6, 2026

CSS/JSON formatting, valid theme JSON, and installable ZIP integrity checked. Default white-on-violet, white-on-deep-violet, dark-text-on-lavender, dark-text-on-gold, and muted-text-on-pale-lavender combinations exceed 4.5:1 contrast. Legacy blue/red palette values were removed from the public stylesheet, carousel fallback styles, and editor palette. This color-only update does not repeat the earlier functional browser suites or certify user-selected colors and artwork.

## 29.1.0 slideshow — September 14, 2026

30 Chrome assertions passed for Horizontal Slide, Fade, Vertical Slide, and None: complete transition keyframes, outgoing/incoming animation, outgoing accessibility exclusion, reverse direction, rapid interruption cleanup, one slide at 375/768/1024/1440px, no horizontal overflow, autoplay, reduced motion, no-JavaScript fallback, and no JavaScript errors. Real WordPress/PHP 8.5 checks passed for normalization of old gallery counts, default Slide animation, four allowed effect values, and widget rendering. Evidence: `.test-artifacts/slideshow-browser.json` and `slideshow-php.log`. Entry points: `tests/slideshow.php` and `tests/slideshow.cjs`. Version 29.0 multi-slide assertions below describe the previous behavior.

## 29.0.0 reusable Carousel — September 12, 2026

- **29 browser assertions passed** in Chrome: independent instances, one-slide hero defaults, navigation, keyboard controls, pagination, non-loop boundaries, automatic rotation, reduced motion, no-JavaScript links, and responsive visible counts without horizontal overflow at 375/768/1024/1440px. Editor assets, authorized preview, nonce rejection, and absence of JavaScript errors were checked. Evidence: `.test-artifacts/component-browser.json`.
- The actual block widgets editor passed Add Slide, duplicate current select values, move up, remove, Media Library selection, rendered preview, and save/reload checks. Rapid form requests are coalesced and use WordPress's widget-saving lock. Saving is awaited through the core editor state. Evidence: `.test-artifacts/component-editor.log` and `component-editor-controls.png`.
- Real WordPress API checks covered settings limits, unsafe URLs, malformed input types, NUL-containing dates, CSS injection, incomplete/disabled slides, protected posts, category/date ordering, responsive media markup, instance IDs, and legacy-ID shortcodes. Evidence: `.test-artifacts/component-php.log`.
- Final real-API checks cover the persisted UUID shortcode reference, invalid references, malicious settings, public/editor empty states, query isolation, and legacy Banner availability. Evidence: `.test-artifacts/component-final-8.5.log` and `component-final-8.2.log`.
- PHP syntax, JavaScript syntax, CSS/JS formatting, whitespace checks, and ZIP integrity are checked for the final files. Screenshots of the mobile/desktop component and widget controls were inspected. Fixture imagery is the existing theme screenshot, not official DILG banner artwork.

The test installation is disposable WordPress Playground with WordPress 7.1; final API checks run on PHP 8.5.10 and the PHP 8.2 branch. This is functional and security-focused testing, not a penetration test or full accessibility certification. Actual authored images/colors, child-theme overrides, third-party plugins, and assistive-technology behavior should be reviewed on the target site. No production site was changed.

Reproduction entry points and the development workflow are documented in [CAROUSEL.md](CAROUSEL.md). The following sections are historical verification for earlier releases.

## 28.2.0 image-only carousel

**21 focused browser checks passed**: 50 ms configuration and repeated automatic advancement, one visible image, removal of captions/numbered controls, preserved Header, manual left/right navigation and pause behavior, linked artwork, reduced motion, full-width rendering and overlay-arrow positioning without overflow at 320/768/1440 pixels, and no JavaScript errors. PHP and JavaScript syntax checks, formatting, and whitespace checks passed. Evidence: `.test-artifacts/carousel-image-results.json` and `carousel-image-*.png`; reproduce with `tests/carousel-image.cjs` against the existing three-banner fixture. Earlier carousel suites describe superseded layouts and controls.

## 28.1.0 homepage placement correction

**27 carousel browser checks passed** after restoring the Header and moving the separately named Carousel to the homepage introduction position. Checks include the restored header region, removal of the pictured introduction/information panel, automatic advancement without interaction, manual controls, wraparound, play/pause, reduced motion, full-width/no-overflow rendering at 320/768/1440 pixels, and the no-JavaScript fallback. Modified PHP files passed syntax checks; JavaScript syntax, formatting, and whitespace checks also passed. Current evidence: `.test-artifacts/carousel-browser.json` and carousel screenshots. The existing test site's saved carousel assignment was reused to verify the preserved widget-area ID.

## 28.0.0 carousel update

**23 carousel browser checks passed** in headless Chrome: header replacement, duplicate-gallery suppression, protected-slide exclusion, direct/previous/next selection, wraparound, inactive-slide hiding, stopping and resuming autoplay, Pause behavior, reduced-motion behavior, full-width/no-overflow layouts at 320/768/1440 pixels, no-JavaScript fallback, and absence of JavaScript errors. Phone and desktop screenshots were reviewed. The fixture uses the existing theme screenshot as a test image; it does not install official DILG artwork or change production banner records.

The new carousel widget passed PHP syntax checks and real WordPress 7.1 / PHP 8.5.10 checks for sanitized settings, bounded slide count/interval, malformed autoplay values, published image filtering, and password-protected banner exclusion. Reproduction fixture: `tests/carousel-seed.php` (disposable sites only); browser suite: `tests/carousel.cjs`. Local evidence is saved as `.test-artifacts/carousel-php.log` and carousel screenshots/results. The earlier full-theme checks below describe 27.0.0, not a repeated matrix for this update.

## Earlier release verification

Tested September 9, 2026 in a disposable local WordPress Playground installation with seeded content. No production site or database was modified.

## Completed checks

- WordPress **7.1 / PHP 8.5.10**: **26 integration checks passed** against real WordPress APIs, covering theme/editor setup, preserved widget registration, URL rejection, widget escaping, nonce and capability checks, malformed input, banner-query isolation, Customizer sanitization, and REST read/write permissions.
- WordPress **7.1 / PHP 8.2.33**: the same **26 integration checks passed**, validating the declared minimum PHP branch.
- **25 PHP files passed syntax checks** with local PHP **8.2.12**.
- **49 browser checks passed** in headless Chrome: 320, 375, 768, 1024, and 1440 pixel widths; mobile toggle and Escape behavior; skip link; keyboard submenu access; no-JavaScript navigation; search and malicious search text; empty results; archive and pagination; single posts and pages; comments; protected posts; 404 responses; and security headers.
- **11 additional navigation checks passed** for explicit touchscreen submenu controls, keyboard focus, Escape, outside dismissal, resize, and mobile overflow after the final navigation enhancement.
- JavaScript syntax, CSS/JS/JSON formatting, and `git diff --check` passed.
- Desktop and phone screenshots were reviewed. Legacy block-widget heading sizes and wrapped search buttons were corrected. The theme's `screenshot.png` now shows the new design.

Local evidence is saved in `.test-artifacts/` (ignored by Git): `integration-8.5.log`, `integration-8.2.log`, `browser-results.json`, `navigation-results.txt`, and desktop/mobile screenshots.

## Reproduce

Use the official Playground CLI command in [MIGRATION.md](MIGRATION.md). Its blueprint activates this theme and runs `tests/integration.php`, then creates disposable sample content. Assertion failures throw exceptions and fail setup.

With Playwright installed and the fixture site running:

```powershell
$env:PLAYWRIGHT_MODULE = 'absolute/path/to/node_modules/playwright'
$env:CHROME_PATH = 'C:/Program Files/Google/Chrome/Application/chrome.exe'
node tests/browser.cjs
node tests/navigation.cjs
```

The optional `TEST_URL` environment variable overrides `http://127.0.0.1:9400`. Run database-changing regression scripts separately from browser checks to avoid SQLite lock contention. `tests/navigation.cjs` also refreshes the theme screenshot.

## Limits

These checks are not a penetration test or a guarantee against every attack. WordPress 6.6 is the declared minimum based on the APIs/schema used, but it was not run in this test matrix. Safari, Firefox, physical mobile devices, third-party plugins, production MySQL/MariaDB, existing child themes, and site-specific data still require staging verification. Security headers on cached/static responses need server/CDN configuration.

The integration environment uses PHP WebAssembly and SQLite. Validate your actual hosting stack before deployment. Only the theme was changed; WordPress core, production PHP, plugins, server configuration, and account policies were not upgraded by this refactor.
