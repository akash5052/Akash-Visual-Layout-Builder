import type { PostsCarouselWidget, PostsGridWidget, PostsListWidget, PostsWidgetDisplay, PostsWidgetQuery } from "./types";

export type PostsWidget = PostsGridWidget | PostsListWidget | PostsCarouselWidget;

export const DEFAULT_POSTS_QUERY: PostsWidgetQuery = {
  postType: "post",
  categoryIds: [],
  tagSlugs: [],
  orderBy: "date",
  order: "DESC",
  postsPerPage: 6,
  offset: 0,
  excludeCurrent: true,
};

export const DEFAULT_POSTS_DISPLAY: PostsWidgetDisplay = {
  showImage: true,
  showTitle: true,
  showExcerpt: true,
  showMeta: true,
  showAuthor: true,
  showDate: true,
  showCategories: true,
  showReadMore: true,
  readMoreText: "Read more",
  excerptLength: 22,
  imageSize: "medium",
  columns: 3,
  gap: "24px",
  titleTag: "h3",
  imageRatio: "16/9",
  cardStyle: "card",
  listImageWidth: "140px",
  carouselHeight: "360px",
  metaDateFormat: "",
};

export function postsWidgetLayout(type: PostsWidget["type"]): "grid" | "list" | "carousel" {
  if (type === "posts-list") return "list";
  if (type === "posts-carousel") return "carousel";
  return "grid";
}

export function buildPostsWidgetPayload(widget: PostsWidget) {
  return {
    layout: postsWidgetLayout(widget.type),
    query: widget.query,
    display: widget.display,
  };
}

export function escAttr(value: string): string {
  return value
    .replace(/&/g, "&amp;")
    .replace(/"/g, "&quot;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;");
}
