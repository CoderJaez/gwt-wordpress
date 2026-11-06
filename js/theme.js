/* Progressive navigation: all links remain accessible if JavaScript is unavailable. */
(() => {
  "use strict";
  const toggle = document.querySelector(".menu-toggle");
  const nav = document.getElementById("primary-navigation");
  if (toggle && nav) {
    const mobile = window.matchMedia("(max-width: 760px)");
    const submenus = [];
    nav.querySelectorAll("li").forEach((item, index) => {
      const list = Array.from(item.children).find(
        (child) => child.tagName === "UL",
      );
      const link = Array.from(item.children).find(
        (child) => child.tagName === "A",
      );
      if (!list || !link) return;
      const control = document.createElement("button");
      control.type = "button";
      control.className = "submenu-toggle";
      control.textContent = "+";
      control.setAttribute(
        "aria-label",
        `${nav.dataset.submenuLabel} ${link.textContent.trim()}`,
      );
      list.id = list.id || `gwt-submenu-${index}`;
      control.setAttribute("aria-controls", list.id);
      const setOpen = (open) => {
        control.setAttribute("aria-expanded", String(open));
        control.textContent = open ? "−" : "+";
        item.classList.toggle("submenu-open", open);
      };
      item.classList.add("has-submenu-toggle");
      link.after(control);
      setOpen(false);
      control.addEventListener("click", () =>
        setOpen(control.getAttribute("aria-expanded") !== "true"),
      );
      item.addEventListener("pointerenter", (event) => {
        if (event.pointerType === "mouse" && !mobile.matches) setOpen(true);
      });
      item.addEventListener("pointerleave", () => {
        if (!item.contains(document.activeElement)) setOpen(false);
      });
      link.addEventListener("focus", () => {
        if (!mobile.matches) setOpen(true);
      });
      item.addEventListener("focusout", () => {
        queueMicrotask(() => {
          if (!item.contains(document.activeElement)) setOpen(false);
        });
      });
      item.addEventListener("keydown", (event) => {
        if (event.key === "Escape" && !mobile.matches) {
          event.stopPropagation();
          setOpen(false);
          control.focus();
        }
      });
      submenus.push({ item, setOpen });
    });
    document.addEventListener("pointerdown", (event) => {
      submenus.forEach(({ item, setOpen }) => {
        if (!item.contains(event.target)) setOpen(false);
      });
    });
    const close = () => {
      toggle.setAttribute("aria-expanded", "false");
      nav.hidden = mobile.matches;
    };
    const sync = () => {
      submenus.forEach(({ setOpen }) => setOpen(false));
      toggle.hidden = !mobile.matches;
      if (!mobile.matches) {
        nav.hidden = false;
        toggle.setAttribute("aria-expanded", "false");
      } else {
        close();
      }
    };
    toggle.addEventListener("click", () => {
      const open = toggle.getAttribute("aria-expanded") !== "true";
      toggle.setAttribute("aria-expanded", String(open));
      nav.hidden = !open;
    });
    document.addEventListener("keydown", (event) => {
      if (event.key === "Escape" && mobile.matches && !nav.hidden) {
        close();
        toggle.focus();
      }
    });
    mobile.addEventListener("change", sync);
    sync();
  }
  // Display device time in the Philippine timezone, without remote scripts.
  const clocks = document.querySelectorAll("[data-ph-time]");
  if (clocks.length) {
    const formatter = new Intl.DateTimeFormat("en-PH", {
      timeZone: "Asia/Manila",
      dateStyle: "medium",
      timeStyle: "short",
    });
    const update = () =>
      clocks.forEach((clock) => {
        clock.textContent = formatter.format(new Date());
      });
    update();
    window.setInterval(update, 60000);
  }
})();
