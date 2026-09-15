import type { StudioPopup, PageSummary } from "../types";
import { PopupConditions } from "./PopupConditions";

interface PopupsSidebarProps {
  popups: StudioPopup[];
  activePopupId: string | null;
  pages: PageSummary[];
  onSelectPopup: (id: string) => void;
  onCreatePopup: () => void;
  onDeletePopup: (id: string) => void;
  onPopupChange: (popup: StudioPopup) => void;
}

export function PopupsSidebar({
  popups,
  activePopupId,
  pages,
  onSelectPopup,
  onCreatePopup,
  onDeletePopup,
  onPopupChange,
}: PopupsSidebarProps) {
  const activePopup = popups.find((p) => p.id === activePopupId) ?? null;

  return (
    <aside className="akash-visual-layout-builder-side-panel">
      <div className="akash-visual-layout-builder-side-panel__header">
        <div className="akash-visual-layout-builder-filetree__label-row">
          <h3>Popups</h3>
          <button type="button" className="akash-visual-layout-builder-filetree__add" onClick={onCreatePopup} title="New popup">
            +
          </button>
        </div>
        <p>Triggers &amp; display rules</p>
      </div>

      {popups.length === 0 ? (
        <p className="akash-visual-layout-builder-filetree__empty">No popups yet</p>
      ) : (
        <div className="akash-visual-layout-builder-popup-list">
          {popups.map((popup) => (
            <div
              key={popup.id}
              className={`akash-visual-layout-builder-popup-item ${activePopupId === popup.id ? "akash-visual-layout-builder-popup-item--active" : ""}`}
            >
              <button type="button" className="akash-visual-layout-builder-popup-item__btn" onClick={() => onSelectPopup(popup.id)}>
                <span className="akash-visual-layout-builder-file__icon">◉</span>
                <span className="akash-visual-layout-builder-popup-item__name">{popup.name}</span>
                {!popup.enabled && <span className="akash-visual-layout-builder-popup-item__badge">off</span>}
              </button>
              <button
                type="button"
                className="akash-visual-layout-builder-popup-item__delete"
                onClick={() => onDeletePopup(popup.id)}
                title="Move popup to trash"
              >
                ×
              </button>
            </div>
          ))}
        </div>
      )}

      {activePopup && (
        <>
          <label className="akash-visual-layout-builder-layout-mode">
            <span>Popup name</span>
            <input
              type="text"
              value={activePopup.name}
              onChange={(e) => onPopupChange({ ...activePopup, name: e.target.value })}
            />
          </label>
          <label className="akash-visual-layout-builder-layout-toggle">
            <input
              type="checkbox"
              checked={activePopup.enabled}
              onChange={(e) => onPopupChange({ ...activePopup, enabled: e.target.checked })}
            />
            <span>Enabled</span>
          </label>
          <PopupConditions popup={activePopup} pages={pages} onChange={onPopupChange} />
        </>
      )}
    </aside>
  );
}
