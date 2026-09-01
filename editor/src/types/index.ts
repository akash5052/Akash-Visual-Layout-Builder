export type CodeFileType = "html" | "css" | "js";

export type EditMode = "visual";

export type LayoutMode = "inherit" | "append" | "replace" | "none";

export type EditorScope =
  | "content"
  | "global-header"
  | "global-footer"
  | "page-header"
  | "page-footer"
  | "popup"
  | "seo"
  | "post-settings";

export type PopupTriggerType = "load" | "scroll" | "exit_intent" | "click" | "inactivity";
export type PopupScope = "entire_site" | "specific" | "exclude" | "homepage";
export type PopupFrequencyType = "always" | "session" | "once" | "days";
export type PopupStatus = "publish" | "draft" | "trash";
export type ListStatusFilter = "all" | "publish" | "draft" | "trash";

export interface PaginatedResult<T> {
  items: T[];
  total: number;
  total_pages: number;
  page: number;
  per_page: number;
}

export interface EpbPopup {
  id: string;
  name: string;
  enabled: boolean;
  html: string;
  css: string;
  js: string;
  edit_mode?: EditMode;
  visual?: Record<string, unknown> | null;
  trigger: {
    type: PopupTriggerType;
    delay: number;
    scroll_percent: number;
    click_selector: string;
  };
  conditions: {
    scope: PopupScope;
    page_ids: number[];
    post_types: ContentPostType[];
  };
  frequency: {
    type: PopupFrequencyType;
    days: number;
  };
  overlay_close: boolean;
  esc_close: boolean;
  status: PopupStatus;
  modified: string;
}

export const EMPTY_POPUP: EpbPopup = {
  id: "",
  name: "New Popup",
  enabled: false,
  status: "draft",
  modified: "",
  edit_mode: "visual",
  visual: null,
  html: "",
  css: "",
  js: "",
  trigger: { type: "load", delay: 3, scroll_percent: 50, click_selector: "" },
  conditions: { scope: "entire_site", page_ids: [], post_types: [] },
  frequency: { type: "session", days: 7 },
  overlay_close: true,
  esc_close: true,
};

export interface PageCode {
  html: string;
  css: string;
  js: string;
}

/** One AI assistant turn — prompt, response, and restorable code snapshot. */
export interface AiChatTurn {
  id: string;
  prompt: string;
  explanation: string;
  action: "append" | "replace" | "none";
  codeBefore: PageCode;
  codeAfter: PageCode;
  createdAt: number;
}

export interface LayoutPart {
  html: string;
  css: string;
  js: string;
  visual?: Record<string, unknown> | null;
}

export interface GlobalLayout {
  header_enabled: boolean;
  footer_enabled: boolean;
  header: LayoutPart;
  footer: LayoutPart;
}

export interface PageLayout {
  header_mode: LayoutMode;
  footer_mode: LayoutMode;
  header: LayoutPart;
  footer: LayoutPart;
}

export interface PageSeo {
  meta_title: string;
  meta_description: string;
  focus_keyword: string;
  social_image: string;
  social_image_id: number;
}

export const EMPTY_PAGE_SEO: PageSeo = {
  meta_title: "",
  meta_description: "",
  focus_keyword: "",
  social_image: "",
  social_image_id: 0,
};

export const EMPTY_LAYOUT_PART: LayoutPart = { html: "", css: "", js: "" };

export const EMPTY_GLOBAL_LAYOUT: GlobalLayout = {
  header_enabled: false,
  footer_enabled: false,
  header: { ...EMPTY_LAYOUT_PART },
  footer: { ...EMPTY_LAYOUT_PART },
};

export const EMPTY_PAGE_LAYOUT: PageLayout = {
  header_mode: "inherit",
  footer_mode: "inherit",
  header: { ...EMPTY_LAYOUT_PART },
  footer: { ...EMPTY_LAYOUT_PART },
};

export type ContentPostType = "page" | "post";

export type AppView = "dashboard" | "pages" | "posts" | "templates" | "site-layout" | "popups" | "svg" | "tracking" | "settings";

export type ImageSuggestionsMode = "auto" | "claude" | "gemini" | "local";
export type EditorThemeSetting = "light" | "dark" | "system";

export interface PluginModelOption {
  value: string;
  label: string;
}

export interface PluginAdminSettings {
  ai_enabled: boolean;
  gemini_model: string;
  claude_model: string;
  image_suggestions: ImageSuggestionsMode;
  is_configured: boolean;
  has_gemini: boolean;
  has_claude: boolean;
  ai_client_available?: boolean;
  ai_client_core?: boolean;
  connectors_url?: string;
  has_smart_engine: boolean;
  images_enabled: boolean;
  image_width: number;
  image_height: number;
  images_replace_broken: boolean;
  default_list_status: string;
  default_preview: boolean;
  default_theme: EditorThemeSetting;
  autosave_delay_ms: number;
  ai_history_turns: number;
  ai_panel_default_open: boolean;
  seo_defer_to_plugins: boolean;
  seo_meta_template: string;
  seo_default_og_image: string;
  popups_enabled: boolean;
  layout_header_default: boolean;
  layout_footer_default: boolean;
  google_fonts_enabled: boolean;
  svg_max_kb: number;
  can_manage_settings: boolean;
  ai_capability?: string;
  builder_capability?: string;
  settings_capability?: string;
  available_gemini_models?: PluginModelOption[];
  available_claude_models?: PluginModelOption[];
}

export type TrackingScope = "entire_site" | "epb_only";

export interface TrackingSettings {
  enabled: boolean;
  scope: TrackingScope;
  gtm_id: string;
  ga4_id: string;
  google_ads_id: string;
  facebook_pixel_id: string;
}

export const EMPTY_TRACKING: TrackingSettings = {
  enabled: false,
  scope: "entire_site",
  gtm_id: "",
  ga4_id: "",
  google_ads_id: "",
  facebook_pixel_id: "",
};

export type ContentTab = "builder" | "layout" | "seo" | "settings";

export interface SvgAsset {
  id: number;
  title: string;
  url: string;
  filename: string;
  date: string;
}

export interface PageSummary {
  id: number;
  title: string;
  post_type: ContentPostType;
  status: string;
  slug: string;
  modified: string;
  preview_url: string;
  full_preview_url: string;
  permalink: string;
  epb_enabled: boolean;
}

export interface PageDetail extends PageSummary {
  code: PageCode;
  edit_mode?: EditMode;
  visual?: Record<string, unknown> | null;
  layout: PageLayout;
  seo: PageSeo;
  post_options: PostOptions;
}

export interface TaxonomyCategory {
  id: number;
  name: string;
  slug: string;
  parent: number;
}

export interface ParentPageOption {
  id: number;
  title: string;
}

export type PageTemplate = "default" | "epb-full-width" | "epb-canvas" | "theme";

export interface PostOptions {
  excerpt: string;
  slug: string;
  status: string;
  featured_image_id: number;
  featured_image_url: string;
  category_ids: number[];
  tag_names: string[];
  parent_id: number;
  page_template: PageTemplate;
  comment_status: "open" | "closed";
  hide_title: boolean;
  menu_order: number;
}

export const EMPTY_POST_OPTIONS: PostOptions = {
  excerpt: "",
  slug: "",
  status: "draft",
  featured_image_id: 0,
  featured_image_url: "",
  category_ids: [],
  tag_names: [],
  parent_id: 0,
  page_template: "epb-full-width",
  comment_status: "closed",
  hide_title: true,
  menu_order: 0,
};

export interface TaxonomyTag {
  id: number;
  name: string;
  slug: string;
}

export interface PostTypeOption {
  name: string;
  label: string;
}

export interface PostMetaLists {
  categories: TaxonomyCategory[];
  tags: TaxonomyTag[];
  post_types: PostTypeOption[];
  parent_pages: ParentPageOption[];
}

export interface EpbData {
  restUrl: string;
  nonce: string;
  ajaxUrl: string;
  aiNonce: string;
  canUseAi: boolean;
  aiReady?: boolean;
  canManageSettings?: boolean;
  adminUrl: string;
  pluginUrl: string;
  buildUrl: string;
  siteUrl: string;
  initialPageId: number;
  initialView: AppView;
  defaultListStatus: ListStatusFilter;
  pluginSettings?: Partial<PluginAdminSettings>;
  adminUrls: {
    dashboard: string;
    pages: string;
    posts: string;
    templates: string;
    siteLayout: string;
    popups: string;
    svg: string;
    tracking: string;
    settings?: string;
  };
  user: {
    name: string;
    email: string;
  };
  version: string;
}

declare global {
  interface Window {
    epbBuilderData: EpbData;
    wp?: {
      media: (args: Record<string, unknown>) => {
        on: (event: string, callback: () => void) => void;
        open: () => void;
        state: () => {
          get: (key: string) => {
            first: () => {
              toJSON: () => { url: string; id: number };
            };
          };
        };
      };
    };
  }
}
