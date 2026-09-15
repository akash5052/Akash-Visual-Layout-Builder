import { useCallback, useEffect, useState } from "react";
import { fetchTracking, saveTracking } from "../api/wordpress";
import type { TrackingScope, TrackingSettings } from "../types";
import { EMPTY_TRACKING } from "../types";

interface TrackingViewProps {
  onNotice: (type: "success" | "error", message: string) => void;
}

export function TrackingView({ onNotice }: TrackingViewProps) {
  const [settings, setSettings] = useState<TrackingSettings>(EMPTY_TRACKING);
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);

  const load = useCallback(async () => {
    setLoading(true);
    try {
      const { tracking } = await fetchTracking();
      setSettings({ ...EMPTY_TRACKING, ...tracking });
    } catch (err) {
      onNotice("error", err instanceof Error ? err.message : "Failed to load tracking settings");
    } finally {
      setLoading(false);
    }
  }, [onNotice]);

  useEffect(() => {
    load();
  }, [load]);

  const update = <K extends keyof TrackingSettings>(key: K, value: TrackingSettings[K]) => {
    setSettings((prev) => ({ ...prev, [key]: value }));
  };

  const handleSave = async () => {
    setSaving(true);
    try {
      const result = await saveTracking(settings);
      setSettings({ ...EMPTY_TRACKING, ...result.tracking });
      onNotice("success", result.message);
    } catch (err) {
      onNotice("error", err instanceof Error ? err.message : "Save failed");
    } finally {
      setSaving(false);
    }
  };

  if (loading) {
    return (
      <div className="akash-visual-layout-builder-screen akash-visual-layout-builder-screen--tracking">
        <div className="akash-visual-layout-builder-screen__empty">
          <div className="akash-visual-layout-builder-spinner" />
        </div>
      </div>
    );
  }

  return (
    <div className="akash-visual-layout-builder-screen akash-visual-layout-builder-screen--tracking">
      <header className="akash-visual-layout-builder-screen__header">
        <div>
          <h1 className="akash-visual-layout-builder-screen__title">Tracking &amp; Analytics</h1>
          <p className="akash-visual-layout-builder-screen__subtitle">
            Enter account IDs only. The plugin loads the official Google Tag Manager, Analytics, Ads, and Meta
            Pixel scripts. Custom HTML, CSS, or JavaScript snippets are not supported.
          </p>
        </div>
        <button type="button" className="akash-visual-layout-builder-btn akash-visual-layout-builder-btn--primary" disabled={saving} onClick={handleSave}>
          {saving ? "Saving…" : "Save settings"}
        </button>
      </header>

      <div className="akash-visual-layout-builder-tracking-form">
        <section className="akash-visual-layout-builder-tracking-section">
          <label className="akash-visual-layout-builder-layout-toggle akash-visual-layout-builder-tracking-toggle">
            <input
              type="checkbox"
              checked={settings.enabled}
              onChange={(e) => update("enabled", e.target.checked)}
            />
            <span>Enable tracking on the front end</span>
          </label>

          <label className="akash-visual-layout-builder-layout-mode">
            <span>Where to load tags</span>
            <select
              value={settings.scope}
              onChange={(e) => update("scope", e.target.value as TrackingScope)}
            >
              <option value="entire_site">Entire site (all pages)</option>
              <option value="akash_visual_layout_builder_only">Akash Visual Layout Builder pages &amp; posts only</option>
            </select>
          </label>
        </section>

        <section className="akash-visual-layout-builder-tracking-section">
          <h2 className="akash-visual-layout-builder-tracking-section__title">Google Tag Manager</h2>
          <p className="akash-visual-layout-builder-tracking-section__hint">
            Container ID from your GTM workspace (e.g. GTM-XXXXXXX). If you use GTM, configure GA4 inside GTM instead
            of below.
          </p>
          <label className="akash-visual-layout-builder-layout-mode">
            <span>GTM Container ID</span>
            <input
              type="text"
              value={settings.gtm_id}
              onChange={(e) => update("gtm_id", e.target.value.toUpperCase())}
              placeholder="GTM-XXXXXXX"
              spellCheck={false}
            />
          </label>
        </section>

        <section className="akash-visual-layout-builder-tracking-section">
          <h2 className="akash-visual-layout-builder-tracking-section__title">Google Analytics &amp; Ads</h2>
          <p className="akash-visual-layout-builder-tracking-section__hint">
            Only used when GTM is not set. GA4 measurement IDs start with G-. Google Ads conversion IDs start with
            AW-.
          </p>
          <div className="akash-visual-layout-builder-tracking-grid">
            <label className="akash-visual-layout-builder-layout-mode">
              <span>GA4 Measurement ID</span>
              <input
                type="text"
                value={settings.ga4_id}
                onChange={(e) => update("ga4_id", e.target.value.toUpperCase())}
                placeholder="G-XXXXXXXXXX"
                spellCheck={false}
              />
            </label>
            <label className="akash-visual-layout-builder-layout-mode">
              <span>Google Ads ID</span>
              <input
                type="text"
                value={settings.google_ads_id}
                onChange={(e) => update("google_ads_id", e.target.value.toUpperCase())}
                placeholder="AW-XXXXXXXXX"
                spellCheck={false}
              />
            </label>
          </div>
        </section>

        <section className="akash-visual-layout-builder-tracking-section">
          <h2 className="akash-visual-layout-builder-tracking-section__title">Meta (Facebook) Pixel</h2>
          <label className="akash-visual-layout-builder-layout-mode">
            <span>Pixel ID</span>
            <input
              type="text"
              value={settings.facebook_pixel_id}
              onChange={(e) => update("facebook_pixel_id", e.target.value.replace(/\D/g, ""))}
              placeholder="123456789012345"
              spellCheck={false}
            />
          </label>
        </section>
      </div>
    </div>
  );
}
