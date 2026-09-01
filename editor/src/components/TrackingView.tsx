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
      <div className="epb-screen epb-screen--tracking">
        <div className="epb-screen__empty">
          <div className="epb-spinner" />
        </div>
      </div>
    );
  }

  return (
    <div className="epb-screen epb-screen--tracking">
      <header className="epb-screen__header">
        <div>
          <h1 className="epb-screen__title">Tracking &amp; Analytics</h1>
          <p className="epb-screen__subtitle">
            Enter account IDs only. The plugin loads the official Google Tag Manager, Analytics, Ads, and Meta
            Pixel scripts. Custom HTML, CSS, or JavaScript snippets are not supported.
          </p>
        </div>
        <button type="button" className="epb-btn epb-btn--primary" disabled={saving} onClick={handleSave}>
          {saving ? "Saving…" : "Save settings"}
        </button>
      </header>

      <div className="epb-tracking-form">
        <section className="epb-tracking-section">
          <label className="epb-layout-toggle epb-tracking-toggle">
            <input
              type="checkbox"
              checked={settings.enabled}
              onChange={(e) => update("enabled", e.target.checked)}
            />
            <span>Enable tracking on the front end</span>
          </label>

          <label className="epb-layout-mode">
            <span>Where to load tags</span>
            <select
              value={settings.scope}
              onChange={(e) => update("scope", e.target.value as TrackingScope)}
            >
              <option value="entire_site">Entire site (all pages)</option>
              <option value="epb_only">WPVisualX pages &amp; posts only</option>
            </select>
          </label>
        </section>

        <section className="epb-tracking-section">
          <h2 className="epb-tracking-section__title">Google Tag Manager</h2>
          <p className="epb-tracking-section__hint">
            Container ID from your GTM workspace (e.g. GTM-XXXXXXX). If you use GTM, configure GA4 inside GTM instead
            of below.
          </p>
          <label className="epb-layout-mode">
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

        <section className="epb-tracking-section">
          <h2 className="epb-tracking-section__title">Google Analytics &amp; Ads</h2>
          <p className="epb-tracking-section__hint">
            Only used when GTM is not set. GA4 measurement IDs start with G-. Google Ads conversion IDs start with
            AW-.
          </p>
          <div className="epb-tracking-grid">
            <label className="epb-layout-mode">
              <span>GA4 Measurement ID</span>
              <input
                type="text"
                value={settings.ga4_id}
                onChange={(e) => update("ga4_id", e.target.value.toUpperCase())}
                placeholder="G-XXXXXXXXXX"
                spellCheck={false}
              />
            </label>
            <label className="epb-layout-mode">
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

        <section className="epb-tracking-section">
          <h2 className="epb-tracking-section__title">Meta (Facebook) Pixel</h2>
          <label className="epb-layout-mode">
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
