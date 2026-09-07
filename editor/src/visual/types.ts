export type WidgetType =
  | "heading"
  | "text"
  | "button"
  | "image"
  | "spacer"
  | "divider"
  | "icon-box"
  | "html"
  | "shortcode"
  | "video"
  | "image-box"
  | "icon-list"
  | "counter"
  | "progress-bar"
  | "testimonial"
  | "tabs"
  | "accordion"
  | "toggle"
  | "social-icons"
  | "alert"
  | "star-rating"
  | "blockquote"
  | "gallery"
  | "image-carousel"
  | "animated-headline"
  | "countdown"
  | "price-table"
  | "price-list"
  | "flip-box"
  | "call-to-action"
  | "dual-button"
  | "team"
  | "business-hours"
  | "share-buttons"
  | "search"
  | "reviews"
  | "table-of-contents"
  | "slides"
  | "posts-grid"
  | "posts-list"
  | "posts-carousel";

export type TextAlign = "left" | "center" | "right";
export type TextTransform = "none" | "uppercase" | "capitalize" | "lowercase";
export type BorderStyle = "none" | "solid" | "dashed" | "dotted";

export interface WidgetStyle {
  marginTop: string;
  marginRight: string;
  marginBottom: string;
  marginLeft: string;
  paddingTop: string;
  paddingRight: string;
  paddingBottom: string;
  paddingLeft: string;
  /** Legacy shorthand — migrated into padding* sides on merge. */
  padding?: string;
  fontFamily: string;
  fontSize: string;
  fontWeight: string;
  lineHeight: string;
  letterSpacing: string;
  textTransform: TextTransform;
  borderRadius: string;
  borderWidth: string;
  borderColor: string;
  borderStyle: BorderStyle;
  boxShadow: string;
  opacity: number;
  background: string;
  maxWidth: string;
  zIndex: string;
}

export interface SectionSettings {
  fullWidth: boolean;
  contentWidth: number | string;
  minHeight: number | string;
  padding: string;
  background: string;
  backgroundImage: string;
  textColor: string;
  gap: number | string;
  zIndex: string;
}

export interface ColumnSettings {
  width: number;
  padding: string;
  background: string;
  verticalAlign: "top" | "middle" | "bottom";
  zIndex: string;
}

export type ResponsiveOverrides<T> = {
  tablet?: Partial<T>;
  mobile?: Partial<T>;
};

export interface WidgetBase {
  id: string;
  type: WidgetType;
  style: WidgetStyle;
  /** Tablet / mobile style overrides — desktop `style` is the base. */
  styleOverrides?: ResponsiveOverrides<WidgetStyle>;
  /** Optional link wrapping the widget content. Empty = no link. */
  linkUrl: string;
  linkNewTab: boolean;
}

export interface HeadingWidget extends WidgetBase {
  type: "heading";
  content: string;
  tag: "h1" | "h2" | "h3" | "h4";
  align: TextAlign;
  color: string;
}

export interface TextWidget extends WidgetBase {
  type: "text";
  content: string;
  align: TextAlign;
  color: string;
}

export interface ButtonWidget extends WidgetBase {
  type: "button";
  label: string;
  url: string;
  align: TextAlign;
  background: string;
  textColor: string;
  fullWidth: boolean;
  paddingX: string;
  paddingY: string;
}

export interface ImageWidget extends WidgetBase {
  type: "image";
  src: string;
  alt: string;
  width: string;
  borderRadius: string;
  align: TextAlign;
  objectFit: "cover" | "contain" | "fill";
}

export interface SpacerWidget extends WidgetBase {
  type: "spacer";
  height: number | string;
}

export interface DividerWidget extends WidgetBase {
  type: "divider";
  color: string;
  thickness: number | string;
  width: string;
  align: TextAlign;
}

export interface IconBoxWidget extends WidgetBase {
  type: "icon-box";
  icon: string;
  title: string;
  text: string;
  align: TextAlign | "center";
  iconSize: string;
  titleColor: string;
  textColor: string;
}

export interface HtmlWidget extends WidgetBase {
  type: "html";
  content: string;
}

export interface ShortcodeWidget extends WidgetBase {
  type: "shortcode";
  content: string;
}

export interface VideoWidget extends WidgetBase {
  type: "video";
  source: "youtube" | "vimeo" | "hosted";
  url: string;
  aspectRatio: string;
  autoplay: boolean;
}

export interface ImageBoxWidget extends WidgetBase {
  type: "image-box";
  src: string;
  title: string;
  text: string;
  align: TextAlign | "center";
  titleColor: string;
  textColor: string;
}

export interface IconListWidget extends WidgetBase {
  type: "icon-list";
  items: { icon: string; text: string; url?: string }[];
  color: string;
}

export interface CounterWidget extends WidgetBase {
  type: "counter";
  prefix: string;
  end: number;
  suffix: string;
  title: string;
  duration: number;
  align: TextAlign | "center";
  numberColor: string;
  titleColor: string;
}

export interface ProgressBarWidget extends WidgetBase {
  type: "progress-bar";
  title: string;
  percent: number;
  barColor: string;
  trackColor: string;
  showPercent: boolean;
}

export interface TestimonialWidget extends WidgetBase {
  type: "testimonial";
  content: string;
  name: string;
  role: string;
  avatar: string;
  align: TextAlign | "center";
}

export interface TabsWidget extends WidgetBase {
  type: "tabs";
  items: { title: string; content: string }[];
}

export interface AccordionWidget extends WidgetBase {
  type: "accordion";
  items: { title: string; content: string }[];
}

export interface ToggleWidget extends WidgetBase {
  type: "toggle";
  items: { title: string; content: string }[];
}

export interface SocialIconsWidget extends WidgetBase {
  type: "social-icons";
  items: { network: string; url: string }[];
  iconSize: string;
  align: TextAlign | "center";
}

export interface AlertWidget extends WidgetBase {
  type: "alert";
  title: string;
  content: string;
  variant: "info" | "success" | "warning" | "danger";
}

export interface StarRatingWidget extends WidgetBase {
  type: "star-rating";
  rating: number;
  max: number;
  title: string;
  align: TextAlign | "center";
  color: string;
}

export interface BlockquoteWidget extends WidgetBase {
  type: "blockquote";
  content: string;
  author: string;
  align: TextAlign | "center";
}

export interface GalleryWidget extends WidgetBase {
  type: "gallery";
  images: { src: string; alt: string; url?: string }[];
  columns: number;
  gap: string;
}

export interface ImageCarouselWidget extends WidgetBase {
  type: "image-carousel";
  images: { src: string; alt: string; url?: string }[];
  height: string;
}

export interface AnimatedHeadlineWidget extends WidgetBase {
  type: "animated-headline";
  before: string;
  highlight: string;
  after: string;
  align: TextAlign | "center";
  highlightColor: string;
}

export interface CountdownWidget extends WidgetBase {
  type: "countdown";
  dueDate: string;
  labelDays: string;
  labelHours: string;
  labelMinutes: string;
  labelSeconds: string;
  align: TextAlign | "center";
}

export interface PriceTableWidget extends WidgetBase {
  type: "price-table";
  title: string;
  price: string;
  period: string;
  features: string;
  buttonLabel: string;
  buttonUrl: string;
  featured: boolean;
  background: string;
  buttonBackground: string;
}

export interface PriceListWidget extends WidgetBase {
  type: "price-list";
  items: { title: string; price: string; description: string; url?: string }[];
}

export interface FlipBoxWidget extends WidgetBase {
  type: "flip-box";
  frontTitle: string;
  frontText: string;
  frontIcon: string;
  frontBackground: string;
  backTitle: string;
  backText: string;
  backButtonLabel: string;
  backButtonUrl: string;
  backBackground: string;
  minHeight: string;
}

export interface CallToActionWidget extends WidgetBase {
  type: "call-to-action";
  title: string;
  text: string;
  buttonLabel: string;
  buttonUrl: string;
  background: string;
  image: string;
}

export interface DualButtonWidget extends WidgetBase {
  type: "dual-button";
  leftLabel: string;
  leftUrl: string;
  leftBackground: string;
  rightLabel: string;
  rightUrl: string;
  rightBackground: string;
  align: TextAlign | "center";
}

export interface TeamWidget extends WidgetBase {
  type: "team";
  name: string;
  role: string;
  bio: string;
  image: string;
  align: TextAlign | "center";
}

export interface ReviewsWidget extends WidgetBase {
  type: "reviews";
  items: { name: string; rating: number; content: string; url?: string }[];
}

export interface BusinessHoursWidget extends WidgetBase {
  type: "business-hours";
  items: { day: string; hours: string }[];
}

export interface ShareButtonsWidget extends WidgetBase {
  type: "share-buttons";
  networks: string;
  align: TextAlign | "center";
}

export interface SearchWidget extends WidgetBase {
  type: "search";
  placeholder: string;
  buttonLabel: string;
}

export interface TableOfContentsWidget extends WidgetBase {
  type: "table-of-contents";
  title: string;
  items: string;
}

export interface SlidesWidget extends WidgetBase {
  type: "slides";
  items: { title: string; text: string; buttonLabel: string; buttonUrl: string; background: string }[];
  height: string;
}

export type PostsOrderBy = "date" | "title" | "modified" | "comment_count" | "rand" | "menu_order";

export interface PostsWidgetQuery {
  postType: string;
  categoryIds: number[];
  tagSlugs: string[];
  orderBy: PostsOrderBy;
  order: "ASC" | "DESC";
  postsPerPage: number;
  offset: number;
  excludeCurrent: boolean;
}

export interface PostsWidgetDisplay {
  showImage: boolean;
  showTitle: boolean;
  showExcerpt: boolean;
  showMeta: boolean;
  showAuthor: boolean;
  showDate: boolean;
  showCategories: boolean;
  showReadMore: boolean;
  readMoreText: string;
  excerptLength: number;
  imageSize: "thumbnail" | "medium" | "large" | "full";
  columns: number;
  gap: string;
  titleTag: "h2" | "h3" | "h4";
  imageRatio: string;
  cardStyle: "default" | "card" | "minimal" | "overlay";
  listImageWidth: string;
  carouselHeight: string;
  metaDateFormat: string;
}

export interface PostsGridWidget extends WidgetBase {
  type: "posts-grid";
  query: PostsWidgetQuery;
  display: PostsWidgetDisplay;
}

export interface PostsListWidget extends WidgetBase {
  type: "posts-list";
  query: PostsWidgetQuery;
  display: PostsWidgetDisplay;
}

export interface PostsCarouselWidget extends WidgetBase {
  type: "posts-carousel";
  query: PostsWidgetQuery;
  display: PostsWidgetDisplay;
}

export type VisualWidget =
  | HeadingWidget
  | TextWidget
  | ButtonWidget
  | ImageWidget
  | SpacerWidget
  | DividerWidget
  | IconBoxWidget
  | HtmlWidget
  | ShortcodeWidget
  | VideoWidget
  | ImageBoxWidget
  | IconListWidget
  | CounterWidget
  | ProgressBarWidget
  | TestimonialWidget
  | TabsWidget
  | AccordionWidget
  | ToggleWidget
  | SocialIconsWidget
  | AlertWidget
  | StarRatingWidget
  | BlockquoteWidget
  | GalleryWidget
  | ImageCarouselWidget
  | AnimatedHeadlineWidget
  | CountdownWidget
  | PriceTableWidget
  | PriceListWidget
  | FlipBoxWidget
  | CallToActionWidget
  | DualButtonWidget
  | TeamWidget
  | BusinessHoursWidget
  | ShareButtonsWidget
  | SearchWidget
  | ReviewsWidget
  | TableOfContentsWidget
  | SlidesWidget
  | PostsGridWidget
  | PostsListWidget
  | PostsCarouselWidget;

export interface InnerSection {
  id: string;
  type: "inner-section";
  settings: SectionSettings;
  settingsOverrides?: ResponsiveOverrides<SectionSettings>;
  columns: VisualColumn[];
}

export type ColumnChild = VisualWidget | InnerSection;

export interface VisualColumn {
  id: string;
  type: "column";
  settings: ColumnSettings;
  settingsOverrides?: ResponsiveOverrides<ColumnSettings>;
  children: ColumnChild[];
}

export interface VisualSection {
  id: string;
  type: "section";
  settings: SectionSettings;
  settingsOverrides?: ResponsiveOverrides<SectionSettings>;
  columns: VisualColumn[];
}

export interface VisualDocument {
  version: 1;
  sections: VisualSection[];
}

export type VisualSelection =
  | { kind: "section"; sectionId: string }
  | { kind: "column"; sectionId: string; columnId: string; innerSectionId?: string; parentColumnId?: string }
  | { kind: "widget"; sectionId: string; columnId: string; widgetId: string; innerSectionId?: string; parentColumnId?: string }
  | { kind: "inner-section"; sectionId: string; columnId: string; innerSectionId: string }
  | null;

export type WidgetPath = {
  sectionId: string;
  columnId: string;
  widgetId?: string;
  innerSectionId?: string;
  parentColumnId?: string;
  index?: number;
};

export const DEFAULT_WIDGET_STYLE: WidgetStyle = {
  marginTop: "0px",
  marginRight: "0px",
  marginBottom: "20px",
  marginLeft: "0px",
  paddingTop: "0px",
  paddingRight: "0px",
  paddingBottom: "0px",
  paddingLeft: "0px",
  fontFamily: "",
  fontSize: "",
  fontWeight: "",
  lineHeight: "",
  letterSpacing: "0",
  textTransform: "none",
  borderRadius: "0px",
  borderWidth: "0px",
  borderColor: "#e2e8f0",
  borderStyle: "none",
  boxShadow: "none",
  opacity: 1,
  background: "transparent",
  maxWidth: "100%",
  zIndex: "",
};

export const DEFAULT_SECTION_SETTINGS: SectionSettings = {
  fullWidth: true,
  contentWidth: 1140,
  minHeight: 0,
  padding: "60px 24px",
  background: "#ffffff",
  backgroundImage: "",
  textColor: "#0f172a",
  gap: 0,
  zIndex: "",
};

export const DEFAULT_COLUMN_SETTINGS: ColumnSettings = {
  width: 100,
  padding: "12px",
  background: "transparent",
  verticalAlign: "top",
  zIndex: "",
};

export function uid(prefix = "av-web-studio"): string {
  return `${prefix}_${Math.random().toString(36).slice(2, 9)}`;
}

/** Expand CSS margin/padding shorthand into four sides. */
export function expandCssBox(value: string | undefined | null, fallback = "0px"): {
  top: string;
  right: string;
  bottom: string;
  left: string;
} {
  const raw = String(value ?? "").trim();
  if (!raw) {
    return { top: fallback, right: fallback, bottom: fallback, left: fallback };
  }
  const parts = raw.split(/\s+/).filter(Boolean);
  if (parts.length === 1) {
    return { top: parts[0], right: parts[0], bottom: parts[0], left: parts[0] };
  }
  if (parts.length === 2) {
    return { top: parts[0], right: parts[1], bottom: parts[0], left: parts[1] };
  }
  if (parts.length === 3) {
    return { top: parts[0], right: parts[1], bottom: parts[2], left: parts[1] };
  }
  return { top: parts[0], right: parts[1], bottom: parts[2], left: parts[3] };
}

export function collapseCssBox(box: { top: string; right: string; bottom: string; left: string }): string {
  const { top, right, bottom, left } = box;
  if (top === right && right === bottom && bottom === left) return top || "0";
  if (top === bottom && right === left) return `${top} ${right}`;
  if (right === left) return `${top} ${right} ${bottom}`;
  return `${top} ${right} ${bottom} ${left}`;
}

export function mergeWidgetStyle(style?: Partial<WidgetStyle> | null): WidgetStyle {
  const incoming = style || {};
  const merged: WidgetStyle = { ...DEFAULT_WIDGET_STYLE, ...incoming };

  const hasPaddingSides =
    incoming.paddingTop !== undefined ||
    incoming.paddingRight !== undefined ||
    incoming.paddingBottom !== undefined ||
    incoming.paddingLeft !== undefined;

  if (!hasPaddingSides && incoming.padding) {
    const box = expandCssBox(incoming.padding, "0px");
    merged.paddingTop = box.top;
    merged.paddingRight = box.right;
    merged.paddingBottom = box.bottom;
    merged.paddingLeft = box.left;
  }

  merged.marginRight = merged.marginRight || "0px";
  merged.marginLeft = merged.marginLeft || "0px";
  merged.paddingTop = merged.paddingTop || "0px";
  merged.paddingRight = merged.paddingRight || "0px";
  merged.paddingBottom = merged.paddingBottom || "0px";
  merged.paddingLeft = merged.paddingLeft || "0px";
  merged.zIndex = merged.zIndex ?? "";

  return merged;
}
