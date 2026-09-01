import type { WidgetType } from "./types";

export type WidgetCatalogItem = {
  type: WidgetType;
  label: string;
  hint: string;
  icon: string;
};

export type WidgetCategory = {
  id: string;
  label: string;
  items: WidgetCatalogItem[];
};

/** Categorized widget library. */
export const WIDGET_CATEGORIES: WidgetCategory[] = [
  {
    id: "basic",
    label: "Basic",
    items: [
      { type: "heading", label: "Heading", hint: "Title text", icon: "H" },
      { type: "text", label: "Text", hint: "Paragraph", icon: "T" },
      { type: "button", label: "Button", hint: "Call to action", icon: "Btn" },
      { type: "image", label: "Image", hint: "Photo / media", icon: "Img" },
      { type: "video", label: "Video", hint: "YouTube / Vimeo / MP4", icon: "▶" },
      { type: "divider", label: "Divider", hint: "Horizontal line", icon: "—" },
      { type: "spacer", label: "Spacer", hint: "Vertical space", icon: "↕" },
      { type: "shortcode", label: "Shortcode", hint: "WordPress shortcode", icon: "[ ]" },
    ],
  },
  {
    id: "general",
    label: "General",
    items: [
      { type: "image-box", label: "Image Box", hint: "Image + title + text", icon: "▣" },
      { type: "icon-box", label: "Icon Box", hint: "Icon + text", icon: "◆" },
      { type: "icon-list", label: "Icon List", hint: "Bulleted icons", icon: "≡" },
      { type: "counter", label: "Counter", hint: "Animated number", icon: "123" },
      { type: "progress-bar", label: "Progress Bar", hint: "Percent bar", icon: "▬" },
      { type: "testimonial", label: "Testimonial", hint: "Quote + author", icon: "❝" },
      { type: "tabs", label: "Tabs", hint: "Tabbed content", icon: "▤" },
      { type: "accordion", label: "Accordion", hint: "Collapsible panels", icon: "☰" },
      { type: "toggle", label: "Toggle", hint: "Expand / collapse", icon: "☑" },
      { type: "social-icons", label: "Social Icons", hint: "Social links", icon: "◎" },
      { type: "alert", label: "Alert", hint: "Notice box", icon: "i" },
      { type: "star-rating", label: "Star Rating", hint: "Rating stars", icon: "★" },
      { type: "blockquote", label: "Blockquote", hint: "Quoted text", icon: "“”" },
      { type: "gallery", label: "Gallery", hint: "Image grid", icon: "▦" },
      { type: "image-carousel", label: "Image Carousel", hint: "Sliding images", icon: "⇄" },
    ],
  },
  {
    id: "wordpress",
    label: "WordPress",
    items: [
      { type: "posts-grid", label: "Posts Grid", hint: "Post cards in a grid", icon: "▦" },
      { type: "posts-list", label: "Posts List", hint: "Vertical post list", icon: "☰" },
      { type: "posts-carousel", label: "Posts Carousel", hint: "Sliding post slider", icon: "⇄" },
    ],
  },
  {
    id: "advanced",
    label: "Advanced",
    items: [
      { type: "animated-headline", label: "Animated Headline", hint: "Highlighted title", icon: "Abc" },
      { type: "countdown", label: "Countdown", hint: "Timer to a date", icon: "⏱" },
      { type: "price-table", label: "Price Table", hint: "Pricing card", icon: "$" },
      { type: "price-list", label: "Price List", hint: "Menu / services", icon: "☰$" },
      { type: "flip-box", label: "Flip Box", hint: "Front / back card", icon: "↻" },
      { type: "call-to-action", label: "Call to Action", hint: "Promo banner", icon: "CTA" },
      { type: "dual-button", label: "Dual Button", hint: "Two buttons", icon: "Btn²" },
      { type: "team", label: "Team", hint: "Member card", icon: "☺" },
      { type: "business-hours", label: "Business Hours", hint: "Opening times", icon: "🕒" },
      { type: "share-buttons", label: "Share Buttons", hint: "Social share", icon: "⤴" },
      { type: "search", label: "Search", hint: "Search form", icon: "⌕" },
      { type: "reviews", label: "Reviews", hint: "Review cards", icon: "★❝" },
      { type: "table-of-contents", label: "Table of Contents", hint: "Page outline", icon: "TOC" },
      { type: "slides", label: "Slides", hint: "Hero slider", icon: "◀▶" },
    ],
  },
];

export const WIDGET_CATALOG: WidgetCatalogItem[] = WIDGET_CATEGORIES.flatMap((c) => c.items);

export const WIDGET_ICONS: Record<WidgetType, string> = Object.fromEntries(
  WIDGET_CATALOG.map((item) => [item.type, item.icon])
) as Record<WidgetType, string>;

export function widgetLabel(type: WidgetType | string): string {
  const found = WIDGET_CATALOG.find((w) => w.type === type);
  if (found) return found.label;
  return String(type).replace(/-/g, " ").replace(/\b\w/g, (c) => c.toUpperCase());
}
