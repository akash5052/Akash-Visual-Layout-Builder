import type { PageSeo } from "../types";
import { openImagePicker } from "../utils/mediaPicker";

interface SeoPanelProps {
  seo: PageSeo;
  pageTitle: string;
  permalink: string;
  isSaving?: boolean;
  onChange: (seo: PageSeo) => void;
  onSave: () => void;
}

function charStatus(length: number, min: number, max: number): "good" | "warn" | "bad" {
  if (length === 0) return "warn";
  if (length >= min && length <= max) return "good";
  if (length < min || length > max + 20) return "bad";
  return "warn";
}

function keywordInText(keyword: string, text: string): boolean {
  if (!keyword.trim() || !text.trim()) return false;
  return text.toLowerCase().includes(keyword.trim().toLowerCase());
}

export function SeoPanel({ seo, pageTitle, permalink, isSaving = false, onChange, onSave }: SeoPanelProps) {
  const titleValue = seo.meta_title || "";
  const descValue = seo.meta_description || "";
  const titleLen = titleValue.length || pageTitle.length;
  const descLen = descValue.length;

  const titleStatus = charStatus(titleValue.length || pageTitle.length, 30, 60);
  const descStatus = charStatus(descLen, 120, 160);
  const keyword = seo.focus_keyword.trim();
  const keywordInTitle = keywordInText(keyword, titleValue || pageTitle);
  const keywordInDesc = keywordInText(keyword, descValue);

  const scoreItems = [
    { ok: titleLen > 0, label: "Meta title set" },
    { ok: descLen >= 120, label: "Meta description (120+ chars)" },
    { ok: keyword.length > 0, label: "Focus keyword set" },
    { ok: keywordInTitle, label: "Keyword in title" },
    { ok: keywordInDesc, label: "Keyword in description" },
    { ok: !!seo.social_image, label: "Social sharing image" },
  ];
  const score = scoreItems.filter((i) => i.ok).length;

  const update = (patch: Partial<PageSeo>) => onChange({ ...seo, ...patch });

  const handlePickImage = () => {
    const opened = openImagePicker((url, id) => {
      update({ social_image: url, social_image_id: id });
    });
    if (!opened) {
      alert("Media library is not available. Paste an image URL instead.");
    }
  };

  const displayTitle = titleValue || pageTitle || "Page title";
  const displayDesc = descValue || "Add a meta description to improve search snippets.";
  const displayImage = seo.social_image;

  return (
    <div className="epb-seo-panel">
      <div className="epb-seo-panel__header">
        <div className="epb-seo-panel__header-main">
          <h3>SEO Settings</h3>
          <p>Optimize how this page appears in search and social shares.</p>
          <div className={`epb-seo-score epb-seo-score--${score >= 5 ? "good" : score >= 3 ? "ok" : "low"}`}>
            <span className="epb-seo-score__value">{score}/{scoreItems.length}</span>
            <span className="epb-seo-score__label">SEO score</span>
          </div>
        </div>
        <button type="button" className="epb-btn epb-btn--primary" disabled={isSaving} onClick={onSave}>
          {isSaving ? "Saving…" : "Save SEO"}
        </button>
      </div>

      <div className="epb-seo-panel__grid">
        <section className="epb-seo-section">
          <label className="epb-seo-field">
            <span className="epb-seo-field__label">
              Meta title
              <span className={`epb-seo-char epb-seo-char--${titleStatus}`}>
                {(titleValue.length || pageTitle.length)}/60
              </span>
            </span>
            <input
              type="text"
              value={seo.meta_title}
              placeholder={pageTitle || "Defaults to page title"}
              onChange={(e) => update({ meta_title: e.target.value })}
            />
            <span className="epb-seo-hint">Shown in browser tab and Google results. Ideal: 50–60 characters.</span>
          </label>

          <label className="epb-seo-field">
            <span className="epb-seo-field__label">
              Meta description
              <span className={`epb-seo-char epb-seo-char--${descStatus}`}>{descLen}/160</span>
            </span>
            <textarea
              rows={4}
              value={seo.meta_description}
              placeholder="Write a compelling summary for search engines..."
              onChange={(e) => update({ meta_description: e.target.value })}
            />
            <span className="epb-seo-hint">Ideal: 120–160 characters. Appears under your title in search results.</span>
          </label>

          <label className="epb-seo-field">
            <span className="epb-seo-field__label">Focus keyword (ranking keyword)</span>
            <input
              type="text"
              value={seo.focus_keyword}
              placeholder="e.g. coffee shop downtown"
              onChange={(e) => update({ focus_keyword: e.target.value })}
            />
            <span className="epb-seo-hint">The main phrase you want this page to rank for.</span>
          </label>
        </section>

        <section className="epb-seo-section">
          <div className="epb-seo-field">
            <span className="epb-seo-field__label">Social sharing image</span>
            {displayImage ? (
              <div className="epb-seo-image-preview">
                <img src={displayImage} alt="" />
                <button type="button" className="epb-btn epb-btn--ghost epb-btn--sm" onClick={() => update({ social_image: "", social_image_id: 0 })}>
                  Remove
                </button>
              </div>
            ) : (
              <div className="epb-seo-image-empty">No image — social shares may use a generic preview.</div>
            )}
            <div className="epb-seo-image-actions">
              <button type="button" className="epb-btn epb-btn--ghost epb-btn--sm" onClick={handlePickImage}>
                Choose from library
              </button>
            </div>
            <input
              type="url"
              value={seo.social_image}
              placeholder="https://example.com/image.jpg"
              onChange={(e) => update({ social_image: e.target.value, social_image_id: 0 })}
            />
            <span className="epb-seo-hint">Recommended: 1200×630px for Facebook, X, and LinkedIn.</span>
          </div>

          <div className="epb-seo-preview">
            <span className="epb-seo-preview__label">Search preview</span>
            <div className="epb-seo-snippet">
              <div className="epb-seo-snippet__title">{displayTitle}</div>
              <div className="epb-seo-snippet__url">{permalink || "yoursite.com/page"}</div>
              <div className="epb-seo-snippet__desc">{displayDesc}</div>
            </div>
          </div>

          <div className="epb-seo-preview">
            <span className="epb-seo-preview__label">Social preview</span>
            <div className="epb-seo-social">
              {displayImage && <img className="epb-seo-social__img" src={displayImage} alt="" />}
              <div className="epb-seo-social__body">
                <div className="epb-seo-social__title">{displayTitle}</div>
                <div className="epb-seo-social__desc">{displayDesc}</div>
              </div>
            </div>
          </div>

          <ul className="epb-seo-checklist">
            {scoreItems.map((item) => (
              <li key={item.label} className={item.ok ? "epb-seo-checklist__item--ok" : ""}>
                {item.ok ? "✓" : "○"} {item.label}
              </li>
            ))}
          </ul>
        </section>
      </div>
    </div>
  );
}
