import type { GlobalLayout, LayoutMode, LayoutPart, PageCode, PageLayout } from "../types";
import { EMPTY_GLOBAL_LAYOUT, EMPTY_LAYOUT_PART, EMPTY_PAGE_LAYOUT } from "../types";

function partHasContent(part: LayoutPart): boolean {
  return !!(part.html.trim() || part.css.trim() || part.js.trim());
}

function joinAssets(a: string, b: string): string {
  if (!a.trim()) return b;
  if (!b.trim()) return a;
  return `${a}\n\n${b}`;
}

function combineRegionHtml(mode: LayoutMode, globalHtml: string, pageHtml: string): string {
  switch (mode) {
    case "none":
      return "";
    case "replace":
      return pageHtml;
    case "append":
      return globalHtml + pageHtml;
    default:
      return globalHtml;
  }
}

function combineAssets(mode: LayoutMode, globalPart: LayoutPart, pagePart: LayoutPart, field: "html" | "css" | "js"): string {
  const g = globalPart[field].trim();
  const p = pagePart[field].trim();
  switch (mode) {
    case "none":
      return "";
    case "replace":
      return p;
    case "append":
      return joinAssets(g, p);
    default:
      return g;
  }
}

function wrapRegion(region: "header" | "footer", scope: "global" | "page", html: string): string {
  const trimmed = html.trim();
  if (!trimmed) return "";
  return `<div class="akash-visual-layout-builder-region akash-visual-layout-builder-region--${region} akash-visual-layout-builder-region--${scope}" data-akash-visual-layout-builder-region="${region}">${trimmed}</div>`;
}

export function resolveLayout(globalLayout: GlobalLayout, pageLayout: PageLayout) {
  const global = globalLayout ?? EMPTY_GLOBAL_LAYOUT;
  const page = pageLayout ?? EMPTY_PAGE_LAYOUT;

  const globalHeaderHtml =
    global.header_enabled && partHasContent(global.header)
      ? wrapRegion("header", "global", global.header.html)
      : "";
  const globalFooterHtml =
    global.footer_enabled && partHasContent(global.footer)
      ? wrapRegion("footer", "global", global.footer.html)
      : "";

  const pageHeaderHtml = partHasContent(page.header) ? wrapRegion("header", "page", page.header.html) : "";
  const pageFooterHtml = partHasContent(page.footer) ? wrapRegion("footer", "page", page.footer.html) : "";

  return {
    headerHtml: combineRegionHtml(page.header_mode, globalHeaderHtml, pageHeaderHtml),
    footerHtml: combineRegionHtml(page.footer_mode, globalFooterHtml, pageFooterHtml),
    headerCss: combineAssets(
      page.header_mode,
      global.header_enabled ? global.header : EMPTY_LAYOUT_PART,
      page.header,
      "css"
    ),
    footerCss: combineAssets(
      page.footer_mode,
      global.footer_enabled ? global.footer : EMPTY_LAYOUT_PART,
      page.footer,
      "css"
    ),
    headerJs: combineAssets(
      page.header_mode,
      global.header_enabled ? global.header : EMPTY_LAYOUT_PART,
      page.header,
      "js"
    ),
    footerJs: combineAssets(
      page.footer_mode,
      global.footer_enabled ? global.footer : EMPTY_LAYOUT_PART,
      page.footer,
      "js"
    ),
  };
}

export function buildPreviewBody(
  code: PageCode,
  globalLayout: GlobalLayout,
  pageLayout: PageLayout
): { html: string; css: string; js: string } {
  const resolved = resolveLayout(globalLayout, pageLayout);
  const bodyHtml = code.html.trim()
    ? `<div class="akash-visual-layout-builder-page">${code.html}</div>`
    : "";

  return {
    html: resolved.headerHtml + bodyHtml + resolved.footerHtml,
    css: joinAssets(joinAssets(resolved.headerCss, code.css), resolved.footerCss),
    js: joinAssets(joinAssets(resolved.headerJs, code.js), resolved.footerJs),
  };
}
