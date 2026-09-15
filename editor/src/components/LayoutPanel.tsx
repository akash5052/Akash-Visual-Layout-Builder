import type { GlobalLayout, LayoutMode, PageLayout } from "../types";

const HEADER_MODES: { value: LayoutMode; label: string }[] = [
  { value: "inherit", label: "Use site header only" },
  { value: "append", label: "Site header + page header" },
  { value: "replace", label: "Page header only" },
  { value: "none", label: "No header on this page" },
];

const FOOTER_MODES: { value: LayoutMode; label: string }[] = [
  { value: "inherit", label: "Use site footer only" },
  { value: "append", label: "Site footer + page footer" },
  { value: "replace", label: "Page footer only" },
  { value: "none", label: "No footer on this page" },
];

export type LayoutCodePart = "page-header" | "page-footer";

interface LayoutPanelProps {
  pageLayout: PageLayout;
  globalLayout: GlobalLayout;
  layoutCodePart: LayoutCodePart;
  isSaving?: boolean;
  onLayoutChange: (layout: PageLayout) => void;
  onLayoutCodePartChange: (part: LayoutCodePart) => void;
  onSave: () => void;
}

function regionHint(
  region: "header" | "footer",
  mode: LayoutMode,
  globalEnabled: boolean
): string {
  switch (mode) {
    case "none":
      return `No ${region} is shown on this page.`;
    case "replace":
      return `Only this page's ${region} is used.`;
    case "append":
      return globalEnabled
        ? `Site ${region} appears with this page's ${region}.`
        : `Site ${region} is disabled. Only this page's ${region} is used.`;
    case "inherit":
    default:
      return globalEnabled
        ? `Site ${region} only. Switch to Append or Replace to build a page ${region}.`
        : `Site ${region} is disabled. Use Replace to build a page ${region}.`;
  }
}

export function LayoutPanel({
  pageLayout,
  globalLayout,
  layoutCodePart,
  isSaving = false,
  onLayoutChange,
  onLayoutCodePartChange,
  onSave,
}: LayoutPanelProps) {
  const regionLabel = layoutCodePart === "page-footer" ? "footer" : "header";
  const canEditHeader = pageLayout.header_mode !== "none";
  const canEditFooter = pageLayout.footer_mode !== "none";

  return (
    <aside className="akash-visual-layout-builder-side-panel akash-visual-layout-builder-layout-sidebar">
      <div className="akash-visual-layout-builder-side-panel__header">
        <h3>Page layout</h3>
        <p>Page header &amp; footer</p>
      </div>

      <button
        type="button"
        className={`akash-visual-layout-builder-file ${layoutCodePart === "page-header" ? "akash-visual-layout-builder-file--active" : ""}`}
        disabled={!canEditHeader}
        onClick={() => onLayoutCodePartChange("page-header")}
      >
        <span className="akash-visual-layout-builder-file__icon">⬆</span>
        <span className="akash-visual-layout-builder-file__path">Page Header</span>
      </button>

      <label className="akash-visual-layout-builder-layout-mode akash-visual-layout-builder-layout-mode--sidebar">
        <span>Header mode</span>
        <select
          value={pageLayout.header_mode}
          onChange={(e) => onLayoutChange({ ...pageLayout, header_mode: e.target.value as LayoutMode })}
        >
          {HEADER_MODES.map((opt) => (
            <option key={opt.value} value={opt.value}>
              {opt.label}
            </option>
          ))}
        </select>
      </label>
      <p className="akash-visual-layout-builder-side-panel__hint">{regionHint("header", pageLayout.header_mode, globalLayout.header_enabled)}</p>

      <button
        type="button"
        className={`akash-visual-layout-builder-file ${layoutCodePart === "page-footer" ? "akash-visual-layout-builder-file--active" : ""}`}
        disabled={!canEditFooter}
        onClick={() => onLayoutCodePartChange("page-footer")}
      >
        <span className="akash-visual-layout-builder-file__icon">⬇</span>
        <span className="akash-visual-layout-builder-file__path">Page Footer</span>
      </button>

      <label className="akash-visual-layout-builder-layout-mode akash-visual-layout-builder-layout-mode--sidebar">
        <span>Footer mode</span>
        <select
          value={pageLayout.footer_mode}
          onChange={(e) => onLayoutChange({ ...pageLayout, footer_mode: e.target.value as LayoutMode })}
        >
          {FOOTER_MODES.map((opt) => (
            <option key={opt.value} value={opt.value}>
              {opt.label}
            </option>
          ))}
        </select>
      </label>
      <p className="akash-visual-layout-builder-side-panel__hint">{regionHint("footer", pageLayout.footer_mode, globalLayout.footer_enabled)}</p>

      <p className="akash-visual-layout-builder-side-panel__hint">
        Visual builder is active for the page {regionLabel}. Use Desktop / Tablet / Mobile for responsive editing.
      </p>

      <div className="akash-visual-layout-builder-side-panel__footer">
        <button type="button" className="akash-visual-layout-builder-btn akash-visual-layout-builder-btn--primary akash-visual-layout-builder-btn--block" disabled={isSaving} onClick={onSave}>
          {isSaving ? "Saving…" : "Save layout"}
        </button>
      </div>
    </aside>
  );
}
