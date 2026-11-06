/**
 * Independent carousel controllers. Configuration comes from validated PHP JSON:
 * autoplay/interval_ms; pause_hover/pause_hidden;
 * stop_interaction/loop; transition/speed. Labels are translated server-side.
 */
(() => {
  "use strict";
  const instances = new Map();

  function initialize(root) {
    if (instances.has(root) || root.dataset.govCarouselReady === "true") return;
    let config;
    try {
      config = JSON.parse(root.dataset.govCarousel);
    } catch {
      return;
    }
    const track = root.querySelector(".gov-carousel__slides");
    const slides = [...root.querySelectorAll(".gov-carousel__slide")];
    if (!track || !slides.length) return;
    root.dataset.govCarouselReady = "true";
    const previous = root.querySelector("[data-previous]");
    const next = root.querySelector("[data-next]");
    const pagination = root.querySelector("[data-pagination]");
    const counter = root.querySelector("[data-counter]");
    const rotation = root.querySelector("[data-rotation]");
    const navigation = root.querySelector("[data-navigation]");
    const status = root.querySelector("[data-status]");
    const motion = window.matchMedia("(prefers-reduced-motion: reduce)");
    const mobile = window.matchMedia("(max-width: 760px)");
    const tablet = window.matchMedia("(max-width: 1000px)");
    const abort = new AbortController();
    const on = (target, event, handler) =>
      target?.addEventListener(event, handler, { signal: abort.signal });
    const delay = Math.max(1000, Number(config.interval_ms) || 5000);
    let index = 0;
    const visible = 1;
    let maximum = 0;
    let timer;
    let finishTransition = () => {};
    let hovered = false;
    let focused = false;
    let allowFocusedPlayback = false;
    let playing =
      Boolean(config.autoplay) && !motion.matches && !config.preview;
    let pointerPlaying;
    let dots = [];

    function rangeLabel() {
      return config.rangeLabel
        .replace("%1$d", String(index + 1))
        .replace("%2$d", String(Math.min(slides.length, index + visible)))
        .replace("%3$d", String(slides.length));
    }

    function schedule() {
      window.clearTimeout(timer);
      if (rotation) {
        rotation.hidden = !maximum || motion.matches;
        rotation.textContent = playing ? config.pauseLabel : config.playLabel;
        rotation.setAttribute("aria-pressed", String(playing));
      }
      if (
        !playing ||
        !maximum ||
        motion.matches ||
        (config.pause_hover && hovered) ||
        (config.pause_hidden && document.hidden) ||
        (focused && !allowFocusedPlayback)
      )
        return;
      timer = window.setTimeout(() => {
        if (!root.isConnected) {
          destroy();
          return;
        }
        if (!config.loop && index >= maximum) {
          playing = false;
          schedule();
          return;
        }
        show(index + 1);
        if (!config.loop && index >= maximum) playing = false;
        schedule();
      }, delay);
    }

    function show(requested, announce = false, animate = true) {
      finishTransition();
      const oldIndex = index;
      const outgoing = slides[oldIndex];
      const direction = requested < index ? -1 : 1;
      index = config.loop
        ? ((requested % (maximum + 1)) + maximum + 1) % (maximum + 1)
        : Math.max(0, Math.min(maximum, requested));
      slides.forEach((slide, offset) => {
        const active = offset >= index && offset < index + visible;
        slide.hidden = !active;
        // Prioritize active and upcoming pictures without fetching every slide.
        if (active || offset === index + visible) {
          slide.querySelectorAll("img").forEach((image) => {
            image.loading = "eager";
          });
        }
      });
      dots.forEach((dot, offset) =>
        dot.setAttribute("aria-pressed", String(offset === index)),
      );
      if (previous) previous.disabled = !config.loop && index === 0;
      if (next) next.disabled = !config.loop && index === maximum;
      if (counter) counter.textContent = rangeLabel();
      if (announce && status) status.textContent = rangeLabel();
      if (
        animate &&
        oldIndex !== index &&
        config.transition !== "none" &&
        !motion.matches &&
        Number(config.speed) > 0 &&
        typeof track.animate === "function"
      ) {
        const rtl = getComputedStyle(root).direction === "rtl" ? -1 : 1;
        const incoming = slides[index];
        const vertical = config.transition === "slide-up";
        const axis = vertical ? "Y" : "X";
        const distance = direction * (vertical ? 1 : rtl) * 100;
        const fade = config.transition === "fade";
        outgoing.hidden = false;
        outgoing.inert = true;
        outgoing.setAttribute("aria-hidden", "true");
        outgoing.classList.add("gov-carousel__slide--leaving");
        const options = {
          duration: Math.min(Number(config.speed), delay - 100),
          easing: "ease-in-out",
          fill: "both",
        };
        const animations = [
          incoming.animate(
            fade
              ? [{ opacity: 0 }, { opacity: 1 }]
              : [
                  { transform: `translate${axis}(${distance}%)` },
                  { transform: `translate${axis}(0)` },
                ],
            options,
          ),
          outgoing.animate(
            fade
              ? [{ opacity: 1 }, { opacity: 0 }]
              : [
                  { transform: `translate${axis}(0)` },
                  { transform: `translate${axis}(${-distance}%)` },
                ],
            options,
          ),
        ];
        let finished = false;
        const cleanup = () => {
          if (finished) return;
          finished = true;
          animations.forEach((animation) => animation.cancel());
          outgoing.hidden = true;
          outgoing.inert = false;
          outgoing.removeAttribute("aria-hidden");
          outgoing.classList.remove("gov-carousel__slide--leaving");
        };
        finishTransition = cleanup;
        Promise.all(animations.map((animation) => animation.finished))
          .then(cleanup)
          .catch(cleanup);
      }
    }

    function manual(requested) {
      if (config.stop_interaction) playing = false;
      show(requested, true);
      schedule();
    }

    function resize() {
      maximum = Math.max(0, slides.length - visible);
      if (previous) previous.hidden = maximum === 0;
      if (next) next.hidden = maximum === 0;
      if (pagination) {
        pagination.replaceChildren();
        dots = [];
        for (let position = 0; position <= maximum; position += 1) {
          const dot = document.createElement("button");
          dot.type = "button";
          dot.className = "gov-carousel__dot";
          dot.setAttribute(
            "aria-label",
            config.pageLabel.replace("%d", String(position + 1)),
          );
          dot.setAttribute("aria-controls", track.id);
          dot.dataset.position = String(position);
          const number = document.createElement("span");
          number.className = "screen-reader-text";
          number.textContent = String(position + 1);
          dot.append(number);
          pagination.append(dot);
          dots.push(dot);
        }
        pagination.hidden = maximum === 0;
      }
      if (navigation)
        navigation.hidden = !(
          counter ||
          (maximum && (pagination || (rotation && !motion.matches)))
        );
      show(Math.min(index, maximum), false, false);
      schedule();
    }

    on(previous, "click", () => manual(index - 1));
    on(next, "click", () => manual(index + 1));
    on(pagination, "click", (event) => {
      const dot = event.target.closest("[data-position]");
      if (dot && pagination.contains(dot)) manual(Number(dot.dataset.position));
    });
    on(rotation, "pointerdown", () => {
      pointerPlaying = playing;
    });
    on(rotation, "pointercancel", () => {
      pointerPlaying = undefined;
    });
    on(rotation, "click", () => {
      playing = !(pointerPlaying ?? playing) && !motion.matches;
      pointerPlaying = undefined;
      allowFocusedPlayback = playing;
      if (playing && !config.loop && index >= maximum) show(0, true);
      schedule();
    });
    on(root, "focusin", () => {
      focused = true;
      allowFocusedPlayback = false;
      if (config.stop_interaction) playing = false;
      schedule();
    });
    on(root, "focusout", () => {
      queueMicrotask(() => {
        focused = root.contains(document.activeElement);
        if (!focused) allowFocusedPlayback = false;
        schedule();
      });
    });
    on(root, "pointerenter", (event) => {
      if (event.pointerType !== "touch") {
        hovered = true;
        schedule();
      }
    });
    on(root, "pointerleave", () => {
      hovered = false;
      schedule();
    });
    on(root, "keydown", (event) => {
      if (
        event.altKey ||
        event.ctrlKey ||
        event.metaKey ||
        event.target.matches("input, textarea, select, [contenteditable=true]")
      )
        return;
      const rtl = getComputedStyle(root).direction === "rtl";
      let requested;
      if (event.key === "ArrowRight") requested = index + (rtl ? -1 : 1);
      else if (event.key === "ArrowLeft") requested = index + (rtl ? 1 : -1);
      else return;
      event.preventDefault();
      // Move focus to a persistent control before its containing slide is hidden.
      if (event.target.closest(".gov-carousel__slide")) {
        root.setAttribute("tabindex", "-1");
        root.focus({ preventScroll: true });
      }
      manual(requested);
    });
    on(document, "visibilitychange", schedule);
    on(motion, "change", () => {
      playing = false;
      finishTransition();
      resize();
    });
    on(mobile, "change", resize);
    on(tablet, "change", resize);
    function destroy() {
      window.clearTimeout(timer);
      finishTransition();
      abort.abort();
      instances.delete(root);
      delete root.dataset.govCarouselReady;
    }
    instances.set(root, { destroy });
    root.classList.add("gov-carousel--ready");
    resize();
  }

  function scan(scope) {
    if (scope.matches?.("[data-gov-carousel]")) initialize(scope);
    scope.querySelectorAll?.("[data-gov-carousel]").forEach(initialize);
  }

  function boot() {
    scan(document);
    // Customizer selective refresh and blocks can replace widgets after initial load.
    const observer = new MutationObserver((records) => {
      records.forEach((record) =>
        record.addedNodes.forEach((node) => {
          if (node.nodeType === 1) scan(node);
        }),
      );
      instances.forEach((instance, root) => {
        if (!root.isConnected) instance.destroy();
      });
    });
    observer.observe(document.body, { childList: true, subtree: true });
  }
  if (document.readyState === "loading")
    document.addEventListener("DOMContentLoaded", boot, { once: true });
  else boot();
})();
