import type { StudioPopup } from "../types";

const POPUP_BASE_CSS = `
.av-web-studio-popup { position: fixed; inset: 0; z-index: 999999; display: flex; align-items: center; justify-content: center; padding: 20px; opacity: 0; visibility: hidden; pointer-events: none; }
.av-web-studio-popup--visible { opacity: 1; visibility: visible; pointer-events: auto; }
.av-web-studio-popup__overlay { position: absolute; inset: 0; background: rgba(0,0,0,0.55); }
.av-web-studio-popup__dialog { position: relative; z-index: 1; width: min(520px, 100%); max-height: min(85vh, 720px); overflow: auto; background: #fff; border-radius: 14px; box-shadow: 0 24px 64px rgba(0,0,0,0.28); margin: 0 auto; }
.av-web-studio-popup__close { position: absolute; top: 10px; right: 12px; z-index: 2; width: 32px; height: 32px; border: none; border-radius: 8px; background: rgba(0,0,0,0.06); font-size: 22px; cursor: pointer; }
.av-web-studio-popup__content { padding: 28px 24px 24px; }
.av-web-studio-popup-preview-wrap { min-height: 100vh; background: #f0f0f5; }
.av-web-studio-popup-preview-wrap .av-web-studio-popup { position: relative; inset: auto; opacity: 1; visibility: visible; pointer-events: auto; min-height: 360px; }
.av-web-studio-popup-preview-wrap .av-web-studio-popup__overlay { position: absolute; }
`;

export function buildPopupPreviewDocument(popup: StudioPopup): string {
  const markup = `<div class="av-web-studio-popup av-web-studio-popup--visible" aria-hidden="false">
  <div class="av-web-studio-popup__overlay"></div>
  <div class="av-web-studio-popup__dialog">
    <button type="button" class="av-web-studio-popup__close">&times;</button>
    <div class="av-web-studio-popup__content">${popup.html}</div>
  </div>
</div>`;

  return `<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>${POPUP_BASE_CSS}\n${popup.css}</style>
</head>
<body class="av-web-studio-popup-preview-wrap">
  ${markup}
</body>
</html>`;
}
