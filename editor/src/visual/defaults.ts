import type {
  ColumnChild,
  InnerSection,
  VisualColumn,
  VisualDocument,
  VisualSection,
  VisualWidget,
  WidgetStyle,
  WidgetType,
} from "./types";
import {
  DEFAULT_COLUMN_SETTINGS,
  DEFAULT_SECTION_SETTINGS,
  DEFAULT_WIDGET_STYLE,
  mergeWidgetStyle,
  uid,
} from "./types";
import { DEFAULT_POSTS_DISPLAY, DEFAULT_POSTS_QUERY } from "./postsWidget";

export function placeholderSrc(): string {
  const base = typeof window !== "undefined" ? window.avWebStudioBuilderData?.pluginUrl || "" : "";
  return base ? `${base}assets/images/placeholder.svg` : "";
}

function withStyle(partial?: Partial<WidgetStyle>): WidgetStyle {
  return mergeWidgetStyle(partial);
}

const LINK_DEFAULTS = { linkUrl: "", linkNewTab: false as boolean };

export function createWidget(type: WidgetType): VisualWidget {
  const id = uid("w");
  switch (type) {
    case "heading":
      return {
        id,
        type,
        ...LINK_DEFAULTS,
        content: "Add Your Heading Here",
        tag: "h2",
        align: "left",
        color: "#0f172a",
        style: withStyle({ fontSize: "36px", fontWeight: "700", lineHeight: "1.2", marginBottom: "20px" }),
      };
    case "text":
      return {
        id,
        type,
        ...LINK_DEFAULTS,
        content: "Click to edit this text. Add a short description for your section or offer.",
        align: "left",
        color: "#475569",
        style: withStyle({ fontSize: "16px", lineHeight: "1.7", marginBottom: "20px" }),
      };
    case "button":
      return {
        id,
        type,
        ...LINK_DEFAULTS,
        label: "Get Started",
        url: "#",
        align: "left",
        background: "#6366f1",
        textColor: "#ffffff",
        fullWidth: false,
        paddingX: "24px",
        paddingY: "12px",
        style: withStyle({
          fontSize: "15px",
          fontWeight: "700",
          borderRadius: "0px",
          marginBottom: "20px",
        }),
      };
    case "image":
      return {
        id,
        type,
        ...LINK_DEFAULTS,
        src: placeholderSrc(),
        alt: "Image",
        width: "100%",
        borderRadius: "0px",
        align: "center",
        objectFit: "cover",
        style: withStyle({ marginBottom: "20px", borderRadius: "0px" }),
      };
    case "spacer":
      return { id, type, ...LINK_DEFAULTS, height: 40, style: withStyle({ marginBottom: "0px" }) };
    case "divider":
      return {
        id,
        type,
        ...LINK_DEFAULTS,
        color: "#e2e8f0",
        thickness: 1,
        width: "100%",
        align: "center",
        style: withStyle({ marginTop: "8px", marginBottom: "8px" }),
      };
    case "icon-box":
      return {
        id,
        type,
        ...LINK_DEFAULTS,
        icon: "✦",
        title: "Feature Title",
        text: "Describe this feature in one or two short sentences.",
        align: "center",
        iconSize: "28px",
        titleColor: "#0f172a",
        textColor: "#64748b",
        style: withStyle({ paddingTop: "12px", paddingRight: "12px", paddingBottom: "12px", paddingLeft: "12px", marginBottom: "20px", borderRadius: "0px" }),
      };
    case "html":
      return createWidget("text");
    case "shortcode":
      return {
        id,
        type,
        ...LINK_DEFAULTS,
        content: '[gallery ids="1,2,3"]',
        style: withStyle({ marginBottom: "20px" }),
      };
    case "video":
      return {
        id,
        type,
        ...LINK_DEFAULTS,
        source: "youtube",
        url: "https://www.youtube.com/watch?v=dQw4w9WgXcQ",
        aspectRatio: "16 / 9",
        autoplay: false,
        style: withStyle({ marginBottom: "20px" }),
      };
    case "image-box":
      return {
        id,
        type,
        ...LINK_DEFAULTS,
        src: placeholderSrc(),
        title: "Image Box Title",
        text: "Add a short description under the image.",
        align: "center",
        titleColor: "#0f172a",
        textColor: "#64748b",
        style: withStyle({ marginBottom: "20px" }),
      };
    case "icon-list":
      return {
        id,
        type,
        ...LINK_DEFAULTS,
        items: [
          { icon: "✓", text: "List item one" },
          { icon: "✓", text: "List item two" },
          { icon: "✓", text: "List item three" },
        ],
        color: "#0f172a",
        style: withStyle({ marginBottom: "20px" }),
      };
    case "counter":
      return {
        id,
        type,
        ...LINK_DEFAULTS,
        prefix: "",
        end: 100,
        suffix: "+",
        title: "Projects Completed",
        duration: 1500,
        align: "center",
        numberColor: "#0f172a",
        titleColor: "#64748b",
        style: withStyle({ marginBottom: "20px" }),
      };
    case "progress-bar":
      return {
        id,
        type,
        ...LINK_DEFAULTS,
        title: "WordPress",
        percent: 80,
        barColor: "#6366f1",
        trackColor: "#e2e8f0",
        showPercent: true,
        style: withStyle({ marginBottom: "20px" }),
      };
    case "testimonial":
      return {
        id,
        type,
        ...LINK_DEFAULTS,
        content: "This product completely changed how we build pages. Highly recommended.",
        name: "Alex Morgan",
        role: "Founder, Studio North",
        avatar: "",
        align: "center",
        style: withStyle({ marginBottom: "20px", paddingTop: "16px", paddingRight: "16px", paddingBottom: "16px", paddingLeft: "16px", background: "#f8fafc" }),
      };
    case "tabs":
      return {
        id,
        type,
        ...LINK_DEFAULTS,
        items: [
          { title: "Tab #1", content: "Tab content goes here." },
          { title: "Tab #2", content: "Second tab content." },
          { title: "Tab #3", content: "Third tab content." },
        ],
        style: withStyle({ marginBottom: "20px" }),
      };
    case "accordion":
    case "toggle":
      return {
        id,
        type,
        ...LINK_DEFAULTS,
        items: [
          { title: "Accordion Title #1", content: "Accordion content item #1" },
          { title: "Accordion Title #2", content: "Accordion content item #2" },
          { title: "Accordion Title #3", content: "Accordion content item #3" },
        ],
        style: withStyle({ marginBottom: "20px" }),
      };
    case "social-icons":
      return {
        id,
        type,
        ...LINK_DEFAULTS,
        items: [
          { network: "Facebook", url: "#" },
          { network: "Twitter", url: "#" },
          { network: "Instagram", url: "#" },
          { network: "LinkedIn", url: "#" },
        ],
        iconSize: "18px",
        align: "center",
        style: withStyle({ marginBottom: "20px" }),
      };
    case "alert":
      return {
        id,
        type,
        ...LINK_DEFAULTS,
        title: "Note",
        content: "This is an alert message for your visitors.",
        variant: "info",
        style: withStyle({ marginBottom: "20px" }),
      };
    case "star-rating":
      return {
        id,
        type,
        ...LINK_DEFAULTS,
        rating: 4.5,
        max: 5,
        title: "Customer Rating",
        align: "center",
        color: "#f59e0b",
        style: withStyle({ marginBottom: "20px" }),
      };
    case "blockquote":
      return {
        id,
        type,
        ...LINK_DEFAULTS,
        content: "Design is not just what it looks like. Design is how it works.",
        author: "Steve Jobs",
        align: "left",
        style: withStyle({ marginBottom: "20px", fontSize: "20px", fontWeight: "500" }),
      };
    case "gallery":
      return {
        id,
        type,
        ...LINK_DEFAULTS,
        images: [
          { src: placeholderSrc(), alt: "Gallery 1" },
          { src: placeholderSrc(), alt: "Gallery 2" },
          { src: placeholderSrc(), alt: "Gallery 3" },
        ],
        columns: 3,
        gap: "8px",
        style: withStyle({ marginBottom: "20px" }),
      };
    case "image-carousel":
      return {
        id,
        type,
        ...LINK_DEFAULTS,
        images: [
          { src: placeholderSrc(), alt: "Slide 1" },
          { src: placeholderSrc(), alt: "Slide 2" },
        ],
        height: "320px",
        style: withStyle({ marginBottom: "20px" }),
      };
    case "animated-headline":
      return {
        id,
        type,
        ...LINK_DEFAULTS,
        before: "We create",
        highlight: "amazing",
        after: "websites",
        align: "center",
        highlightColor: "#6366f1",
        style: withStyle({ fontSize: "40px", fontWeight: "800", marginBottom: "20px" }),
      };
    case "countdown":
      return {
        id,
        type,
        ...LINK_DEFAULTS,
        dueDate: "2027-01-01T00:00:00",
        labelDays: "Days",
        labelHours: "Hours",
        labelMinutes: "Minutes",
        labelSeconds: "Seconds",
        align: "center",
        style: withStyle({ marginBottom: "20px" }),
      };
    case "price-table":
      return {
        id,
        type,
        ...LINK_DEFAULTS,
        title: "Pro",
        price: "$49",
        period: "/month",
        features: "Unlimited pages\nPriority support\nCustom domains",
        buttonLabel: "Choose Plan",
        buttonUrl: "#",
        featured: true,
        background: "#ffffff",
        buttonBackground: "#6366f1",
        style: withStyle({
          marginBottom: "20px",
          paddingTop: "24px",
          paddingRight: "24px",
          paddingBottom: "24px",
          paddingLeft: "24px",
          borderStyle: "solid",
          borderWidth: "1px",
          borderColor: "#e2e8f0",
        }),
      };
    case "price-list":
      return {
        id,
        type,
        ...LINK_DEFAULTS,
        items: [
          { title: "Design Consultation", price: "$99", description: "1 hour strategy call" },
          { title: "Landing Page", price: "$499", description: "Single page design + build" },
          { title: "Brand Kit", price: "$299", description: "Logo + colors + typography" },
        ],
        style: withStyle({ marginBottom: "20px" }),
      };
    case "flip-box":
      return {
        id,
        type,
        ...LINK_DEFAULTS,
        frontTitle: "Hover me",
        frontText: "Front side content",
        frontIcon: "✦",
        frontBackground: "#0f172a",
        backTitle: "Discover more",
        backText: "Back side content with a call to action.",
        backButtonLabel: "Learn More",
        backButtonUrl: "#",
        backBackground: "#6366f1",
        minHeight: "240px",
        style: withStyle({ marginBottom: "20px" }),
      };
    case "call-to-action":
      return {
        id,
        type,
        ...LINK_DEFAULTS,
        title: "Ready to get started?",
        text: "Build beautiful pages faster with AV Web Studio.",
        buttonLabel: "Start Now",
        buttonUrl: "#",
        background: "#0f172a",
        image: "",
        style: withStyle({
          marginBottom: "20px",
          paddingTop: "40px",
          paddingRight: "32px",
          paddingBottom: "40px",
          paddingLeft: "32px",
        }),
      };
    case "dual-button":
      return {
        id,
        type,
        ...LINK_DEFAULTS,
        leftLabel: "Learn More",
        leftUrl: "#",
        leftBackground: "#6366f1",
        rightLabel: "Contact Us",
        rightUrl: "#",
        rightBackground: "#0f172a",
        align: "center",
        style: withStyle({ marginBottom: "20px" }),
      };
    case "team":
      return {
        id,
        type,
        ...LINK_DEFAULTS,
        name: "Jordan Lee",
        role: "Creative Director",
        bio: "Designer and strategist focused on clear, memorable brands.",
        image: placeholderSrc(),
        align: "center",
        style: withStyle({ marginBottom: "20px" }),
      };
    case "business-hours":
      return {
        id,
        type,
        ...LINK_DEFAULTS,
        items: [
          { day: "Monday - Friday", hours: "9:00 AM – 6:00 PM" },
          { day: "Saturday", hours: "10:00 AM – 4:00 PM" },
          { day: "Sunday", hours: "Closed" },
        ],
        style: withStyle({ marginBottom: "20px" }),
      };
    case "share-buttons":
      return {
        id,
        type,
        ...LINK_DEFAULTS,
        networks: "facebook,twitter,linkedin,whatsapp",
        align: "left",
        style: withStyle({ marginBottom: "20px" }),
      };
    case "search":
      return {
        id,
        type,
        ...LINK_DEFAULTS,
        placeholder: "Search…",
        buttonLabel: "Search",
        style: withStyle({ marginBottom: "20px" }),
      };
    case "reviews":
      return {
        id,
        type,
        ...LINK_DEFAULTS,
        items: [
          { name: "Sam", rating: 5, content: "Outstanding experience from start to finish." },
          { name: "Riley", rating: 4, content: "Clean design and easy to use." },
        ],
        style: withStyle({ marginBottom: "20px" }),
      };
    case "table-of-contents":
      return {
        id,
        type,
        ...LINK_DEFAULTS,
        title: "Table of Contents",
        items: "Introduction\nFeatures\nPricing\nFAQ",
        style: withStyle({ marginBottom: "20px", paddingTop: "16px", paddingRight: "16px", paddingBottom: "16px", paddingLeft: "16px", background: "#f8fafc" }),
      };
    case "slides":
      return {
        id,
        type,
        ...LINK_DEFAULTS,
        items: [
          {
            title: "Build faster",
            text: "Create landing pages visually in minutes.",
            buttonLabel: "Get Started",
            buttonUrl: "#",
            background: "linear-gradient(135deg,#0f172a,#334155)",
          },
          {
            title: "Design freely",
            text: "Sections, columns, and rich widgets.",
            buttonLabel: "Explore",
            buttonUrl: "#",
            background: "linear-gradient(135deg,#312e81,#6366f1)",
          },
        ],
        height: "360px",
        style: withStyle({ marginBottom: "20px" }),
      };
    case "posts-grid":
    case "posts-list":
    case "posts-carousel":
      return {
        id,
        type,
        ...LINK_DEFAULTS,
        query: { ...DEFAULT_POSTS_QUERY },
        display: { ...DEFAULT_POSTS_DISPLAY },
        style: withStyle({ marginBottom: "20px" }),
      };
  }
}

export function createColumn(width = 100, children: ColumnChild[] = []): VisualColumn {
  return {
    id: uid("col"),
    type: "column",
    settings: { ...DEFAULT_COLUMN_SETTINGS, width },
    children: children.map(normalizeChild),
  };
}

export function createInnerSection(columnCount = 1): InnerSection {
  const widths = splitWidths(columnCount);
  return {
    id: uid("inner"),
    type: "inner-section",
    settings: { ...DEFAULT_SECTION_SETTINGS, padding: "20px 0", background: "transparent" },
    columns: widths.map((w) => createColumn(w)),
  };
}

export function createSection(columnCount = 1, widgets: VisualWidget[] = []): VisualSection {
  const widths = splitWidths(columnCount);
  const columns = widths.map((w, i) => createColumn(w, i === 0 ? widgets : []));
  return {
    id: uid("sec"),
    type: "section",
    settings: { ...DEFAULT_SECTION_SETTINGS },
    columns,
  };
}

export function createEmptyDocument(): VisualDocument {
  return {
    version: 1,
    sections: [],
  };
}

export function documentFromHtml(html: string): VisualDocument {
  const trimmed = html.trim();
  if (!trimmed) {
    return createEmptyDocument();
  }

  const widgets: VisualWidget[] = [];
  const pushText = (text: string) => {
    const content = text.replace(/\s+/g, " ").trim();
    if (content) {
      widgets.push(normalizeWidget({ type: "text", content }));
    }
  };

  if (typeof DOMParser !== "undefined") {
    const parsed = new DOMParser().parseFromString(
      trimmed.replace(/<script[\s\S]*?<\/script>/gi, "").replace(/<style[\s\S]*?<\/style>/gi, ""),
      "text/html"
    );
    const walk = (el: Element) => {
      if (widgets.length >= 80) return;
      const tag = el.tagName.toLowerCase();
      if (tag === "h1" || tag === "h2" || tag === "h3" || tag === "h4") {
        const content = (el.textContent || "").trim();
        if (content) {
          widgets.push(normalizeWidget({ type: "heading", tag, content }));
        }
        return;
      }
      if (tag === "p" || tag === "li" || tag === "blockquote") {
        pushText(el.textContent || "");
        return;
      }
      if (tag === "img") {
        const src = el.getAttribute("src") || "";
        if (src) {
          widgets.push(normalizeWidget({ type: "image", src, alt: el.getAttribute("alt") || "" }));
        }
        return;
      }
      if (tag === "a") {
        const label = (el.textContent || "").trim();
        if (label) {
          widgets.push(normalizeWidget({ type: "button", label, url: el.getAttribute("href") || "#" }));
        }
        return;
      }
      if (el.children.length) {
        Array.from(el.children).forEach(walk);
        return;
      }
      pushText(el.textContent || "");
    };
    Array.from(parsed.body.children).forEach(walk);
  }

  if (!widgets.length) {
    pushText(trimmed.replace(/<[^>]+>/g, " "));
  }

  return {
    version: 1,
    sections: widgets.length ? [createSection(1, widgets)] : [],
  };
}

export function normalizeWidget(raw: Partial<VisualWidget> & { type: WidgetType; id?: string }): VisualWidget {
  if (raw.type === "html") {
    const content = String((raw as { content?: string }).content || "")
      .replace(/<[^>]+>/g, " ")
      .replace(/\s+/g, " ")
      .trim();
    raw = { ...raw, type: "text", content };
  }
  const base = createWidget(raw.type);
  const merged = {
    ...base,
    ...raw,
    id: raw.id || base.id,
    linkUrl: raw.linkUrl ?? base.linkUrl ?? "",
    linkNewTab: raw.linkNewTab ?? base.linkNewTab ?? false,
    style: withStyle({ ...base.style, ...(raw.style || {}) }),
    styleOverrides: raw.styleOverrides,
  };
  return merged as VisualWidget;
}

function normalizeChild(child: ColumnChild): ColumnChild {
  if (child.type === "inner-section") {
    return {
      ...child,
      settings: { ...DEFAULT_SECTION_SETTINGS, ...child.settings },
      settingsOverrides: child.settingsOverrides,
      columns: (child.columns || []).map((col) => ({
        ...col,
        settings: { ...DEFAULT_COLUMN_SETTINGS, ...col.settings },
        settingsOverrides: col.settingsOverrides,
        children: (col.children || []).map(normalizeChild),
      })),
    };
  }
  return normalizeWidget(child);
}

export function normalizeDocument(raw: unknown): VisualDocument {
  if (!raw || typeof raw !== "object") {
    return createEmptyDocument();
  }
  const doc = raw as VisualDocument;
  if (!Array.isArray(doc.sections) || doc.sections.length === 0) {
    return createEmptyDocument();
  }
  return {
    version: 1,
    sections: doc.sections.map((section) => ({
      ...section,
      settings: { ...DEFAULT_SECTION_SETTINGS, ...section.settings },
      settingsOverrides: section.settingsOverrides,
      columns: (section.columns || []).map((col) => ({
        ...col,
        settings: { ...DEFAULT_COLUMN_SETTINGS, ...col.settings },
        settingsOverrides: col.settingsOverrides,
        children: (col.children || []).map(normalizeChild),
      })),
    })),
  };
}

function splitWidths(count: number): number[] {
  const n = Math.max(1, Math.min(4, count));
  const base = Math.floor(100 / n);
  const widths = Array.from({ length: n }, () => base);
  widths[widths.length - 1] += 100 - base * n;
  return widths;
}

export { WIDGET_CATALOG, WIDGET_CATEGORIES, WIDGET_ICONS, widgetLabel } from "./widgetCatalog";

export const SECTION_PRESETS: { label: string; columns: number }[] = [
  { label: "1 column", columns: 1 },
  { label: "2 columns", columns: 2 },
  { label: "3 columns", columns: 3 },
  { label: "4 columns", columns: 4 },
];

export { DEFAULT_WIDGET_STYLE };
