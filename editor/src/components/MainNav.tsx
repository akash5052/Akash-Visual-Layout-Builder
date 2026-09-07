import type { MouseEvent, ReactNode } from "react";
import type { AppView } from "../types";
import { shouldSkipAdminNavigation } from "../utils/adminNav";
import { LogoMark } from "./Logo";

const VIEW_LABELS: Record<AppView, string> = {
  dashboard: "Dashboard",
  pages: "Pages",
  posts: "Posts",
  templates: "Templates",
  "site-layout": "Site Layout",
  popups: "Popups",
  svg: "SVG Library",
  tracking: "Tracking",
  settings: "Settings",
};

function NavIcon({ children }: { children: ReactNode }) {
  return (
    <svg
      className="av-web-studio-main-nav__svg"
      viewBox="0 0 24 24"
      width="18"
      height="18"
      fill="none"
      stroke="currentColor"
      strokeWidth="1.75"
      strokeLinecap="round"
      strokeLinejoin="round"
      aria-hidden="true"
    >
      {children}
    </svg>
  );
}

const VIEW_ICONS: Record<AppView, ReactNode> = {
  dashboard: (
    <NavIcon>
      <path d="M4 10.5 12 4l8 6.5V20a1 1 0 0 1-1 1h-5v-6H10v6H5a1 1 0 0 1-1-1v-9.5z" />
    </NavIcon>
  ),
  pages: (
    <NavIcon>
      <path d="M8 3.5h6.5L18.5 8v12.5a1 1 0 0 1-1 1H8a1 1 0 0 1-1-1V4.5a1 1 0 0 1 1-1z" />
      <path d="M14.5 3.5V8H18.5" />
      <path d="M10 12h6M10 15.5h6" />
    </NavIcon>
  ),
  posts: (
    <NavIcon>
      <path d="M5 19.5 14.5 10l3.5 3.5L8.5 23H5v-3.5z" />
      <path d="M13 11.5 16.5 15" />
      <path d="M16.5 7.5 19 5a1.8 1.8 0 0 1 2.5 2.5L19 10" />
    </NavIcon>
  ),
  templates: (
    <NavIcon>
      <rect x="4" y="4" width="7" height="7" rx="1.5" />
      <rect x="13" y="4" width="7" height="7" rx="1.5" />
      <rect x="4" y="13" width="7" height="7" rx="1.5" />
      <rect x="13" y="13" width="7" height="7" rx="1.5" />
    </NavIcon>
  ),
  "site-layout": (
    <NavIcon>
      <rect x="3.5" y="3.5" width="17" height="4" rx="1" />
      <rect x="3.5" y="10" width="7" height="10.5" rx="1" />
      <rect x="13" y="10" width="7.5" height="10.5" rx="1" />
    </NavIcon>
  ),
  popups: (
    <NavIcon>
      <rect x="4" y="6" width="16" height="12" rx="2" />
      <path d="M9 6V5a3 3 0 0 1 6 0v1" />
      <path d="M9 12h6" />
    </NavIcon>
  ),
  svg: (
    <NavIcon>
      <path d="M12 3.5 20.5 12 12 20.5 3.5 12 12 3.5z" />
      <path d="M12 8v8M8 12h8" />
    </NavIcon>
  ),
  tracking: (
    <NavIcon>
      <path d="M4 19.5h16" />
      <path d="M7 16.5v-5" />
      <path d="M12 16.5V8" />
      <path d="M17 16.5v-8.5" />
    </NavIcon>
  ),
  settings: (
    <NavIcon>
      <circle cx="12" cy="12" r="3" />
      <path d="M12 3.5v2.2M12 18.3v2.2M4.9 6.5l1.6 1.6M17.5 15.9l1.6 1.6M3.5 12h2.2M18.3 12h2.2M4.9 17.5l1.6-1.6M17.5 8.1l1.6-1.6" />
    </NavIcon>
  ),
};

interface MainNavProps {
  activeView: AppView;
  editingContent?: boolean;
  editingPopup?: boolean;
  onBackToList?: () => void;
  onPopupsBackToList?: () => void;
}

export function MainNav({
  activeView,
  editingContent,
  editingPopup,
  onBackToList,
  onPopupsBackToList,
}: MainNavProps) {
  const urls = window.avWebStudioBuilderData.adminUrls;
  const items: { view: AppView; href: string }[] = [
    { view: "dashboard", href: urls.dashboard },
    { view: "pages", href: urls.pages },
    { view: "posts", href: urls.posts },
    { view: "templates", href: urls.templates },
    { view: "site-layout", href: urls.siteLayout },
    { view: "popups", href: urls.popups },
    { view: "svg", href: urls.svg },
    { view: "tracking", href: urls.tracking },
  ];
  if (urls.settings && (window.avWebStudioBuilderData.canManageSettings || window.avWebStudioBuilderData.pluginSettings?.can_manage_settings)) {
    items.push({ view: "settings", href: urls.settings });
  }

  const handleNavClick = (event: MouseEvent<HTMLAnchorElement>, view: AppView, href: string) => {
    if (activeView !== view) {
      if (shouldSkipAdminNavigation(href)) {
        event.preventDefault();
      }
      return;
    }

    event.preventDefault();

    if ((view === "pages" || view === "posts") && editingContent) {
      onBackToList?.();
      return;
    }

    if (view === "popups" && editingPopup) {
      onPopupsBackToList?.();
    }
  };

  return (
    <nav className="av-web-studio-main-nav" aria-label="AV Web Studio sections">
      <div className="av-web-studio-main-nav__brand">
        <LogoMark className="av-web-studio-main-nav__brand-mark" />
        <span className="av-web-studio-main-nav__brand-text">AV Web Studio</span>
      </div>

      <ul className="av-web-studio-main-nav__list">
        {items.map((item) => (
          <li key={item.view}>
            <a
              href={item.href}
              className={`av-web-studio-main-nav__link ${activeView === item.view && !editingContent ? "av-web-studio-main-nav__link--active" : ""}`}
              onClick={(event) => handleNavClick(event, item.view, item.href)}
            >
              <span className="av-web-studio-main-nav__icon">{VIEW_ICONS[item.view]}</span>
              <span className="av-web-studio-main-nav__label">{VIEW_LABELS[item.view]}</span>
            </a>
          </li>
        ))}
      </ul>

      <div className="av-web-studio-main-nav__bottom">
        {editingContent && onBackToList && (
          <button type="button" className="av-web-studio-main-nav__back" onClick={onBackToList}>
            ← Back to list
          </button>
        )}
        <div className="av-web-studio-main-nav__footer">
          <span className="av-web-studio-main-nav__version">v{window.avWebStudioBuilderData.version}</span>
        </div>
      </div>
    </nav>
  );
}
