import type { EpbPopup, PageSummary, PopupFrequencyType, PopupScope, PopupTriggerType } from "../types";

interface PopupConditionsProps {
  popup: EpbPopup;
  pages: PageSummary[];
  onChange: (popup: EpbPopup) => void;
}

const TRIGGERS: { value: PopupTriggerType; label: string }[] = [
  { value: "load", label: "On page load" },
  { value: "scroll", label: "On scroll" },
  { value: "exit_intent", label: "Exit intent" },
  { value: "click", label: "On click" },
  { value: "inactivity", label: "After inactivity" },
];

const SCOPES: { value: PopupScope; label: string }[] = [
  { value: "entire_site", label: "Entire site" },
  { value: "homepage", label: "Homepage only" },
  { value: "specific", label: "Specific pages/posts" },
  { value: "exclude", label: "All except selected" },
];

const FREQUENCIES: { value: PopupFrequencyType; label: string }[] = [
  { value: "always", label: "Every visit" },
  { value: "session", label: "Once per session" },
  { value: "once", label: "Once ever" },
  { value: "days", label: "Once every X days" },
];

export function PopupConditions({ popup, pages, onChange }: PopupConditionsProps) {
  const update = (patch: Partial<EpbPopup>) => onChange({ ...popup, ...patch });

  const togglePageId = (id: number) => {
    const ids = popup.conditions.page_ids.includes(id)
      ? popup.conditions.page_ids.filter((x) => x !== id)
      : [...popup.conditions.page_ids, id];
    update({
      conditions: { ...popup.conditions, page_ids: ids },
    });
  };

  return (
    <div className="epb-popup-conditions">
      <label className="epb-layout-toggle">
        <input
          type="checkbox"
          checked={popup.enabled}
          onChange={(e) => update({ enabled: e.target.checked })}
        />
        <span>Popup enabled</span>
      </label>

      <label className="epb-layout-mode">
        <span>Trigger</span>
        <select
          value={popup.trigger.type}
          onChange={(e) =>
            update({ trigger: { ...popup.trigger, type: e.target.value as PopupTriggerType } })
          }
        >
          {TRIGGERS.map((t) => (
            <option key={t.value} value={t.value}>{t.label}</option>
          ))}
        </select>
      </label>

      {(popup.trigger.type === "load" || popup.trigger.type === "inactivity") && (
        <label className="epb-layout-mode">
          <span>Delay (seconds)</span>
          <input
            type="number"
            min={0}
            value={popup.trigger.delay}
            onChange={(e) =>
              update({ trigger: { ...popup.trigger, delay: Number(e.target.value) || 0 } })
            }
          />
        </label>
      )}

      {popup.trigger.type === "scroll" && (
        <label className="epb-layout-mode">
          <span>Scroll depth (%)</span>
          <input
            type="number"
            min={1}
            max={100}
            value={popup.trigger.scroll_percent}
            onChange={(e) =>
              update({ trigger: { ...popup.trigger, scroll_percent: Number(e.target.value) || 50 } })
            }
          />
        </label>
      )}

      {popup.trigger.type === "click" && (
        <label className="epb-layout-mode">
          <span>CSS selector</span>
          <input
            type="text"
            value={popup.trigger.click_selector}
            placeholder="#my-button, .cta"
            onChange={(e) =>
              update({ trigger: { ...popup.trigger, click_selector: e.target.value } })
            }
          />
        </label>
      )}

      <label className="epb-layout-mode">
        <span>Display on</span>
        <select
          value={popup.conditions.scope}
          onChange={(e) =>
            update({ conditions: { ...popup.conditions, scope: e.target.value as PopupScope } })
          }
        >
          {SCOPES.map((s) => (
            <option key={s.value} value={s.value}>{s.label}</option>
          ))}
        </select>
      </label>

      {(popup.conditions.scope === "specific" || popup.conditions.scope === "exclude") && (
        <div className="epb-popup-pages">
          <span className="epb-popup-pages__label">Pages / posts</span>
          <div className="epb-popup-pages__list">
            {pages.map((p) => (
              <label key={p.id} className="epb-popup-pages__item">
                <input
                  type="checkbox"
                  checked={popup.conditions.page_ids.includes(p.id)}
                  onChange={() => togglePageId(p.id)}
                />
                <span>{p.title} ({p.post_type})</span>
              </label>
            ))}
          </div>
        </div>
      )}

      <label className="epb-layout-mode">
        <span>Show frequency</span>
        <select
          value={popup.frequency.type}
          onChange={(e) =>
            update({ frequency: { ...popup.frequency, type: e.target.value as PopupFrequencyType } })
          }
        >
          {FREQUENCIES.map((f) => (
            <option key={f.value} value={f.value}>{f.label}</option>
          ))}
        </select>
      </label>

      {popup.frequency.type === "days" && (
        <label className="epb-layout-mode">
          <span>Days between shows</span>
          <input
            type="number"
            min={1}
            value={popup.frequency.days}
            onChange={(e) =>
              update({ frequency: { ...popup.frequency, days: Number(e.target.value) || 7 } })
            }
          />
        </label>
      )}

      <label className="epb-layout-toggle">
        <input
          type="checkbox"
          checked={popup.overlay_close}
          onChange={(e) => update({ overlay_close: e.target.checked })}
        />
        <span>Close on overlay click</span>
      </label>

      <label className="epb-layout-toggle">
        <input
          type="checkbox"
          checked={popup.esc_close}
          onChange={(e) => update({ esc_close: e.target.checked })}
        />
        <span>Close on Escape key</span>
      </label>
    </div>
  );
}
