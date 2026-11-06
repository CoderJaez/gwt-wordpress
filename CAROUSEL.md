# Carousel component — version 29.2

Version 29.2 uses the GAD violet, lavender, white, and gold theme palette. Default overlay, controls, buttons, and highlights inherit the new colors. Explicit widget color overrides and image artwork are preserved.

Version 29.1 turns the component into a single-slide slideshow on every device. Old multi-column settings are normalized to one without rewriting the database. Horizontal Slide, Fade, Vertical Slide, and None are available under **Playback → Animation**. Incoming and outgoing slides animate together; outgoing content is temporarily inert and hidden from assistive technology, then removed from view when the transition completes. Rapid clicks, resizing, and reduced-motion changes cancel and clean up transitions safely.

## Architecture and registration

This theme already uses `WP_Widget`, so Carousel extends that system and works inside the core Legacy Widget block. There is no Elementor dependency. A Shortcode block can render an existing saved instance on a page. Copy the complete `[gwt_carousel ref="…"]` shortcode displayed in the editor after saving. The UUID reference remains stable when WordPress assigns a different internal widget number. Existing `[gwt_carousel id="2"]` numeric-ID embeds also work. Keep the saved widget in a widget area or Inactive Widgets when reusing its shortcode. Deleting it removes its shortcode output.

`functions.php` includes `inc/carousel-widget.php`. That bootstrap loads the component classes, registers `GWT_Banner_Carousel` on `widgets_init`, retains the `gwt_banner_carousel` option ID and `header-carousel` sidebar ID, registers assets on `init`, and registers the `gwt_carousel` shortcode with `ref` and legacy `id` selectors.

The posts-based homepage renders this sidebar through `template-parts/home-intro.php`. A static front page can use the Shortcode block. Header branding remains in the independent Header widget area.

## Files

| File | Responsibility |
| --- | --- |
| `inc/carousel-widget.php` | Registration, shortcode, conditional assets, preview endpoint hook |
| `inc/widgets/carousel/class-carousel-widget.php` | Native widget adapter and empty state |
| `inc/widgets/carousel/class-carousel-settings.php` | Shared schema, defaults, bounds, sanitization |
| `inc/widgets/carousel/carousel-content.php` | Manual, legacy Banner, and public post queries |
| `inc/widgets/carousel/carousel-render.php` | Escaped semantic HTML, responsive pictures, instance configuration |
| `inc/widgets/carousel/class-carousel-admin.php` | Repeater controls and authorized preview response |
| `css/carousel.css` | Scoped responsive component styles and DILG variable fallbacks |
| `js/carousel.js` | Independent controllers, navigation, playback, motion preferences, lifecycle cleanup |
| `css/carousel-admin.css` | Widget editor styles |
| `js/carousel-admin.js` | Media selection, duplicate/reorder controls, debounced preview, latest-edit encoding and WordPress save locks |

Other integration changes: `inc/function-enqueue_scripts.php` no longer unconditionally loads the old carousel script; `template-parts/home-intro.php` suppresses empty public wrappers; `style.css` sets version 29.0.0; `tests/package.py` includes this documentation. The existing secure Banner URL helper remains in `inc/vendors/envato-flex-slider/slider-img-type.php`.

## Content and controls

Manual slides require an image and title. Optional fields are subtitle, short plain-text description, primary and secondary button labels/URLs, independent new-tab toggles, category/tag label, date, badge, alt text, mobile image, image position, and custom X/Y focal percentages. Disabled or incomplete slides stay editable but do not render. Use the move buttons for keyboard-accessible reordering; duplicates copy all current field values.

Existing Banner images use their featured image, title, excerpt, and Banner link. Dynamic mode supports registered public posts, pages, and custom post types, with a featured image, title, excerpt, date, and permalink. Category/tag ID filters apply only when that taxonomy is registered to the selected post type. Both filters must match when both are set. Publish date, title, and menu order support ascending/descending order, with stable date/ID tie-breaks. Private, draft, and password-protected content is excluded. No global main query is modified.

| Setting | Options / bounds | Default |
| --- | --- | --- |
| Layout | Full available parent width; boxed; content | Full |
| Boxed width | 320–1920px | 1200px |
| Height | Auto image ratio; small 320; medium 480; large 600; custom 200–1000px | Medium |
| Visible slides | One at every screen size; gallery controls removed | 1 |
| Previous / next / dots / counter | Independent switches | On |
| Autoplay delay | 1000–60000ms | 5000ms |
| Hover / inactive-tab pause | Independent switches | On |
| Stop after interaction / loop | Independent switches | On |
| Animation / speed | Horizontal Slide, Fade, Vertical Slide, None; 0–2000ms | Horizontal Slide / 600ms |
| Content position | Left center, center, right center, bottom left/center/right | Bottom left |
| Alignment / text width | Left/center/right; 200–1000px | Left / 650px |
| Overlay / opacity | Hex color or inherited theme color; 0–100% | Theme / 75% |
| Text color | Valid hex color | White |
| Hide description on mobile | Explicit switch | Off |

Desktop starts above 1000px, tablet spans 761–1000px, and mobile is 760px or below. These match the theme navigation breakpoints. Heights decrease on smaller screens and expand when necessary for text; they are not clipping limits. Mobile buttons stack and arrows move above content. Full width fills the containing widget area; it does not break out of a narrow page column. Content width uses `--content-width` with a 760px fallback.

Images use `object-fit: cover` and a focal point. New uploads receive a maximum 1920px `gwt-carousel` derivative, while older uploads fall back to WordPress `large`. Responsive `srcset` and `sizes` are supplied by WordPress when image metadata is available. Existing uploads without derivatives may require thumbnail regeneration. The first slide is eager/high-priority; later slides are lazy until active or upcoming. Use consistent artwork dimensions to avoid crop changes between slides.

## Accessibility and behavior

The named carousel section contains labeled slide groups. Inactive slides use `hidden`, removing their links from keyboard navigation and the accessibility tree. Arrow and pagination buttons have labels and 44px targets. Manual slide changes update a polite status announcement; automatic changes do not repeatedly announce. Left/right keys follow reading direction. Tab navigation is unrestricted. Focus remains on persistent controls when keyboard navigation hides a slide.

Play/pause remains available whenever autoplay is configured. Focus pauses rotation; the default stop-after-interaction option requires an explicit Play action to resume. Hover and document visibility switches are independent. Reduced-motion preferences stop autoplay and remove transitions even when playback was previously enabled. Each DOM instance has its own timer, state, and responsive listeners. Removed widgets dispose their listeners/timers, and selectively refreshed widgets initialize once.

Without JavaScript, the first complete slide and its links remain visible; unused navigation stays hidden. Public empty carousels produce no wrapper. Editors see “No carousel slides have been added yet.” and Add Slide guidance.

The default palette inherits `--blue`, `--blue-dark`, `--red`, `--gold`, and `--ink`. A dark gradient supports text readability; supplied colors and images still determine the final contrast. Editors should review their chosen combinations, particularly when lowering overlay opacity. The component provides WCAG-oriented semantics and controls; it does not certify arbitrary authored content.

## Security and performance

WordPress enforces widget-save permissions and nonces. The preview endpoint separately checks `edit_theme_options` and its nonce. Preview HTML uses the same validation and renderer as public output and runs inside a sandboxed iframe. Invalid JSON is rejected. Media selection uses WordPress's authenticated Media Library.

All settings pass through a shared allowlist and bounds checks, even on render. Text is plain text, URLs permit HTTP(S) or root-relative paths, colors require valid hex, attachment IDs must identify images, and dates must be valid YYYY-MM-DD strings. Script URLs, protocol-relative URLs, injected markup, malformed field arrays, and arbitrary style values are rejected or normalized. Output uses context-appropriate escaping; new-tab links include `noopener noreferrer`. There are no custom SQL statements or executable user templates.

Styles are preloaded on predictable carousel/widget/shortcode surfaces to prevent repaint and printed late if an otherwise-unpredictable template renders one. Active widget detection is conservative, so some pages may load assets for an active carousel area that the page template does not display. JavaScript is local, deferred, dependency-free, and initializes only existing elements. The editor loads Media Library assets only on widget/customizer screens. Live previews debounce changes and cancel stale requests. Preview initially pauses rotation so editors can inspect content; Play tests configured autoplay.

These protections apply to this component. Overall site security also depends on maintained WordPress, plugins, hosting, and account access; no theme can guarantee immunity from every attack.

The block widgets editor encodes legacy forms asynchronously. A component-scoped WordPress API middleware coalesces overlapping Carousel encode requests and discards superseded responses, preventing older edits from replacing newer ones. WordPress's widget-saving lock covers outstanding requests. Wait until the screen finishes saving before leaving it. Classic widget and Customizer saving remain handled by WordPress.

## Development and packaging

No build step is needed for production. Edit the modular files directly and bump the theme version to invalidate asset caches. New widgets can reuse the schema → validated content → renderer → widget adapter pattern without coupling their state to this carousel.

`tests/carousel-component.php` tests real WordPress APIs and seeds a disposable Playground fixture; it changes that test installation's widget settings, posts, and test admin password. Never run it against a real site. Activate this theme in the disposable installation, load WordPress, define `GWT_TEST_RUN`, and include the fixture. Serve that installation on `http://127.0.0.1:9400`. `tests/carousel-component.cjs` exercises the fixture with Playwright/Chrome. Then run `tests/carousel-admin.cjs` to verify preview permissions and save test authentication, followed by `tests/carousel-editor.cjs` for the editing/save/reload workflow. `tests/carousel-component-check.php` verifies the saved reference and final fallback/security behavior. Test scripts and authentication artifacts are excluded from the installable ZIP. Historical carousel tests target older versions and are not the version 29 test entry points.

For version 29.1, run `tests/slideshow.php` inside the disposable WordPress fixture to validate settings and export rendered markup, then `tests/slideshow.cjs` with Playwright/Chrome. It tests all four effects, complete movement, cleanup, autoplay, responsive behavior, and accessibility fallbacks. Earlier version 29 browser expectations for multiple visible slides are superseded.

Run PHP lint on component files, `node --check js/carousel.js`, `node --check js/carousel-admin.js`, and the disposable integration/browser fixtures. Build with `python tests/package.py`; the result is `.test-artifacts/gwt-dilg-29.2.0.zip`.
