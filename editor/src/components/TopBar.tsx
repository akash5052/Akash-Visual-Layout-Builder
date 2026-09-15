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
    <header className="akash-visual-layout-builder-topbar akash-visual-layout-builder-slide-down">
      <div className="akash-visual-layout-builder-topbar__left">
        <Logo />
        {variant === "content-edit" && onBack && (
          <button type="button" className="akash-visual-layout-builder-btn akash-visual-layout-builder-btn--ghost akash-visual-layout-builder-btn--sm akash-visual-layout-builder-topbar__back" onClick={onBack}>
            ← Back
          </button>
        )}
        {variant === "workspace" && onBack && (
          <button type="button" className="akash-visual-layout-builder-btn akash-visual-layout-builder-btn--ghost akash-visual-layout-builder-btn--sm akash-visual-layout-builder-topbar__back" onClick={onBack}>
            ← Back
          </button>
        )}
        {variant === "workspace" && workspaceTitle && (
          <span className="akash-visual-layout-builder-topbar__workspace-title">{workspaceTitle}</span>
        )}
      </div>

      <div className="akash-visual-layout-builder-topbar__center">
        {variant === "content-edit" && (
          <>
            <span className={`akash-visual-layout-builder-type-badge akash-visual-layout-builder-type-badge--${postType}`}>{typeLabel(postType)}</span>
            <input
              className="akash-visual-layout-builder-title-input"
              value={pageTitle}
              onChange={(e) => onTitleChange?.(e.target.value)}
              placeholder={`${typeLabel(postType)} title`}
            />
            {pageStatus && <span className={`akash-visual-layout-builder-status akash-visual-layout-builder-status--${pageStatus}`}>{pageStatus}</span>}
            {isSaving && <span className="akash-visual-layout-builder-saving">Saving...</span>}
          </>
        )}
        {variant === "workspace" && isSaving && <span className="akash-visual-layout-builder-saving">Saving...</span>}
      </div>

      <div className="akash-visual-layout-builder-topbar__right">
        {onDevicePreviewChange && (
          <>
            <DeviceSwitcher value={devicePreview} onChange={onDevicePreviewChange} />
            <span className="akash-visual-layout-builder-topbar__divider" aria-hidden="true" />
          </>
        )}

        <select
          className="akash-visual-layout-builder-select akash-visual-layout-builder-theme-select"
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
            <button type="button" className="akash-visual-layout-builder-btn akash-visual-layout-builder-btn--ghost" onClick={onFullPagePreview} title="Preview changes">
              Preview changes
            </button>
            <button type="button" className="akash-visual-layout-builder-btn akash-visual-layout-builder-btn--publish" onClick={onPublish} disabled={isPublishing}>
              {isPublishing ? "Publishing..." : "Publish"}
            </button>
          </>
        )}

        {variant === "workspace" && onPublish && (
          <button type="button" className="akash-visual-layout-builder-btn akash-visual-layout-builder-btn--publish" onClick={onPublish} disabled={isPublishing}>
            {isPublishing ? "Publishing..." : "Publish"}
          </button>
        )}
      </div>
    </header>
  );
}
