import { useEffect, useState } from "react";
import { fetchPostMetaLists } from "../api/wordpress";
import type { ContentPostType, PageTemplate, PostMetaLists, PostOptions, TaxonomyCategory } from "../types";
import { openImagePicker } from "../utils/mediaPicker";

interface PostOptionsPanelProps {
  postType: ContentPostType;
  options: PostOptions;
  pageId: number;
  pageTitle?: string;
  onTitleChange?: (title: string) => void;
  onChange: (options: PostOptions) => void;
}

const STATUS_OPTIONS = [
  { value: "draft", label: "Draft" },
  { value: "publish", label: "Published" },
  { value: "pending", label: "Pending review" },
  { value: "private", label: "Private" },
];

const PAGE_TEMPLATE_OPTIONS: {
  value: PageTemplate;
  label: string;
  description: string;
}[] = [
  {
    value: "default",
    label: "Default",
    description: "Theme header and footer with Akash Visual Layout Builder content in the content area.",
  },
  {
    value: "akash-visual-layout-builder-full-width",
    label: "Akash Visual Layout Builder Full Width",
    description: "Stretch content edge-to-edge while keeping the theme header and footer.",
  },
  {
    value: "akash-visual-layout-builder-canvas",
    label: "Akash Visual Layout Builder Canvas",
    description: "No header, no footer — just your Akash Visual Layout Builder page content.",
  },
  {
    value: "theme",
    label: "Theme",
    description: "Use your theme layout with minimal Akash Visual Layout Builder layout overrides.",
  },
];

function CategoryTree({
  categories,
  selected,
  parentId = 0,
  depth = 0,
  onToggle,
}: {
  categories: TaxonomyCategory[];
  selected: number[];
  parentId?: number;
  depth?: number;
  onToggle: (id: number) => void;
}) {
  const items = categories.filter((c) => c.parent === parentId);

  if (items.length === 0) return null;

  return (
    <ul className="akash-visual-layout-builder-post-categories" style={{ marginLeft: depth ? 12 : 0 }}>
      {items.map((cat) => (
        <li key={cat.id}>
          <label className="akash-visual-layout-builder-post-categories__item">
            <input
              type="checkbox"
              checked={selected.includes(cat.id)}
              onChange={() => onToggle(cat.id)}
            />
            <span>{cat.name}</span>
          </label>
          <CategoryTree
            categories={categories}
            selected={selected}
            parentId={cat.id}
            depth={depth + 1}
            onToggle={onToggle}
          />
        </li>
      ))}
    </ul>
  );
}

function ToggleField({
  label,
  checked,
  onChange,
  hint,
}: {
  label: string;
  checked: boolean;
  onChange: (next: boolean) => void;
  hint?: string;
}) {
  return (
    <label className="akash-visual-layout-builder-post-toggle">
      <span className="akash-visual-layout-builder-post-toggle__copy">
        <span className="akash-visual-layout-builder-seo-field__label">{label}</span>
        {hint ? <span className="akash-visual-layout-builder-seo-hint">{hint}</span> : null}
      </span>
      <span className="akash-visual-layout-builder-post-toggle__control">
        <input type="checkbox" checked={checked} onChange={(e) => onChange(e.target.checked)} />
        <span>{checked ? "Yes" : "No"}</span>
      </span>
    </label>
  );
}

function FeaturedImageField({
  url,
  onPick,
  onRemove,
}: {
  url: string;
  onPick: () => void;
  onRemove: () => void;
}) {
  return (
    <div className="akash-visual-layout-builder-seo-field">
      <span className="akash-visual-layout-builder-seo-field__label">Featured image</span>
      {url ? (
        <div className="akash-visual-layout-builder-seo-image-preview">
          <img src={url} alt="" />
          <button type="button" className="akash-visual-layout-builder-btn akash-visual-layout-builder-btn--ghost akash-visual-layout-builder-btn--sm" onClick={onRemove}>
            Remove
          </button>
        </div>
      ) : (
        <button type="button" className="akash-visual-layout-builder-post-featured-empty" onClick={onPick}>
          <span aria-hidden="true">+</span>
          <span>Set featured image</span>
        </button>
      )}
    </div>
  );
}

export function PostOptionsPanel({
  postType,
  options,
  pageId,
  pageTitle = "",
  onTitleChange,
  onChange,
}: PostOptionsPanelProps) {
  const [lists, setLists] = useState<PostMetaLists>({
    categories: [],
    tags: [],
    post_types: [],
    parent_pages: [],
  });
  const [tagsInput, setTagsInput] = useState(options.tag_names.join(", "));

  useEffect(() => {
    fetchPostMetaLists(pageId).then(setLists).catch(() => {});
  }, [pageId]);

  useEffect(() => {
    setTagsInput(options.tag_names.join(", "));
  }, [options.tag_names]);

  const update = (patch: Partial<PostOptions>) => onChange({ ...options, ...patch });

  const toggleCategory = (id: number) => {
    const ids = options.category_ids.includes(id)
      ? options.category_ids.filter((x) => x !== id)
      : [...options.category_ids, id];
    update({ category_ids: ids });
  };

  const commitTags = () => {
    const names = tagsInput
      .split(",")
      .map((t) => t.trim())
      .filter(Boolean);
    update({ tag_names: names });
  };

  const handleFeaturedImage = () => {
    const opened = openImagePicker((url, id) => {
      update({ featured_image_id: id, featured_image_url: url });
    });
    if (!opened) alert("Media library is not available. Set featured image from WordPress admin.");
  };

  const isPost = postType === "post";
  const isPage = postType === "page";
  const selectedTemplate = PAGE_TEMPLATE_OPTIONS.find((item) => item.value === options.page_template);

  return (
    <div className="akash-visual-layout-builder-post-options">
      <div className="akash-visual-layout-builder-post-options__header">
        <h3>{isPost ? "Post Settings" : "Page Settings"}</h3>
        <p>General WordPress settings for this {isPost ? "post" : "page"}.</p>
      </div>

      <div className="akash-visual-layout-builder-post-options__columns">
        <section className="akash-visual-layout-builder-post-options__col">
          <h4 className="akash-visual-layout-builder-post-options__section-title">
            {isPage ? "Content & status" : "General settings"}
          </h4>

          <label className="akash-visual-layout-builder-seo-field">
            <span className="akash-visual-layout-builder-seo-field__label">Title</span>
            <input
              type="text"
              value={pageTitle}
              placeholder="Page title"
              onChange={(e) => onTitleChange?.(e.target.value)}
            />
          </label>

          <label className="akash-visual-layout-builder-seo-field">
            <span className="akash-visual-layout-builder-seo-field__label">Status</span>
            <select value={options.status} onChange={(e) => update({ status: e.target.value })}>
              {STATUS_OPTIONS.map((opt) => (
                <option key={opt.value} value={opt.value}>
                  {opt.label}
                </option>
              ))}
            </select>
          </label>

          <FeaturedImageField
            url={options.featured_image_url}
            onPick={handleFeaturedImage}
            onRemove={() => update({ featured_image_id: 0, featured_image_url: "" })}
          />

          <label className="akash-visual-layout-builder-seo-field">
            <span className="akash-visual-layout-builder-seo-field__label">Order</span>
            <input
              type="number"
              min={0}
              step={1}
              value={options.menu_order}
              onChange={(e) => update({ menu_order: Number(e.target.value) || 0 })}
            />
          </label>

          <ToggleField
            label="Allow comments"
            checked={options.comment_status === "open"}
            onChange={(open) => update({ comment_status: open ? "open" : "closed" })}
          />

          {isPost ? (
            <>
              <label className="akash-visual-layout-builder-seo-field">
                <span className="akash-visual-layout-builder-seo-field__label">URL slug</span>
                <input
                  type="text"
                  value={options.slug}
                  placeholder="auto-generated-from-title"
                  onChange={(e) => update({ slug: e.target.value })}
                />
              </label>

              <label className="akash-visual-layout-builder-seo-field">
                <span className="akash-visual-layout-builder-seo-field__label">Excerpt</span>
                <textarea
                  rows={4}
                  value={options.excerpt}
                  placeholder="Short summary for archives and search..."
                  onChange={(e) => update({ excerpt: e.target.value })}
                />
              </label>
            </>
          ) : null}
        </section>

        <section className="akash-visual-layout-builder-post-options__col">
          <h4 className="akash-visual-layout-builder-post-options__section-title">
            {isPage ? "Layout & publishing" : "Taxonomy"}
          </h4>

          {isPage ? (
            <>
              <ToggleField
                label="Hide title"
                checked={options.hide_title}
                onChange={(hide_title) => update({ hide_title })}
                hint="Hide the theme page title on the front end."
              />

              <label className="akash-visual-layout-builder-seo-field">
                <span className="akash-visual-layout-builder-seo-field__label">Page layout</span>
                <select
                  value={options.page_template}
                  onChange={(e) => update({ page_template: e.target.value as PageTemplate })}
                >
                  {PAGE_TEMPLATE_OPTIONS.map((opt) => (
                    <option key={opt.value} value={opt.value}>
                      {opt.label}
                    </option>
                  ))}
                </select>
                {selectedTemplate ? (
                  <span className="akash-visual-layout-builder-seo-hint akash-visual-layout-builder-seo-hint--italic">{selectedTemplate.description}</span>
                ) : null}
              </label>

              <label className="akash-visual-layout-builder-seo-field">
                <span className="akash-visual-layout-builder-seo-field__label">URL slug</span>
                <input
                  type="text"
                  value={options.slug}
                  placeholder="auto-generated-from-title"
                  onChange={(e) => update({ slug: e.target.value })}
                />
              </label>

              <label className="akash-visual-layout-builder-seo-field">
                <span className="akash-visual-layout-builder-seo-field__label">Excerpt</span>
                <textarea
                  rows={4}
                  value={options.excerpt}
                  placeholder="Short summary for archives and search..."
                  onChange={(e) => update({ excerpt: e.target.value })}
                />
              </label>

              <label className="akash-visual-layout-builder-seo-field">
                <span className="akash-visual-layout-builder-seo-field__label">Parent page</span>
                <select
                  value={options.parent_id}
                  onChange={(e) => update({ parent_id: Number(e.target.value) })}
                >
                  {lists.parent_pages.map((p) => (
                    <option key={p.id} value={p.id}>
                      {p.title}
                    </option>
                  ))}
                </select>
              </label>
            </>
          ) : (
            <>
              <div className="akash-visual-layout-builder-seo-field">
                <span className="akash-visual-layout-builder-seo-field__label">Categories</span>
                {lists.categories.length === 0 ? (
                  <p className="akash-visual-layout-builder-seo-hint">
                    No categories yet. Create them under Posts → Categories in WordPress.
                  </p>
                ) : (
                  <div className="akash-visual-layout-builder-post-categories-wrap">
                    <CategoryTree
                      categories={lists.categories}
                      selected={options.category_ids}
                      onToggle={toggleCategory}
                    />
                  </div>
                )}
              </div>

              <label className="akash-visual-layout-builder-seo-field">
                <span className="akash-visual-layout-builder-seo-field__label">Tags</span>
                <input
                  type="text"
                  value={tagsInput}
                  placeholder="design, tutorial, news"
                  onChange={(e) => setTagsInput(e.target.value)}
                  onBlur={commitTags}
                  onKeyDown={(e) => {
                    if (e.key === "Enter") {
                      e.preventDefault();
                      commitTags();
                    }
                  }}
                />
                <span className="akash-visual-layout-builder-seo-hint">Comma-separated. Press Enter or click away to save.</span>
              </label>
            </>
          )}
        </section>
      </div>
    </div>
  );
}
