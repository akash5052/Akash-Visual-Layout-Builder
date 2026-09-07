import type { VisualWidget } from "./types";
import { mergeWidgetStyle } from "./types";
import { buildPostsWidgetPayload, escAttr, postsWidgetLayout } from "./postsWidget";

function esc(value: string): string {
  return value
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;");
}

function stripTags(value: string): string {
  return value.replace(/<[^>]+>/g, " ").replace(/\s+/g, " ").trim();
}

/** Widgets that already manage their own primary links / interactive controls. */
const SKIP_WIDGET_LINK_WRAP = new Set([
  "button",
  "dual-button",
  "social-icons",
  "share-buttons",
  "search",
  "video",
  "tabs",
  "accordion",
  "toggle",
  "html",
  "shortcode",
  "slides",
  "price-table",
  "call-to-action",
  "posts-grid",
  "posts-list",
  "posts-carousel",
]);

function wrapWidgetLink(widget: VisualWidget, inner: string): string {
  const url = (widget.linkUrl || "").trim();
  if (!url || SKIP_WIDGET_LINK_WRAP.has(widget.type)) return inner;
  const target = widget.linkNewTab ? ' target="_blank" rel="noopener noreferrer"' : "";
  return `<a class="av-web-studio-w-link" href="${esc(url)}"${target} style="display:block;color:inherit;text-decoration:none;">${inner}</a>`;
}

function itemLink(url: string | undefined, inner: string, newTab = false): string {
  const href = (url || "").trim();
  if (!href) return inner;
  const target = newTab ? ' target="_blank" rel="noopener noreferrer"' : "";
  return `<a href="${esc(href)}"${target} style="color:inherit;text-decoration:none;">${inner}</a>`;
}

function styleCss(
  style: VisualWidget["style"] | undefined,
  extra: Record<string, string | number | undefined> = {}
): string {
  const s = mergeWidgetStyle(style);
  const parts: string[] = [];
  const push = (key: string, value?: string | number) => {
    if (value === undefined || value === null || value === "") return;
    parts.push(`${key}:${value}`);
  };

  push("margin-top", s.marginTop);
  push("margin-right", s.marginRight);
  push("margin-bottom", s.marginBottom);
  push("margin-left", s.marginLeft);
  push("padding-top", s.paddingTop);
  push("padding-right", s.paddingRight);
  push("padding-bottom", s.paddingBottom);
  push("padding-left", s.paddingLeft);
  push("font-family", s.fontFamily);
  push("font-size", s.fontSize);
  push("font-weight", s.fontWeight);
  push("line-height", s.lineHeight);
  push("letter-spacing", s.letterSpacing);
  push("text-transform", s.textTransform);
  push("border-radius", s.borderRadius);
  if (s.borderStyle !== "none" && s.borderWidth && s.borderWidth !== "0px") {
    push("border", `${s.borderWidth} ${s.borderStyle} ${s.borderColor}`);
  }
  push("box-shadow", s.boxShadow === "none" ? undefined : s.boxShadow);
  push("opacity", s.opacity !== 1 ? s.opacity : undefined);
  push("background", s.background !== "transparent" ? s.background : undefined);
  push("max-width", s.maxWidth !== "100%" ? s.maxWidth : undefined);
  if (s.zIndex !== undefined && s.zIndex !== null && String(s.zIndex).trim() !== "") {
    push("z-index", s.zIndex);
    push("position", "relative");
  }
  Object.entries(extra).forEach(([key, value]) => push(key, value));
  return parts.join(";");
}

function videoEmbedUrl(url: string, source: string, autoplay: boolean): string {
  const auto = autoplay ? "1" : "0";
  if (source === "vimeo" || /vimeo\.com/.test(url)) {
    const id = url.match(/vimeo\.com\/(\d+)/)?.[1] || url;
    return `https://player.vimeo.com/video/${id}?autoplay=${auto}`;
  }
  if (source === "youtube" || /youtu/.test(url)) {
    const id =
      url.match(/(?:v=|youtu\.be\/|embed\/)([a-zA-Z0-9_-]{6,})/)?.[1] || url;
    return `https://www.youtube.com/embed/${id}?autoplay=${auto}&rel=0`;
  }
  return url;
}

function starsHtml(rating: number, max: number, color: string): string {
  const full = Math.floor(rating);
  const half = rating - full >= 0.5;
  const parts: string[] = [];
  for (let i = 1; i <= max; i++) {
    const fill = i <= full || (i === full + 1 && half) ? color : "#cbd5e1";
    parts.push(`<span style="color:${fill}">★</span>`);
  }
  return parts.join("");
}

export function compileWidgetHtml(widget: VisualWidget): string {
  const inner = injectDataId(compileWidgetInner(widget), widget.id);
  return wrapWidgetLink(widget, inner);
}

function injectDataId(html: string, id: string): string {
  if (!id) return html;
  return html.replace(/^(<\w+)/, `$1 data-av-web-studio-id="${esc(id)}"`);
}

function compileWidgetInner(widget: VisualWidget): string {
  switch (widget.type) {
    case "heading": {
      const tag = widget.tag || "h2";
      const css = styleCss(widget.style, {
        "text-align": widget.align,
        color: widget.color,
        display: "block",
        "white-space": "pre-line",
      });
      const content = esc(String(widget.content || "").replace(/<br\s*\/?>/gi, "\n").replace(/\\n/g, "\n"));
      return `<${tag} class="av-web-studio-w av-web-studio-w-heading" style="${css}">${content}</${tag}>`;
    }
    case "text": {
      const css = styleCss(widget.style, {
        "text-align": widget.align,
        color: widget.color,
        display: "block",
        "white-space": "pre-line",
      });
      const content = esc(String(widget.content || "").replace(/<br\s*\/?>/gi, "\n").replace(/\\n/g, "\n"));
      return `<p class="av-web-studio-w av-web-studio-w-text" style="${css}">${content}</p>`;
    }
    case "button": {
      const wrap = styleCss(
        {
          ...widget.style,
          paddingTop: "0px",
          paddingRight: "0px",
          paddingBottom: "0px",
          paddingLeft: "0px",
          background: "transparent",
          borderStyle: "none",
          boxShadow: "none",
        },
        { "text-align": widget.align }
      );
      const btn = [
        "display:inline-flex",
        "align-items:center",
        "justify-content:center",
        `padding:${widget.paddingY || "12px"} ${widget.paddingX || "24px"}`,
        `border-radius:${widget.style?.borderRadius || "0px"}`,
        `background:${esc(widget.background)}`,
        `color:${esc(widget.textColor)}`,
        "text-decoration:none",
        `font-weight:${widget.style?.fontWeight || "700"}`,
        `font-size:${widget.style?.fontSize || "15px"}`,
        widget.fullWidth ? "width:100%" : "",
      ]
        .filter(Boolean)
        .join(";");
      return `<div class="av-web-studio-w av-web-studio-w-button" style="${wrap}"><a href="${esc(widget.url || "#")}" style="${btn}">${esc(widget.label)}</a></div>`;
    }
    case "image": {
      const wrap = styleCss(widget.style, { "text-align": widget.align });
      const img = [
        "display:inline-block",
        `width:${esc(widget.width || "100%")}`,
        "max-width:100%",
        "height:auto",
        `border-radius:${esc(widget.borderRadius || widget.style?.borderRadius || "0")}`,
        `object-fit:${widget.objectFit || "cover"}`,
      ].join(";");
      return `<figure class="av-web-studio-w av-web-studio-w-image" style="${wrap}"><img src="${esc(widget.src)}" alt="${esc(widget.alt)}" style="${img}" loading="lazy" /></figure>`;
    }
    case "spacer": {
      const h = typeof widget.height === "number" ? `${widget.height || 24}px` : widget.height || "24px";
      return `<div class="av-web-studio-w av-web-studio-w-spacer" style="${styleCss(widget.style, { height: h, "margin-bottom": "0" })}" aria-hidden="true"></div>`;
    }
    case "divider": {
      const wrap = styleCss(widget.style, { "text-align": widget.align });
      const thick = typeof widget.thickness === "number" ? `${widget.thickness || 1}px` : widget.thickness || "1px";
      return `<div class="av-web-studio-w av-web-studio-w-divider" style="${wrap}"><hr style="border:none;border-top:${thick} solid ${esc(widget.color)};width:${esc(widget.width || "100%")};margin:0 auto;display:inline-block;" /></div>`;
    }
    case "icon-box": {
      const css = styleCss(widget.style, { "text-align": widget.align });
      return `<div class="av-web-studio-w av-web-studio-w-iconbox" style="${css}"><div style="font-size:${esc(widget.iconSize || "28px")};margin-bottom:10px;line-height:1;">${esc(widget.icon)}</div><h3 style="margin:0 0 8px;font-size:1.1rem;color:${esc(widget.titleColor || "#0f172a")};">${esc(widget.title)}</h3><p style="margin:0;color:${esc(widget.textColor || "#64748b")};line-height:1.6;">${esc(widget.text)}</p></div>`;
    }
    case "html":
      return `<p class="av-web-studio-w av-web-studio-w-text" style="${styleCss(widget.style)}">${esc(stripTags(widget.content))}</p>`;
    case "shortcode":
      return `<div class="av-web-studio-w av-web-studio-w-shortcode" style="${styleCss(widget.style)}">${esc(widget.content)}</div>`;
    case "video": {
      const css = styleCss(widget.style);
      if (widget.source === "hosted" || /\.(mp4|webm|ogg)(\?|$)/i.test(widget.url)) {
        return `<div class="av-web-studio-w av-web-studio-w-video" style="${css}"><video src="${esc(widget.url)}" controls style="width:100%;aspect-ratio:${esc(widget.aspectRatio || "16 / 9")};background:#000;"${widget.autoplay ? " autoplay muted playsinline" : ""}></video></div>`;
      }
      const src = videoEmbedUrl(widget.url, widget.source, widget.autoplay);
      return `<div class="av-web-studio-w av-web-studio-w-video" style="${css}"><div style="position:relative;width:100%;aspect-ratio:${esc(widget.aspectRatio || "16 / 9")};background:#000;"><iframe src="${esc(src)}" title="Video" style="position:absolute;inset:0;width:100%;height:100%;border:0;" allowfullscreen loading="lazy"></iframe></div></div>`;
    }
    case "image-box": {
      const css = styleCss(widget.style, { "text-align": widget.align });
      return `<div class="av-web-studio-w av-web-studio-w-imagebox" style="${css}"><img src="${esc(widget.src)}" alt="${esc(widget.title)}" style="width:100%;height:auto;display:block;margin-bottom:12px;" loading="lazy" /><h3 style="margin:0 0 8px;color:${esc(widget.titleColor)};">${esc(widget.title)}</h3><p style="margin:0;color:${esc(widget.textColor)};line-height:1.6;">${esc(widget.text)}</p></div>`;
    }
    case "icon-list": {
      const css = styleCss(widget.style, { color: widget.color });
      const items = (widget.items || [])
        .map((item) => {
          const row = `<span aria-hidden="true">${esc(item.icon)}</span><span>${esc(item.text)}</span>`;
          return `<li style="display:flex;gap:10px;align-items:flex-start;margin:0 0 10px;">${itemLink(item.url, row, widget.linkNewTab)}</li>`;
        })
        .join("");
      return `<ul class="av-web-studio-w av-web-studio-w-iconlist" style="${css};list-style:none;padding:0;margin:0;">${items}</ul>`;
    }
    case "counter": {
      const css = styleCss(widget.style, { "text-align": widget.align });
      return `<div class="av-web-studio-w av-web-studio-w-counter" style="${css}" data-av-web-studio-counter data-end="${widget.end}" data-duration="${widget.duration || 1500}"><div style="font-size:2.5rem;font-weight:800;line-height:1;color:${esc(widget.numberColor)};"><span>${esc(widget.prefix)}</span><span data-av-web-studio-counter-value>0</span><span>${esc(widget.suffix)}</span></div><div style="margin-top:8px;color:${esc(widget.titleColor)};">${esc(widget.title)}</div></div>`;
    }
    case "progress-bar": {
      const css = styleCss(widget.style);
      const pct = Math.max(0, Math.min(100, widget.percent || 0));
      return `<div class="av-web-studio-w av-web-studio-w-progress" style="${css}"><div style="display:flex;justify-content:space-between;margin-bottom:8px;font-weight:600;"><span>${esc(widget.title)}</span>${widget.showPercent ? `<span>${pct}%</span>` : ""}</div><div style="height:10px;background:${esc(widget.trackColor)};overflow:hidden;"><div style="height:100%;width:${pct}%;background:${esc(widget.barColor)};"></div></div></div>`;
    }
    case "testimonial": {
      const css = styleCss(widget.style, { "text-align": widget.align });
      const avatar = widget.avatar
        ? `<img src="${esc(widget.avatar)}" alt="${esc(widget.name)}" style="width:48px;height:48px;border-radius:50%;object-fit:cover;margin:0 auto 12px;display:block;" />`
        : "";
      return `<blockquote class="av-web-studio-w av-web-studio-w-testimonial" style="${css}">${avatar}<p style="margin:0 0 12px;font-size:1.05rem;line-height:1.7;">“${esc(widget.content)}”</p><footer style="font-weight:700;">${esc(widget.name)}${widget.role ? `<span style="font-weight:500;opacity:.7;"> — ${esc(widget.role)}</span>` : ""}</footer></blockquote>`;
    }
    case "tabs": {
      const css = styleCss(widget.style);
      const items = widget.items || [];
      const nav = items
        .map(
          (item, i) =>
            `<button type="button" class="av-web-studio-tabs__btn${i === 0 ? " is-active" : ""}" data-av-web-studio-tab="${i}" style="border:none;background:transparent;padding:10px 14px;cursor:pointer;font:inherit;font-weight:700;border-bottom:2px solid ${i === 0 ? "#6366f1" : "transparent"};">${esc(item.title)}</button>`
        )
        .join("");
      const panels = items
        .map(
          (item, i) =>
            `<div class="av-web-studio-tabs__panel${i === 0 ? " is-active" : ""}" data-av-web-studio-tab-panel="${i}" style="display:${i === 0 ? "block" : "none"};padding:16px 0;line-height:1.7;">${esc(item.content)}</div>`
        )
        .join("");
      return `<div class="av-web-studio-w av-web-studio-w-tabs av-web-studio-tabs" style="${css}" data-av-web-studio-tabs><div class="av-web-studio-tabs__nav" style="display:flex;flex-wrap:wrap;gap:4px;border-bottom:1px solid #e2e8f0;">${nav}</div>${panels}</div>`;
    }
    case "accordion":
    case "toggle": {
      const css = styleCss(widget.style);
      const items = (widget.items || [])
        .map(
          (item, i) =>
            `<div class="av-web-studio-acc__item" style="border:1px solid #e2e8f0;margin-bottom:8px;"><button type="button" class="av-web-studio-acc__btn" data-av-web-studio-acc="${i}" style="width:100%;text-align:left;padding:12px 14px;border:none;background:#f8fafc;font:inherit;font-weight:700;cursor:pointer;display:flex;justify-content:space-between;gap:12px;"><span>${esc(item.title)}</span><span aria-hidden="true">+</span></button><div class="av-web-studio-acc__panel" data-av-web-studio-acc-panel="${i}" style="display:none;padding:12px 14px;line-height:1.7;">${esc(item.content)}</div></div>`
        )
        .join("");
      return `<div class="av-web-studio-w av-web-studio-w-accordion av-web-studio-acc" style="${css}" data-av-web-studio-accordion>${items}</div>`;
    }
    case "social-icons": {
      const css = styleCss(widget.style, { "text-align": widget.align });
      const items = (widget.items || [])
        .map(
          (item) =>
            `<a href="${esc(item.url || "#")}" style="display:inline-flex;align-items:center;justify-content:center;min-width:36px;height:36px;padding:0 10px;margin:4px;border:1px solid #e2e8f0;text-decoration:none;color:#0f172a;font-size:${esc(widget.iconSize || "14px")};font-weight:700;">${esc(item.network.slice(0, 2).toUpperCase())}</a>`
        )
        .join("");
      return `<div class="av-web-studio-w av-web-studio-w-social" style="${css}">${items}</div>`;
    }
    case "alert": {
      const colors: Record<string, { bg: string; border: string; text: string }> = {
        info: { bg: "#eff6ff", border: "#93c5fd", text: "#1e3a8a" },
        success: { bg: "#ecfdf5", border: "#6ee7b7", text: "#065f46" },
        warning: { bg: "#fffbeb", border: "#fcd34d", text: "#92400e" },
        danger: { bg: "#fef2f2", border: "#fca5a5", text: "#991b1b" },
      };
      const c = colors[widget.variant] || colors.info;
      const css = styleCss(widget.style, {
        background: c.bg,
        color: c.text,
        border: `1px solid ${c.border}`,
        padding: "14px 16px",
      });
      return `<div class="av-web-studio-w av-web-studio-w-alert" style="${css}" role="alert"><strong style="display:block;margin-bottom:4px;">${esc(widget.title)}</strong><div>${esc(widget.content)}</div></div>`;
    }
    case "star-rating": {
      const css = styleCss(widget.style, { "text-align": widget.align });
      return `<div class="av-web-studio-w av-web-studio-w-stars" style="${css}"><div style="font-size:22px;letter-spacing:2px;line-height:1;">${starsHtml(widget.rating, widget.max || 5, widget.color || "#f59e0b")}</div>${widget.title ? `<div style="margin-top:8px;font-weight:600;">${esc(widget.title)}</div>` : ""}</div>`;
    }
    case "blockquote": {
      const css = styleCss(widget.style, {
        "text-align": widget.align,
        "border-left": "4px solid #6366f1",
        "padding-left": "16px",
        margin: "0",
      });
      return `<blockquote class="av-web-studio-w av-web-studio-w-quote" style="${css}"><p style="margin:0 0 10px;">${esc(widget.content)}</p>${widget.author ? `<cite style="font-style:normal;opacity:.7;">— ${esc(widget.author)}</cite>` : ""}</blockquote>`;
    }
    case "gallery": {
      const css = styleCss(widget.style);
      const cols = Math.max(1, Math.min(6, widget.columns || 3));
      const imgs = (widget.images || [])
        .map((img) => {
          const tag = `<img src="${esc(img.src)}" alt="${esc(img.alt)}" style="width:100%;height:160px;object-fit:cover;display:block;" loading="lazy" />`;
          return itemLink(img.url, tag, widget.linkNewTab);
        })
        .join("");
      return `<div class="av-web-studio-w av-web-studio-w-gallery" style="${css};display:grid;grid-template-columns:repeat(${cols},minmax(0,1fr));gap:${esc(widget.gap || "8px")};">${imgs}</div>`;
    }
    case "image-carousel": {
      const css = styleCss(widget.style);
      const imgs = (widget.images || [])
        .map((img, i) => {
          const tag = `<img src="${esc(img.src)}" alt="${esc(img.alt)}" style="width:100%;height:${esc(widget.height || "320px")};object-fit:cover;display:block;" loading="lazy" />`;
          return `<div class="av-web-studio-carousel__slide${i === 0 ? " is-active" : ""}" data-av-web-studio-slide="${i}" style="display:${i === 0 ? "block" : "none"};">${itemLink(img.url, tag, widget.linkNewTab)}</div>`;
        })
        .join("");
      return `<div class="av-web-studio-w av-web-studio-w-carousel av-web-studio-carousel" style="${css};position:relative;" data-av-web-studio-carousel><div>${imgs}</div><button type="button" data-av-web-studio-carousel-prev style="position:absolute;left:8px;top:50%;transform:translateY(-50%);border:none;background:rgba(15,23,42,.7);color:#fff;width:32px;height:32px;cursor:pointer;">‹</button><button type="button" data-av-web-studio-carousel-next style="position:absolute;right:8px;top:50%;transform:translateY(-50%);border:none;background:rgba(15,23,42,.7);color:#fff;width:32px;height:32px;cursor:pointer;">›</button></div>`;
    }
    case "animated-headline": {
      const css = styleCss(widget.style, { "text-align": widget.align });
      return `<div class="av-web-studio-w av-web-studio-w-animated-headline" style="${css}"><span>${esc(widget.before)} </span><span class="av-web-studio-headline-highlight" style="color:${esc(widget.highlightColor)};text-decoration:underline;text-underline-offset:6px;">${esc(widget.highlight)}</span><span> ${esc(widget.after)}</span></div>`;
    }
    case "countdown": {
      const css = styleCss(widget.style, { "text-align": widget.align });
      const cell = (label: string, key: string) =>
        `<div style="min-width:70px;padding:12px;background:#f8fafc;border:1px solid #e2e8f0;"><div data-av-web-studio-cd="${key}" style="font-size:1.75rem;font-weight:800;line-height:1;">00</div><div style="margin-top:6px;font-size:12px;opacity:.7;">${esc(label)}</div></div>`;
      return `<div class="av-web-studio-w av-web-studio-w-countdown" style="${css}" data-av-web-studio-countdown data-due="${esc(widget.dueDate)}"><div style="display:inline-flex;flex-wrap:wrap;gap:10px;justify-content:center;">${cell(widget.labelDays, "d")}${cell(widget.labelHours, "h")}${cell(widget.labelMinutes, "m")}${cell(widget.labelSeconds, "s")}</div></div>`;
    }
    case "price-table": {
      const css = styleCss(widget.style, { background: widget.background, "text-align": "center" });
      const features = (widget.features || "")
        .split("\n")
        .filter(Boolean)
        .map((f) => `<li style="padding:8px 0;border-bottom:1px solid #e2e8f0;">${esc(f)}</li>`)
        .join("");
      return `<div class="av-web-studio-w av-web-studio-w-price-table" style="${css}"><div style="font-weight:700;margin-bottom:8px;">${esc(widget.title)}${widget.featured ? ' <span style="color:#6366f1;">★</span>' : ""}</div><div style="font-size:2.4rem;font-weight:800;line-height:1;">${esc(widget.price)}<span style="font-size:1rem;font-weight:500;opacity:.7;">${esc(widget.period)}</span></div><ul style="list-style:none;padding:16px 0;margin:16px 0;text-align:left;">${features}</ul><a href="${esc(widget.buttonUrl || "#")}" style="display:inline-block;padding:12px 20px;background:${esc(widget.buttonBackground)};color:#fff;text-decoration:none;font-weight:700;">${esc(widget.buttonLabel)}</a></div>`;
    }
    case "price-list": {
      const css = styleCss(widget.style);
      const items = (widget.items || [])
        .map((item) => {
          const row = `<div style="display:flex;justify-content:space-between;align-items:flex-start;gap:20px;padding:14px 0;border-bottom:1px solid rgba(15,23,42,.1);"><div style="min-width:0;flex:1 1 auto;"><div style="font-weight:700;line-height:1.35;">${esc(item.title)}</div><div style="opacity:.72;font-size:13px;margin-top:4px;line-height:1.45;">${esc(item.description)}</div></div><div style="font-weight:800;white-space:nowrap;flex:0 0 auto;padding-left:12px;">${esc(item.price)}</div></div>`;
          return itemLink(item.url, row, widget.linkNewTab);
        })
        .join("");
      return `<div class="av-web-studio-w av-web-studio-w-price-list" style="${css}">${items}</div>`;
    }
    case "flip-box": {
      const css = styleCss(widget.style);
      return `<div class="av-web-studio-w av-web-studio-w-flipbox" style="${css}" data-av-web-studio-flipbox><div class="av-web-studio-flipbox" style="perspective:1000px;min-height:${esc(widget.minHeight || "240px")};"><div class="av-web-studio-flipbox__inner" style="position:relative;width:100%;height:100%;min-height:${esc(widget.minHeight || "240px")};transition:transform .6s;transform-style:preserve-3d;"><div class="av-web-studio-flipbox__face" style="position:absolute;inset:0;backface-visibility:hidden;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:24px;text-align:center;color:#fff;background:${esc(widget.frontBackground)};"><div style="font-size:28px;margin-bottom:10px;">${esc(widget.frontIcon)}</div><h3 style="margin:0 0 8px;">${esc(widget.frontTitle)}</h3><p style="margin:0;opacity:.9;">${esc(widget.frontText)}</p></div><div class="av-web-studio-flipbox__face av-web-studio-flipbox__back" style="position:absolute;inset:0;backface-visibility:hidden;transform:rotateY(180deg);display:flex;flex-direction:column;align-items:center;justify-content:center;padding:24px;text-align:center;color:#fff;background:${esc(widget.backBackground)};"><h3 style="margin:0 0 8px;">${esc(widget.backTitle)}</h3><p style="margin:0 0 14px;opacity:.9;">${esc(widget.backText)}</p><a href="${esc(widget.backButtonUrl || "#")}" style="color:#fff;font-weight:700;">${esc(widget.backButtonLabel)}</a></div></div></div></div>`;
    }
    case "call-to-action": {
      const css = styleCss(widget.style, {
        background: widget.background,
        color: "#fff",
        "text-align": "center",
        "background-image": widget.image ? `url('${widget.image.replace(/'/g, "%27")}')` : undefined,
        "background-size": widget.image ? "cover" : undefined,
        "background-position": widget.image ? "center" : undefined,
      });
      return `<div class="av-web-studio-w av-web-studio-w-cta" style="${css}"><h2 style="margin:0 0 10px;font-size:1.75rem;">${esc(widget.title)}</h2><p style="margin:0 0 18px;opacity:.9;">${esc(widget.text)}</p><a href="${esc(widget.buttonUrl || "#")}" style="display:inline-block;padding:12px 22px;background:#fff;color:#0f172a;text-decoration:none;font-weight:700;">${esc(widget.buttonLabel)}</a></div>`;
    }
    case "dual-button": {
      const css = styleCss(widget.style, { "text-align": widget.align });
      const a = (label: string, url: string, bg: string) =>
        `<a href="${esc(url || "#")}" style="display:inline-block;padding:12px 18px;background:${esc(bg)};color:#fff;text-decoration:none;font-weight:700;margin:4px;">${esc(label)}</a>`;
      return `<div class="av-web-studio-w av-web-studio-w-dual-button" style="${css}">${a(widget.leftLabel, widget.leftUrl, widget.leftBackground)}${a(widget.rightLabel, widget.rightUrl, widget.rightBackground)}</div>`;
    }
    case "team": {
      const css = styleCss(widget.style, { "text-align": widget.align });
      return `<div class="av-web-studio-w av-web-studio-w-team" style="${css}"><img src="${esc(widget.image)}" alt="${esc(widget.name)}" style="width:120px;height:120px;border-radius:50%;object-fit:cover;display:block;margin:0 auto 12px;" loading="lazy" /><h3 style="margin:0 0 4px;">${esc(widget.name)}</h3><div style="opacity:.7;margin-bottom:8px;">${esc(widget.role)}</div><p style="margin:0;line-height:1.6;">${esc(widget.bio)}</p></div>`;
    }
    case "business-hours": {
      const css = styleCss(widget.style);
      const rows = (widget.items || [])
        .map(
          (item) =>
            `<div style="display:flex;justify-content:space-between;gap:16px;padding:10px 0;border-bottom:1px solid #e2e8f0;"><span style="font-weight:600;">${esc(item.day)}</span><span>${esc(item.hours)}</span></div>`
        )
        .join("");
      return `<div class="av-web-studio-w av-web-studio-w-hours" style="${css}">${rows}</div>`;
    }
    case "share-buttons": {
      const css = styleCss(widget.style, { "text-align": widget.align });
      const nets = (widget.networks || "facebook,twitter,linkedin")
        .split(",")
        .map((n) => n.trim())
        .filter(Boolean);
      const buttons = nets
        .map((n) => `<a href="#" style="display:inline-block;padding:8px 12px;margin:4px;border:1px solid #e2e8f0;text-decoration:none;color:#0f172a;font-size:12px;font-weight:700;text-transform:capitalize;">${esc(n)}</a>`)
        .join("");
      return `<div class="av-web-studio-w av-web-studio-w-share" style="${css}">${buttons}</div>`;
    }
    case "search": {
      const css = styleCss(widget.style);
      return `<form class="av-web-studio-w av-web-studio-w-search" style="${css};display:flex;gap:8px;" role="search" action="/" method="get"><input type="search" name="s" placeholder="${esc(widget.placeholder || "Search…")}" style="flex:1;padding:10px 12px;border:1px solid #e2e8f0;" /><button type="submit" style="padding:10px 16px;border:none;background:#0f172a;color:#fff;font-weight:700;cursor:pointer;">${esc(widget.buttonLabel || "Search")}</button></form>`;
    }
    case "reviews": {
      const css = styleCss(widget.style);
      const cards = (widget.items || [])
        .map((item) => {
          const card = `<div style="padding:14px;border:1px solid #e2e8f0;margin-bottom:10px;"><div style="color:#f59e0b;margin-bottom:6px;">${starsHtml(item.rating, 5, "#f59e0b")}</div><p style="margin:0 0 8px;line-height:1.6;">${esc(item.content)}</p><strong>${esc(item.name)}</strong></div>`;
          return itemLink(item.url, card, widget.linkNewTab);
        })
        .join("");
      return `<div class="av-web-studio-w av-web-studio-w-reviews" style="${css}">${cards}</div>`;
    }
    case "table-of-contents": {
      const css = styleCss(widget.style);
      const links = (widget.items || "")
        .split("\n")
        .map((line) => line.trim())
        .filter(Boolean)
        .map((line) => `<li style="margin:0 0 8px;"><a href="#${esc(line.toLowerCase().replace(/\s+/g, "-"))}" style="color:inherit;">${esc(line)}</a></li>`)
        .join("");
      return `<nav class="av-web-studio-w av-web-studio-w-toc" style="${css}"><strong style="display:block;margin-bottom:10px;">${esc(widget.title)}</strong><ol style="margin:0;padding-left:18px;">${links}</ol></nav>`;
    }
    case "slides": {
      const css = styleCss(widget.style);
      const slides = (widget.items || [])
        .map(
          (item, i) =>
            `<div class="av-web-studio-slides__item${i === 0 ? " is-active" : ""}" data-av-web-studio-slide="${i}" style="display:${i === 0 ? "flex" : "none"};align-items:center;justify-content:center;min-height:${esc(widget.height || "360px")};padding:40px 24px;text-align:center;color:#fff;background:${esc(item.background)};"><div><h2 style="margin:0 0 10px;font-size:2rem;">${esc(item.title)}</h2><p style="margin:0 0 16px;opacity:.9;">${esc(item.text)}</p><a href="${esc(item.buttonUrl || "#")}" style="display:inline-block;padding:12px 20px;background:#fff;color:#0f172a;text-decoration:none;font-weight:700;">${esc(item.buttonLabel)}</a></div></div>`
        )
        .join("");
      return `<div class="av-web-studio-w av-web-studio-w-slides av-web-studio-slides" style="${css};position:relative;" data-av-web-studio-carousel>${slides}<button type="button" data-av-web-studio-carousel-prev style="position:absolute;left:8px;top:50%;transform:translateY(-50%);border:none;background:rgba(255,255,255,.2);color:#fff;width:32px;height:32px;cursor:pointer;">‹</button><button type="button" data-av-web-studio-carousel-next style="position:absolute;right:8px;top:50%;transform:translateY(-50%);border:none;background:rgba(255,255,255,.2);color:#fff;width:32px;height:32px;cursor:pointer;">›</button></div>`;
    }
    case "posts-grid":
    case "posts-list":
    case "posts-carousel": {
      const css = styleCss(widget.style);
      const layout = postsWidgetLayout(widget.type);
      const payload = escAttr(JSON.stringify(buildPostsWidgetPayload(widget)));
      return `<div class="av-web-studio-w av-web-studio-w-posts av-web-studio-w-posts-${layout}" style="${css}" data-av-web-studio-posts="${payload}"></div>`;
    }
  }
}

export const VISUAL_WIDGET_CSS = `
.av-web-studio-flipbox:hover .av-web-studio-flipbox__inner, .av-web-studio-flipbox.is-flipped .av-web-studio-flipbox__inner { transform: rotateY(180deg); }
.av-web-studio-headline-highlight { animation: av-web-studio-headline-pulse 2.4s ease-in-out infinite; }
@keyframes av-web-studio-headline-pulse { 0%,100% { opacity: 1; } 50% { opacity: .72; } }
.av-web-studio-posts { --av-web-studio-posts-gap: 24px; --av-web-studio-posts-cols: 3; --av-web-studio-posts-image-ratio: 16/9; --av-web-studio-posts-list-image-width: 140px; --av-web-studio-posts-carousel-height: 360px; }
.av-web-studio-posts--grid { display: grid; grid-template-columns: repeat(var(--av-web-studio-posts-cols), minmax(0, 1fr)); gap: var(--av-web-studio-posts-gap); }
.av-web-studio-posts--list { display: flex; flex-direction: column; gap: var(--av-web-studio-posts-gap); }
.av-web-studio-posts--carousel { position: relative; }
.av-web-studio-posts__item { display: flex; flex-direction: column; min-width: 0; }
.av-web-studio-posts--list .av-web-studio-posts__item { flex-direction: row; gap: 16px; align-items: flex-start; }
.av-web-studio-posts--card .av-web-studio-posts__item { border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; background: #fff; }
.av-web-studio-posts--minimal .av-web-studio-posts__item { border: none; background: transparent; }
.av-web-studio-posts--overlay .av-web-studio-posts__item { position: relative; border-radius: 8px; overflow: hidden; }
.av-web-studio-posts__media { display: block; overflow: hidden; flex-shrink: 0; }
.av-web-studio-posts--grid .av-web-studio-posts__media { aspect-ratio: var(--av-web-studio-posts-image-ratio); }
.av-web-studio-posts--list .av-web-studio-posts__media { width: var(--av-web-studio-posts-list-image-width); aspect-ratio: var(--av-web-studio-posts-image-ratio); }
.av-web-studio-posts__media img { width: 100%; height: 100%; object-fit: cover; display: block; }
.av-web-studio-posts__body { padding: 16px; flex: 1 1 auto; min-width: 0; }
.av-web-studio-posts--list .av-web-studio-posts__body { padding: 0; }
.av-web-studio-posts--overlay .av-web-studio-posts__body { position: absolute; inset: auto 0 0 0; padding: 20px; color: #fff; background: linear-gradient(transparent, rgba(15,23,42,.85)); }
.av-web-studio-posts__title { margin: 0 0 8px; font-size: 1.125rem; line-height: 1.35; }
.av-web-studio-posts__title a { color: inherit; text-decoration: none; }
.av-web-studio-posts__title a:hover { text-decoration: underline; }
.av-web-studio-posts__meta { font-size: 12px; opacity: .72; margin-bottom: 8px; display: flex; flex-wrap: wrap; gap: 8px; }
.av-web-studio-posts__excerpt { margin: 0 0 12px; font-size: 14px; line-height: 1.55; opacity: .88; }
.av-web-studio-posts__read-more { font-size: 13px; font-weight: 700; color: #6366f1; text-decoration: none; }
.av-web-studio-posts__read-more:hover { text-decoration: underline; }
.av-web-studio-posts__empty { padding: 24px; text-align: center; opacity: .65; font-size: 14px; border: 1px dashed #cbd5e1; border-radius: 8px; }
.av-web-studio-posts--carousel .av-web-studio-posts__slide { display: none; }
.av-web-studio-posts--carousel .av-web-studio-posts__slide.is-active { display: block; }
.av-web-studio-posts--carousel .av-web-studio-posts__slide .av-web-studio-posts__media { height: var(--av-web-studio-posts-carousel-height); aspect-ratio: auto; }
`;

export const VISUAL_WIDGET_JS = `(() => {
  const qsa = (sel, root = document) => Array.from(root.querySelectorAll(sel));
  qsa("[data-av-web-studio-tabs]").forEach((root) => {
    qsa("[data-av-web-studio-tab]", root).forEach((btn) => {
      btn.addEventListener("click", () => {
        const id = btn.getAttribute("data-av-web-studio-tab");
        qsa("[data-av-web-studio-tab]", root).forEach((b) => {
          b.classList.toggle("is-active", b === btn);
          b.style.borderBottomColor = b === btn ? "#6366f1" : "transparent";
        });
        qsa("[data-av-web-studio-tab-panel]", root).forEach((panel) => {
          const on = panel.getAttribute("data-av-web-studio-tab-panel") === id;
          panel.style.display = on ? "block" : "none";
        });
      });
    });
  });
  qsa("[data-av-web-studio-accordion]").forEach((root) => {
    qsa("[data-av-web-studio-acc]", root).forEach((btn) => {
      btn.addEventListener("click", () => {
        const id = btn.getAttribute("data-av-web-studio-acc");
        const panel = root.querySelector('[data-av-web-studio-acc-panel="' + id + '"]');
        if (!panel) return;
        const open = panel.style.display === "block";
        panel.style.display = open ? "none" : "block";
        const mark = btn.querySelector("span:last-child");
        if (mark) mark.textContent = open ? "+" : "−";
      });
    });
  });
  qsa("[data-av-web-studio-carousel]").forEach((root) => {
    const slides = qsa("[data-av-web-studio-slide]", root);
    if (!slides.length) return;
    let i = 0;
    const show = (next) => {
      i = (next + slides.length) % slides.length;
      slides.forEach((s, idx) => { s.style.display = idx === i ? (s.classList.contains("av-web-studio-slides__item") ? "flex" : "block") : "none"; });
    };
    root.querySelector("[data-av-web-studio-carousel-prev]")?.addEventListener("click", () => show(i - 1));
    root.querySelector("[data-av-web-studio-carousel-next]")?.addEventListener("click", () => show(i + 1));
  });
  qsa("[data-av-web-studio-countdown]").forEach((root) => {
    const due = new Date(root.getAttribute("data-due") || "").getTime();
    const tick = () => {
      const diff = Math.max(0, due - Date.now());
      const d = Math.floor(diff / 86400000);
      const h = Math.floor((diff % 86400000) / 3600000);
      const m = Math.floor((diff % 3600000) / 60000);
      const s = Math.floor((diff % 60000) / 1000);
      const set = (k, v) => { const el = root.querySelector('[data-av-web-studio-cd="' + k + '"]'); if (el) el.textContent = String(v).padStart(2, "0"); };
      set("d", d); set("h", h); set("m", m); set("s", s);
    };
    tick();
    setInterval(tick, 1000);
  });
  qsa("[data-av-web-studio-counter]").forEach((root) => {
    const end = Number(root.getAttribute("data-end") || 0);
    const duration = Number(root.getAttribute("data-duration") || 1500);
    const valueEl = root.querySelector("[data-av-web-studio-counter-value]");
    if (!valueEl) return;
    const start = performance.now();
    const step = (now) => {
      const t = Math.min(1, (now - start) / duration);
      valueEl.textContent = String(Math.round(end * t));
      if (t < 1) requestAnimationFrame(step);
    };
    requestAnimationFrame(step);
  });
})();
`;
