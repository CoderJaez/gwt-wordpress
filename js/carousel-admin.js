/* Native widget controls: no global editor state, DOM-safe media and slide updates. */
(() => {
  "use strict";
  const settings = window.gwtCarouselEditor;
  if (!settings) return;
  const state = new WeakMap();
  const encodings = new Map();
  const lockTimers = new Map();
  const numberOf = (editor) =>
    editor.dataset.fieldPrefix.match(/\[([^\]]+)\]\[\]$/)?.[1];
  function saveLock(number, locked) {
    // The block widgets editor provides this public lock; classic widgets do not.
    if (!window.wp?.editWidgets) return;
    const actions = window.wp?.data?.dispatch("core/edit-widgets");
    if (!actions?.lockWidgetSaving) return;
    const name = `gwt-carousel-${number}`;
    if (locked) actions.lockWidgetSaving(name);
    else actions.unlockWidgetSaving(name);
  }
  // Core encodes legacy forms asynchronously. Coalesce overlapping requests so
  // a slow response cannot replace newer repeater edits or publish stale data.
  window.wp.apiFetch.use((options, next) => {
    if (
      options.path !== "/wp/v2/widget-types/gwt_banner_carousel/encode" ||
      !options.data?.form_data
    )
      return next(options);
    const number = String(options.data.number);
    clearTimeout(lockTimers.get(number));
    saveLock(number, true);
    const current = encodings.get(number) || { waiting: [], sequence: 0 };
    current.controller?.abort();
    current.controller = new AbortController();
    const sequence = ++current.sequence;
    encodings.set(number, current);
    const result = new Promise((resolve, reject) =>
      current.waiting.push({ resolve, reject }),
    );
    next({ ...options, signal: current.controller.signal })
      .then((response) => {
        if (sequence !== current.sequence) return;
        encodings.delete(number);
        current.waiting.forEach((job) => job.resolve(response));
        // Let core apply the returned instance before enabling its Update button.
        setTimeout(() => {
          if (!encodings.has(number)) saveLock(number, false);
        }, 0);
      })
      .catch((error) => {
        if (sequence !== current.sequence) return;
        encodings.delete(number);
        current.waiting.forEach((job) => job.reject(error));
        saveLock(number, false);
      });
    return result;
  });
  const getState = (editor) => {
    if (!state.has(editor))
      state.set(editor, { index: Date.now(), sequence: 0 });
    return state.get(editor);
  };
  const notify = (editor) =>
    editor
      .querySelector("[name]")
      ?.dispatchEvent(new Event("change", { bubbles: true }));
  function refresh(editor) {
    const manual =
      editor.querySelector('select[name$="[source]"]')?.value === "manual";
    editor.querySelector(".gwt-carousel-manual").hidden = !manual;
    editor.querySelector("[data-empty-state]").hidden =
      editor.querySelector("[data-slide-list]").children.length > 0;
  }
  function newSlide(editor, source) {
    const list = editor.querySelector("[data-slide-list]");
    if (list.children.length >= 30) {
      editor.querySelector("[data-preview-status]").textContent =
        settings.limit;
      return;
    }
    const index = ++getState(editor).index;
    const slide = source
      ? source.cloneNode(true)
      : editor
          .querySelector("[data-slide-template]")
          .content.firstElementChild.cloneNode(true);
    if (source) {
      const originals = source.querySelectorAll("input, textarea, select");
      slide
        .querySelectorAll("input, textarea, select")
        .forEach((input, position) => {
          input.value = originals[position].value;
          if (input.type === "checkbox")
            input.checked = originals[position].checked;
        });
    }
    slide.querySelectorAll("[name], [id], [for]").forEach((element) => {
      ["name", "id", "for"].forEach((attribute) => {
        const value = element.getAttribute(attribute);
        if (value)
          element.setAttribute(
            attribute,
            value
              .replace(/\[slides\]\[[^\]]+\]/g, `[slides][${index}]`)
              .replace(/slide-(?:__INDEX__|\d+)-/g, `slide-${index}-`),
          );
      });
    });
    slide.open = true;
    if (source) source.after(slide);
    else list.append(slide);
    refresh(editor);
    notify(editor);
    slide.querySelector('input[name$="[title]"]')?.focus();
  }
  function serialize(editor) {
    const result = { slides: [] };
    const prefix = editor.dataset.fieldPrefix.replace(/\[\]$/, "");
    const rows = new Map();
    editor
      .querySelectorAll("input[name], textarea[name], select[name]")
      .forEach((input) => {
        if (input.disabled || (input.type === "checkbox" && !input.checked))
          return;
        const path = input.name
          .slice(prefix.length)
          .match(/\[([^\]]+)\]/g)
          ?.map((part) => part.slice(1, -1));
        if (!path) return;
        if (path[0] === "slides" && path.length === 3) {
          if (!rows.has(path[1])) rows.set(path[1], {});
          rows.get(path[1])[path[2]] = input.value;
        } else if (path.length === 1) result[path[0]] = input.value;
      });
    result.slides = [...rows.values()];
    return result;
  }
  async function preview(editor) {
    const current = getState(editor);
    current.controller?.abort();
    current.controller = new AbortController();
    const sequence = ++current.sequence;
    const status = editor.querySelector("[data-preview-status]");
    status.textContent = settings.loading;
    try {
      const response = await fetch(settings.url, {
        method: "POST",
        credentials: "same-origin",
        signal: current.controller.signal,
        body: new URLSearchParams({
          action: "gwt_carousel_preview",
          nonce: settings.nonce,
          settings: JSON.stringify(serialize(editor)),
        }),
      });
      const result = await response.json();
      if (!response.ok || !result.success) throw new Error("Preview failed");
      if (sequence !== current.sequence || !editor.isConnected) return;
      const frame = editor.querySelector("iframe");
      frame.srcdoc = result.data.html;
      frame.hidden = false;
      status.textContent = settings.updated;
    } catch (error) {
      if (error.name !== "AbortError") status.textContent = settings.failed;
    }
  }
  document.addEventListener("click", (event) => {
    const editor = event.target.closest(".gwt-carousel-editor");
    if (!editor) return;
    const button = event.target.closest("button");
    if (!button) return;
    if (button.matches("[data-add-slide]")) {
      event.preventDefault();
      newSlide(editor);
    }
    if (button.matches("[data-slide-action]")) {
      event.preventDefault();
      const slide = button.closest(".gwt-carousel-slide-editor");
      const action = button.dataset.slideAction;
      if (action === "duplicate") {
        newSlide(editor, slide);
        return;
      }
      if (action === "up" && slide.previousElementSibling)
        slide.previousElementSibling.before(slide);
      if (action === "down" && slide.nextElementSibling)
        slide.nextElementSibling.after(slide);
      if (action === "remove") {
        slide.remove();
        editor.querySelector("[data-add-slide]").focus();
      }
      refresh(editor);
      notify(editor);
    }
    if (button.matches("[data-choose-image]")) {
      event.preventDefault();
      const media = button.closest(".gwt-carousel-media");
      const frame = window.wp.media({
        title: settings.chooseImage,
        library: { type: "image" },
        button: { text: settings.useImage },
        multiple: false,
      });
      frame.on("select", () => {
        const image = frame.state().get("selection").first().toJSON();
        media.querySelector("[data-media-id]").value = image.id;
        const thumbnail = media.querySelector("img");
        thumbnail.src = image.sizes?.medium?.url || image.url;
        thumbnail.hidden = false;
        notify(editor);
      });
      frame.open();
    }
    if (button.matches("[data-remove-image]")) {
      event.preventDefault();
      const media = button.closest(".gwt-carousel-media");
      media.querySelector("[data-media-id]").value = "0";
      media.querySelector("img").removeAttribute("src");
      media.querySelector("img").hidden = true;
      notify(editor);
    }
    if (button.matches("[data-preview-carousel]")) {
      event.preventDefault();
      preview(editor);
    }
  });
  function changed(event) {
    const editor = event.target.closest(".gwt-carousel-editor");
    if (!editor) return;
    if (event.type === "change" && event.target.name) {
      const number = numberOf(editor);
      saveLock(number, true);
      clearTimeout(lockTimers.get(number));
      // Cover core's 200 ms debounce without locking classic/customizer forms.
      lockTimers.set(
        number,
        setTimeout(() => {
          if (!encodings.has(number)) saveLock(number, false);
        }, 1000),
      );
    }
    refresh(editor);
    if (event.target.matches('input[name$="[title]"]')) {
      const slide = event.target.closest(".gwt-carousel-slide-editor");
      if (slide)
        slide.querySelector("[data-slide-heading]").textContent =
          event.target.value || settings.newSlide;
    }
    if (editor.querySelector("[data-live-preview]").checked) {
      const current = getState(editor);
      clearTimeout(current.timer);
      current.timer = setTimeout(() => preview(editor), 500);
    }
  }
  document.addEventListener("change", changed);
  document.addEventListener("input", changed);
  const scan = (scope) => {
    if (scope.matches?.(".gwt-carousel-editor")) refresh(scope);
    scope.querySelectorAll?.(".gwt-carousel-editor").forEach(refresh);
  };
  function boot() {
    scan(document);
    new MutationObserver((records) =>
      records.forEach((record) =>
        record.addedNodes.forEach((node) => {
          if (node.nodeType === 1) scan(node);
        }),
      ),
    ).observe(document.body, { childList: true, subtree: true });
  }
  if (document.readyState === "loading")
    document.addEventListener("DOMContentLoaded", boot, { once: true });
  else boot();
})();
