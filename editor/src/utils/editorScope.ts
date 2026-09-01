import type {
  CodeFileType,
  EditorScope,
  EpbPopup,
  GlobalLayout,
  LayoutPart,
  PageCode,
  PageLayout,
} from "../types";
import { EMPTY_GLOBAL_LAYOUT, EMPTY_PAGE_LAYOUT } from "../types";

export function getScopePart(
  scope: EditorScope,
  code: PageCode,
  globalLayout: GlobalLayout,
  pageLayout: PageLayout,
  popup: EpbPopup | null
): LayoutPart | PageCode {
  if (scope === "popup" && popup) {
    return { html: popup.html, css: popup.css, js: popup.js };
  }

  switch (scope) {
    case "global-header":
      return globalLayout.header;
    case "global-footer":
      return globalLayout.footer;
    case "page-header":
      return pageLayout.header;
    case "page-footer":
      return pageLayout.footer;
    default:
      return code;
  }
}

export function getScopeValue(
  scope: EditorScope,
  file: CodeFileType,
  code: PageCode,
  globalLayout: GlobalLayout,
  pageLayout: PageLayout,
  popup: EpbPopup | null
): string {
  const part = getScopePart(scope, code, globalLayout, pageLayout, popup);
  return part[file] ?? "";
}

export function applyScopeValue(
  scope: EditorScope,
  file: CodeFileType,
  value: string,
  code: PageCode,
  globalLayout: GlobalLayout,
  pageLayout: PageLayout,
  popup: EpbPopup | null
): { code: PageCode; globalLayout: GlobalLayout; pageLayout: PageLayout; popup: EpbPopup | null } {
  if (scope === "popup" && popup) {
    return {
      code,
      globalLayout,
      pageLayout,
      popup: { ...popup, [file]: value },
    };
  }

  switch (scope) {
    case "global-header":
      return {
        code,
        globalLayout: { ...globalLayout, header: { ...globalLayout.header, [file]: value } },
        pageLayout,
        popup,
      };
    case "global-footer":
      return {
        code,
        globalLayout: { ...globalLayout, footer: { ...globalLayout.footer, [file]: value } },
        pageLayout,
        popup,
      };
    case "page-header":
      return {
        code,
        globalLayout,
        pageLayout: { ...pageLayout, header: { ...pageLayout.header, [file]: value } },
        popup,
      };
    case "page-footer":
      return {
        code,
        globalLayout,
        pageLayout: { ...pageLayout, footer: { ...pageLayout.footer, [file]: value } },
        popup,
      };
    default:
      return {
        code: { ...code, [file]: value },
        globalLayout,
        pageLayout,
        popup,
      };
  }
}

export function scopeLabel(scope: EditorScope, popupName?: string): string {
  if (scope === "popup") {
    return popupName ? `Popup: ${popupName}` : "Popup";
  }
  switch (scope) {
    case "global-header":
      return "Site Header";
    case "global-footer":
      return "Site Footer";
    case "page-header":
      return "Page Header";
    case "page-footer":
      return "Page Footer";
    case "seo":
      return "SEO";
    case "post-settings":
      return "Settings";
    default:
      return "Main Content";
  }
}

export function normalizePageLayout(layout?: PageLayout): PageLayout {
  const base = EMPTY_PAGE_LAYOUT;
  if (!layout) {
    return {
      ...base,
      header: { ...base.header },
      footer: { ...base.footer },
    };
  }

  const headerMode = layout.header_mode ?? base.header_mode;
  const footerMode = layout.footer_mode ?? base.footer_mode;

  return {
    header_mode: ["inherit", "append", "replace", "none"].includes(headerMode) ? headerMode : base.header_mode,
    footer_mode: ["inherit", "append", "replace", "none"].includes(footerMode) ? footerMode : base.footer_mode,
    header: { ...base.header, ...(layout.header ?? {}) },
    footer: { ...base.footer, ...(layout.footer ?? {}) },
  };
}

export function normalizeGlobalLayout(layout?: GlobalLayout): GlobalLayout {
  const base = EMPTY_GLOBAL_LAYOUT;
  if (!layout) {
    return {
      ...base,
      header: { ...base.header },
      footer: { ...base.footer },
    };
  }

  return {
    header_enabled: !!layout.header_enabled,
    footer_enabled: !!layout.footer_enabled,
    header: { ...base.header, ...(layout.header ?? {}) },
    footer: { ...base.footer, ...(layout.footer ?? {}) },
  };
}
