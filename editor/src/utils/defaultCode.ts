import type { PageCode } from "../types";

export const EMPTY_PAGE_CODE: PageCode = { html: "", css: "", js: "" };

export function createEmptyCode(_title = "Untitled Page"): PageCode {
  return EMPTY_PAGE_CODE;
}
