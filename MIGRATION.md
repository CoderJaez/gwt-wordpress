# GWT DILG 27.0.0

**29.1.0 slideshow:** old multi-column Carousel settings now render one slide at a time. New widgets default to Horizontal Slide, with Fade, Vertical Slide, and None available in Playback. Existing saved animation choices are retained. Header branding and saved slides are preserved. Package: `gwt-dilg-29.1.0.zip`.

**Current release: 29.0.0.** Carousel now supports manual slides, dynamic content, overlays, responsive layouts, and independent controls. Existing Header and Carousel assignments are preserved. Autoplay defaults to **5,000 ms** with a **600 ms fade**; the older 50 ms setting below is superseded. Save an existing widget once to obtain its stable page-embedding shortcode. See [CAROUSEL.md](CAROUSEL.md) and [README.md](README.md). The remaining notes describe earlier releases.

**28.2.0:** the Carousel is now image-only with overlaid previous/next arrows. Banner links apply to the full image. Captions and numbered controls are removed. The new `interval_ms` setting defaults to the requested **50 milliseconds** and supersedes the old seconds-based timing setting. See the current README for configuration.

**28.1.0 correction:** the **Header** widget is restored. The separately named **Carousel** widget area now replaces the homepage introduction and information panel below navigation. Existing carousel assignments retain their internal ID and automatically appear in the new location. Automatic rotation is enabled, with a default interval of five seconds for new instances. See the current [README](README.md) for setup details; older placement instructions below are historical.

For the **28.0.0 full-width Header Carousel** update, see the current [README](README.md). The notes below document the original 27.0.0 refactor. In 28.0.0 the new carousel is optional, the `ef_slider` shortcode remains static, and the automatically displayed homepage gallery is suppressed when the Header Carousel area is active. Banner images now also support excerpts and ordering.

This is a major presentation refactor of GWT 26.0.0. Back up the site and test the theme on staging before updating a production installation. No database migration runs automatically.

## Requirements and target versions

- Minimum: WordPress 6.6 and PHP 8.2.
- Current compatibility target: WordPress 7.1 and PHP 8.5. Install the latest maintenance release provided by your hosting platform.
- A classic PHP theme with modern block-editor support; no JavaScript build step, CDN, web fonts, or front-end framework is required.

Version references, checked September 9, 2026: [WordPress release archive](https://wordpress.org/download/releases/), [PHP downloads](https://www.php.net/downloads.php), and [WordPress PHP compatibility guidance](https://make.wordpress.org/core/handbook/references/php-compatibility-and-wordpress-versions/).

## Set up the design

1. In **Appearance → Customize → Site Identity**, upload your approved DILG logo. A plain DILG text mark appears if no logo is configured. An existing GWT logo URL remains a fallback.
   To replace the logo and agency name entirely, add blocks to **Appearance → Widgets → Header**. Use an Image block for a header banner (with meaningful alt text and an optional homepage link), or a Group block for custom branding. The search form stays alongside it and moves below on small screens. Remove all Header widgets to restore the default branding.
2. In **Appearance → Customize → DILG homepage**, set the agency name, tagline, introduction, and services, transparency, and contact URLs. Optional links are hidden until configured. Existing agency identity settings remain fallbacks and can be overridden here.
3. Assign the primary menu to **Left Menu Top bar**. Existing menu location identifiers are unchanged. Auxiliary and government links retain their original locations.
4. Review all widgets in **Appearance → Widgets**. All 18 original widget area IDs remain registered; empty areas do not render. Left and right sidebars now appear together to the right on large screens and below content on small screens.
5. **Settings → Reading → Your latest posts** uses the new introduction and news grid. A static homepage continues to display its authored page content. Build its layout with WordPress blocks if a custom homepage is preferred.
6. Set an actual privacy policy page in WordPress to show the footer privacy link. Review Discussion settings: the theme now respects the site's comment settings instead of forcibly disabling comments.

The palette uses accessible blue, red, and yellow digital accents, informed by the [DILG brand identity manual](https://downloads.caraga.dilg.gov.ph/Brand%20Identity%20Manual/DILG%20Brand%20Identity%20Manual.pdf) and [DILG-CAR's description of the brand colors](https://car.dilg.gov.ph/2023-dilg-car-unite-journey-annual-report/). These UI shades are not a claim of exact official print/Pantone values. Use the approved logo artwork without recoloring it.

## Preserved data and intentional changes

- Posts, pages, attachments, menu assignments, widget instances, legacy options, and `slider-image` records are not deleted or rewritten.
- The old Theme Options page, arbitrary layout/color settings, Foundation markup, icon fonts, and bespoke menu walkers have been retired. Existing child themes that call legacy functions or depend on their markup must be updated.
- The `[ef_slider]` shortcode and Banner images admin screen remain. Up to six published banners render as a static, responsive gallery. No autoplay, carousel dependency, or main-query mutation remains. Use an absolute HTTP(S) URL or a path beginning with `/` for banner links. Legacy bare relative paths must be updated.
- Block editing, native comments, and public read-only REST access are restored. WordPress continues to enforce authentication and authorization for protected content and writes.
- No widgets or navigation items are created automatically on activation.
- The Philippine time widget displays local device time in the Asia/Manila timezone, with a server-rendered fallback. It is not an independently synchronized official time service.
- Use a child theme for further changes. Asset handles and templates have changed; review plugin-specific style dependencies during staging.

## Security scope

Theme-controlled plain text, URLs, and attributes are escaped at output. Customizer values have sanitization callbacks and use WordPress capability and nonce checks. Banner metadata writes verify the post type, edit capability, nonce, autosave/revision state, scalar input, and permitted URL schemes. Legacy widget values are escaped when rendered as well as sanitized when saved. Queries use WordPress APIs. The theme adds `nosniff`, same-origin frame protection, and a referrer policy via the WordPress headers filter. Configure equivalent policies at the server/CDN for cached responses.

Rich post content and registered widget output use the normal WordPress rendering pipeline, so trusted editors and plugins retain their intended HTML and block functionality. The theme does not impose a blanket content security policy that would break plugin scripts or the editor.

No theme can guarantee immunity to every attack. Keep WordPress, PHP, plugins, and hosting patched; maintain backups, HTTPS, least-privilege accounts, and appropriate login/rate-limit protection. These hosting and plugin controls are outside this theme's code.

## Verification

`tests/integration.php` exercises the theme against real WordPress APIs. Run it only in a disposable site using `tests/blueprint.json`; the blueprint creates sample posts and menus. Test files are guarded against direct HTTP execution.

Example with the official Playground CLI:

```powershell
npx.cmd @wp-playground/cli@3.1.53 server --wp=7.1 --php=8.5 --port=9400 --workers=1 --mount-dir . /wordpress/wp-content/themes/gwt-wordpress --blueprint=tests/blueprint.json
```

Before production, verify menus, widgets, Customizer, block and classic content, search/no-results, archives/pagination, attachment pages, password-protected posts, comments, and your site's plugins. Test keyboard access, 200% zoom, reduced motion, long content, and phone/tablet/desktop widths. See `TESTING.md` for the checks actually completed for this refactor.

Exclude `.git`, `.test-tools`, `.test-artifacts`, and `tests` from production ZIPs. Test dependencies are local development tools and are not theme runtime dependencies.
