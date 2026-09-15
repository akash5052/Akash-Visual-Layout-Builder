import type { StudioPopup } from "../types";

const POPUP_BASE_CSS = `
.akash-visual-layout-builder-popup { position: fixed; inset: 0; z-index: 999999; display: flex; align-items: center; justify-content: center; padding: 20px; opacity: 0; visibility: hidden; pointer-events: none; }
.akash-visual-layout-builder-popup--visible { opacity: 1; visibility: visible; pointer-events: auto; }
.akash-visual-layout-builder-popup__overlay { position: absolute; inset: 0; background: rgba(0,0,0,0.55); }
.akash-visual-layout-builder-popup__dialog { position: relative; z-index: 1; width: min(520px, 100%); max-height: min(85vh, 720px); overflow: auto; background: #fff; border-radius: 14px; box-shadow: 0 24px 64px rgba(0,0,0,0.28); margin: 0 auto; }
.akash-visual-layout-builder-popup__close { position: absolute; top: 10px; right: 12px; z-index: 2; width: 32px; height: 32px; border: none; border-radius: 8px; background: rgba(0,0,0,0.06); font-size: 22px; cursor: pointer; }
.akash-visual-layout-builder-popup__content { padding: 28px 24px 24px; }
.akash-visual-layout-builder-popup-preview-wrap { min-height: 100vh; background: #f0f0f5; }
.akash-visual-layout-builder-popup-preview-wrap .akash-visual-layout-builder-popup { position: relative; inset: auto; opacity: 1; visibility: visible; pointer-events: auto; min-height: 360px; }
.akash-visual-layout-builder-popup-preview-wrap .akash-visual-layout-builder-popup__overlay { position: absolute; }
`;

export function buildPopupPreviewDocument(popup: StudioPopup): string {
  const markup = `<div class="akash-visual-layout-builder-popup akash-visual-layout-builder-popup--visible" aria-hidden="false">
  <div class="akash-visual-layout-builder-popup__overlay"></div>
  <div class="akash-visual-layout-builder-popup__dialog">
    <button type="button" class="akash-visual-layout-builder-popup__close">&times;</button>
    <div class="akash-visual-layout-builder-popup__content">${popup.html}</div>
  </div>
</div>`;

  return `<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>${POPUP_BASE_CSS}\n${popup.css}</style>
</head>
<body class="akash-visual-layout-builder-popup-preview-wrap">
  ${markup}
</body>
</html>`;
}
