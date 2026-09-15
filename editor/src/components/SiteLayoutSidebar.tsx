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
    <aside className="akash-visual-layout-builder-side-panel">
      <div className="akash-visual-layout-builder-side-panel__header">
        <h3>Site layout</h3>
        <p>Global header &amp; footer</p>
      </div>

      <button
        type="button"
        className={`akash-visual-layout-builder-file ${activeScope === "global-header" ? "akash-visual-layout-builder-file--active" : ""}`}
        onClick={() => onSelectScope("global-header")}
      >
        <span className="akash-visual-layout-builder-file__icon">⬆</span>
        <span className="akash-visual-layout-builder-file__path">Global Header</span>
      </button>

      {activeScope === "global-header" && (
        <label className="akash-visual-layout-builder-layout-toggle">
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
        className={`akash-visual-layout-builder-file ${activeScope === "global-footer" ? "akash-visual-layout-builder-file--active" : ""}`}
        onClick={() => onSelectScope("global-footer")}
      >
        <span className="akash-visual-layout-builder-file__icon">⬇</span>
        <span className="akash-visual-layout-builder-file__path">Global Footer</span>
      </button>

      {activeScope === "global-footer" && (
        <label className="akash-visual-layout-builder-layout-toggle">
          <input
            type="checkbox"
            checked={globalLayout.footer_enabled}
            onChange={(e) => onGlobalLayoutChange({ ...globalLayout, footer_enabled: e.target.checked })}
          />
          <span>Enabled on entire site</span>
        </label>
      )}

      <p className="akash-visual-layout-builder-side-panel__hint">
        Visual builder is active for the global {regionLabel}. Use Desktop / Tablet / Mobile in the top bar for
        responsive editing.
      </p>

      <div className="akash-visual-layout-builder-side-panel__footer">
        <button type="button" className="akash-visual-layout-builder-btn akash-visual-layout-builder-btn--primary akash-visual-layout-builder-btn--block" disabled={isSaving} onClick={onSave}>
          {isSaving ? "Saving…" : "Save site layout"}
        </button>
      </div>
    </aside>
  );
}
