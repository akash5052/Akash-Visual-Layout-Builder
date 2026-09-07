import type { AppView } from "../types";

const CARDS = [
  {
    title: "Pages",
    description: "Build and edit landing pages with the visual drag-and-drop editor.",
    hrefKey: "pages" as const,
    view: "pages" as const,
    countKey: "pageCount" as const,
  },
  {
    title: "Posts",
    description: "Create blog posts with the same visual builder.",
    hrefKey: "posts" as const,
    view: "posts" as const,
    countKey: "postCount" as const,
  },
  {
    title: "Templates",
    description: "Polished landing pages with images and animations — one click to start.",
    hrefKey: "templates" as const,
    view: "templates" as const,
    countKey: null,
  },
  {
    title: "Site Layout",
    description: "Global headers and footers shared across your site.",
    hrefKey: "siteLayout" as const,
    view: "site-layout" as const,
    countKey: null,
  },
  {
    title: "Popups",
    description: "Popups with triggers and display rules.",
    hrefKey: "popups" as const,
    view: "popups" as const,
    countKey: "popupCount" as const,
  },
  {
    title: "SVG Library",
    description: "Upload, manage, and insert SVG graphics into your pages.",
    hrefKey: "svg" as const,
    view: "svg" as const,
    countKey: null,
  },
  {
    title: "Tracking",
    description: "Google Tag Manager, GA4, Ads, and Meta Pixel using account IDs only.",
    hrefKey: "tracking" as const,
    view: "tracking" as const,
    countKey: null,
  },
  {
    title: "Settings",
    description: "AI keys, models, images, editor defaults, SEO, popups, and permissions.",
    hrefKey: "settings" as const,
    view: "settings" as const,
    countKey: null,
  },
];

interface DashboardViewProps {
  activeView: AppView;
  pageCount: number;
  postCount: number;
  popupCount: number;
}

export function DashboardView({ activeView, pageCount, postCount, popupCount }: DashboardViewProps) {
  const counts = { pageCount, postCount, popupCount };

  return (
    <div className="av-web-studio-screen av-web-studio-screen--dashboard">
      <header className="av-web-studio-screen__header">
        <div>
          <h1 className="av-web-studio-screen__title">Welcome back</h1>
          <p className="av-web-studio-screen__subtitle">
            Choose a section below. Pages and posts each have their own workspace.
            <span className="av-web-studio-screen__version"> v{window.avWebStudioBuilderData.version}</span>
          </p>
        </div>
      </header>

      <div className="av-web-studio-dashboard-grid">
        {CARDS.map((card) => {
          const count = card.countKey ? counts[card.countKey] : null;
          const href = window.avWebStudioBuilderData.adminUrls[card.hrefKey as keyof typeof window.avWebStudioBuilderData.adminUrls];
          if (!href) return null;
          return (
            <a
              key={card.hrefKey}
              href={href}
              className="av-web-studio-dashboard-card"
              onClick={(event) => {
                if (card.view === activeView) {
                  event.preventDefault();
                }
              }}
            >
              <h2 className="av-web-studio-dashboard-card__title">{card.title}</h2>
              {count !== null && <span className="av-web-studio-dashboard-card__count">{count}</span>}
              <p className="av-web-studio-dashboard-card__text">{card.description}</p>
              <span className="av-web-studio-dashboard-card__cta">Open →</span>
            </a>
          );
        })}
      </div>
    </div>
  );
}
