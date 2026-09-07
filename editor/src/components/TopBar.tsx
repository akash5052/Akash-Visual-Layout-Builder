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
    <header className="av-web-studio-topbar av-web-studio-slide-down">
      <div className="av-web-studio-topbar__left">
        <Logo />
        {variant === "content-edit" && onBack && (
          <button type="button" className="av-web-studio-btn av-web-studio-btn--ghost av-web-studio-btn--sm av-web-studio-topbar__back" onClick={onBack}>
            ← Back
          </button>
        )}
        {variant === "workspace" && onBack && (
          <button type="button" className="av-web-studio-btn av-web-studio-btn--ghost av-web-studio-btn--sm av-web-studio-topbar__back" onClick={onBack}>
            ← Back
          </button>
        )}
        {variant === "workspace" && workspaceTitle && (
          <span className="av-web-studio-topbar__workspace-title">{workspaceTitle}</span>
        )}
      </div>

      <div className="av-web-studio-topbar__center">
        {variant === "content-edit" && (
          <>
            <span className={`av-web-studio-type-badge av-web-studio-type-badge--${postType}`}>{typeLabel(postType)}</span>
            <input
              className="av-web-studio-title-input"
              value={pageTitle}
              onChange={(e) => onTitleChange?.(e.target.value)}
              placeholder={`${typeLabel(postType)} title`}
            />
            {pageStatus && <span className={`av-web-studio-status av-web-studio-status--${pageStatus}`}>{pageStatus}</span>}
            {isSaving && <span className="av-web-studio-saving">Saving...</span>}
          </>
        )}
        {variant === "workspace" && isSaving && <span className="av-web-studio-saving">Saving...</span>}
      </div>

      <div className="av-web-studio-topbar__right">
        {onDevicePreviewChange && (
          <>
            <DeviceSwitcher value={devicePreview} onChange={onDevicePreviewChange} />
            <span className="av-web-studio-topbar__divider" aria-hidden="true" />
          </>
        )}

        <select
          className="av-web-studio-select av-web-studio-theme-select"
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
            <button type="button" className="av-web-studio-btn av-web-studio-btn--ghost" onClick={onFullPagePreview} title="Preview changes">
              Preview changes
            </button>
            <button type="button" className="av-web-studio-btn av-web-studio-btn--publish" onClick={onPublish} disabled={isPublishing}>
              {isPublishing ? "Publishing..." : "Publish"}
            </button>
          </>
        )}

        {variant === "workspace" && onPublish && (
          <button type="button" className="av-web-studio-btn av-web-studio-btn--publish" onClick={onPublish} disabled={isPublishing}>
            {isPublishing ? "Publishing..." : "Publish"}
          </button>
        )}
      </div>
    </header>
  );
}
