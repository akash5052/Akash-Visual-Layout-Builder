import { useCallback, useEffect, useState } from "react";
import { fetchAdminSettings, saveAdminSettings } from "../api/wordpress";
import type { PluginAdminSettings } from "../types";

interface SettingsViewProps {
  onNotice: (type: "success" | "error", message: string) => void;
}

const EMPTY: PluginAdminSettings = {
  ai_enabled: true,
  gemini_model: "gemini-2.5-flash",
  claude_model: "claude-haiku-4-5-20251001",
  image_suggestions: "auto",
  is_configured: false,
  has_gemini: false,
  has_claude: false,
  ai_client_available: false,
  ai_client_core: false,
  connectors_url: "",
  has_smart_engine: true,
  images_enabled: false,
  image_width: 1200,
  image_height: 700,
  images_replace_broken: true,
  default_list_status: "publish",
  default_preview: true,
  default_theme: "system",
  autosave_delay_ms: 1500,
  ai_history_turns: 40,
  ai_panel_default_open: false,
  seo_defer_to_plugins: true,
  seo_meta_template: "",
  seo_default_og_image: "",
  popups_enabled: true,
  layout_header_default: true,
  layout_footer_default: true,
  google_fonts_enabled: false,
  svg_max_kb: 512,
  can_manage_settings: true,
  ai_capability: "manage_options",
  builder_capability: "edit_posts",
  settings_capability: "manage_options",
  available_gemini_models: [],
  available_claude_models: [],
};

export function SettingsView({ onNotice }: SettingsViewProps) {
  const [settings, setSettings] = useState<PluginAdminSettings>(EMPTY);
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);

  const load = useCallback(async () => {
    setLoading(true);
    try {
      const data = await fetchAdminSettings();
      setSettings({ ...EMPTY, ...data });
    } catch (err) {
      onNotice("error", err instanceof Error ? err.message : "Failed to load settings");
    } finally {
      setLoading(false);
    }
  }, [onNotice]);

  useEffect(() => {
    load();
  }, [load]);

  const update = <K extends keyof PluginAdminSettings>(key: K, value: PluginAdminSettings[K]) => {
    setSettings((prev) => ({ ...prev, [key]: value }));
  };

  const handleSave = async () => {
    setSaving(true);
    try {
      const result = await saveAdminSettings(settings);
      setSettings({ ...EMPTY, ...result.settings });
      onNotice("success", result.message || "Settings saved.");
    } catch (err) {
      onNotice("error", err instanceof Error ? err.message : "Save failed");
    } finally {
      setSaving(false);
    }
  };

  if (loading) {
    return (
      <div className="akash-visual-layout-builder-screen akash-visual-layout-builder-screen--settings">
        <div className="akash-visual-layout-builder-screen__empty">
          <div className="akash-visual-layout-builder-spinner" />
        </div>
      </div>
    );
  }

  if (!settings.can_manage_settings && !window.akashVisualLayoutBuilderData?.canManageSettings) {
    return (
      <div className="akash-visual-layout-builder-screen akash-visual-layout-builder-screen--settings">
        <header className="akash-visual-layout-builder-screen__header">
          <div>
            <h1 className="akash-visual-layout-builder-screen__title">Settings</h1>
            <p className="akash-visual-layout-builder-screen__subtitle">You do not have permission to manage plugin settings.</p>
          </div>
        </header>
      </div>
    );
  }

  const geminiModels = settings.available_gemini_models?.length
    ? settings.available_gemini_models
    : [{ value: "gemini-2.5-flash", label: "Gemini 2.5 Flash" }];
  const claudeModels = settings.available_claude_models?.length
    ? settings.available_claude_models
    : [{ value: "claude-haiku-4-5-20251001", label: "Claude Haiku 4.5" }];

  return (
    <div className="akash-visual-layout-builder-screen akash-visual-layout-builder-screen--settings">
      <header className="akash-visual-layout-builder-screen__header">
        <div>
          <h1 className="akash-visual-layout-builder-screen__title">Settings</h1>
          <p className="akash-visual-layout-builder-screen__subtitle">
            Configure AI, images, editor defaults, SEO, popups, and permissions for Akash Visual Layout Builder.
          </p>
        </div>
        <button type="button" className="akash-visual-layout-builder-btn akash-visual-layout-builder-btn--primary" disabled={saving} onClick={handleSave}>
          {saving ? "Saving…" : "Save settings"}
        </button>
      </header>

      <div className="akash-visual-layout-builder-tracking-form akash-visual-layout-builder-settings-form">
        <section className="akash-visual-layout-builder-tracking-section">
          <h2 className="akash-visual-layout-builder-tracking-section__title">AI Assistant</h2>
          <p className="akash-visual-layout-builder-tracking-section__hint">
            Cloud AI uses the WordPress AI Client. Add a provider under{" "}
            {settings.connectors_url ? (
              <a href={settings.connectors_url}>Settings → Connectors</a>
            ) : (
              <strong>Settings → Connectors</strong>
            )}{" "}
            (WordPress 7.0+), for example{" "}
            <a href="https://wordpress.org/plugins/ai-provider-for-google/" target="_blank" rel="noreferrer">
              Google
            </a>
            ,{" "}
            <a href="https://wordpress.org/plugins/ai-provider-for-anthropic/" target="_blank" rel="noreferrer">
              Anthropic
            </a>
            , or{" "}
            <a href="https://wordpress.org/plugins/ai-provider-for-openai/" target="_blank" rel="noreferrer">
              OpenAI
            </a>
            . This plugin does not store provider API keys.
          </p>
          <p className="akash-visual-layout-builder-tracking-section__hint">
            {settings.ai_client_available
              ? "A text-generation provider is configured and ready."
              : settings.ai_client_core
                ? "No AI provider is configured yet. Local templates still work when AI is enabled."
                : "This site is below WordPress 7.0, so only local templates are used."}
          </p>

          <label className="akash-visual-layout-builder-layout-toggle akash-visual-layout-builder-tracking-toggle">
            <input
              type="checkbox"
              checked={settings.ai_enabled}
              onChange={(e) => update("ai_enabled", e.target.checked)}
            />
            <span>Enable AI assistant</span>
          </label>

          <div className="akash-visual-layout-builder-tracking-grid">
            <label className="akash-visual-layout-builder-layout-mode">
              <span>Preferred Gemini model</span>
              <select
                value={settings.gemini_model}
                onChange={(e) => update("gemini_model", e.target.value)}
              >
                {geminiModels.map((m) => (
                  <option key={m.value} value={m.value}>
                    {m.label}
                  </option>
                ))}
              </select>
            </label>
            <label className="akash-visual-layout-builder-layout-mode">
              <span>Preferred Claude model</span>
              <select
                value={settings.claude_model}
                onChange={(e) => update("claude_model", e.target.value)}
              >
                {claudeModels.map((m) => (
                  <option key={m.value} value={m.value}>
                    {m.label}
                  </option>
                ))}
              </select>
            </label>
          </div>

          <div className="akash-visual-layout-builder-tracking-grid">
            <label className="akash-visual-layout-builder-layout-mode">
              <span>Image suggestions provider</span>
              <select
                value={settings.image_suggestions}
                onChange={(e) =>
                  update("image_suggestions", e.target.value as PluginAdminSettings["image_suggestions"])
                }
              >
                <option value="auto">Auto (WordPress AI Client → local)</option>
                <option value="claude">Prefer Claude models</option>
                <option value="gemini">Prefer Gemini models</option>
                <option value="local">Local recipes only</option>
              </select>
            </label>
            <label className="akash-visual-layout-builder-layout-mode">
              <span>Who can use AI</span>
              <select
                value={settings.ai_capability}
                onChange={(e) => update("ai_capability", e.target.value)}
              >
                <option value="manage_options">Administrators</option>
                <option value="edit_pages">Editors &amp; admins</option>
                <option value="edit_posts">Authors &amp; above</option>
              </select>
            </label>
          </div>

          <div className="akash-visual-layout-builder-tracking-grid">
            <label className="akash-visual-layout-builder-layout-mode">
              <span>AI chat history turns</span>
              <input
                type="number"
                min={5}
                max={100}
                value={settings.ai_history_turns}
                onChange={(e) => update("ai_history_turns", Number(e.target.value) || 40)}
              />
            </label>
            <label className="akash-visual-layout-builder-layout-toggle akash-visual-layout-builder-tracking-toggle">
              <input
                type="checkbox"
                checked={settings.ai_panel_default_open}
                onChange={(e) => update("ai_panel_default_open", e.target.checked)}
              />
              <span>Open AI panel by default</span>
            </label>
          </div>
        </section>

        <section className="akash-visual-layout-builder-tracking-section">
          <h2 className="akash-visual-layout-builder-tracking-section__title">Images</h2>
          <p className="akash-visual-layout-builder-tracking-section__hint">
            AI does not load remote stock photos. Choose images from the WordPress Media Library.
          </p>
          <label className="akash-visual-layout-builder-layout-toggle akash-visual-layout-builder-tracking-toggle">
            <input
              type="checkbox"
              checked={settings.images_enabled}
              onChange={(e) => update("images_enabled", e.target.checked)}
            />
            <span>Allow AI to insert local placeholder image slots</span>
          </label>
          <label className="akash-visual-layout-builder-layout-toggle akash-visual-layout-builder-tracking-toggle">
            <input
              type="checkbox"
              checked={settings.images_replace_broken}
              onChange={(e) => update("images_replace_broken", e.target.checked)}
            />
            <span>Auto-replace broken / unsupported image URLs</span>
          </label>
          <div className="akash-visual-layout-builder-tracking-grid">
            <label className="akash-visual-layout-builder-layout-mode">
              <span>Default image width</span>
              <input
                type="number"
                min={200}
                max={2400}
                value={settings.image_width}
                onChange={(e) => update("image_width", Number(e.target.value) || 1200)}
              />
            </label>
            <label className="akash-visual-layout-builder-layout-mode">
              <span>Default image height</span>
              <input
                type="number"
                min={200}
                max={1800}
                value={settings.image_height}
                onChange={(e) => update("image_height", Number(e.target.value) || 700)}
              />
            </label>
          </div>
        </section>

        <section className="akash-visual-layout-builder-tracking-section">
          <h2 className="akash-visual-layout-builder-tracking-section__title">Google Fonts</h2>
          <p className="akash-visual-layout-builder-tracking-section__hint">
            Off by default. When enabled, pages that use Google Font families load stylesheets from fonts.googleapis.com.
          </p>
          <label className="akash-visual-layout-builder-layout-toggle akash-visual-layout-builder-tracking-toggle">
            <input
              type="checkbox"
              checked={!!settings.google_fonts_enabled}
              onChange={(e) => update("google_fonts_enabled", e.target.checked)}
            />
            <span>Load Google Fonts on the front end and in the editor canvas</span>
          </label>
        </section>

        <section className="akash-visual-layout-builder-tracking-section">
          <h2 className="akash-visual-layout-builder-tracking-section__title">Editor defaults</h2>
          <div className="akash-visual-layout-builder-tracking-grid">
            <label className="akash-visual-layout-builder-layout-mode">
              <span>Default list status filter</span>
              <select
                value={settings.default_list_status}
                onChange={(e) => update("default_list_status", e.target.value)}
              >
                <option value="publish">Published</option>
                <option value="draft">Draft</option>
                <option value="pending">Pending</option>
                <option value="private">Private</option>
                <option value="any">Any</option>
              </select>
            </label>
            <label className="akash-visual-layout-builder-layout-mode">
              <span>Default editor theme</span>
              <select
                value={settings.default_theme}
                onChange={(e) =>
                  update("default_theme", e.target.value as PluginAdminSettings["default_theme"])
                }
              >
                <option value="system">System</option>
                <option value="light">Light</option>
                <option value="dark">Dark</option>
              </select>
            </label>
          </div>
          <div className="akash-visual-layout-builder-tracking-grid">
            <label className="akash-visual-layout-builder-layout-mode">
              <span>Autosave delay (ms)</span>
              <input
                type="number"
                min={500}
                max={30000}
                step={100}
                value={settings.autosave_delay_ms}
                onChange={(e) => update("autosave_delay_ms", Number(e.target.value) || 1500)}
              />
            </label>
            <label className="akash-visual-layout-builder-layout-toggle akash-visual-layout-builder-tracking-toggle">
              <input
                type="checkbox"
                checked={settings.default_preview}
                onChange={(e) => update("default_preview", e.target.checked)}
              />
              <span>Show live preview by default</span>
            </label>
          </div>
        </section>

        <section className="akash-visual-layout-builder-tracking-section">
          <h2 className="akash-visual-layout-builder-tracking-section__title">SEO defaults</h2>
          <p className="akash-visual-layout-builder-tracking-section__hint">
            Per-page SEO is edited in the page SEO tab. These are site-wide defaults.
          </p>
          <label className="akash-visual-layout-builder-layout-toggle akash-visual-layout-builder-tracking-toggle">
            <input
              type="checkbox"
              checked={settings.seo_defer_to_plugins}
              onChange={(e) => update("seo_defer_to_plugins", e.target.checked)}
            />
            <span>Defer to Yoast / Rank Math / AIOSEO when active</span>
          </label>
          <label className="akash-visual-layout-builder-layout-mode">
            <span>Default meta description template</span>
            <input
              type="text"
              value={settings.seo_meta_template}
              onChange={(e) => update("seo_meta_template", e.target.value)}
              placeholder="e.g. {{title}} — professional services"
            />
          </label>
          <label className="akash-visual-layout-builder-layout-mode">
            <span>Default social / OG image URL</span>
            <input
              type="url"
              value={settings.seo_default_og_image}
              onChange={(e) => update("seo_default_og_image", e.target.value)}
              placeholder="https://…"
            />
          </label>
        </section>

        <section className="akash-visual-layout-builder-tracking-section">
          <h2 className="akash-visual-layout-builder-tracking-section__title">Popups &amp; layout</h2>
          <label className="akash-visual-layout-builder-layout-toggle akash-visual-layout-builder-tracking-toggle">
            <input
              type="checkbox"
              checked={settings.popups_enabled}
              onChange={(e) => update("popups_enabled", e.target.checked)}
            />
            <span>Enable popups on the front end</span>
          </label>
          <label className="akash-visual-layout-builder-layout-toggle akash-visual-layout-builder-tracking-toggle">
            <input
              type="checkbox"
              checked={settings.layout_header_default}
              onChange={(e) => update("layout_header_default", e.target.checked)}
            />
            <span>Prefer global header by default for new pages</span>
          </label>
          <label className="akash-visual-layout-builder-layout-toggle akash-visual-layout-builder-tracking-toggle">
            <input
              type="checkbox"
              checked={settings.layout_footer_default}
              onChange={(e) => update("layout_footer_default", e.target.checked)}
            />
            <span>Prefer global footer by default for new pages</span>
          </label>
          <p className="akash-visual-layout-builder-tracking-section__hint">
            Tracking scripts are managed under <strong>Tracking</strong>. Site header/footer code is under{" "}
            <strong>Site Layout</strong>.
          </p>
        </section>

        <section className="akash-visual-layout-builder-tracking-section">
          <h2 className="akash-visual-layout-builder-tracking-section__title">SVG &amp; permissions</h2>
          <div className="akash-visual-layout-builder-tracking-grid">
            <label className="akash-visual-layout-builder-layout-mode">
              <span>Max SVG upload size (KB)</span>
              <input
                type="number"
                min={64}
                max={5120}
                value={settings.svg_max_kb}
                onChange={(e) => update("svg_max_kb", Number(e.target.value) || 512)}
              />
            </label>
            <label className="akash-visual-layout-builder-layout-mode">
              <span>Who can open the builder</span>
              <select
                value={settings.builder_capability}
                onChange={(e) => update("builder_capability", e.target.value)}
              >
                <option value="edit_posts">Authors &amp; above</option>
                <option value="edit_pages">Editors &amp; admins</option>
                <option value="manage_options">Administrators only</option>
              </select>
            </label>
          </div>
          <label className="akash-visual-layout-builder-layout-mode">
            <span>Who can manage these settings</span>
            <select
              value={settings.settings_capability}
              onChange={(e) => update("settings_capability", e.target.value)}
            >
              <option value="manage_options">Administrators</option>
              <option value="edit_pages">Editors &amp; admins</option>
            </select>
          </label>
        </section>

        <div className="akash-visual-layout-builder-settings-actions">
          <button type="button" className="akash-visual-layout-builder-btn akash-visual-layout-builder-btn--primary" disabled={saving} onClick={handleSave}>
            {saving ? "Saving…" : "Save settings"}
          </button>
        </div>
      </div>
    </div>
  );
}
