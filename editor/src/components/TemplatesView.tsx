import { useCallback, useEffect, useRef, useState } from "react";

import {
  applyTemplate,
  deleteTemplate,
  fetchTemplates,
  uploadTemplate,
  type StarterTemplate,
} from "../api/wordpress";

interface TemplatesViewProps {
  onNotice: (type: "success" | "error", message: string) => void;
}

export function TemplatesView({ onNotice }: TemplatesViewProps) {
  const [templates, setTemplates] = useState<StarterTemplate[]>([]);
  const [loading, setLoading] = useState(true);
  const [applying, setApplying] = useState<string | null>(null);
  const [uploading, setUploading] = useState(false);
  const [deleting, setDeleting] = useState<string | null>(null);
  const fileInputRef = useRef<HTMLInputElement>(null);

  const load = useCallback(async () => {
    setLoading(true);
    try {
      const result = await fetchTemplates();
      setTemplates(result.templates ?? []);
    } catch (err) {
      onNotice("error", err instanceof Error ? err.message : "Failed to load templates.");
    } finally {
      setLoading(false);
    }
  }, [onNotice]);

  useEffect(() => {
    void load();
  }, [load]);

  const handleUse = async (tpl: StarterTemplate) => {
    if (applying) return;
    setApplying(tpl.id);
    try {
      const page = await applyTemplate(tpl.id, tpl.brand);
      onNotice("success", `Created “${page.title}” from ${tpl.title}. Opening editor…`);
      const editUrl = `${window.epbBuilderData.adminUrls.pages}&page_id=${page.id}`;
      window.location.href = editUrl;
    } catch (err) {
      onNotice("error", err instanceof Error ? err.message : "Could not apply template.");
      setApplying(null);
    }
  };

  const handleUpload = async (files: FileList | null) => {
    if (!files?.length) return;
    setUploading(true);
    try {
      for (const file of Array.from(files)) {
        const result = await uploadTemplate(file);
        setTemplates((prev) => {
          const without = prev.filter((t) => t.id !== result.template.id);
          return [...without, result.template];
        });
      }
      onNotice("success", "Template zip uploaded.");
    } catch (err) {
      onNotice("error", err instanceof Error ? err.message : "Template upload failed.");
    } finally {
      setUploading(false);
      if (fileInputRef.current) {
        fileInputRef.current.value = "";
      }
    }
  };

  const handleDelete = async (tpl: StarterTemplate) => {
    if (!tpl.can_delete || deleting) return;
    if (!confirm(`Delete uploaded template “${tpl.title}”?`)) return;
    setDeleting(tpl.id);
    try {
      await deleteTemplate(tpl.id);
      setTemplates((prev) => prev.filter((t) => t.id !== tpl.id));
      onNotice("success", "Template deleted.");
    } catch (err) {
      onNotice("error", err instanceof Error ? err.message : "Could not delete template.");
    } finally {
      setDeleting(null);
    }
  };

  return (
    <div className="epb-screen epb-screen--templates">
      <header className="epb-screen__header">
        <div>
          <h1 className="epb-screen__title">Templates</h1>
          <p className="epb-screen__subtitle">
            Start from a polished visual landing page — fully editable with sections and widgets. Or upload a zip with{" "}
            <code>manifest.json</code> plus <code>document.json</code>.
          </p>
        </div>
        <div className="epb-screen__actions">
          <input
            ref={fileInputRef}
            type="file"
            accept=".zip,application/zip"
            hidden
            onChange={(e) => void handleUpload(e.target.files)}
          />
          <button
            type="button"
            className="epb-btn epb-btn--primary"
            disabled={uploading}
            onClick={() => fileInputRef.current?.click()}
          >
            {uploading ? "Uploading…" : "+ Upload zip"}
          </button>
        </div>
      </header>

      {loading && <p className="epb-templates__loading">Loading templates…</p>}

      {!loading && templates.length === 0 && (
        <div className="epb-screen__empty">
          <p>No templates yet. Upload a template zip to get started.</p>
        </div>
      )}

      {!loading && templates.length > 0 && (
        <div className="epb-templates-grid">
          {templates.map((tpl) => (
            <article key={tpl.id} className="epb-template-card" style={{ ["--epb-tpl-accent" as string]: tpl.accent }}>
              <div className="epb-template-card__media">
                {tpl.preview ? <img src={tpl.preview} alt="" loading="lazy" /> : <div className="epb-template-card__placeholder" />}
                <span className="epb-template-card__badge">{tpl.brand}</span>
                {tpl.badge && <span className="epb-template-card__source">{tpl.badge}</span>}
                {tpl.source === "uploaded" && !tpl.badge && <span className="epb-template-card__source">Uploaded</span>}
              </div>
              <div className="epb-template-card__body">
                <h2>{tpl.title}</h2>
                <p>{tpl.description}</p>
                <div className="epb-template-card__actions">
                  <button
                    type="button"
                    className="epb-btn epb-btn--primary"
                    disabled={applying === tpl.id}
                    onClick={() => void handleUse(tpl)}
                  >
                    {applying === tpl.id ? "Creating…" : "Use template"}
                  </button>
                  {tpl.can_delete && (
                    <button
                      type="button"
                      className="epb-btn epb-btn--ghost"
                      disabled={deleting === tpl.id}
                      onClick={() => void handleDelete(tpl)}
                    >
                      {deleting === tpl.id ? "Deleting…" : "Delete"}
                    </button>
                  )}
                </div>
              </div>
            </article>
          ))}
        </div>
      )}
    </div>
  );
}
