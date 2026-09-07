import type {
  PageDetail,
  PageCode,
  PageSummary,
  ContentPostType,
  GlobalLayout,
  PageLayout,
  StudioPopup,
  PageSeo,
  PostOptions,
  PostMetaLists,
  ListStatusFilter,
  PaginatedResult,
} from "../types";

function getHeaders(): HeadersInit {
  return {
    "Content-Type": "application/json",
    "X-WP-Nonce": window.avWebStudioBuilderData.nonce,
  };
}

async function request<T>(endpoint: string, options: RequestInit = {}): Promise<T> {
  const response = await fetch(`${window.avWebStudioBuilderData.restUrl}${endpoint}`, {
    ...options,
    headers: { ...getHeaders(), ...options.headers },
  });

  const data = await response.json();

  if (!response.ok) {
    throw new Error(data?.message || data?.code || "Request failed");
  }

  return data as T;
}

export interface FetchPagesParams {
  post_type?: ContentPostType | "all";
  status?: ListStatusFilter;
  page?: number;
  per_page?: number;
  search?: string;
}

export interface FetchPopupsParams {
  status?: ListStatusFilter;
  page?: number;
  per_page?: number;
}

function buildQuery(params: Record<string, string | number | undefined>): string {
  const query = new URLSearchParams();
  for (const [key, value] of Object.entries(params)) {
    if (value !== undefined && value !== "") {
      query.set(key, String(value));
    }
  }
  const qs = query.toString();
  return qs ? `?${qs}` : "";
}

export function fetchPages(params: FetchPagesParams = {}): Promise<PaginatedResult<PageSummary>> {
  return request<PaginatedResult<PageSummary>>(
    `pages${buildQuery({
      post_type: params.post_type,
      status: params.status,
      page: params.page,
      per_page: params.per_page,
      search: params.search,
    })}`
  );
}

/** Recent or title-matched pages/posts for URL autocomplete. */
export function fetchUrlSuggestions(search = "", limit = 8): Promise<PageSummary[]> {
  return fetchPages({
    post_type: "all",
    status: "publish",
    per_page: limit,
    search: search.trim() || undefined,
  }).then((result) => result.items);
}

export function fetchPagesForPicker(): Promise<PageSummary[]> {
  return fetchPages({ post_type: "all", status: "publish", per_page: 100 }).then((result) => result.items);
}

export function createPage(title: string, postType: ContentPostType = "page"): Promise<PageDetail> {
  return request<PageDetail>("pages", {
    method: "POST",
    body: JSON.stringify({ title, post_type: postType }),
  });
}

export function fetchPage(id: number): Promise<PageDetail> {
  return request<PageDetail>(`pages/${id}`);
}

export function deletePage(id: number, force = false): Promise<{ success: boolean; id: number; message: string }> {
  return request(`pages/${id}`, {
    method: "DELETE",
    body: JSON.stringify({ force }),
  });
}

export function restorePage(id: number): Promise<{ success: boolean; id: number; message: string }> {
  return request(`pages/${id}/restore`, { method: "POST" });
}

export function quickEditPage(
  id: number,
  data: { title?: string; status?: string; slug?: string }
): Promise<{ success: boolean; page: PageSummary; message: string }> {
  return request(`pages/${id}/quick`, {
    method: "POST",
    body: JSON.stringify(data),
  });
}

export function bulkPages(
  action: "trash" | "restore" | "delete",
  ids: number[],
  force = false
): Promise<{ success: boolean; processed: number; errors: number; message: string }> {
  return request("pages/bulk", {
    method: "POST",
    body: JSON.stringify({ action, ids, force }),
  });
}

export function savePage(
  id: number,
  title: string,
  code: PageCode,
  layout?: PageLayout,
  seo?: PageSeo,
  postOptions?: PostOptions,
  visual?: import("../visual/types").VisualDocument | null
): Promise<{ success: boolean; page: PageDetail; full_preview_url: string; message: string }> {
  return request(`pages/${id}`, {
    method: "POST",
    body: JSON.stringify({
      title,
      code,
      layout,
      seo,
      post_options: postOptions,
      edit_mode: "visual",
      visual,
    }),
  });
}

export function publishPage(
  id: number,
  title: string,
  code: PageCode,
  layout?: PageLayout,
  seo?: PageSeo,
  postOptions?: PostOptions,
  visual?: import("../visual/types").VisualDocument | null
): Promise<{ success: boolean; page: PageDetail; preview_url: string; message: string }> {
  return request(`pages/${id}/publish`, {
    method: "POST",
    body: JSON.stringify({
      title,
      code,
      layout,
      seo,
      post_options: postOptions,
      edit_mode: "visual",
      visual,
    }),
  });
}

export function fetchPostMetaLists(postId = 0): Promise<PostMetaLists> {
  const query = postId > 0 ? `?post_id=${postId}` : "";
  return request(`post-meta/lists${query}`);
}

export function renderPostsWidget(
  config: Record<string, unknown>,
  postId = 0
): Promise<{ html: string }> {
  return request("posts/render", {
    method: "POST",
    body: JSON.stringify({ config, post_id: postId }),
  });
}

export function fetchGlobalLayout(): Promise<{ layout: GlobalLayout }> {
  return request("layout/global");
}

export function saveGlobalLayout(layout: GlobalLayout): Promise<{ success: boolean; layout: GlobalLayout; message: string }> {
  return request("layout/global", {
    method: "POST",
    body: JSON.stringify({ layout }),
  });
}

export function fetchPopups(params: FetchPopupsParams = {}): Promise<PaginatedResult<StudioPopup>> {
  return request<PaginatedResult<StudioPopup>>(
    `popups${buildQuery({
      status: params.status,
      page: params.page,
      per_page: params.per_page,
    })}`
  );
}

export function createPopup(name = "New Popup"): Promise<{ success: boolean; popup: StudioPopup }> {
  return request("popups", {
    method: "POST",
    body: JSON.stringify({ name }),
  });
}

export function savePopup(popup: StudioPopup): Promise<{ success: boolean; popup: StudioPopup; message: string }> {
  return request(`popups/${popup.id}`, {
    method: "POST",
    body: JSON.stringify({ popup }),
  });
}

export function deletePopup(id: string, force = false): Promise<{ success: boolean; id: string; message: string }> {
  return request(`popups/${id}`, {
    method: "DELETE",
    body: JSON.stringify({ force }),
  });
}

export function restorePopup(id: string): Promise<{ success: boolean; id: string; message: string }> {
  return request(`popups/${id}/restore`, { method: "POST" });
}

export function quickEditPopup(
  id: string,
  data: { name?: string; status?: "publish" | "draft" }
): Promise<{ success: boolean; popup: StudioPopup; message: string }> {
  return request(`popups/${id}/quick`, {
    method: "POST",
    body: JSON.stringify(data),
  });
}

export function bulkPopups(
  action: "trash" | "restore" | "delete",
  ids: string[],
  force = false
): Promise<{ success: boolean; processed: number; errors: number; message: string }> {
  return request("popups/bulk", {
    method: "POST",
    body: JSON.stringify({ action, ids, force }),
  });
}

export async function generateAiCode(
  prompt: string,
  code?: PageCode,
  title?: string
): Promise<{ success: boolean; code: PageCode; source: string; message: string; action: "append" | "replace" | "none" }> {
  // Secure admin-ajax.php proxy (nonce-verified, admin-only) — relays to Gemini server-side.
  const form = new FormData();
  form.append("action", "av_web_studio_ai_generate");
  form.append("nonce", window.avWebStudioBuilderData.aiNonce);
  form.append("prompt", prompt);
  if (title) form.append("title", title);
  if (code) {
    form.append("code[html]", code.html ?? "");
    form.append("code[css]", "");
    form.append("code[js]", "");
  }

  const response = await fetch(window.avWebStudioBuilderData.ajaxUrl, { method: "POST", body: form });
  const payload = await response.json();

  if (!response.ok || !payload?.success) {
    throw new Error(payload?.data?.message || "AI generation failed");
  }

  const d = payload.data;
  return {
    success: true,
    code: { html: d.html ?? "", css: "", js: "" },
    source: d.source ?? "ai",
    message: d.explanation ?? "",
    action: d.action ?? "append",
  };
}

export function fetchAdminSettings(): Promise<import("../types").PluginAdminSettings> {
  return request("settings");
}

export interface StarterTemplate {
  id: string;
  title: string;
  description: string;
  topic: string;
  brand: string;
  preview: string;
  accent: string;
  source?: "builtin" | "uploaded";
  can_delete?: boolean;
  badge?: string;
}

export function fetchTemplates(): Promise<{ templates: StarterTemplate[] }> {
  return request("templates");
}

export function applyTemplate(id: string, title?: string): Promise<PageDetail> {
  return request("templates/apply", {
    method: "POST",
    body: JSON.stringify({ id, title, post_type: "page" }),
  });
}

export async function uploadTemplate(
  file: File
): Promise<{ success: boolean; template: StarterTemplate; message: string }> {
  const form = new FormData();
  form.append("file", file);

  const response = await fetch(`${window.avWebStudioBuilderData.restUrl}templates/upload`, {
    method: "POST",
    headers: { "X-WP-Nonce": window.avWebStudioBuilderData.nonce },
    body: form,
  });

  const data = await response.json();
  if (!response.ok) {
    throw new Error(data?.message || data?.code || "Upload failed");
  }
  return data;
}

export function deleteTemplate(id: string): Promise<{ success: boolean; message: string }> {
  return request(`templates/${encodeURIComponent(id)}`, { method: "DELETE" });
}

export function saveAdminSettings(
  settings: Partial<import("../types").PluginAdminSettings>
): Promise<{ success: boolean; settings: import("../types").PluginAdminSettings; message: string }> {
  return request("settings", {
    method: "POST",
    body: JSON.stringify(settings),
  });
}

export function fetchSettings(): Promise<import("../types").PluginAdminSettings> {
  return fetchAdminSettings();
}

export function fetchSvgs(): Promise<{ svgs: import("../types").SvgAsset[] }> {
  return request("svg");
}

export async function uploadSvg(file: File): Promise<{ success: boolean; svg: import("../types").SvgAsset; message: string }> {
  const form = new FormData();
  form.append("file", file);

  const response = await fetch(`${window.avWebStudioBuilderData.restUrl}svg`, {
    method: "POST",
    headers: { "X-WP-Nonce": window.avWebStudioBuilderData.nonce },
    body: form,
  });

  const data = await response.json();
  if (!response.ok) {
    throw new Error(data?.message || data?.code || "Upload failed");
  }
  return data;
}

export function deleteSvg(id: number): Promise<{ success: boolean; message: string }> {
  return request(`svg/${id}`, { method: "DELETE" });
}

export function fetchTracking(): Promise<{ tracking: import("../types").TrackingSettings }> {
  return request("tracking");
}

export function saveTracking(tracking: import("../types").TrackingSettings): Promise<{
  success: boolean;
  tracking: import("../types").TrackingSettings;
  message: string;
}> {
  return request("tracking", {
    method: "POST",
    body: JSON.stringify({ tracking }),
  });
}

export function getContentEditUrl(postType: ContentPostType, id: number): string {
  const base = postType === "post" ? window.avWebStudioBuilderData.adminUrls.posts : window.avWebStudioBuilderData.adminUrls.pages;
  return `${base}&page_id=${id}`;
}

export function getContentViewUrl(
  item: Pick<PageSummary, "status" | "permalink" | "preview_url" | "full_preview_url">
): string {
  if (item.status === "publish") {
    return item.permalink;
  }
  return item.preview_url || item.full_preview_url || item.permalink;
}

export function openContentView(item: PageSummary): void {
  const url = getContentViewUrl(item).trim();
  if (!url) {
    throw new Error("No view URL available for this item.");
  }
  const tab = window.open(url, "_blank", "noopener,noreferrer");
  if (!tab) {
    throw new Error("Pop-up blocked. Allow pop-ups to view this page.");
  }
}
