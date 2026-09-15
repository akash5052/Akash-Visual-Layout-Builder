import { useEffect, useState, type ReactNode } from "react";
import { quickEditPage, quickEditPopup } from "../api/wordpress";
import type { ContentPostType, StudioPopup, PageSummary } from "../types";

interface ModalShellProps {
  title: string;
  subtitle?: string;
  onClose: () => void;
  children: ReactNode;
  footer: ReactNode;
}

function ModalShell({ title, subtitle, onClose, children, footer }: ModalShellProps) {
  useEffect(() => {
    const onKeyDown = (event: KeyboardEvent) => {
      if (event.key === "Escape") {
        onClose();
      }
    };
    window.addEventListener("keydown", onKeyDown);
    return () => window.removeEventListener("keydown", onKeyDown);
  }, [onClose]);

  return (
    <div className="akash-visual-layout-builder-quick-edit" role="presentation" onClick={onClose}>
      <div
        className="akash-visual-layout-builder-quick-edit__dialog"
        role="dialog"
        aria-modal="true"
        aria-labelledby="akash-visual-layout-builder-quick-edit-title"
        onClick={(event) => event.stopPropagation()}
      >
        <header className="akash-visual-layout-builder-quick-edit__header">
          <div>
            <h2 id="akash-visual-layout-builder-quick-edit-title" className="akash-visual-layout-builder-quick-edit__title">
              {title}
            </h2>
            {subtitle && <p className="akash-visual-layout-builder-quick-edit__subtitle">{subtitle}</p>}
          </div>
          <button type="button" className="akash-visual-layout-builder-quick-edit__close" onClick={onClose} aria-label="Close">
            ×
          </button>
        </header>
        <div className="akash-visual-layout-builder-quick-edit__body">{children}</div>
        <footer className="akash-visual-layout-builder-quick-edit__footer">{footer}</footer>
      </div>
    </div>
  );
}

function normalizeContentStatus(status: string): "publish" | "draft" {
  return status === "publish" ? "publish" : "draft";
}

interface ContentQuickEditModalProps {
  item: PageSummary;
  postType: ContentPostType;
  onClose: () => void;
  onSaved: () => void;
  onNotice: (type: "success" | "error", message: string) => void;
}

function slugFromItem(item: PageSummary): string {
  if (item.slug) {
    return item.slug;
  }
  try {
    const parts = new URL(item.permalink).pathname.split("/").filter(Boolean);
    return parts[parts.length - 1] || "";
  } catch {
    return "";
  }
}

interface CreateContentModalProps {
  postType: ContentPostType;
  onClose: () => void;
  onCreate: (title: string) => Promise<void>;
}

export function CreateContentModal({ postType, onClose, onCreate }: CreateContentModalProps) {
  const singular = postType === "post" ? "Post" : "Page";
  const [title, setTitle] = useState(postType === "post" ? "New Post" : "New Page");
  const [creating, setCreating] = useState(false);

  const handleClose = () => {
    if (!creating) {
      onClose();
    }
  };

  const handleCreate = async () => {
    const trimmed = title.trim();
    if (!trimmed || creating) return;

    setCreating(true);
    try {
      await onCreate(trimmed);
    } catch {
      // Parent surfaces the error notice; keep the modal open for retry.
    } finally {
      setCreating(false);
    }
  };

  return (
    <ModalShell
      title={`New ${singular}`}
      subtitle={`Create a draft ${singular.toLowerCase()} and open it in Akash Visual Layout Builder.`}
      onClose={handleClose}
      footer={
        <>
          <button type="button" className="akash-visual-layout-builder-btn akash-visual-layout-builder-btn--ghost" onClick={handleClose} disabled={creating}>
            Cancel
          </button>
          <button
            type="button"
            className="akash-visual-layout-builder-btn akash-visual-layout-builder-btn--primary"
            onClick={handleCreate}
            disabled={creating || !title.trim()}
          >
            {creating ? "Creating…" : `Create ${singular}`}
          </button>
        </>
      }
    >
      <label className="akash-visual-layout-builder-quick-edit__field">
        <span>Title</span>
        <input
          type="text"
          value={title}
          onChange={(e) => setTitle(e.target.value)}
          onKeyDown={(e) => {
            if (e.key === "Enter") {
              e.preventDefault();
              void handleCreate();
            }
          }}
          autoFocus
          disabled={creating}
        />
        <span className="akash-visual-layout-builder-quick-edit__hint">You can change this later in the builder.</span>
      </label>
    </ModalShell>
  );
}

export function ContentQuickEditModal({
  item,
  postType,
  onClose,
  onSaved,
  onNotice,
}: ContentQuickEditModalProps) {
  const singular = postType === "post" ? "Post" : "Page";
  const [title, setTitle] = useState(item.title);
  const [status, setStatus] = useState<"publish" | "draft">(normalizeContentStatus(item.status));
  const [slug, setSlug] = useState(() => slugFromItem(item));
  const [saving, setSaving] = useState(false);

  const handleSave = async () => {
    const trimmedTitle = title.trim();
    const trimmedSlug = slug.trim();
    if (!trimmedTitle) {
      onNotice("error", "Title is required.");
      return;
    }
    if (!trimmedSlug) {
      onNotice("error", "Slug is required.");
      return;
    }

    setSaving(true);
    try {
      const result = await quickEditPage(item.id, {
        title: trimmedTitle,
        status,
        slug: trimmedSlug,
      });
      onNotice("success", result.message);
      onSaved();
      onClose();
    } catch (err) {
      onNotice("error", err instanceof Error ? err.message : "Quick edit failed");
    } finally {
      setSaving(false);
    }
  };

  return (
    <ModalShell
      title={`Quick Edit ${singular}`}
      subtitle="Update basic details without opening the full builder."
      onClose={onClose}
      footer={
        <>
          <button type="button" className="akash-visual-layout-builder-btn akash-visual-layout-builder-btn--ghost" onClick={onClose} disabled={saving}>
            Cancel
          </button>
          <button type="button" className="akash-visual-layout-builder-btn akash-visual-layout-builder-btn--primary" onClick={handleSave} disabled={saving}>
            {saving ? "Updating…" : "Update"}
          </button>
        </>
      }
    >
      <label className="akash-visual-layout-builder-quick-edit__field">
        <span>Title</span>
        <input type="text" value={title} onChange={(e) => setTitle(e.target.value)} autoFocus />
      </label>
      <label className="akash-visual-layout-builder-quick-edit__field">
        <span>Status</span>
        <select value={status} onChange={(e) => setStatus(e.target.value as "publish" | "draft")}>
          <option value="publish">Published</option>
          <option value="draft">Draft</option>
        </select>
      </label>
      <label className="akash-visual-layout-builder-quick-edit__field">
        <span>Slug</span>
        <input type="text" value={slug} onChange={(e) => setSlug(e.target.value)} spellCheck={false} />
        <span className="akash-visual-layout-builder-quick-edit__hint">URL segment for this {postType === "post" ? "post" : "page"}.</span>
      </label>
    </ModalShell>
  );
}

interface PopupQuickEditModalProps {
  popup: StudioPopup;
  onClose: () => void;
  onSaved: () => void;
  onNotice: (type: "success" | "error", message: string) => void;
}

export function PopupQuickEditModal({ popup, onClose, onSaved, onNotice }: PopupQuickEditModalProps) {
  const [name, setName] = useState(popup.name);
  const [status, setStatus] = useState<"publish" | "draft">(
    popup.status === "publish" ? "publish" : "draft"
  );
  const [saving, setSaving] = useState(false);

  const handleSave = async () => {
    const trimmedName = name.trim();
    if (!trimmedName) {
      onNotice("error", "Name is required.");
      return;
    }

    setSaving(true);
    try {
      const result = await quickEditPopup(popup.id, { name: trimmedName, status });
      onNotice("success", result.message);
      onSaved();
      onClose();
    } catch (err) {
      onNotice("error", err instanceof Error ? err.message : "Quick edit failed");
    } finally {
      setSaving(false);
    }
  };

  return (
    <ModalShell
      title="Quick Edit Popup"
      subtitle="Change the popup name and visibility."
      onClose={onClose}
      footer={
        <>
          <button type="button" className="akash-visual-layout-builder-btn akash-visual-layout-builder-btn--ghost" onClick={onClose} disabled={saving}>
            Cancel
          </button>
          <button type="button" className="akash-visual-layout-builder-btn akash-visual-layout-builder-btn--primary" onClick={handleSave} disabled={saving}>
            {saving ? "Updating…" : "Update"}
          </button>
        </>
      }
    >
      <label className="akash-visual-layout-builder-quick-edit__field">
        <span>Name</span>
        <input type="text" value={name} onChange={(e) => setName(e.target.value)} autoFocus />
      </label>
      <label className="akash-visual-layout-builder-quick-edit__field">
        <span>Status</span>
        <select value={status} onChange={(e) => setStatus(e.target.value as "publish" | "draft")}>
          <option value="publish">Published (enabled)</option>
          <option value="draft">Draft (disabled)</option>
        </select>
      </label>
    </ModalShell>
  );
}
