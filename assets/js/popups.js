(function () {
  "use strict";

  var popups = window.avWebStudioBuilderPopups;
  if (!Array.isArray(popups) || !popups.length) {
    return;
  }

  function storageKey(id) {
    return "av_web_studio_popup_" + id;
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
    return document.getElementById("av-web-studio-popup-" + id);
  }

  function showPopup(config) {
    var el = getEl(config.id);
    if (!el || el.classList.contains("av-web-studio-popup--visible")) {
      return;
    }
    el.classList.add("av-web-studio-popup--visible");
    el.setAttribute("aria-hidden", "false");
    document.body.classList.add("av-web-studio-popup-open");
    markShown(config);
    document.dispatchEvent(new CustomEvent("av-web-studio:popup:open", { detail: { id: config.id } }));
  }

  function hidePopup(config) {
    var el = getEl(config.id);
    if (!el) {
      return;
    }
    el.classList.remove("av-web-studio-popup--visible");
    el.setAttribute("aria-hidden", "true");
    if (!document.querySelector(".av-web-studio-popup.av-web-studio-popup--visible")) {
      document.body.classList.remove("av-web-studio-popup-open");
    }
    document.dispatchEvent(new CustomEvent("av-web-studio:popup:close", { detail: { id: config.id } }));
  }

  function bindClose(config) {
    var el = getEl(config.id);
    if (!el) {
      return;
    }

    el.querySelectorAll("[data-av-web-studio-close]").forEach(function (node) {
      node.addEventListener("click", function () {
        if (config.overlay_close !== false || node.classList.contains("av-web-studio-popup__close")) {
          hidePopup(config);
        }
      });
    });

    if (config.esc_close !== false) {
      document.addEventListener("keydown", function (e) {
        if (e.key === "Escape" && el.classList.contains("av-web-studio-popup--visible")) {
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
