import { useEffect, useMemo, useState } from "react";

import { fetchPostMetaLists, renderPostsWidget } from "../api/wordpress";
import type { PostMetaLists } from "../types";
import type { PostsCarouselWidget, PostsGridWidget, PostsListWidget } from "../visual/types";
import { buildPostsWidgetPayload } from "../visual/postsWidget";

type PostsWidget = PostsGridWidget | PostsListWidget | PostsCarouselWidget;

function Field({ label, children }: { label: string; children: React.ReactNode }) {
  return (
    <div className="akash-visual-layout-builder-visual__field">
      <span className="akash-visual-layout-builder-visual__field-label">{label}</span>
      {children}
    </div>
  );
}

function SectionTitle({ children }: { children: React.ReactNode }) {
  return <div className="akash-visual-layout-builder-visual__section-title">{children}</div>;
}

export function PostsWidgetFields({
  widget,
  onChange,
  pageId = 0,
}: {
  widget: PostsWidget;
  onChange: (patch: Partial<PostsWidget>) => void;
  pageId?: number;
}) {
  const [lists, setLists] = useState<PostMetaLists>({
    categories: [],
    tags: [],
    post_types: [{ name: "post", label: "Posts" }],
    parent_pages: [],
  });

  useEffect(() => {
    fetchPostMetaLists(pageId).then(setLists).catch(() => {});
  }, [pageId]);

  const query = widget.query;
  const display = widget.display;

  const updateQuery = (patch: Partial<typeof query>) => onChange({ query: { ...query, ...patch } });
  const updateDisplay = (patch: Partial<typeof display>) => onChange({ display: { ...display, ...patch } });

  const categoryOptions = useMemo(
    () =>
      lists.categories.map((cat) => ({
        ...cat,
        label: cat.parent ? `— ${cat.name}` : cat.name,
      })),
    [lists.categories]
  );

  return (
    <>
      <SectionTitle>Query</SectionTitle>
      <Field label="Post type">
        <select value={query.postType} onChange={(e) => updateQuery({ postType: e.target.value })}>
          {(lists.post_types.length ? lists.post_types : [{ name: "post", label: "Posts" }]).map((pt) => (
            <option key={pt.name} value={pt.name}>
              {pt.label}
            </option>
          ))}
        </select>
      </Field>
      <Field label="Categories">
        <select
          multiple
          size={Math.min(6, Math.max(3, categoryOptions.length))}
          value={query.categoryIds.map(String)}
          onChange={(e) => {
            const selected = Array.from(e.target.selectedOptions).map((o) => Number(o.value));
            updateQuery({ categoryIds: selected });
          }}
        >
          {categoryOptions.map((cat) => (
            <option key={cat.id} value={cat.id}>
              {cat.label}
            </option>
          ))}
        </select>
      </Field>
      <Field label="Tags">
        <select
          multiple
          size={Math.min(6, Math.max(3, lists.tags.length || 3))}
          value={query.tagSlugs}
          onChange={(e) => {
            const selected = Array.from(e.target.selectedOptions).map((o) => o.value);
            updateQuery({ tagSlugs: selected });
          }}
        >
          {lists.tags.map((tag) => (
            <option key={tag.id} value={tag.slug}>
              {tag.name}
            </option>
          ))}
        </select>
      </Field>
      <Field label="Order by">
        <select value={query.orderBy} onChange={(e) => updateQuery({ orderBy: e.target.value as typeof query.orderBy })}>
          <option value="date">Date</option>
          <option value="title">Title</option>
          <option value="modified">Last modified</option>
          <option value="comment_count">Comment count</option>
          <option value="menu_order">Menu order</option>
          <option value="rand">Random</option>
        </select>
      </Field>
      <Field label="Order">
        <select value={query.order} onChange={(e) => updateQuery({ order: e.target.value as "ASC" | "DESC" })}>
          <option value="DESC">Descending</option>
          <option value="ASC">Ascending</option>
        </select>
      </Field>
      <Field label="Posts count">
        <input
          type="number"
          min={1}
          max={24}
          value={query.postsPerPage}
          onChange={(e) => updateQuery({ postsPerPage: Math.max(1, Math.min(24, Number(e.target.value) || 6)) })}
        />
      </Field>
      <Field label="Offset">
        <input
          type="number"
          min={0}
          max={100}
          value={query.offset}
          onChange={(e) => updateQuery({ offset: Math.max(0, Number(e.target.value) || 0) })}
        />
      </Field>
      <Field label="Exclude current post">
        <label style={{ display: "flex", alignItems: "center", gap: 8 }}>
          <input
            type="checkbox"
            checked={query.excludeCurrent}
            onChange={(e) => updateQuery({ excludeCurrent: e.target.checked })}
          />
          Skip the page being viewed
        </label>
      </Field>

      <SectionTitle>Display</SectionTitle>
      <Field label="Card style">
        <select
          value={display.cardStyle}
          onChange={(e) => updateDisplay({ cardStyle: e.target.value as typeof display.cardStyle })}
        >
          <option value="default">Default</option>
          <option value="card">Card</option>
          <option value="minimal">Minimal</option>
          <option value="overlay">Overlay</option>
        </select>
      </Field>
      {widget.type === "posts-grid" ? (
        <Field label="Columns">
          <input
            type="number"
            min={1}
            max={6}
            value={display.columns}
            onChange={(e) => updateDisplay({ columns: Math.max(1, Math.min(6, Number(e.target.value) || 3)) })}
          />
        </Field>
      ) : null}
      {widget.type === "posts-list" ? (
        <Field label="Image width">
          <input type="text" value={display.listImageWidth} onChange={(e) => updateDisplay({ listImageWidth: e.target.value })} />
        </Field>
      ) : null}
      {widget.type === "posts-carousel" ? (
        <Field label="Slide height">
          <input type="text" value={display.carouselHeight} onChange={(e) => updateDisplay({ carouselHeight: e.target.value })} />
        </Field>
      ) : null}
      <Field label="Gap">
        <input type="text" value={display.gap} onChange={(e) => updateDisplay({ gap: e.target.value })} />
      </Field>
      <Field label="Image ratio">
        <input type="text" value={display.imageRatio} onChange={(e) => updateDisplay({ imageRatio: e.target.value })} placeholder="16/9" />
      </Field>
      <Field label="Image size">
        <select value={display.imageSize} onChange={(e) => updateDisplay({ imageSize: e.target.value as typeof display.imageSize })}>
          <option value="thumbnail">Thumbnail</option>
          <option value="medium">Medium</option>
          <option value="large">Large</option>
          <option value="full">Full</option>
        </select>
      </Field>
      <Field label="Title tag">
        <select value={display.titleTag} onChange={(e) => updateDisplay({ titleTag: e.target.value as typeof display.titleTag })}>
          <option value="h2">H2</option>
          <option value="h3">H3</option>
          <option value="h4">H4</option>
        </select>
      </Field>
      <Field label="Excerpt length (words)">
        <input
          type="number"
          min={5}
          max={80}
          value={display.excerptLength}
          onChange={(e) => updateDisplay({ excerptLength: Math.max(5, Math.min(80, Number(e.target.value) || 22)) })}
        />
      </Field>
      <Field label="Read more text">
        <input type="text" value={display.readMoreText} onChange={(e) => updateDisplay({ readMoreText: e.target.value })} />
      </Field>
      {(
        [
          ["showImage", "Featured image"],
          ["showTitle", "Title"],
          ["showExcerpt", "Excerpt"],
          ["showMeta", "Meta row"],
          ["showAuthor", "Author"],
          ["showDate", "Date"],
          ["showCategories", "Categories"],
          ["showReadMore", "Read more link"],
        ] as const
      ).map(([key, label]) => (
        <Field key={key} label={label}>
          <label style={{ display: "flex", alignItems: "center", gap: 8 }}>
            <input type="checkbox" checked={display[key]} onChange={(e) => updateDisplay({ [key]: e.target.checked })} />
            Show
          </label>
        </Field>
      ))}
    </>
  );
}

export function PostsWidgetPreview({ widget, pageId = 0 }: { widget: PostsWidget; pageId?: number }) {
  const [html, setHtml] = useState("");
  const [loading, setLoading] = useState(true);
  const payload = useMemo(() => buildPostsWidgetPayload(widget), [widget]);

  useEffect(() => {
    let cancelled = false;
    setLoading(true);
    renderPostsWidget(payload, pageId)
      .then((result) => {
        if (!cancelled) setHtml(result.html);
      })
      .catch(() => {
        if (!cancelled) setHtml('<div class="akash-visual-layout-builder-posts__empty">Unable to load posts preview.</div>');
      })
      .finally(() => {
        if (!cancelled) setLoading(false);
      });
    return () => {
      cancelled = true;
    };
  }, [payload, pageId]);

  if (loading && !html) {
    return <div className="akash-visual-layout-builder-posts__empty">Loading posts…</div>;
  }

  return <div className="akash-visual-layout-builder-visual-widget__compiled" dangerouslySetInnerHTML={{ __html: html }} />;
}
