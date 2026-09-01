import { useCallback, useEffect, useRef, useState } from "react";
import { deleteSvg, fetchSvgs, uploadSvg } from "../api/wordpress";
import type { SvgAsset } from "../types";

interface SvgLibraryViewProps {
  onNotice: (type: "success" | "error", message: string) => void;
}

export function SvgLibraryView({ onNotice }: SvgLibraryViewProps) {
  const [svgs, setSvgs] = useState<SvgAsset[]>([]);
  const [loading, setLoading] = useState(true);
  const [uploading, setUploading] = useState(false);
  const fileInputRef = useRef<HTMLInputElement>(null);

  const load = useCallback(async () => {
    setLoading(true);
    try {
      const { svgs: items } = await fetchSvgs();
      setSvgs(items);
    } catch (err) {
      onNotice("error", err instanceof Error ? err.message : "Failed to load SVG library");
    } finally {
      setLoading(false);
    }
  }, [onNotice]);

  useEffect(() => {
    load();
  }, [load]);

  const handleUpload = async (files: FileList | null) => {
    if (!files?.length) return;
    setUploading(true);
    try {
      for (const file of Array.from(files)) {
        const result = await uploadSvg(file);
        setSvgs((prev) => [result.svg, ...prev.filter((s) => s.id !== result.svg.id)]);
      }
      onNotice("success", "SVG uploaded");
    } catch (err) {
      onNotice("error", err instanceof Error ? err.message : "Upload failed");
    } finally {
      setUploading(false);
      if (fileInputRef.current) fileInputRef.current.value = "";
    }
  };

  const handleDelete = async (id: number) => {
    if (!confirm("Delete this SVG from the media library?")) return;
    try {
      await deleteSvg(id);
      setSvgs((prev) => prev.filter((s) => s.id !== id));
      onNotice("success", "SVG deleted");
    } catch (err) {
      onNotice("error", err instanceof Error ? err.message : "Delete failed");
    }
  };

  const copySnippet = async (url: string, mode: "img" | "url") => {
    const snippet = mode === "img"
      ? `<img src="${url}" alt="" width="120" height="120" />`
      : url;
    try {
      await navigator.clipboard.writeText(snippet);
      onNotice("success", "Copied to clipboard");
    } catch {
      onNotice("error", "Could not copy to clipboard");
    }
  };

  return (
    <div className="epb-screen epb-screen--svg">
      <header className="epb-screen__header">
        <div>
          <h1 className="epb-screen__title">SVG Library</h1>
          <p className="epb-screen__subtitle">
            Upload SVG files and copy snippets into your page HTML. SVG uploads are sanitized for safety.
          </p>
        </div>
        <div className="epb-screen__actions">
          <input
            ref={fileInputRef}
            type="file"
            accept=".svg,image/svg+xml"
            multiple
            hidden
            onChange={(e) => handleUpload(e.target.files)}
          />
          <button
            type="button"
            className="epb-btn epb-btn--primary"
            disabled={uploading}
            onClick={() => fileInputRef.current?.click()}
          >
            {uploading ? "Uploading…" : "+ Upload SVG"}
          </button>
        </div>
      </header>

      {loading ? (
        <div className="epb-screen__empty">
          <div className="epb-spinner" />
        </div>
      ) : svgs.length === 0 ? (
        <div className="epb-screen__empty">
          <p>No SVG files yet. Upload your first icon or illustration.</p>
        </div>
      ) : (
        <div className="epb-svg-grid">
          {svgs.map((svg) => (
            <article key={svg.id} className="epb-svg-card">
              <div className="epb-svg-card__preview">
                <img src={svg.url} alt={svg.title || svg.filename} />
              </div>
              <div className="epb-svg-card__body">
                <h3 className="epb-svg-card__title">{svg.title || svg.filename}</h3>
                <p className="epb-svg-card__meta">{svg.filename}</p>
                <div className="epb-svg-card__actions">
                  <button type="button" className="epb-btn epb-btn--ghost epb-btn--sm" onClick={() => copySnippet(svg.url, "img")}>
                    Copy &lt;img&gt;
                  </button>
                  <button type="button" className="epb-btn epb-btn--ghost epb-btn--sm" onClick={() => copySnippet(svg.url, "url")}>
                    Copy URL
                  </button>
                  <button type="button" className="epb-btn epb-btn--ghost epb-btn--sm" onClick={() => handleDelete(svg.id)}>
                    Delete
                  </button>
                </div>
              </div>
            </article>
          ))}
        </div>
      )}
    </div>
  );
}
