(function () {
  "use strict";

  var cfg = window.avWebStudioTracking || {};

  if (cfg.gtmId) {
    window.dataLayer = window.dataLayer || [];
    window.dataLayer.push({ "gtm.start": new Date().getTime(), event: "gtm.js" });
  } else if (cfg.gaIds && cfg.gaIds.length) {
    window.dataLayer = window.dataLayer || [];
    window.gtag = function () {
      window.dataLayer.push(arguments);
    };
    window.gtag("js", new Date());
    cfg.gaIds.forEach(function (id) {
      window.gtag("config", id);
    });
  }

  if (cfg.pixelId) {
    if (!window.fbq) {
      var n = function () {
        if (n.callMethod) {
          n.callMethod.apply(n, arguments);
        } else {
          n.queue.push(arguments);
        }
      };
      n.push = n;
      n.loaded = true;
      n.version = "2.0";
      n.queue = [];
      window.fbq = n;
      window._fbq = n;
    }
    window.fbq("init", cfg.pixelId);
    window.fbq("track", "PageView");
  }
})();
