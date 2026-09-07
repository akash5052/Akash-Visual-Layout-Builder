import {
  useCallback,
  useEffect,
  useId,
  useLayoutEffect,
  useRef,
  useState,
  type CSSProperties,
  type KeyboardEvent,
} from "react";
import { createPortal } from "react-dom";
import { fetchUrlSuggestions, getContentViewUrl } from "../api/wordpress";
import type { PageSummary } from "../types";

interface UrlSuggestFieldProps {
  value: string;
  onChange: (url: string) => void;
  placeholder?: string;
  id?: string;
}

function looksLikeRawUrl(value: string): boolean {
  const v = value.trim().toLowerCase();
  return (
    v.startsWith("http://") ||
    v.startsWith("https://") ||
    v.startsWith("mailto:") ||
    v.startsWith("tel:") ||
    v.startsWith("#") ||
    (v.startsWith("/") && v.length > 1 && !/\s/.test(v))
  );
}

function suggestionUrl(item: PageSummary): string {
  return getContentViewUrl(item) || item.permalink || "";
}

let recentCache: PageSummary[] | null = null;
let recentCacheAt = 0;
const RECENT_TTL_MS = 60_000;

async function loadRecent(): Promise<PageSummary[]> {
  if (recentCache && Date.now() - recentCacheAt < RECENT_TTL_MS) {
    return recentCache;
  }
  const items = await fetchUrlSuggestions("", 10);
  recentCache = items;
  recentCacheAt = Date.now();
  return items;
}

export function UrlSuggestField({
  value,
  onChange,
  placeholder = "Search pages or paste a URL",
  id,
}: UrlSuggestFieldProps) {
  const listId = useId();
  const rootRef = useRef<HTMLDivElement>(null);
  const inputRef = useRef<HTMLInputElement>(null);
  const panelRef = useRef<HTMLDivElement>(null);
  const blurTimer = useRef<ReturnType<typeof setTimeout> | null>(null);
  const [open, setOpen] = useState(false);
  const [loading, setLoading] = useState(false);
  const [items, setItems] = useState<PageSummary[]>([]);
  const [activeIndex, setActiveIndex] = useState(0);
  const [mode, setMode] = useState<"recent" | "search">("recent");
  const [panelStyle, setPanelStyle] = useState<CSSProperties>({});

  const close = useCallback(() => {
    setOpen(false);
    setActiveIndex(0);
  }, []);

  const updatePanelPosition = useCallback(() => {
    const input = inputRef.current;
    if (!input) return;
    const rect = input.getBoundingClientRect();
    const width = Math.max(rect.width, 220);
    const left = Math.min(rect.left, window.innerWidth - width - 8);
    const spaceBelow = window.innerHeight - rect.bottom;
    const maxHeight = Math.min(240, Math.max(120, spaceBelow - 12));
    const placeAbove = spaceBelow < 160 && rect.top > spaceBelow;
    setPanelStyle({
      position: "fixed",
      left: Math.max(8, left),
      width,
      maxHeight,
      zIndex: 10000,
      ...(placeAbove
        ? { bottom: window.innerHeight - rect.top + 4, top: "auto" }
        : { top: rect.bottom + 4, bottom: "auto" }),
    });
  }, []);

  const loadSuggestions = useCallback(async (query: string) => {
    const trimmed = query.trim();
    const searching = trimmed.length > 0 && !looksLikeRawUrl(trimmed);
    setLoading(true);
    try {
      const next = searching ? await fetchUrlSuggestions(trimmed, 10) : await loadRecent();
      setItems(next);
      setMode(searching ? "search" : "recent");
      setActiveIndex(0);
    } catch {
      setItems([]);
    } finally {
      setLoading(false);
    }
  }, []);

  useLayoutEffect(() => {
    if (!open) return;
    updatePanelPosition();
  }, [open, items, loading, updatePanelPosition]);

  useEffect(() => {
    if (!open) return;
    const handle = window.setTimeout(() => {
      void loadSuggestions(value);
    }, value.trim() && !looksLikeRawUrl(value) ? 220 : 0);
    return () => window.clearTimeout(handle);
  }, [open, value, loadSuggestions]);

  useEffect(() => {
    if (!open) return;
    const onReposition = () => updatePanelPosition();
    window.addEventListener("resize", onReposition);
    window.addEventListener("scroll", onReposition, true);
    return () => {
      window.removeEventListener("resize", onReposition);
      window.removeEventListener("scroll", onReposition, true);
    };
  }, [open, updatePanelPosition]);

  useEffect(() => {
    const onPointerDown = (event: MouseEvent) => {
      const target = event.target as Node;
      if (rootRef.current?.contains(target) || panelRef.current?.contains(target)) return;
      close();
    };
    document.addEventListener("mousedown", onPointerDown);
    return () => document.removeEventListener("mousedown", onPointerDown);
  }, [close]);

  const pick = (item: PageSummary) => {
    const url = suggestionUrl(item);
    if (!url) return;
    onChange(url);
    close();
  };

  const onFocus = () => {
    if (blurTimer.current) {
      clearTimeout(blurTimer.current);
      blurTimer.current = null;
    }
    setOpen(true);
  };

  const onBlur = () => {
    blurTimer.current = setTimeout(() => close(), 140);
  };

  const onKeyDown = (event: KeyboardEvent<HTMLInputElement>) => {
    if (!open && (event.key === "ArrowDown" || event.key === "ArrowUp")) {
      setOpen(true);
      return;
    }
    if (!open) return;

    if (event.key === "Escape") {
      event.preventDefault();
      close();
      return;
    }
    if (event.key === "ArrowDown") {
      event.preventDefault();
      setActiveIndex((i) => Math.min(i + 1, Math.max(0, items.length - 1)));
      return;
    }
    if (event.key === "ArrowUp") {
      event.preventDefault();
      setActiveIndex((i) => Math.max(i - 1, 0));
      return;
    }
    if (event.key === "Enter" && items[activeIndex]) {
      event.preventDefault();
      pick(items[activeIndex]);
    }
  };

  const panel = open
    ? createPortal(
        <div className="av-web-studio-url-suggest__panel" id={listId} role="listbox" ref={panelRef} style={panelStyle}>
          <div className="av-web-studio-url-suggest__heading">
            {loading ? "Loading…" : mode === "search" ? "Matching content" : "Recently updated"}
          </div>
          {!loading && items.length === 0 ? (
            <div className="av-web-studio-url-suggest__empty">
              {mode === "search" ? "No pages or posts match that name." : "No published pages or posts yet."}
            </div>
          ) : (
            items.map((item, index) => {
              const url = suggestionUrl(item);
              return (
                <button
                  key={item.id}
                  type="button"
                  role="option"
                  aria-selected={index === activeIndex}
                  className={`av-web-studio-url-suggest__item ${index === activeIndex ? "is-active" : ""}`}
                  onMouseDown={(e) => e.preventDefault()}
                  onClick={() => pick(item)}
                  onMouseEnter={() => setActiveIndex(index)}
                >
                  <span className="av-web-studio-url-suggest__title">{item.title || "(Untitled)"}</span>
                  <span className="av-web-studio-url-suggest__meta">
                    <span className={`av-web-studio-url-suggest__type av-web-studio-url-suggest__type--${item.post_type}`}>
                      {item.post_type === "post" ? "Post" : "Page"}
                    </span>
                    <span className="av-web-studio-url-suggest__path">{url.replace(/^https?:\/\/[^/]+/i, "") || url}</span>
                  </span>
                </button>
              );
            })
          )}
        </div>,
        document.body
      )
    : null;

  return (
    <div className={`av-web-studio-url-suggest ${open ? "is-open" : ""}`} ref={rootRef}>
      <input
        ref={inputRef}
        id={id}
        type="text"
        inputMode="url"
        autoComplete="off"
        role="combobox"
        aria-expanded={open}
        aria-controls={listId}
        aria-autocomplete="list"
        value={value}
        placeholder={placeholder}
        onChange={(e) => {
          onChange(e.target.value);
          setOpen(true);
        }}
        onFocus={onFocus}
        onBlur={onBlur}
        onKeyDown={onKeyDown}
        onClick={() => setOpen(true)}
      />
      {panel}
    </div>
  );
}
