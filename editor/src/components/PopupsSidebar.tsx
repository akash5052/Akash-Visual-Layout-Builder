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
    <aside className="av-web-studio-side-panel">
      <div className="av-web-studio-side-panel__header">
        <div className="av-web-studio-filetree__label-row">
          <h3>Popups</h3>
          <button type="button" className="av-web-studio-filetree__add" onClick={onCreatePopup} title="New popup">
            +
          </button>
        </div>
        <p>Triggers &amp; display rules</p>
      </div>

      {popups.length === 0 ? (
        <p className="av-web-studio-filetree__empty">No popups yet</p>
      ) : (
        <div className="av-web-studio-popup-list">
          {popups.map((popup) => (
            <div
              key={popup.id}
              className={`av-web-studio-popup-item ${activePopupId === popup.id ? "av-web-studio-popup-item--active" : ""}`}
            >
              <button type="button" className="av-web-studio-popup-item__btn" onClick={() => onSelectPopup(popup.id)}>
                <span className="av-web-studio-file__icon">◉</span>
                <span className="av-web-studio-popup-item__name">{popup.name}</span>
                {!popup.enabled && <span className="av-web-studio-popup-item__badge">off</span>}
              </button>
              <button
                type="button"
                className="av-web-studio-popup-item__delete"
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
          <label className="av-web-studio-layout-mode">
            <span>Popup name</span>
            <input
              type="text"
              value={activePopup.name}
              onChange={(e) => onPopupChange({ ...activePopup, name: e.target.value })}
            />
          </label>
          <label className="av-web-studio-layout-toggle">
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
