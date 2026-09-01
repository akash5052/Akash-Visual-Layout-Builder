import type { PageCode, GlobalLayout, PageLayout } from "../types";
import { buildPreviewBody } from "./layout";

/**
 * Strip outer .epb-page wrapper so we can append inner sections only.
 */
function stripPageWrapper(html: string): string {
  const trimmed = html.trim();
  const match = trimmed.match(/^<div[^>]*class="[^"]*epb-page[^"]*"[^>]*>([\s\S]*)<\/div>\s*$/i);
  if (match) {
    return match[1].trim();
  }
  return trimmed;
}

/**
 * Append a new HTML section into the existing page markup.
 */
export function mergeHtml(existing: string, generated: string): string {
  const section = stripPageWrapper(generated);
  if (!section) {
    return existing;
  }

  const current = existing.trim();

  if (!current) {
    return `<div class="epb-page">\n${section}\n</div>`;
  }

  const rootMatch = current.match(
    /^(\s*<div[^>]*class="[^"]*epb-page[^"]*"[^>]*>)([\s\S]*?)(<\/div>\s*)$/i
  );

  if (rootMatch) {
    const inner = rootMatch[2].trim();
    return `${rootMatch[1]}\n${inner}\n\n${section}\n${rootMatch[3]}`;
  }

  return `${current}\n\n${section}`;
}

/**
 * Append generated CSS to existing styles.
 */
export function mergeCss(existing: string, generated: string): string {
  const next = generated.trim();
  if (!next) return existing;
  const current = existing.trim();
  if (!current) return next;
  return `${current}\n\n/* --- New Section --- */\n${next}`;
}

/**
 * Append generated JS to existing scripts.
 */
export function mergeJs(_existing: string, _generated: string): string {
  return "";
}

/**
 * Merge AI-generated code into existing page code (append sections).
 */
export function mergeAiCode(existing: PageCode, generated: PageCode): PageCode {
  return {
    html: mergeHtml(existing.html, generated.html),
    css: mergeCss(existing.css, generated.css),
    js: mergeJs(existing.js, generated.js),
  };
}

export function buildPreviewDocument(
  code: PageCode,
  globalLayout?: GlobalLayout,
  pageLayout?: PageLayout
): string {
  const merged = globalLayout && pageLayout ? buildPreviewBody(code, globalLayout, pageLayout) : code;

  return `<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>${merged.css}</style>
</head>
<body>
  ${merged.html}
</body>
</html>`;
}

/** Open the saved page preview in a new browser tab (stable URL, refresh-safe). */
export function openFullPagePreviewUrl(url: string): void {
  const tab = window.open(url, "_blank", "noopener,noreferrer");
  if (!tab) {
    throw new Error("Pop-up blocked. Allow pop-ups for this site to preview the full page.");
  }
}

export function getCodeValue(code: PageCode, file: keyof PageCode): string {
  return code[file] ?? "";
}

export function setCodeValue(code: PageCode, file: keyof PageCode, value: string): PageCode {
  return { ...code, [file]: value };
}
