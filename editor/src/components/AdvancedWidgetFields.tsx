import type { ReactNode } from "react";

import type { VisualWidget } from "../visual/types";
import { UrlSuggestField } from "./UrlSuggestField";
import { PostsWidgetFields } from "./PostsWidgetFields";

function Field({ label, children }: { label: string; children: ReactNode }) {
  return (
    <div className="av-web-studio-visual__field">
      <span className="av-web-studio-visual__field-label">{label}</span>
      {children}
    </div>
  );
}

function linesToItems(text: string, map: (parts: string[]) => Record<string, unknown>) {
  return text
    .split("\n")
    .map((line) => line.trim())
    .filter(Boolean)
    .map((line) => map(line.split("|").map((p) => p.trim())));
}

export function AdvancedWidgetFields({
  widget,
  onChange,
  pageId = 0,
}: {
  widget: VisualWidget;
  onChange: (patch: Partial<VisualWidget>) => void;
  pageId?: number;
}) {
  switch (widget.type) {
    case "shortcode":
      return (
        <Field label="Shortcode">
          <textarea rows={3} value={widget.content} onChange={(e) => onChange({ content: e.target.value })} />
        </Field>
      );
    case "video":
      return (
        <>
          <Field label="Source">
            <select
              value={widget.source}
              onChange={(e) => onChange({ source: e.target.value as typeof widget.source })}
            >
              <option value="youtube">YouTube</option>
              <option value="vimeo">Vimeo</option>
              <option value="hosted">Self hosted</option>
            </select>
          </Field>
          <Field label="URL">
            <input type="text" value={widget.url} onChange={(e) => onChange({ url: e.target.value })} />
          </Field>
          <Field label="Aspect ratio">
            <input
              type="text"
              value={widget.aspectRatio}
              onChange={(e) => onChange({ aspectRatio: e.target.value })}
              placeholder="16 / 9"
            />
          </Field>
          <label className="av-web-studio-visual__check">
            <input
              type="checkbox"
              checked={widget.autoplay}
              onChange={(e) => onChange({ autoplay: e.target.checked })}
            />
            Autoplay
          </label>
        </>
      );
    case "image-box":
      return (
        <>
          <Field label="Image URL">
            <input type="text" value={widget.src} onChange={(e) => onChange({ src: e.target.value })} />
          </Field>
          <Field label="Title">
            <input type="text" value={widget.title} onChange={(e) => onChange({ title: e.target.value })} />
          </Field>
          <Field label="Text">
            <textarea rows={3} value={widget.text} onChange={(e) => onChange({ text: e.target.value })} />
          </Field>
        </>
      );
    case "icon-list":
      return (
        <Field label="Items (icon|text|url per line)">
          <textarea
            rows={5}
            value={(widget.items || []).map((i) => `${i.icon}|${i.text}${i.url ? `|${i.url}` : ""}`).join("\n")}
            onChange={(e) =>
              onChange({
                items: linesToItems(e.target.value, (p) => ({
                  icon: p[0] || "•",
                  text: p[1] || p[0] || "",
                  url: p[2] || "",
                })) as { icon: string; text: string; url?: string }[],
              })
            }
          />
        </Field>
      );
    case "counter":
      return (
        <>
          <Field label="Prefix">
            <input type="text" value={widget.prefix} onChange={(e) => onChange({ prefix: e.target.value })} />
          </Field>
          <Field label="Number">
            <input
              type="number"
              value={widget.end}
              onChange={(e) => onChange({ end: Number(e.target.value) || 0 })}
            />
          </Field>
          <Field label="Suffix">
            <input type="text" value={widget.suffix} onChange={(e) => onChange({ suffix: e.target.value })} />
          </Field>
          <Field label="Title">
            <input type="text" value={widget.title} onChange={(e) => onChange({ title: e.target.value })} />
          </Field>
        </>
      );
    case "progress-bar":
      return (
        <>
          <Field label="Title">
            <input type="text" value={widget.title} onChange={(e) => onChange({ title: e.target.value })} />
          </Field>
          <Field label="Percent">
            <input
              type="number"
              min={0}
              max={100}
              value={widget.percent}
              onChange={(e) => onChange({ percent: Math.max(0, Math.min(100, Number(e.target.value) || 0)) })}
            />
          </Field>
        </>
      );
    case "testimonial":
      return (
        <>
          <Field label="Quote">
            <textarea rows={4} value={widget.content} onChange={(e) => onChange({ content: e.target.value })} />
          </Field>
          <Field label="Name">
            <input type="text" value={widget.name} onChange={(e) => onChange({ name: e.target.value })} />
          </Field>
          <Field label="Role">
            <input type="text" value={widget.role} onChange={(e) => onChange({ role: e.target.value })} />
          </Field>
        </>
      );
    case "tabs":
    case "accordion":
    case "toggle":
      return (
        <Field label="Items (title|content per line)">
          <textarea
            rows={6}
            value={(widget.items || []).map((i) => `${i.title}|${i.content}`).join("\n")}
            onChange={(e) =>
              onChange({
                items: linesToItems(e.target.value, (p) => ({
                  title: p[0] || "Title",
                  content: p.slice(1).join("|") || "",
                })) as { title: string; content: string }[],
              })
            }
          />
        </Field>
      );
    case "social-icons":
      return (
        <Field label="Networks (Name|URL per line)">
          <textarea
            rows={5}
            value={(widget.items || []).map((i) => `${i.network}|${i.url}`).join("\n")}
            onChange={(e) =>
              onChange({
                items: linesToItems(e.target.value, (p) => ({
                  network: p[0] || "Link",
                  url: p[1] || "#",
                })) as { network: string; url: string }[],
              })
            }
          />
        </Field>
      );
    case "alert":
      return (
        <>
          <Field label="Variant">
            <select
              value={widget.variant}
              onChange={(e) => onChange({ variant: e.target.value as typeof widget.variant })}
            >
              <option value="info">Info</option>
              <option value="success">Success</option>
              <option value="warning">Warning</option>
              <option value="danger">Danger</option>
            </select>
          </Field>
          <Field label="Title">
            <input type="text" value={widget.title} onChange={(e) => onChange({ title: e.target.value })} />
          </Field>
          <Field label="Message">
            <textarea rows={3} value={widget.content} onChange={(e) => onChange({ content: e.target.value })} />
          </Field>
        </>
      );
    case "star-rating":
      return (
        <>
          <Field label="Rating">
            <input
              type="number"
              min={0}
              max={widget.max}
              step={0.5}
              value={widget.rating}
              onChange={(e) => onChange({ rating: Number(e.target.value) || 0 })}
            />
          </Field>
          <Field label="Title">
            <input type="text" value={widget.title} onChange={(e) => onChange({ title: e.target.value })} />
          </Field>
        </>
      );
    case "blockquote":
      return (
        <>
          <Field label="Quote">
            <textarea rows={4} value={widget.content} onChange={(e) => onChange({ content: e.target.value })} />
          </Field>
          <Field label="Author">
            <input type="text" value={widget.author} onChange={(e) => onChange({ author: e.target.value })} />
          </Field>
        </>
      );
    case "gallery":
    case "image-carousel":
      return (
        <>
          <Field label="Images (src|alt|url per line)">
            <textarea
              rows={5}
              value={(widget.images || [])
                .map((i) => `${i.src}|${i.alt}${i.url ? `|${i.url}` : ""}`)
                .join("\n")}
              onChange={(e) =>
                onChange({
                  images: linesToItems(e.target.value, (p) => ({
                    src: p[0] || "",
                    alt: p[1] || "",
                    url: p[2] || "",
                  })) as { src: string; alt: string; url?: string }[],
                })
              }
            />
          </Field>
          {widget.type === "gallery" ? (
            <Field label="Columns">
              <input
                type="number"
                min={1}
                max={6}
                value={widget.columns}
                onChange={(e) => onChange({ columns: Math.max(1, Math.min(6, Number(e.target.value) || 3)) })}
              />
            </Field>
          ) : (
            <Field label="Height">
              <input type="text" value={widget.height} onChange={(e) => onChange({ height: e.target.value })} />
            </Field>
          )}
        </>
      );
    case "animated-headline":
      return (
        <>
          <Field label="Before">
            <input type="text" value={widget.before} onChange={(e) => onChange({ before: e.target.value })} />
          </Field>
          <Field label="Highlight">
            <input type="text" value={widget.highlight} onChange={(e) => onChange({ highlight: e.target.value })} />
          </Field>
          <Field label="After">
            <input type="text" value={widget.after} onChange={(e) => onChange({ after: e.target.value })} />
          </Field>
        </>
      );
    case "countdown":
      return (
        <Field label="Due date (ISO)">
          <input type="text" value={widget.dueDate} onChange={(e) => onChange({ dueDate: e.target.value })} />
        </Field>
      );
    case "price-table":
      return (
        <>
          <Field label="Title">
            <input type="text" value={widget.title} onChange={(e) => onChange({ title: e.target.value })} />
          </Field>
          <Field label="Price">
            <input type="text" value={widget.price} onChange={(e) => onChange({ price: e.target.value })} />
          </Field>
          <Field label="Period">
            <input type="text" value={widget.period} onChange={(e) => onChange({ period: e.target.value })} />
          </Field>
          <Field label="Features (one per line)">
            <textarea rows={4} value={widget.features} onChange={(e) => onChange({ features: e.target.value })} />
          </Field>
          <Field label="Button label">
            <input
              type="text"
              value={widget.buttonLabel}
              onChange={(e) => onChange({ buttonLabel: e.target.value })}
            />
          </Field>
          <Field label="Button URL">
            <UrlSuggestField value={widget.buttonUrl} onChange={(url) => onChange({ buttonUrl: url })} />
          </Field>
        </>
      );
    case "price-list":
      return (
        <Field label="Items (title|price|description|url)">
          <textarea
            rows={6}
            value={(widget.items || [])
              .map((i) => `${i.title}|${i.price}|${i.description}${i.url ? `|${i.url}` : ""}`)
              .join("\n")}
            onChange={(e) =>
              onChange({
                items: linesToItems(e.target.value, (p) => ({
                  title: p[0] || "",
                  price: p[1] || "",
                  description: p[2] || "",
                  url: p[3] || "",
                })) as { title: string; price: string; description: string; url?: string }[],
              })
            }
          />
        </Field>
      );
    case "flip-box":
      return (
        <>
          <Field label="Front title">
            <input
              type="text"
              value={widget.frontTitle}
              onChange={(e) => onChange({ frontTitle: e.target.value })}
            />
          </Field>
          <Field label="Front text">
            <textarea
              rows={2}
              value={widget.frontText}
              onChange={(e) => onChange({ frontText: e.target.value })}
            />
          </Field>
          <Field label="Back title">
            <input type="text" value={widget.backTitle} onChange={(e) => onChange({ backTitle: e.target.value })} />
          </Field>
          <Field label="Back text">
            <textarea rows={2} value={widget.backText} onChange={(e) => onChange({ backText: e.target.value })} />
          </Field>
          <Field label="Back button">
            <input
              type="text"
              value={widget.backButtonLabel}
              onChange={(e) => onChange({ backButtonLabel: e.target.value })}
            />
          </Field>
        </>
      );
    case "call-to-action":
      return (
        <>
          <Field label="Title">
            <input type="text" value={widget.title} onChange={(e) => onChange({ title: e.target.value })} />
          </Field>
          <Field label="Text">
            <textarea rows={3} value={widget.text} onChange={(e) => onChange({ text: e.target.value })} />
          </Field>
          <Field label="Button label">
            <input
              type="text"
              value={widget.buttonLabel}
              onChange={(e) => onChange({ buttonLabel: e.target.value })}
            />
          </Field>
          <Field label="Button URL">
            <UrlSuggestField value={widget.buttonUrl} onChange={(url) => onChange({ buttonUrl: url })} />
          </Field>
        </>
      );
    case "dual-button":
      return (
        <>
          <Field label="Left label">
            <input type="text" value={widget.leftLabel} onChange={(e) => onChange({ leftLabel: e.target.value })} />
          </Field>
          <Field label="Left URL">
            <UrlSuggestField value={widget.leftUrl} onChange={(url) => onChange({ leftUrl: url })} />
          </Field>
          <Field label="Right label">
            <input
              type="text"
              value={widget.rightLabel}
              onChange={(e) => onChange({ rightLabel: e.target.value })}
            />
          </Field>
          <Field label="Right URL">
            <UrlSuggestField value={widget.rightUrl} onChange={(url) => onChange({ rightUrl: url })} />
          </Field>
        </>
      );
    case "team":
      return (
        <>
          <Field label="Photo URL">
            <input type="text" value={widget.image} onChange={(e) => onChange({ image: e.target.value })} />
          </Field>
          <Field label="Name">
            <input type="text" value={widget.name} onChange={(e) => onChange({ name: e.target.value })} />
          </Field>
          <Field label="Role">
            <input type="text" value={widget.role} onChange={(e) => onChange({ role: e.target.value })} />
          </Field>
          <Field label="Bio">
            <textarea rows={3} value={widget.bio} onChange={(e) => onChange({ bio: e.target.value })} />
          </Field>
        </>
      );
    case "business-hours":
      return (
        <Field label="Hours (day|hours per line)">
          <textarea
            rows={5}
            value={(widget.items || []).map((i) => `${i.day}|${i.hours}`).join("\n")}
            onChange={(e) =>
              onChange({
                items: linesToItems(e.target.value, (p) => ({
                  day: p[0] || "",
                  hours: p[1] || "",
                })) as { day: string; hours: string }[],
              })
            }
          />
        </Field>
      );
    case "share-buttons":
      return (
        <Field label="Networks (comma separated)">
          <input type="text" value={widget.networks} onChange={(e) => onChange({ networks: e.target.value })} />
        </Field>
      );
    case "search":
      return (
        <>
          <Field label="Placeholder">
            <input
              type="text"
              value={widget.placeholder}
              onChange={(e) => onChange({ placeholder: e.target.value })}
            />
          </Field>
          <Field label="Button label">
            <input
              type="text"
              value={widget.buttonLabel}
              onChange={(e) => onChange({ buttonLabel: e.target.value })}
            />
          </Field>
        </>
      );
    case "reviews":
      return (
        <Field label="Reviews (name|rating|content|url)">
          <textarea
            rows={6}
            value={(widget.items || [])
              .map((i) => `${i.name}|${i.rating}|${i.content}${i.url ? `|${i.url}` : ""}`)
              .join("\n")}
            onChange={(e) =>
              onChange({
                items: linesToItems(e.target.value, (p) => {
                  const maybeUrl = p.length >= 4 ? p[p.length - 1] : "";
                  const hasUrl = Boolean(maybeUrl && (maybeUrl.startsWith("http") || maybeUrl.startsWith("#") || maybeUrl.startsWith("/") || maybeUrl.startsWith("mailto:")));
                  return {
                    name: p[0] || "",
                    rating: Number(p[1]) || 5,
                    content: (hasUrl ? p.slice(2, -1) : p.slice(2)).join("|") || "",
                    url: hasUrl ? maybeUrl : "",
                  };
                }) as { name: string; rating: number; content: string; url?: string }[],
              })
            }
          />
        </Field>
      );
    case "table-of-contents":
      return (
        <>
          <Field label="Title">
            <input type="text" value={widget.title} onChange={(e) => onChange({ title: e.target.value })} />
          </Field>
          <Field label="Items (one per line)">
            <textarea rows={5} value={widget.items} onChange={(e) => onChange({ items: e.target.value })} />
          </Field>
        </>
      );
    case "slides":
      return (
        <>
          <Field label="Slides (title|text|button|url|background)">
            <textarea
              rows={6}
              value={(widget.items || [])
                .map((i) => `${i.title}|${i.text}|${i.buttonLabel}|${i.buttonUrl}|${i.background}`)
                .join("\n")}
              onChange={(e) =>
                onChange({
                  items: linesToItems(e.target.value, (p) => ({
                    title: p[0] || "",
                    text: p[1] || "",
                    buttonLabel: p[2] || "Learn More",
                    buttonUrl: p[3] || "#",
                    background: p[4] || "#0f172a",
                  })) as {
                    title: string;
                    text: string;
                    buttonLabel: string;
                    buttonUrl: string;
                    background: string;
                  }[],
                })
              }
            />
          </Field>
          <Field label="Height">
            <input type="text" value={widget.height} onChange={(e) => onChange({ height: e.target.value })} />
          </Field>
        </>
      );
    case "posts-grid":
    case "posts-list":
    case "posts-carousel":
      return <PostsWidgetFields widget={widget} onChange={onChange} pageId={pageId} />;
    default:
      return null;
  }
}
