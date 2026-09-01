import type { InnerSection, VisualSection, VisualWidget } from "./types";

export type BuilderClipboard =
  | { kind: "widget"; widget: VisualWidget }
  | { kind: "section"; section: VisualSection }
  | { kind: "inner-section"; inner: InnerSection };

let clipboard: BuilderClipboard | null = null;

export function getBuilderClipboard(): BuilderClipboard | null {
  return clipboard;
}

export function setBuilderClipboard(next: BuilderClipboard | null): void {
  clipboard = next;
}
