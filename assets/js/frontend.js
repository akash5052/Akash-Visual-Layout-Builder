(function () {
  "use strict";

  function qsa(selector, root) {
    return Array.prototype.slice.call((root || document).querySelectorAll(selector));
  }

  function initAccordions() {
    qsa("[data-akash-visual-layout-builder-accordion]").forEach(function (root) {
      qsa("[data-akash-visual-layout-builder-acc]", root).forEach(function (btn) {
        btn.addEventListener("click", function () {
          var id = btn.getAttribute("data-akash-visual-layout-builder-acc");
          var panel = root.querySelector('[data-akash-visual-layout-builder-acc-panel="' + id + '"]');
          if (!panel) {
            return;
          }
          var open = panel.style.display === "block";
          panel.style.display = open ? "none" : "block";
          var mark = btn.querySelector("span:last-child");
          if (mark) {
            mark.textContent = open ? "+" : "−";
          }
        });
      });
    });
  }

  function initCounters() {
    qsa("[data-akash-visual-layout-builder-counter]").forEach(function (root) {
      var end = Number(root.getAttribute("data-end") || 0);
      var duration = Number(root.getAttribute("data-duration") || 1500);
      var valueEl = root.querySelector("[data-akash-visual-layout-builder-counter-value]");
      if (!valueEl) {
        return;
      }
      var start = performance.now();
      var step = function (now) {
        var t = Math.min(1, (now - start) / duration);
        valueEl.textContent = String(Math.round(end * t));
        if (t < 1) {
          requestAnimationFrame(step);
        }
      };
      requestAnimationFrame(step);
    });
  }

  initAccordions();
  initCounters();
})();
