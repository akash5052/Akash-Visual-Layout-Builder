import type { EpbPopup, PageSummary } from "../types";
import { PopupConditions } from "./PopupConditions";

interface PopupsSidebarProps {
  popups: EpbPopup[];
  activePopupId: string | null;
  pages: PageSummary[];
  onSelectPopup: (id: string) => void;
  onCreatePopup: () => void;
  onDeletePopup: (id: string) => void;
  onPopupChange: (popup: EpbPopup) => void;
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
    <aside className="epb-side-panel">
      <div className="epb-side-panel__header">
        <div className="epb-filetree__label-row">
          <h3>Popups</h3>
          <button type="button" className="epb-filetree__add" onClick={onCreatePopup} title="New popup">
            +
          </button>
        </div>
        <p>Triggers &amp; display rules</p>
      </div>

      {popups.length === 0 ? (
        <p className="epb-filetree__empty">No popups yet</p>
      ) : (
        <div className="epb-popup-list">
          {popups.map((popup) => (
            <div
              key={popup.id}
              className={`epb-popup-item ${activePopupId === popup.id ? "epb-popup-item--active" : ""}`}
            >
              <button type="button" className="epb-popup-item__btn" onClick={() => onSelectPopup(popup.id)}>
                <span className="epb-file__icon">◉</span>
                <span className="epb-popup-item__name">{popup.name}</span>
                {!popup.enabled && <span className="epb-popup-item__badge">off</span>}
              </button>
              <button
                type="button"
                className="epb-popup-item__delete"
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
          <label className="epb-layout-mode">
            <span>Popup name</span>
            <input
              type="text"
              value={activePopup.name}
              onChange={(e) => onPopupChange({ ...activePopup, name: e.target.value })}
            />
          </label>
          <label className="epb-layout-toggle">
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
