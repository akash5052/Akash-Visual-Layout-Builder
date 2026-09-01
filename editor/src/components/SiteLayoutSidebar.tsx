import type { EditorScope, GlobalLayout } from "../types";

interface SiteLayoutViewProps {
  globalLayout: GlobalLayout;
  activeScope: Extract<EditorScope, "global-header" | "global-footer">;
  isSaving?: boolean;
  onSelectScope: (scope: EditorScope) => void;
  onGlobalLayoutChange: (layout: GlobalLayout) => void;
  onSave: () => void;
}

export function SiteLayoutSidebar({
  globalLayout,
  activeScope,
  isSaving = false,
  onSelectScope,
  onGlobalLayoutChange,
  onSave,
}: SiteLayoutViewProps) {
  const regionLabel = activeScope === "global-footer" ? "footer" : "header";

  return (
    <aside className="epb-side-panel">
      <div className="epb-side-panel__header">
        <h3>Site layout</h3>
        <p>Global header &amp; footer</p>
      </div>

      <button
        type="button"
        className={`epb-file ${activeScope === "global-header" ? "epb-file--active" : ""}`}
        onClick={() => onSelectScope("global-header")}
      >
        <span className="epb-file__icon">⬆</span>
        <span className="epb-file__path">Global Header</span>
      </button>

      {activeScope === "global-header" && (
        <label className="epb-layout-toggle">
          <input
            type="checkbox"
            checked={globalLayout.header_enabled}
            onChange={(e) => onGlobalLayoutChange({ ...globalLayout, header_enabled: e.target.checked })}
          />
          <span>Enabled on entire site</span>
        </label>
      )}

      <button
        type="button"
        className={`epb-file ${activeScope === "global-footer" ? "epb-file--active" : ""}`}
        onClick={() => onSelectScope("global-footer")}
      >
        <span className="epb-file__icon">⬇</span>
        <span className="epb-file__path">Global Footer</span>
      </button>

      {activeScope === "global-footer" && (
        <label className="epb-layout-toggle">
          <input
            type="checkbox"
            checked={globalLayout.footer_enabled}
            onChange={(e) => onGlobalLayoutChange({ ...globalLayout, footer_enabled: e.target.checked })}
          />
          <span>Enabled on entire site</span>
        </label>
      )}

      <p className="epb-side-panel__hint">
        Visual builder is active for the global {regionLabel}. Use Desktop / Tablet / Mobile in the top bar for
        responsive editing.
      </p>

      <div className="epb-side-panel__footer">
        <button type="button" className="epb-btn epb-btn--primary epb-btn--block" disabled={isSaving} onClick={onSave}>
          {isSaving ? "Saving…" : "Save site layout"}
        </button>
      </div>
    </aside>
  );
}
