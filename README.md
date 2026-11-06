# GWT DILG for WordPress

## Current update: 29.2.0 — GAD color scheme

The theme now uses violet **#6A0DAD**, lavender **#C8A2C8**, white **#FFFFFF**, and gold **#FFD700**. Deep violet supports headings, navigation, and carousel overlays; pale lavender backgrounds keep body text readable. Links, buttons, borders, footer, carousel controls, and block-editor colors share the new palette. Existing palette slugs and CSS variable names remain compatible. Explicitly chosen widget colors, inline content colors, and uploaded artwork retain their own colors.

Installable package: **gwt-dilg-29.2.0.zip**. Build with `python tests/package.py`.

## Previous update: 29.1.0 — single-slide slideshow

Carousel now displays **one slide at a time on every device**, including existing widgets previously configured with multiple columns. Horizontal Slide moves the outgoing and incoming slides across the full banner width. Choose **Slide horizontally, Fade, Slide vertically, or None** under **Appearance → Widgets → Carousel → Playback → Animation**; adjust **Transition speed** to control duration. New widgets default to Slide horizontally. Existing saved Fade selections remain respected.

The Carousel is now a configurable native WordPress widget with manual slide editing, Media Library images, dynamic content, responsive layouts, text overlays, and independent playback controls. The **Header** widget remains separate. On the posts-based homepage, **Carousel** appears below navigation and replaces the former introduction.

The theme uses its existing WordPress widget architecture, PHP, CSS, and vanilla JavaScript. No page builder, carousel library, CDN, or front-end build step is required.

### Add a carousel

1. Open **Appearance → Widgets** and add **Carousel** to the **Carousel** area. In the block-based widgets editor, use the **Legacy Widget** block and choose **Carousel**.
2. Choose **Manual slides**, click **Add Slide**, and select an image from the Media Library. Enter the required slide title.
3. Optionally add a mobile image, alt text, subtitle, description, two links, category/tag label, date, and badge. Each slide has enable, duplicate, move up/down, and remove controls.
4. Configure the grouped layout, navigation, playback, and overlay settings. Click **Update preview**, or enable **Live preview**, then save/update the widget screen to publish.
5. To use the saved carousel on a page, insert a core **Shortcode** block and copy the complete `[gwt_carousel ref="…"]` shortcode shown in the widget editor. Each placement operates independently. Existing numeric-ID shortcodes remain supported.

For artwork that already includes text, turn off **Show text overlay**. The primary URL then makes the image clickable. Use **Auto** height and consistently sized images to preserve the artwork's aspect ratio. Fixed heights use `object-fit: cover`; adjust focal position or supply mobile artwork to control cropping.

### Defaults and compatibility

- One slide at a time; autoplay every **5,000 ms**; **600 ms horizontal slide** by default; previous/next arrows, dots, and counter.
- Hover and inactive-tab pausing enabled. Keyboard focus pauses playback; manual interaction stops it by default. Play explicitly resumes. Reduced-motion preferences disable autoplay and animation.
- Medium height **480px**, full available width, bottom-left overlay, maximum text width **650px**. Content can expand height to remain readable.
- Manual slides support up to 30 entries; dynamic and Banner sources support up to 50 results. Incomplete/disabled manual slides and unpublished/password-protected dynamic content are excluded.
- Existing widget IDs, the Carousel sidebar ID, Banner image records, and Header assignments are preserved. Existing instances without a source continue using **Banner images**. New empty widgets start in manual mode.
- The former 50 ms interval is replaced with 5,000 ms, following the updated specification. Existing supported delays are retained. No database migration runs automatically.
- Requires WordPress **6.6+** and PHP **8.2+**. Component integration tested with WordPress **7.1 / PHP 8.5.10**; PHP 8.2 syntax checks are also run.

See [CAROUSEL.md](CAROUSEL.md) for all settings, architecture, accessibility, security, files, and development instructions. See [MIGRATION.md](MIGRATION.md) for the earlier theme refactor and child-theme migration notes. [TESTING.md](TESTING.md) records verification scope.

Build the installable **gwt-dilg-29.2.0.zip** with `python tests/package.py`. Development tools, fixtures, and tests are excluded.

## Earlier major updates

- **28.2:** Image-only homepage carousel and millisecond timing; timing and controls superseded by version 29.
- **28.1:** Restored separate Header widget; moved Carousel below navigation on the homepage.
- **28.0:** Introduced the initial carousel widget.
- **27:** Modernized the original government theme with a minimalist DILG palette, responsive navigation, block-editor support, local assets, and validation/escaping of settings and metadata. Preserved content, menu locations, widget areas, banners, and the legacy `[ef_slider]` static gallery shortcode.
