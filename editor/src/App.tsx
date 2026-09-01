import { useCallback, useEffect, useRef, useState } from "react";
import {
  createPage,
  createPopup,
  deletePopup,
  fetchGlobalLayout,
  fetchPage,
  fetchPages,
  fetchPagesForPicker,
  fetchPopups,
  generateAiCode,
  publishPage,
  saveGlobalLayout,
  savePage,
  savePopup,
} from "./api/wordpress";
import { AiPanel } from "./components/AiPanel";
import { ContentListView } from "./components/ContentListView";
import { ContentTabs } from "./components/ContentTabs";
import { DashboardView } from "./components/DashboardView";
import { LayoutPanel, type LayoutCodePart } from "./components/LayoutPanel";
import { MainNav } from "./components/MainNav";
import { PopupsListView } from "./components/PopupsListView";
import { PopupsSidebar } from "./components/PopupsSidebar";
import { PostOptionsPanel } from "./components/PostOptionsPanel";
import { SeoPanel } from "./components/SeoPanel";
import { SiteLayoutSidebar } from "./components/SiteLayoutSidebar";
import { SvgLibraryView } from "./components/SvgLibraryView";
import { TrackingView } from "./components/TrackingView";
import { SettingsView } from "./components/SettingsView";
import { TemplatesView } from "./components/TemplatesView";
import { TopBar } from "./components/TopBar";
import { useEditorTheme } from "./hooks/useEditorTheme";
import type { DevicePreview } from "./devicePreview";
import type { AiChatTurn, AppView, ContentTab, PageCode, PageDetail, PageSummary, ContentPostType, EditorScope, GlobalLayout, PageLayout, EpbPopup, PageSeo, PostOptions } from "./types";
import { VisualBuilder } from "./components/VisualBuilder";
import { compileVisualDocument, mergeVisualCompile } from "./visual/compile";
import { createEmptyDocument, documentFromHtml, normalizeDocument } from "./visual/defaults";
import type { VisualDocument } from "./visual/types";
import { EMPTY_GLOBAL_LAYOUT, EMPTY_PAGE_LAYOUT, EMPTY_PAGE_SEO, EMPTY_POST_OPTIONS } from "./types";
import { clearAiChatHistory, createTurnId, loadAiChatHistory, saveAiChatHistory } from "./utils/aiHistory";
import { createEmptyCode, EMPTY_PAGE_CODE } from "./utils/defaultCode";
import { normalizeGlobalLayout, normalizePageLayout } from "./utils/editorScope";
import { mergeAiCode, openFullPagePreviewUrl } from "./utils/preview";
import { replaceAdminQuery } from "./utils/adminNav";

export default function App() {
  const { theme, setTheme, resolved } = useEditorTheme();
  const appView: AppView = window.epbBuilderData?.initialView || "dashboard";
  const [pickerPages, setPickerPages] = useState<PageSummary[]>([]);
  const [contentStats, setContentStats] = useState({ pages: 0, posts: 0, popups: 0 });
  const [pageId, setPageId] = useState<number | null>(null);
  const [pageTitle, setPageTitle] = useState("");
  const [pageStatus, setPageStatus] = useState("");
  const [postType, setPostType] = useState<ContentPostType>("page");
  const [code, setCode] = useState<PageCode>(EMPTY_PAGE_CODE);
  const [visualDoc, setVisualDoc] = useState<VisualDocument>(createEmptyDocument());
  const [headerVisualDoc, setHeaderVisualDoc] = useState<VisualDocument>(createEmptyDocument());
  const [footerVisualDoc, setFooterVisualDoc] = useState<VisualDocument>(createEmptyDocument());
  const [pageHeaderVisualDoc, setPageHeaderVisualDoc] = useState<VisualDocument>(createEmptyDocument());
  const [pageFooterVisualDoc, setPageFooterVisualDoc] = useState<VisualDocument>(createEmptyDocument());
  const [popupVisualDoc, setPopupVisualDoc] = useState<VisualDocument>(createEmptyDocument());
  const [globalLayout, setGlobalLayout] = useState<GlobalLayout>(EMPTY_GLOBAL_LAYOUT);
  const [pageLayout, setPageLayout] = useState<PageLayout>(EMPTY_PAGE_LAYOUT);
  const [pageSeo, setPageSeo] = useState<PageSeo>(EMPTY_PAGE_SEO);
  const [postOptions, setPostOptions] = useState<PostOptions>(EMPTY_POST_OPTIONS);
  const [permalink, setPermalink] = useState("");
  const [popups, setPopups] = useState<EpbPopup[]>([]);
  const [activePopupId, setActivePopupId] = useState<string | null>(null);
  const [editorScope, setEditorScope] = useState<EditorScope>("content");
  const [contentTab, setContentTab] = useState<ContentTab>("builder");
  const [layoutCodePart, setLayoutCodePart] = useState<LayoutCodePart>("page-header");
  const [devicePreview, setDevicePreview] = useState<DevicePreview>("desktop");
  const [isLoading, setIsLoading] = useState(true);
  const [isPageLoading, setIsPageLoading] = useState(false);
  const [isSaving, setIsSaving] = useState(false);
  const [isPublishing, setIsPublishing] = useState(false);
  const [isAiLoading, setIsAiLoading] = useState(false);
  const [aiChatHistory, setAiChatHistory] = useState<AiChatTurn[]>([]);
  const [activeAiTurnId, setActiveAiTurnId] = useState<string | null>(null);
  const [pendingAiPrompt, setPendingAiPrompt] = useState<string | null>(null);
  const [notice, setNotice] = useState<{ type: "success" | "error"; message: string } | null>(null);
  const saveTimer = useRef<ReturnType<typeof setTimeout> | null>(null);
  const codeRef = useRef<PageCode>(EMPTY_PAGE_CODE);
  const visualDocRef = useRef<VisualDocument>(createEmptyDocument());
  const visualSavedRef = useRef(false);
  /** HTML snapshot used to build the current visual doc. */
  const visualSourceHtmlRef = useRef<string>("");
  const headerVisualDocRef = useRef<VisualDocument>(createEmptyDocument());
  const footerVisualDocRef = useRef<VisualDocument>(createEmptyDocument());
  const headerVisualSavedRef = useRef(false);
  const footerVisualSavedRef = useRef(false);
  const headerVisualSourceHtmlRef = useRef<string>("");
  const footerVisualSourceHtmlRef = useRef<string>("");
  const pageHeaderVisualDocRef = useRef<VisualDocument>(createEmptyDocument());
  const pageFooterVisualDocRef = useRef<VisualDocument>(createEmptyDocument());
  const pageHeaderVisualSavedRef = useRef(false);
  const pageFooterVisualSavedRef = useRef(false);
  const pageHeaderVisualSourceHtmlRef = useRef<string>("");
  const pageFooterVisualSourceHtmlRef = useRef<string>("");
  const popupVisualDocRef = useRef<VisualDocument>(createEmptyDocument());
  const popupVisualSavedRef = useRef(false);
  const popupVisualSourceHtmlRef = useRef<string>("");
  const globalLayoutRef = useRef<GlobalLayout>(EMPTY_GLOBAL_LAYOUT);
  const pageLayoutRef = useRef<PageLayout>(EMPTY_PAGE_LAYOUT);
  const pageSeoRef = useRef<PageSeo>(EMPTY_PAGE_SEO);
  const postOptionsRef = useRef<PostOptions>(EMPTY_POST_OPTIONS);
  const titleRef = useRef("");
  const globalSaveTimer = useRef<ReturnType<typeof setTimeout> | null>(null);
  const popupSaveTimer = useRef<ReturnType<typeof setTimeout> | null>(null);
  const popupsRef = useRef<EpbPopup[]>([]);
  const activePopupRef = useRef<EpbPopup | null>(null);
  const pageLoadSeqRef = useRef(0);
  const pageIdRef = useRef<number | null>(null);
  /** Serialize page saves so an older in-flight draft cannot overwrite newer visual edits. */
  const pageSaveChainRef = useRef(Promise.resolve());
  const pageSaveAgainRef = useRef(false);
  const saveNowRef = useRef<
    (
      nextCode?: PageCode,
      title?: string,
      nextPageLayout?: PageLayout,
      nextSeo?: PageSeo,
      nextPostOptions?: PostOptions
    ) => Promise<void>
  >(async () => {});

  useEffect(() => {
    codeRef.current = code;
  }, [code]);

  useEffect(() => {
    visualDocRef.current = visualDoc;
  }, [visualDoc]);

  useEffect(() => {
    headerVisualDocRef.current = headerVisualDoc;
  }, [headerVisualDoc]);

  useEffect(() => {
    footerVisualDocRef.current = footerVisualDoc;
  }, [footerVisualDoc]);

  useEffect(() => {
    pageHeaderVisualDocRef.current = pageHeaderVisualDoc;
  }, [pageHeaderVisualDoc]);

  useEffect(() => {
    pageFooterVisualDocRef.current = pageFooterVisualDoc;
  }, [pageFooterVisualDoc]);

  useEffect(() => {
    popupVisualDocRef.current = popupVisualDoc;
  }, [popupVisualDoc]);

  useEffect(() => {
    globalLayoutRef.current = globalLayout;
  }, [globalLayout]);

  useEffect(() => {
    pageLayoutRef.current = pageLayout;
  }, [pageLayout]);

  useEffect(() => {
    titleRef.current = pageTitle;
  }, [pageTitle]);

  useEffect(() => {
    popupsRef.current = popups;
  }, [popups]);

  const activePopup = popups.find((p) => p.id === activePopupId) ?? null;

  useEffect(() => {
    activePopupRef.current = activePopup;
  }, [activePopup]);

  useEffect(() => {
    pageIdRef.current = pageId;
  }, [pageId]);

  const resetPageEditorState = useCallback(() => {
    const emptyCode = EMPTY_PAGE_CODE;
    const emptyLayout = normalizePageLayout();
    setCode(emptyCode);
    codeRef.current = emptyCode;
    setPageLayout(emptyLayout);
    pageLayoutRef.current = emptyLayout;
    setPageSeo({ ...EMPTY_PAGE_SEO });
    pageSeoRef.current = { ...EMPTY_PAGE_SEO };
    setPostOptions({ ...EMPTY_POST_OPTIONS });
    postOptionsRef.current = { ...EMPTY_POST_OPTIONS };
    setPermalink("");
    setAiChatHistory([]);
    setActiveAiTurnId(null);
    setPendingAiPrompt(null);
  }, []);

  const applyPageDetail = useCallback((page: PageDetail) => {
    setPageId(page.id);
    setPageTitle(page.title);
    setPageStatus(page.status);
    setPostType(page.post_type === "post" ? "post" : "page");
    const loaded = page.code ?? createEmptyCode(page.title);
    const loadedPageLayout = normalizePageLayout(page.layout);
    const loadedSeo = page.seo ? { ...EMPTY_PAGE_SEO, ...page.seo } : { ...EMPTY_PAGE_SEO };
    const loadedOptions = page.post_options ? { ...EMPTY_POST_OPTIONS, ...page.post_options } : { ...EMPTY_POST_OPTIONS };
    setCode(loaded);
    const hasVisual = !!page.visual;
    const doc = hasVisual
      ? normalizeDocument(page.visual)
      : documentFromHtml(loaded.html || "");
    setVisualDoc(doc);
    visualDocRef.current = doc;
    visualSavedRef.current = true;
    visualSourceHtmlRef.current = loaded.html;
    setPageLayout(loadedPageLayout);
    setPageSeo(loadedSeo);
    setPostOptions(loadedOptions);
    setPermalink(page.permalink ?? "");
    codeRef.current = loaded;
    pageLayoutRef.current = loadedPageLayout;
    pageSeoRef.current = loadedSeo;
    postOptionsRef.current = loadedOptions;
    setEditorScope("content");
    setContentTab("builder");
    pageHeaderVisualSavedRef.current = false;
    pageFooterVisualSavedRef.current = false;
    pageHeaderVisualSourceHtmlRef.current = "";
    pageFooterVisualSourceHtmlRef.current = "";
    const turns = loadAiChatHistory(page.id);
    setAiChatHistory(turns);
    setActiveAiTurnId(turns.length > 0 ? turns[turns.length - 1].id : null);
    setPendingAiPrompt(null);
  }, []);

  const showNotice = useCallback((type: "success" | "error", message: string) => {
    setNotice({ type, message });
    setTimeout(() => setNotice(null), 4000);
  }, []);

  const refreshPickerPages = useCallback(async () => {
    const items = await fetchPagesForPicker();
    setPickerPages(items);
    return items;
  }, []);

  const refreshEditorPopups = useCallback(async () => {
    const result = await fetchPopups({ status: "all", per_page: 100 });
    const active = result.items.filter((popup) => popup.status !== "trash");
    setPopups(active);
    popupsRef.current = active;
    return active;
  }, []);

  const refreshStats = useCallback(async () => {
    const [pagesRes, postsRes, popupsRes] = await Promise.all([
      fetchPages({ post_type: "page", per_page: 1, status: "all" }),
      fetchPages({ post_type: "post", per_page: 1, status: "all" }),
      fetchPopups({ per_page: 1, status: "all" }),
    ]);
    setContentStats({
      pages: pagesRes.total,
      posts: postsRes.total,
      popups: popupsRes.total,
    });
  }, []);

  const loadPage = useCallback(async (id: number, options?: { silent?: boolean }) => {
    if (!Number.isFinite(id) || id < 1) {
      return;
    }

    const previousId = pageIdRef.current;
    if (previousId && previousId !== id && saveTimer.current) {
      clearTimeout(saveTimer.current);
      saveTimer.current = null;
      try {
        await saveNowRef.current(
          codeRef.current,
          titleRef.current,
          pageLayoutRef.current,
          pageSeoRef.current,
          postOptionsRef.current
        );
      } catch {
        // saveNow already shows notice
      }
    }

    const loadSeq = ++pageLoadSeqRef.current;
    setIsPageLoading(true);
    resetPageEditorState();
    setPageId(id);
    setPageTitle("");
    setPageStatus("");
    setEditorScope("content");
    setContentTab("builder");

    if (!options?.silent) {
      setIsLoading(true);
    }
    try {
      const page = await fetchPage(id);
      if (loadSeq !== pageLoadSeqRef.current) {
        return;
      }

      const expectedType: ContentPostType = appView === "posts" ? "post" : "page";
      if (page.post_type !== expectedType) {
        const base =
          page.post_type === "post" ? window.epbBuilderData.adminUrls.posts : window.epbBuilderData.adminUrls.pages;
        window.location.href = `${base}&page_id=${page.id}`;
        return;
      }

      applyPageDetail(page);
    } catch (err) {
      if (loadSeq !== pageLoadSeqRef.current) {
        return;
      }
      setPageId(null);
      const url = new URL(window.location.href);
      if (url.searchParams.has("page_id")) {
        url.searchParams.delete("page_id");
        window.history.replaceState({}, "", url.toString());
      }
      const message = err instanceof Error ? err.message : "Failed to load page";
      if (message !== "Content not found.") {
        showNotice("error", message);
      }
    } finally {
      if (loadSeq === pageLoadSeqRef.current) {
        setIsPageLoading(false);
        if (!options?.silent) {
          setIsLoading(false);
        }
      }
    }
  }, [appView, applyPageDetail, resetPageEditorState, showNotice]);

  useEffect(() => {
    const init = async () => {
      try {
        const [{ layout }] = await Promise.all([
          fetchGlobalLayout(),
          refreshPickerPages(),
          refreshEditorPopups(),
          refreshStats(),
        ]);
        const normalizedGlobal = normalizeGlobalLayout(layout);
        setGlobalLayout(normalizedGlobal);
        globalLayoutRef.current = normalizedGlobal;
        const initialId = Math.max(0, Number(window.epbBuilderData?.initialPageId) || 0);
        if (initialId > 0 && (appView === "pages" || appView === "posts")) {
          await loadPage(initialId);
        } else {
          setPageId(null);
          setIsLoading(false);
        }
      } catch (err) {
        showNotice("error", err instanceof Error ? err.message : "Failed to initialize");
      } finally {
        setIsLoading(false);
      }
    };
    init();
  }, [appView, loadPage, refreshEditorPopups, refreshPickerPages, refreshStats, showNotice]);

  /** Compile the visual document to HTML/CSS/JS before save/publish. */
  const syncCodeFromVisual = useCallback(() => {
    const merged = {
      ...mergeVisualCompile(
        codeRef.current,
        compileVisualDocument(normalizeDocument(visualDocRef.current))
      ),
      js: "",
    };
    codeRef.current = merged;
    setCode(merged);
    visualSourceHtmlRef.current = merged.html;
    visualSavedRef.current = true;
    return merged;
  }, []);

  const saveNow = useCallback(
    async (
      nextCode?: PageCode,
      title?: string,
      nextPageLayout?: PageLayout,
      nextSeo?: PageSeo,
      nextPostOptions?: PostOptions
    ) => {
      if (!pageId) return;
      if (saveTimer.current) {
        clearTimeout(saveTimer.current);
        saveTimer.current = null;
      }

      if (nextCode) {
        codeRef.current = nextCode;
        setCode(nextCode);
      }
      if (title !== undefined) {
        titleRef.current = title;
        setPageTitle(title);
      }
      if (nextPageLayout) {
        pageLayoutRef.current = nextPageLayout;
        setPageLayout(nextPageLayout);
      }
      if (nextSeo) {
        pageSeoRef.current = nextSeo;
        setPageSeo(nextSeo);
      }
      if (nextPostOptions) {
        postOptionsRef.current = nextPostOptions;
        setPostOptions(nextPostOptions);
      }

      pageSaveAgainRef.current = true;

      const runSave = async () => {
        setIsSaving(true);
        try {
          do {
            pageSaveAgainRef.current = false;
            const codeToSave = syncCodeFromVisual();
            const result = await savePage(
              pageId,
              titleRef.current,
              codeToSave,
              pageLayoutRef.current,
              pageSeoRef.current,
              postOptionsRef.current,
              visualDocRef.current
            );
            if (pageSaveAgainRef.current) {
              continue;
            }
            if (result.page.post_options) {
              const saved = { ...EMPTY_POST_OPTIONS, ...result.page.post_options };
              setPostOptions(saved);
              postOptionsRef.current = saved;
            }
            if (result.page.status) {
              setPageStatus(result.page.status);
            }
            if (result.page.seo) {
              const savedSeo = { ...EMPTY_PAGE_SEO, ...result.page.seo };
              setPageSeo(savedSeo);
              pageSeoRef.current = savedSeo;
            }
            if (result.page.layout) {
              const savedLayout = normalizePageLayout(result.page.layout);
              setPageLayout(savedLayout);
              pageLayoutRef.current = savedLayout;
            }
          } while (pageSaveAgainRef.current);
        } catch (err) {
          showNotice("error", err instanceof Error ? err.message : "Save failed");
          throw err;
        } finally {
          setIsSaving(false);
        }
      };

      const previous = pageSaveChainRef.current;
      let release!: () => void;
      pageSaveChainRef.current = new Promise<void>((resolve) => {
        release = resolve;
      });
      await previous.catch(() => undefined);
      try {
        await runSave();
      } finally {
        release();
      }
    },
    [pageId, showNotice, syncCodeFromVisual]
  );

  useEffect(() => {
    saveNowRef.current = saveNow;
  }, [saveNow]);

  const syncPopupFromVisual = useCallback((popup: EpbPopup): EpbPopup => {
    const merged = {
      ...mergeVisualCompile(
        { html: popup.html, css: popup.css, js: popup.js },
        compileVisualDocument(normalizeDocument(popupVisualDocRef.current))
      ),
      js: "",
    };
    popupVisualSourceHtmlRef.current = merged.html;
    popupVisualSavedRef.current = true;
    return {
      ...popup,
      ...merged,
      edit_mode: "visual",
      visual: popupVisualDocRef.current as unknown as Record<string, unknown>,
    };
  }, []);

  const savePopupNow = useCallback(
    async (popup: EpbPopup) => {
      if (popupSaveTimer.current) {
        clearTimeout(popupSaveTimer.current);
        popupSaveTimer.current = null;
      }
      setIsSaving(true);
      try {
        const toSave =
          popup.id === activePopupRef.current?.id ? syncPopupFromVisual(popup) : popup;
        const result = await savePopup(toSave);
        setPopups((prev) => prev.map((p) => (p.id === result.popup.id ? result.popup : p)));
        if (activePopupRef.current?.id === result.popup.id) {
          activePopupRef.current = result.popup;
        }
      } catch (err) {
        showNotice("error", err instanceof Error ? err.message : "Failed to save popup");
        throw err;
      } finally {
        setIsSaving(false);
      }
    },
    [showNotice, syncPopupFromVisual]
  );

  const autoSavePopup = useCallback(
    (popup: EpbPopup) => {
      if (popupSaveTimer.current) clearTimeout(popupSaveTimer.current);
      popupSaveTimer.current = setTimeout(async () => {
        try {
          await savePopupNow(popup);
        } catch {
          // savePopupNow already shows notice
        }
      }, 1200);
    },
    [savePopupNow]
  );

  const saveGlobalNow = useCallback(
    async (nextGlobal: GlobalLayout) => {
      if (globalSaveTimer.current) {
        clearTimeout(globalSaveTimer.current);
        globalSaveTimer.current = null;
      }
      setIsSaving(true);
      try {
        const result = await saveGlobalLayout(nextGlobal);
        const saved = normalizeGlobalLayout(result.layout);
        setGlobalLayout(saved);
        globalLayoutRef.current = saved;
        return result;
      } catch (err) {
        showNotice("error", err instanceof Error ? err.message : "Failed to save site layout");
        throw err;
      } finally {
        setIsSaving(false);
      }
    },
    [showNotice]
  );

  const autoSaveGlobal = useCallback(
    (nextGlobal: GlobalLayout) => {
      if (globalSaveTimer.current) clearTimeout(globalSaveTimer.current);
      globalSaveTimer.current = setTimeout(async () => {
        try {
          await saveGlobalNow(nextGlobal);
        } catch {
          // saveGlobalNow already shows notice
        }
      }, 1200);
    },
    [saveGlobalNow]
  );

  const autoSave = useCallback(
    (
      nextCode: PageCode,
      title: string,
      nextPageLayout?: PageLayout,
      nextSeo?: PageSeo,
      nextPostOptions?: PostOptions
    ) => {
      if (!pageId) return;
      codeRef.current = nextCode;
      setCode(nextCode);
      titleRef.current = title;
      if (nextPageLayout) {
        pageLayoutRef.current = nextPageLayout;
        setPageLayout(nextPageLayout);
      }
      if (nextSeo) {
        pageSeoRef.current = nextSeo;
        setPageSeo(nextSeo);
      }
      if (nextPostOptions) {
        postOptionsRef.current = nextPostOptions;
        setPostOptions(nextPostOptions);
      }
      if (saveTimer.current) clearTimeout(saveTimer.current);
      // Read from refs when the timer fires so a slow draft never ships stale visual HTML.
      saveTimer.current = setTimeout(async () => {
        try {
          await saveNow();
        } catch {
          // saveNow already shows notice
        }
      }, 1200);
    },
    [pageId, saveNow]
  );

  const handlePopupChange = useCallback(
    (updated: EpbPopup) => {
      setPopups((prev) => prev.map((p) => (p.id === updated.id ? updated : p)));
      activePopupRef.current = updated;
      autoSavePopup(updated);
    },
    [autoSavePopup]
  );

  const handleSelectPopup = useCallback((id: string) => {
    if (activePopupId === id) return;
    const popup = popupsRef.current.find((p) => p.id === id) ?? null;
    setActivePopupId(id);
    setEditorScope("popup");

    const html = popup?.html || "";
    const hasVisual = !!popup?.visual;
    const doc = hasVisual
      ? normalizeDocument(popup!.visual)
      : documentFromHtml(html);
    setPopupVisualDoc(doc);
    popupVisualDocRef.current = doc;
    popupVisualSavedRef.current = true;
    popupVisualSourceHtmlRef.current = html;
  }, [activePopupId]);

  const handlePublishPopup = useCallback(async () => {
    const current = activePopupRef.current;
    if (!current) return;
    if (popupSaveTimer.current) {
      clearTimeout(popupSaveTimer.current);
      popupSaveTimer.current = null;
    }
    setIsPublishing(true);
    try {
      const synced = syncPopupFromVisual({
        ...current,
        status: "publish",
        enabled: true,
        edit_mode: "visual",
        visual: popupVisualDocRef.current as unknown as Record<string, unknown>,
      });
      const result = await savePopup(synced);
      setPopups((prev) => prev.map((p) => (p.id === result.popup.id ? result.popup : p)));
      activePopupRef.current = result.popup;
      showNotice("success", result.message || "Popup published.");
      await refreshStats();
    } catch (err) {
      showNotice("error", err instanceof Error ? err.message : "Publish failed");
    } finally {
      setIsPublishing(false);
    }
  }, [refreshStats, showNotice, syncPopupFromVisual]);

  const handleCreatePopup = useCallback(async () => {
    try {
      const result = await createPopup();
      const popup = result.popup;
      setPopups((prev) => [...prev, popup]);
      popupsRef.current = [...popupsRef.current, popup];
      setActivePopupId(popup.id);
      setEditorScope("popup");
      const html = popup.html || "";
      const doc = popup.visual
        ? normalizeDocument(popup.visual)
        : documentFromHtml(html);
      setPopupVisualDoc(doc);
      popupVisualDocRef.current = doc;
      popupVisualSavedRef.current = true;
      popupVisualSourceHtmlRef.current = html;
      await refreshStats();
      showNotice("success", "Popup created");
    } catch (err) {
      showNotice("error", err instanceof Error ? err.message : "Failed to create popup");
    }
  }, [refreshStats, showNotice]);

  const handleDeletePopup = useCallback(
    async (id: string) => {
      if (!confirm("Move this popup to trash?")) return;
      try {
        const result = await deletePopup(id, false);
        await refreshEditorPopups();
        await refreshStats();
        if (activePopupId === id) {
          setActivePopupId(null);
          setEditorScope("content");
        }
        showNotice("success", result.message);
      } catch (err) {
        showNotice("error", err instanceof Error ? err.message : "Failed to delete popup");
      }
    },
    [activePopupId, refreshEditorPopups, refreshStats, showNotice]
  );

  const syncSiteLayoutVisualDoc = useCallback((part: "header" | "footer") => {
    const html = (part === "footer" ? globalLayoutRef.current.footer.html : globalLayoutRef.current.header.html) || "";
    const savedRef = part === "footer" ? footerVisualSavedRef : headerVisualSavedRef;
    const sourceRef = part === "footer" ? footerVisualSourceHtmlRef : headerVisualSourceHtmlRef;
    const currentDoc = part === "footer" ? footerVisualDocRef.current : headerVisualDocRef.current;
    const shouldImport = !savedRef.current || sourceRef.current !== html;
    const doc = shouldImport ? documentFromHtml(html) : normalizeDocument(currentDoc);

    if (part === "footer") {
      setFooterVisualDoc(doc);
      footerVisualDocRef.current = doc;
      footerVisualSavedRef.current = true;
      footerVisualSourceHtmlRef.current = html;
    } else {
      setHeaderVisualDoc(doc);
      headerVisualDocRef.current = doc;
      headerVisualSavedRef.current = true;
      headerVisualSourceHtmlRef.current = html;
    }
    return doc;
  }, []);

  const syncPageLayoutVisualDoc = useCallback((part: "header" | "footer") => {
    const html = (part === "footer" ? pageLayoutRef.current.footer.html : pageLayoutRef.current.header.html) || "";
    const savedRef = part === "footer" ? pageFooterVisualSavedRef : pageHeaderVisualSavedRef;
    const sourceRef = part === "footer" ? pageFooterVisualSourceHtmlRef : pageHeaderVisualSourceHtmlRef;
    const currentDoc = part === "footer" ? pageFooterVisualDocRef.current : pageHeaderVisualDocRef.current;
    const shouldImport = !savedRef.current || sourceRef.current !== html;
    const doc = shouldImport ? documentFromHtml(html) : normalizeDocument(currentDoc);

    if (part === "footer") {
      setPageFooterVisualDoc(doc);
      pageFooterVisualDocRef.current = doc;
      pageFooterVisualSavedRef.current = true;
      pageFooterVisualSourceHtmlRef.current = html;
    } else {
      setPageHeaderVisualDoc(doc);
      pageHeaderVisualDocRef.current = doc;
      pageHeaderVisualSavedRef.current = true;
      pageHeaderVisualSourceHtmlRef.current = html;
    }
    return doc;
  }, []);

  const handleSelectScope = useCallback(
    (scope: EditorScope) => {
      setEditorScope(scope);
      if (scope !== "popup") {
        setActivePopupId(null);
      }

      if (scope === "global-header" || scope === "global-footer") {
        syncSiteLayoutVisualDoc(scope === "global-footer" ? "footer" : "header");
      }
    },
    [syncSiteLayoutVisualDoc]
  );

  const handleContentTabChange = useCallback(
    (tab: ContentTab) => {
      if (pageId && saveTimer.current) {
        if (contentTab === "seo" || contentTab === "layout") {
          clearTimeout(saveTimer.current);
          saveTimer.current = null;
          void saveNow(
            codeRef.current,
            titleRef.current,
            pageLayoutRef.current,
            pageSeoRef.current,
            postOptionsRef.current
          );
        }
      }

      setContentTab(tab);
      if (tab === "builder") {
        setEditorScope("content");
      } else if (tab === "layout") {
        setEditorScope(layoutCodePart);
        syncPageLayoutVisualDoc(layoutCodePart === "page-footer" ? "footer" : "header");
      } else if (tab === "seo") {
        setEditorScope("seo");
      } else if (tab === "settings") {
        setEditorScope("post-settings");
      }
    },
    [contentTab, layoutCodePart, pageId, saveNow, syncPageLayoutVisualDoc]
  );

  const handleLayoutCodePartChange = useCallback(
    (part: LayoutCodePart) => {
      setLayoutCodePart(part);
      setEditorScope(part);
      setContentTab("layout");
      syncPageLayoutVisualDoc(part === "page-footer" ? "footer" : "header");
    },
    [syncPageLayoutVisualDoc]
  );

  const handleBackToList = useCallback(() => {
    if (!pageId) return;
    if (saveTimer.current) {
      clearTimeout(saveTimer.current);
      saveTimer.current = null;
      void saveNowRef.current(
        codeRef.current,
        titleRef.current,
        pageLayoutRef.current,
        pageSeoRef.current,
        postOptionsRef.current
      );
    }
    pageLoadSeqRef.current += 1;
    resetPageEditorState();
    setPageTitle("");
    setPageStatus("");
    setIsPageLoading(false);
    setPageId(null);
    replaceAdminQuery({ page_id: null });
  }, [pageId, resetPageEditorState]);

  const handleOpenContent = useCallback(
    async (id: number) => {
      if (pageId === id) return;
      await loadPage(id, { silent: true });
      replaceAdminQuery({ page_id: String(id) });
    },
    [loadPage, pageId]
  );

  const handlePopupsBackToList = useCallback(() => {
    if (!activePopupId) return;
    setActivePopupId(null);
    setEditorScope("content");
  }, [activePopupId]);

  useEffect(() => {
    if (appView === "site-layout") {
      setEditorScope("global-header");
      headerVisualSavedRef.current = false;
      footerVisualSavedRef.current = false;
      headerVisualSourceHtmlRef.current = "";
      footerVisualSourceHtmlRef.current = "";
    }
  }, [appView]);

  const handlePageLayoutChange = useCallback(
    (nextLayout: PageLayout) => {
      setPageLayout(nextLayout);
      pageLayoutRef.current = nextLayout;
      if (pageId) {
        autoSave(codeRef.current, titleRef.current, nextLayout, pageSeoRef.current, postOptionsRef.current);
      }
    },
    [autoSave, pageId]
  );

  const handleSeoChange = useCallback(
    (nextSeo: PageSeo) => {
      setPageSeo(nextSeo);
      pageSeoRef.current = nextSeo;
      if (pageId) {
        autoSave(codeRef.current, titleRef.current, pageLayoutRef.current, nextSeo, postOptionsRef.current);
      }
    },
    [autoSave, pageId]
  );

  const handleSaveSeo = useCallback(async () => {
    if (!pageId) return;
    if (saveTimer.current) {
      clearTimeout(saveTimer.current);
      saveTimer.current = null;
    }
    try {
      await saveNow(
        codeRef.current,
        titleRef.current,
        pageLayoutRef.current,
        pageSeoRef.current,
        postOptionsRef.current
      );
      showNotice("success", "SEO settings saved.");
    } catch {
      // saveNow already shows notice
    }
  }, [pageId, saveNow, showNotice]);

  const handleSavePageLayout = useCallback(async () => {
    if (!pageId) return;
    if (saveTimer.current) {
      clearTimeout(saveTimer.current);
      saveTimer.current = null;
    }
    try {
      await saveNow(
        codeRef.current,
        titleRef.current,
        pageLayoutRef.current,
        pageSeoRef.current,
        postOptionsRef.current
      );
      showNotice("success", "Page layout saved.");
    } catch {
      // saveNow already shows notice
    }
  }, [pageId, saveNow, showNotice]);

  const handleSaveGlobalLayout = useCallback(async () => {
    try {
      const result = await saveGlobalNow(globalLayoutRef.current);
      showNotice("success", result.message || "Site layout saved.");
    } catch {
      // saveGlobalNow already shows notice
    }
  }, [saveGlobalNow, showNotice]);

  const handlePostOptionsChange = useCallback(
    (nextOptions: PostOptions) => {
      setPostOptions(nextOptions);
      postOptionsRef.current = nextOptions;
      setPageStatus(nextOptions.status);
      if (pageId) {
        autoSave(codeRef.current, titleRef.current, pageLayoutRef.current, pageSeoRef.current, nextOptions);
      }
    },
    [autoSave, pageId]
  );

  const handleGlobalLayoutChange = useCallback(
    (nextLayout: GlobalLayout) => {
      setGlobalLayout(nextLayout);
      globalLayoutRef.current = nextLayout;
      autoSaveGlobal(nextLayout);
    },
    [autoSaveGlobal]
  );

  const handlePublish = async () => {
    if (!pageId) return;
    if (saveTimer.current) {
      clearTimeout(saveTimer.current);
      saveTimer.current = null;
    }
    setIsPublishing(true);
    try {
      // Serialize with draft saves so an older autosave cannot overwrite this publish.
      const previous = pageSaveChainRef.current;
      let release!: () => void;
      pageSaveChainRef.current = new Promise<void>((resolve) => {
        release = resolve;
      });
      await previous.catch(() => undefined);

      try {
        const latestCode = syncCodeFromVisual();
        const latestTitle = titleRef.current;
        const publishOptions = { ...postOptionsRef.current, status: "publish" };
        const result = await publishPage(
          pageId,
          latestTitle,
          latestCode,
          pageLayoutRef.current,
          pageSeoRef.current,
          publishOptions,
          visualDocRef.current
        );
        setPageStatus(result.page.status);
        if (result.page.post_options) {
          const saved = { ...EMPTY_POST_OPTIONS, ...result.page.post_options, status: "publish" };
          setPostOptions(saved);
          postOptionsRef.current = saved;
        } else {
          setPostOptions(publishOptions);
          postOptionsRef.current = publishOptions;
        }
        if (result.page.code) {
          setCode(result.page.code);
          codeRef.current = result.page.code;
        }
        if (result.page.seo) {
          const nextSeo = { ...EMPTY_PAGE_SEO, ...result.page.seo };
          setPageSeo(nextSeo);
          pageSeoRef.current = nextSeo;
        }
        showNotice("success", result.message);
        await refreshStats();
      } finally {
        release();
      }
    } catch (err) {
      showNotice("error", err instanceof Error ? err.message : "Publish failed");
    } finally {
      setIsPublishing(false);
    }
  };

  const handleCreateContent = async (type: ContentPostType, title: string) => {
    const label = type === "post" ? "post" : "page";
    const trimmed = title.trim();
    if (!trimmed) {
      showNotice("error", `${label === "post" ? "Post" : "Page"} title is required.`);
      throw new Error("Title is required");
    }
    try {
      const item = await createPage(trimmed, type);
      await refreshStats();
      const targetView = type === "post" ? "posts" : "pages";
      if (appView === targetView) {
        const loadSeq = ++pageLoadSeqRef.current;
        setIsPageLoading(true);
        resetPageEditorState();
        setPageTitle("");
        setPageStatus("");
        setEditorScope("content");
        setContentTab("builder");
        applyPageDetail(item);
        if (loadSeq === pageLoadSeqRef.current) {
          setIsPageLoading(false);
        }
        replaceAdminQuery({ page_id: String(item.id) });
        return;
      }
      const editBase = type === "post" ? window.epbBuilderData.adminUrls.posts : window.epbBuilderData.adminUrls.pages;
      window.location.href = `${editBase}&page_id=${item.id}`;
    } catch (err) {
      showNotice("error", err instanceof Error ? err.message : `Failed to create ${label}`);
      throw err;
    }
  };

  const handleCreatePage = (title: string) => handleCreateContent("page", title);
  const handleCreatePost = (title: string) => handleCreateContent("post", title);

  const handleOpenPopup = handleSelectPopup;

  const handleAiGenerate = async (prompt: string) => {
    if (!window.epbBuilderData?.aiReady) {
      showNotice("error", "Enable AI in WPVisualX → Settings.");
      return;
    }
    if (!pageId) {
      showNotice("error", "Please select a page or post to edit.");
      return;
    }
    setIsAiLoading(true);
    setPendingAiPrompt(prompt);
    try {
      const codeBefore = { ...codeRef.current };
      const result = await generateAiCode(prompt, codeRef.current, titleRef.current);
      if (result.action === "none") {
        showNotice("error", result.message || "Could not apply that change.");
        return;
      }
      if (result.code) {
        const nextCode = result.action === "replace"
          ? result.code
          : mergeAiCode(codeRef.current, result.code);

        const turn: AiChatTurn = {
          id: createTurnId(),
          prompt,
          explanation: result.message || "Page updated.",
          action: result.action,
          codeBefore,
          codeAfter: { ...nextCode },
          createdAt: Date.now(),
        };

        setAiChatHistory((prev) => {
          const next = [...prev, turn];
          saveAiChatHistory(pageId, next);
          return next;
        });
        setActiveAiTurnId(turn.id);

        const doc = documentFromHtml(nextCode.html || "");
        setVisualDoc(doc);
        visualDocRef.current = doc;
        visualSavedRef.current = true;
        visualSourceHtmlRef.current = nextCode.html;
        setCode(nextCode);
        codeRef.current = nextCode;
        await saveNow(nextCode, titleRef.current);
        showNotice("success", turn.explanation);
      }
    } catch (err) {
      showNotice("error", err instanceof Error ? err.message : "AI generation failed");
    } finally {
      setIsAiLoading(false);
      setPendingAiPrompt(null);
    }
  };

  const handleAiRestore = async (turnId: string) => {
    const turn = aiChatHistory.find((t) => t.id === turnId);
    if (!turn) return;

    const after = turn.codeAfter;
    const afterDoc = documentFromHtml(after.html || "");
    setVisualDoc(afterDoc);
    visualDocRef.current = afterDoc;
    visualSavedRef.current = true;
    visualSourceHtmlRef.current = after.html;
    setCode(after);
    codeRef.current = after;
    setActiveAiTurnId(turnId);
    try {
      await saveNow(turn.codeAfter, titleRef.current);
      showNotice("success", turn.explanation || "Restored previous AI version.");
    } catch {
      // saveNow already shows notice
    }
  };

  const handleAiRestoreBefore = async (turnId: string) => {
    const turn = aiChatHistory.find((t) => t.id === turnId);
    if (!turn) return;

    const idx = aiChatHistory.findIndex((t) => t.id === turnId);
    const prevTurnId = idx > 0 ? aiChatHistory[idx - 1].id : null;

    const before = turn.codeBefore;
    const beforeDoc = documentFromHtml(before.html || "");
    setVisualDoc(beforeDoc);
    visualDocRef.current = beforeDoc;
    visualSavedRef.current = true;
    visualSourceHtmlRef.current = before.html;
    setCode(before);
    codeRef.current = before;
    setActiveAiTurnId(prevTurnId);
    try {
      await saveNow(turn.codeBefore, titleRef.current);
      showNotice("success", "Restored to state before this change.");
    } catch {
      // saveNow already shows notice
    }
  };

  const handleClearAiHistory = () => {
    setAiChatHistory([]);
    setActiveAiTurnId(null);
    if (pageId) {
      clearAiChatHistory(pageId);
    }
    showNotice("success", "AI chat history cleared.");
  };

  const handleFullPagePreview = async () => {
    if (!pageId) return;
    try {
      if (saveTimer.current) {
        clearTimeout(saveTimer.current);
        saveTimer.current = null;
      }
      const previous = pageSaveChainRef.current;
      let release!: () => void;
      pageSaveChainRef.current = new Promise<void>((resolve) => {
        release = resolve;
      });
      await previous.catch(() => undefined);
      setIsSaving(true);
      try {
        const result = await savePage(
          pageId,
          titleRef.current,
          syncCodeFromVisual(),
          pageLayoutRef.current,
          pageSeoRef.current,
          postOptionsRef.current,
          visualDocRef.current
        );
        openFullPagePreviewUrl(result.full_preview_url);
      } finally {
        setIsSaving(false);
        release();
      }
    } catch (err) {
      showNotice("error", err instanceof Error ? err.message : "Could not open preview");
    }
  };

  const listPostType: ContentPostType = appView === "posts" ? "post" : "page";
  const editingContent = (appView === "pages" || appView === "posts") && !!pageId;
  const editingPopup = appView === "popups" && !!activePopupId;
  const editorFocusMode = editingContent || editingPopup || appView === "site-layout";

  const handleBackFromSiteLayout = useCallback(() => {
    window.location.href = window.epbBuilderData.adminUrls.dashboard;
  }, []);

  const topBarVariant = editingContent ? "content-edit" : appView === "site-layout" || appView === "popups" ? "workspace" : "minimal";
  const workspaceTitle =
    appView === "site-layout"
      ? "Site Layout"
      : editingPopup && activePopup
        ? activePopup.name || "Popup"
        : appView === "popups"
          ? "Popups"
          : appView === "settings"
            ? "Settings"
            : undefined;

  if (isLoading && (appView === "pages" || appView === "posts") && Number(window.epbBuilderData?.initialPageId) > 0) {
    return (
      <div className="epb-loading epb-fade-in" data-theme={resolved}>
        <div className="epb-loading__spinner-wrap">
          <div className="epb-spinner" />
        </div>
        <p className="epb-loading__text">Loading WPVisualX...</p>
      </div>
    );
  }

  return (
    <div
      className={`epb-editor epb-app-layout epb-fade-in${editorFocusMode ? " epb-app-layout--focus" : ""}`}
      data-theme={resolved}
    >
      {!editorFocusMode && (
        <MainNav
          activeView={appView}
          editingContent={editingContent}
          editingPopup={editingPopup}
          onBackToList={editingContent ? handleBackToList : undefined}
          onPopupsBackToList={appView === "popups" ? handlePopupsBackToList : undefined}
        />
      )}

      <div className="epb-editor__main">
        <TopBar
          variant={topBarVariant}
          pageTitle={pageTitle}
          pageStatus={pageStatus}
          postType={postType}
          isSaving={isSaving}
          isPublishing={isPublishing}
          theme={theme}
          workspaceTitle={workspaceTitle}
          devicePreview={devicePreview}
          onThemeChange={setTheme}
          onBack={
            editingContent
              ? handleBackToList
              : editingPopup
                ? handlePopupsBackToList
                : appView === "site-layout"
                  ? handleBackFromSiteLayout
                  : undefined
          }
          onTitleChange={(title) => {
            setPageTitle(title);
            titleRef.current = title;
            autoSave(codeRef.current, title);
          }}
          onFullPagePreview={handleFullPagePreview}
          onPublish={
            editingContent ? handlePublish : editingPopup ? handlePublishPopup : undefined
          }
          onDevicePreviewChange={
            editingContent || editingPopup || appView === "site-layout" ? setDevicePreview : undefined
          }
        />

        {notice && (
          <div key={notice.message} className={`epb-notice epb-notice--${notice.type}`}>
            {notice.message}
          </div>
        )}

        {appView === "dashboard" && (
          <DashboardView
            activeView={appView}
            pageCount={contentStats.pages}
            postCount={contentStats.posts}
            popupCount={contentStats.popups}
          />
        )}

        {(appView === "pages" || appView === "posts") && !pageId && (
          <ContentListView
            postType={listPostType}
            onCreate={listPostType === "post" ? handleCreatePost : handleCreatePage}
            onOpen={handleOpenContent}
            onNotice={showNotice}
          />
        )}

        {editingContent && (
          <>
            <ContentTabs
              active={contentTab}
              settingsLabel={postType === "post" ? "Post Options" : "Page Options"}
              onChange={handleContentTabChange}
            />
            <div key={pageId} className={`epb-workspace epb-slide-up${isPageLoading ? " epb-workspace--loading" : ""}`}>
              {isPageLoading && (
                <div className="epb-workspace__loading" aria-live="polite">
                  <div className="epb-spinner epb-spinner--sm" />
                  <span>Loading page…</span>
                </div>
              )}

              {contentTab === "layout" ? (
                <>
                  <LayoutPanel
                    pageLayout={pageLayout}
                    globalLayout={globalLayout}
                    layoutCodePart={layoutCodePart}
                    isSaving={isSaving}
                    onLayoutChange={handlePageLayoutChange}
                    onLayoutCodePartChange={handleLayoutCodePartChange}
                    onSave={handleSavePageLayout}
                  />
                  <VisualBuilder
                    document={layoutCodePart === "page-footer" ? pageFooterVisualDoc : pageHeaderVisualDoc}
                    devicePreview={devicePreview}
                    onDevicePreviewChange={setDevicePreview}
                    onChange={(doc, compiled) => {
                      const isFooter = layoutCodePart === "page-footer";
                      if (isFooter) {
                        setPageFooterVisualDoc(doc);
                        pageFooterVisualDocRef.current = doc;
                        pageFooterVisualSavedRef.current = true;
                      } else {
                        setPageHeaderVisualDoc(doc);
                        pageHeaderVisualDocRef.current = doc;
                        pageHeaderVisualSavedRef.current = true;
                      }
                      const existing = isFooter
                        ? pageLayoutRef.current.footer
                        : pageLayoutRef.current.header;
                      const merged = {
                        ...mergeVisualCompile(existing, compiled),
                        js: "",
                        visual: doc as unknown as Record<string, unknown>,
                      };
                      if (isFooter) {
                        pageFooterVisualSourceHtmlRef.current = merged.html;
                      } else {
                        pageHeaderVisualSourceHtmlRef.current = merged.html;
                      }
                      const nextLayout: PageLayout = isFooter
                        ? { ...pageLayoutRef.current, footer: merged }
                        : { ...pageLayoutRef.current, header: merged };
                      setPageLayout(nextLayout);
                      pageLayoutRef.current = nextLayout;
                      autoSave(codeRef.current, titleRef.current, nextLayout);
                    }}
                  />
                </>
              ) : contentTab === "seo" ? (
                <SeoPanel
                  seo={pageSeo}
                  pageTitle={pageTitle}
                  permalink={permalink}
                  isSaving={isSaving}
                  onChange={handleSeoChange}
                  onSave={handleSaveSeo}
                />
              ) : contentTab === "settings" && pageId ? (
                <PostOptionsPanel
                  postType={postType}
                  options={postOptions}
                  pageId={pageId}
                  pageTitle={pageTitle}
                  onTitleChange={(title) => {
                    setPageTitle(title);
                    titleRef.current = title;
                    autoSave(codeRef.current, title);
                  }}
                  onChange={handlePostOptionsChange}
                />
              ) : (
                <VisualBuilder
                  document={visualDoc}
                  pageId={pageId ?? 0}
                  devicePreview={devicePreview}
                  onDevicePreviewChange={setDevicePreview}
                  onChange={(doc, compiled) => {
                    setVisualDoc(doc);
                    visualDocRef.current = doc;
                    visualSavedRef.current = true;
                    const merged = { ...mergeVisualCompile(codeRef.current, compiled), js: "" };
                    visualSourceHtmlRef.current = merged.html;
                    setCode(merged);
                    codeRef.current = merged;
                    autoSave(merged, titleRef.current);
                  }}
                />
              )}
            </div>
            <AiPanel
              enabled={!!window.epbBuilderData?.canUseAi && contentTab === "builder"}
              aiReady={!!window.epbBuilderData?.aiReady}
              settingsUrl={window.epbBuilderData?.adminUrls?.settings}
              defaultOpen={!!window.epbBuilderData?.pluginSettings?.ai_panel_default_open}
              theme={resolved}
              history={aiChatHistory}
              activeTurnId={activeAiTurnId}
              pendingPrompt={pendingAiPrompt}
              onGenerate={handleAiGenerate}
              onRestore={handleAiRestore}
              onRestoreBefore={handleAiRestoreBefore}
              onClearHistory={handleClearAiHistory}
              isLoading={isAiLoading}
            />
          </>
        )}

        {appView === "site-layout" && (
          <div className="epb-workspace epb-slide-up">
            <SiteLayoutSidebar
              globalLayout={globalLayout}
              activeScope={
                editorScope === "global-footer" ? "global-footer" : "global-header"
              }
              isSaving={isSaving}
              onSelectScope={handleSelectScope}
              onGlobalLayoutChange={handleGlobalLayoutChange}
              onSave={handleSaveGlobalLayout}
            />
            <VisualBuilder
              document={editorScope === "global-footer" ? footerVisualDoc : headerVisualDoc}
              devicePreview={devicePreview}
              onDevicePreviewChange={setDevicePreview}
              onChange={(doc, compiled) => {
                const isFooter = editorScope === "global-footer";
                if (isFooter) {
                  setFooterVisualDoc(doc);
                  footerVisualDocRef.current = doc;
                  footerVisualSavedRef.current = true;
                } else {
                  setHeaderVisualDoc(doc);
                  headerVisualDocRef.current = doc;
                  headerVisualSavedRef.current = true;
                }
                const existing = isFooter
                  ? globalLayoutRef.current.footer
                  : globalLayoutRef.current.header;
                const merged = {
                  ...mergeVisualCompile(existing, compiled),
                  js: "",
                  visual: doc as unknown as Record<string, unknown>,
                };
                if (isFooter) {
                  footerVisualSourceHtmlRef.current = merged.html;
                } else {
                  headerVisualSourceHtmlRef.current = merged.html;
                }
                const nextLayout: GlobalLayout = isFooter
                  ? { ...globalLayoutRef.current, footer: merged }
                  : { ...globalLayoutRef.current, header: merged };
                setGlobalLayout(nextLayout);
                globalLayoutRef.current = nextLayout;
                autoSaveGlobal(nextLayout);
              }}
            />
          </div>
        )}

        {appView === "popups" && !activePopupId && (
          <PopupsListView
            onCreate={handleCreatePopup}
            onOpen={handleOpenPopup}
            onNotice={showNotice}
          />
        )}

        {appView === "popups" && activePopupId && (
          <div className="epb-workspace epb-slide-up">
            <PopupsSidebar
              popups={popups}
              activePopupId={activePopupId}
              pages={pickerPages}
              onSelectPopup={handleSelectPopup}
              onCreatePopup={handleCreatePopup}
              onDeletePopup={handleDeletePopup}
              onPopupChange={handlePopupChange}
            />
            {activePopup ? (
              <VisualBuilder
                document={popupVisualDoc}
                devicePreview={devicePreview}
                onDevicePreviewChange={setDevicePreview}
                onChange={(doc, compiled) => {
                  setPopupVisualDoc(doc);
                  popupVisualDocRef.current = doc;
                  popupVisualSavedRef.current = true;
                  const existing = {
                    html: activePopup.html,
                    css: activePopup.css,
                    js: activePopup.js,
                  };
                  const merged = {
                    ...mergeVisualCompile(existing, compiled),
                    js: "",
                  };
                  popupVisualSourceHtmlRef.current = merged.html;
                  const nextPopup: EpbPopup = {
                    ...activePopup,
                    ...merged,
                    edit_mode: "visual",
                    visual: doc as unknown as Record<string, unknown>,
                  };
                  setPopups((prev) => prev.map((p) => (p.id === nextPopup.id ? nextPopup : p)));
                  activePopupRef.current = nextPopup;
                  autoSavePopup(nextPopup);
                }}
              />
            ) : null}
          </div>
        )}

        {appView === "svg" && <SvgLibraryView onNotice={showNotice} />}
        {appView === "templates" && <TemplatesView onNotice={showNotice} />}
        {appView === "tracking" && <TrackingView onNotice={showNotice} />}
        {appView === "settings" && <SettingsView onNotice={showNotice} />}
      </div>
    </div>
  );
}
