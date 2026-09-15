(function () {
  "use strict";

  var popups = window.akashVisualLayoutBuilderPopups;
  if (!Array.isArray(popups) || !popups.length) {
    return;
  }

  function storageKey(id) {
    return "akash_visual_layout_builder_popup_" + id;
  }

  function shouldShow(config) {
    var freq = config.frequency || {};
    var key = storageKey(config.id);

    if (freq.type === "once" && localStorage.getItem(key)) {
      return false;
    }

    if (freq.type === "session" && sessionStorage.getItem(key)) {
      return false;
    }

    if (freq.type === "days") {
      var stored = localStorage.getItem(key);
      if (stored) {
        var elapsed = Date.now() - parseInt(stored, 10);
        if (!isNaN(elapsed) && elapsed < (freq.days || 7) * 86400000) {
          return false;
        }
      }
    }

    return true;
  }

  function markShown(config) {
    var freq = config.frequency || {};
    var key = storageKey(config.id);

    if (freq.type === "once") {
      localStorage.setItem(key, "1");
    } else if (freq.type === "session") {
      sessionStorage.setItem(key, "1");
    } else if (freq.type === "days") {
      localStorage.setItem(key, String(Date.now()));
    }
  }

  function getEl(id) {
    return document.getElementById("akash-visual-layout-builder-popup-" + id);
  }

  function showPopup(config) {
    var el = getEl(config.id);
    if (!el || el.classList.contains("akash-visual-layout-builder-popup--visible")) {
      return;
    }
    el.classList.add("akash-visual-layout-builder-popup--visible");
    el.setAttribute("aria-hidden", "false");
    document.body.classList.add("akash-visual-layout-builder-popup-open");
    markShown(config);
    document.dispatchEvent(new CustomEvent("akash-visual-layout-builder:popup:open", { detail: { id: config.id } }));
  }

  function hidePopup(config) {
    var el = getEl(config.id);
    if (!el) {
      return;
    }
    el.classList.remove("akash-visual-layout-builder-popup--visible");
    el.setAttribute("aria-hidden", "true");
    if (!document.querySelector(".akash-visual-layout-builder-popup.akash-visual-layout-builder-popup--visible")) {
      document.body.classList.remove("akash-visual-layout-builder-popup-open");
    }
    document.dispatchEvent(new CustomEvent("akash-visual-layout-builder:popup:close", { detail: { id: config.id } }));
  }

  function bindClose(config) {
    var el = getEl(config.id);
    if (!el) {
      return;
    }

    el.querySelectorAll("[data-akash-visual-layout-builder-close]").forEach(function (node) {
      node.addEventListener("click", function () {
        if (config.overlay_close !== false || node.classList.contains("akash-visual-layout-builder-popup__close")) {
          hidePopup(config);
        }
      });
    });

    if (config.esc_close !== false) {
      document.addEventListener("keydown", function (e) {
        if (e.key === "Escape" && el.classList.contains("akash-visual-layout-builder-popup--visible")) {
          hidePopup(config);
        }
      });
    }
  }

  function attachTrigger(config) {
    var trigger = config.trigger || {};
    var type = trigger.type || "load";

    if (type === "load") {
      var delay = (trigger.delay || 0) * 1000;
      setTimeout(function () {
        showPopup(config);
      }, delay);
      return;
    }

    if (type === "scroll") {
      var target = trigger.scroll_percent || 50;
      var fired = false;
      window.addEventListener("scroll", function () {
        if (fired) {
          return;
        }
        var doc = document.documentElement;
        var scrollTop = window.pageYOffset || doc.scrollTop;
        var max = (doc.scrollHeight || 0) - window.innerHeight;
        if (max <= 0) {
          return;
        }
        if ((scrollTop / max) * 100 >= target) {
          fired = true;
          showPopup(config);
        }
      }, { passive: true });
      return;
    }

    if (type === "exit_intent") {
      var exitFired = false;
      document.addEventListener("mouseout", function (e) {
        if (exitFired || e.clientY > 0) {
          return;
        }
        exitFired = true;
        showPopup(config);
      });
      return;
    }

    if (type === "click") {
      var selector = trigger.click_selector;
      if (!selector) {
        return;
      }
      document.addEventListener("click", function (e) {
        var target = e.target;
        if (target && target.closest && target.closest(selector)) {
          showPopup(config);
        }
      });
      return;
    }

    if (type === "inactivity") {
      var timeout = (trigger.delay || 30) * 1000;
      var timer = null;
      var inactiveFired = false;
      var reset = function () {
        if (inactiveFired) {
          return;
        }
        clearTimeout(timer);
        timer = setTimeout(function () {
          inactiveFired = true;
          showPopup(config);
        }, timeout);
      };
      ["mousemove", "keydown", "scroll", "touchstart"].forEach(function (evt) {
        document.addEventListener(evt, reset, { passive: true });
      });
      reset();
    }
  }

  popups.forEach(function (config) {
    if (!shouldShow(config)) {
      return;
    }
    bindClose(config);
    attachTrigger(config);
  });
})();
