import type { EditorTheme } from "../hooks/useEditorTheme";
import type { DevicePreview } from "../devicePreview";
import { DeviceSwitcher } from "./DeviceSwitcher";
import { Logo } from "./Logo";

interface TopBarProps {
  variant: "minimal" | "content-edit" | "workspace";
  pageTitle?: string;
  pageStatus?: string;
  postType?: string;
  isSaving?: boolean;
  isPublishing?: boolean;
  theme: EditorTheme;
  workspaceTitle?: string;
  devicePreview?: DevicePreview;
  onThemeChange: (theme: EditorTheme) => void;
  onBack?: () => void;
  onTitleChange?: (title: string) => void;
  onFullPagePreview?: () => void;
  onPublish?: () => void;
  onDevicePreviewChange?: (device: DevicePreview) => void;
}

function typeLabel(postType?: string) {
  return postType === "post" ? "Post" : "Page";
}

export function TopBar({
  variant,
  pageTitle = "",
  pageStatus = "",
  postType = "page",
  isSaving = false,
  isPublishing = false,
  theme,
  workspaceTitle,
  devicePreview = "desktop",
  onThemeChange,
  onBack,
  onTitleChange,
  onFullPagePreview,
  onPublish,
  onDevicePreviewChange,
}: TopBarProps) {
  return (
    <header className="epb-topbar epb-slide-down">
      <div className="epb-topbar__left">
        <Logo />
        {variant === "content-edit" && onBack && (
          <button type="button" className="epb-btn epb-btn--ghost epb-btn--sm epb-topbar__back" onClick={onBack}>
            ← Back
          </button>
        )}
        {variant === "workspace" && onBack && (
          <button type="button" className="epb-btn epb-btn--ghost epb-btn--sm epb-topbar__back" onClick={onBack}>
            ← Back
          </button>
        )}
        {variant === "workspace" && workspaceTitle && (
          <span className="epb-topbar__workspace-title">{workspaceTitle}</span>
        )}
      </div>

      <div className="epb-topbar__center">
        {variant === "content-edit" && (
          <>
            <span className={`epb-type-badge epb-type-badge--${postType}`}>{typeLabel(postType)}</span>
            <input
              className="epb-title-input"
              value={pageTitle}
              onChange={(e) => onTitleChange?.(e.target.value)}
              placeholder={`${typeLabel(postType)} title`}
            />
            {pageStatus && <span className={`epb-status epb-status--${pageStatus}`}>{pageStatus}</span>}
            {isSaving && <span className="epb-saving">Saving...</span>}
          </>
        )}
        {variant === "workspace" && isSaving && <span className="epb-saving">Saving...</span>}
      </div>

      <div className="epb-topbar__right">
        {onDevicePreviewChange && (
          <>
            <DeviceSwitcher value={devicePreview} onChange={onDevicePreviewChange} />
            <span className="epb-topbar__divider" aria-hidden="true" />
          </>
        )}

        <select
          className="epb-select epb-theme-select"
          value={theme}
          onChange={(e) => onThemeChange(e.target.value as EditorTheme)}
          title="Editor theme"
          aria-label="Editor theme"
        >
          <option value="system">System theme</option>
          <option value="light">Light theme</option>
          <option value="dark">Dark theme</option>
        </select>

        {variant === "content-edit" && (
          <>
            <button type="button" className="epb-btn epb-btn--ghost" onClick={onFullPagePreview} title="Preview changes">
              Preview changes
            </button>
            <button type="button" className="epb-btn epb-btn--publish" onClick={onPublish} disabled={isPublishing}>
              {isPublishing ? "Publishing..." : "Publish"}
            </button>
          </>
        )}

        {variant === "workspace" && onPublish && (
          <button type="button" className="epb-btn epb-btn--publish" onClick={onPublish} disabled={isPublishing}>
            {isPublishing ? "Publishing..." : "Publish"}
          </button>
        )}
      </div>
    </header>
  );
}
