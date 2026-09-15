import type { ContentTab } from "../types";

const TABS: { id: ContentTab; label: string }[] = [
  { id: "builder", label: "Builder" },
  { id: "layout", label: "Layout" },
  { id: "seo", label: "SEO" },
  { id: "settings", label: "Settings" },
];

interface ContentTabsProps {
  active: ContentTab;
  settingsLabel?: string;
  onChange: (tab: ContentTab) => void;
}

export function ContentTabs({ active, settingsLabel = "Settings", onChange }: ContentTabsProps) {
  return (
    <div className="akash-visual-layout-builder-content-tabs" role="tablist" aria-label="Content sections">
      {TABS.map((tab) => (
        <button
          key={tab.id}
          type="button"
          role="tab"
          aria-selected={active === tab.id}
          className={`akash-visual-layout-builder-content-tabs__btn ${active === tab.id ? "akash-visual-layout-builder-content-tabs__btn--active" : ""}`}
          onClick={() => onChange(tab.id)}
        >
          {tab.id === "settings" ? settingsLabel : tab.label}
        </button>
      ))}
    </div>
  );
}
