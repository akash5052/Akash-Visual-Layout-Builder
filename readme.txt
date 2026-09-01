=== WPVisualX ===
Contributors: akash5052
Tags: page builder, visual editor, landing page, popups, seo
Requires at least: 6.0
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Build WordPress pages with a visual drag-and-drop editor. Live preview, SEO, layouts, popups, and optional AI.

== Description ==

**WPVisualX** is a visual page builder for WordPress. After you activate it, you can create pages, posts, and popups using sections, columns, and widgets — no coding and no extra plugins required.

= Features =

* **Visual builder** — sections, columns, and widgets with responsive desktop / tablet / mobile editing
* **Site layout** — global header and footer
* **Page layout** — per-page header and footer modes (inherit, append, replace, or none)
* **SEO panel** — title, description, focus keyword, and social image
* **Popups** — create and publish popups with the same visual builder
* **SVG library** — upload and reuse SVG assets
* **Tracking (optional)** — Google Tag Manager, GA4, Ads, and Meta Pixel IDs (no custom script pasting)
* **AI assistant (optional)** — generate or improve page content through the WordPress AI Client when a provider is configured

The plugin is visual-only. Layout, popup, and page output is generated from the visual builder. Users cannot paste arbitrary HTML, CSS, JavaScript, or PHP.

Starter templates include local preview and section images. They do not load remote stock-photo CDNs.

= How to use (fresh install) =

1. Activate **WPVisualX** from the **Plugins** screen.
2. Open **WPVisualX** in the WordPress admin menu.
3. Go to **Pages** (or **Posts**), create a new item, or click **Build with WPVisualX** on an existing page/post.
4. Add sections, columns, and widgets on the live canvas. Use the desktop / tablet / mobile controls to check each breakpoint.
5. Open **Layout** to set header and footer behavior, then **SEO** for search and social metadata.
6. Save or publish. The page is live with no API keys or third-party accounts required.

Optional screens (all off until you turn them on):

* **Settings** — editor defaults, permissions, optional Google Fonts, and optional AI
* **Tracking** — GTM, GA4, Ads, or Meta Pixel IDs

= Optional AI features =

AI features are **off by default**. To use them:

1. Open **WPVisualX → Settings** and enable AI.
2. On WordPress 7.0 or later, install an AI provider plugin (Google, Anthropic, or OpenAI) and add its key under **Settings → Connectors**.

Those providers are third-party SaaS products. This plugin does not store their API keys. It sends prompts through `wp_ai_client_prompt()` only when AI is enabled and a site administrator has configured a connector. On WordPress 6.x, or when no provider is configured, the assistant uses local templates only.

= Privacy =

* Tracking scripts are **disabled until you enable them** and enter IDs in the Tracking screen.
* AI network requests only happen when AI is enabled and configured by a site administrator.
* Google Fonts are **disabled until you enable them** in Settings.
* The plugin does not load remote stock-photo CDNs.

== External services ==

WPVisualX can connect to the third-party services below. Each connection is optional and off by default until a site administrator enables it. The plugin does not accept pasted tracking snippets; official vendor scripts are enqueued from the IDs you enter.

**WordPress AI Client** (optional AI content generation)

Why: to generate or edit visual-builder content when an editor uses Generate.

When: a site administrator enables AI in WPVisualX, WordPress 7.0+ has an AI provider configured under **Settings → Connectors**, and an editor submits a Generate request.

Data sent: the editor prompt, page title, and the current page content needed to apply the change. The request is made from your WordPress server through Core's AI Client to whichever provider the site owner connected (typically Google, Anthropic, or OpenAI). Visitor browsers do not call those APIs.

Provider plugins: [AI Provider for Google](https://wordpress.org/plugins/ai-provider-for-google/), [AI Provider for Anthropic](https://wordpress.org/plugins/ai-provider-for-anthropic/), [AI Provider for OpenAI](https://wordpress.org/plugins/ai-provider-for-openai/)

Terms of Service: [Gemini API terms](https://ai.google.dev/gemini-api/terms), [Anthropic terms](https://www.anthropic.com/legal/terms), [OpenAI terms](https://openai.com/policies/terms-of-use)

Privacy Policy: [Google Privacy Policy](https://policies.google.com/privacy), [Anthropic privacy](https://www.anthropic.com/legal/privacy), [OpenAI privacy](https://openai.com/policies/privacy-policy)

**Google Fonts** (optional webfonts)

Why: to load the font families selected in a design so published pages and the editor preview can render those typefaces.

When: a site administrator enables **Load Google Fonts** in **WPVisualX → Settings**, and a design uses those families.

Data sent: the font family names in the stylesheet URL. The visitor's (or editor's) browser then requests CSS and font files from Google, which receives standard request data such as IP address and user agent.

Terms of Service: [Google Fonts terms](https://developers.google.com/fonts/terms)

Privacy Policy: [Google Privacy Policy](https://policies.google.com/privacy)

**Google Tag Manager** (optional tracking)

Why: to load the site owner's GTM container so they can manage marketing/analytics tags.

When: a site administrator enters a GTM container ID on the Tracking screen and tracking is enabled for that page.

Data sent: the GTM container ID. Google's `gtm.js` then collects page-view and related visitor data according to the container's tags (typically page URL, referrer, and browser information).

Terms of Service: [Google Tag Manager terms](https://marketingplatform.google.com/about/tag-manager/terms/)

Privacy Policy: [Google Privacy Policy](https://policies.google.com/privacy)

**Google Analytics 4** (optional tracking)

Why: to measure traffic and events on pages the site owner chooses.

When: a site administrator enters a GA4 measurement ID on the Tracking screen and tracking is enabled. (If GTM is also set, GA4 is expected to be loaded from the GTM container instead of a second gtag snippet.)

Data sent: the GA4 measurement ID, plus standard Analytics data such as page URL, referrer, and browser information collected by Google's gtag script.

Terms of Service: [Google Analytics terms](https://marketingplatform.google.com/about/analytics/terms/us/)

Privacy Policy: [Google Privacy Policy](https://policies.google.com/privacy)

**Google Ads** (optional conversion tracking)

Why: to load Google Ads conversion tracking when the site owner enters an Ads ID.

When: a site administrator enters a Google Ads ID on the Tracking screen and tracking is enabled, and GTM is not already loading gtag.

Data sent: the Ads ID, plus standard conversion/page-view data collected by Google's gtag script.

Terms of Service: [Google Ads terms](https://www.google.com/ads/terms)

Privacy Policy: [Google Privacy Policy](https://policies.google.com/privacy)

**Pexels** (not used)

Why listed: starter photos used to be loaded from Pexels in earlier builds. This plugin no longer contacts Pexels.

When: never. Template and AI image slots use JPEG files shipped in `assets/images/templates/`. Remote stock-photo URLs are not requested.

Data sent: none. No visitor or editor data is sent to Pexels.

Terms of Service: [Pexels terms](https://www.pexels.com/terms-of-service/) (not applicable; the service is not called)

Privacy Policy: [Pexels privacy](https://www.pexels.com/privacy-policy/) (not applicable; the service is not called)

**Meta Pixel** (optional tracking)

Why: to load the official Meta Pixel when the site owner enters a Pixel ID.

When: a site administrator enters a Meta Pixel ID on the Tracking screen and tracking is enabled.

Data sent: the Pixel ID, plus standard pixel events Meta collects on page views.

Terms of Service: [Meta terms](https://www.facebook.com/legal/terms)

Privacy Policy: [Meta Privacy Policy](https://www.facebook.com/privacy/policy/)

= Source code =

The public repository is [EP-Builder on GitHub](https://github.com/akash5052/EP-Builder). Compiled editor assets are shipped in `assets/build/`. The React/TypeScript source is included in the `editor/` directory of this plugin.

== Installation ==

1. Upload the `wpvisualx` folder to `/wp-content/plugins/`, or install the zip via **Plugins → Add New → Upload Plugin**.
2. Activate **WPVisualX** through the **Plugins** screen.
3. Open **WPVisualX** in the admin menu and create a page with the visual builder.

== Frequently Asked Questions ==

= Can I paste custom CSS or JavaScript? =

No. WPVisualX is a visual editor. Styles come from widget and layout settings. Tracking uses account ID fields that load official Google and Meta scripts. There is no HTML, CSS, or JavaScript editor.

= Do I need an API key to use the plugin? =

No. The visual builder, layouts, SEO, popups, and publishing work without any external API. A provider key in **Settings → Connectors** is only needed for optional cloud AI.

= Will this replace my theme? =

No. The builder outputs content for pages and posts. Your theme still controls the overall site chrome unless you use site header/footer layout features.

= Is the AI required? =

No. AI is optional and disabled by default.

= Do pages load Google Fonts automatically? =

No. Enable **Load Google Fonts** in **WPVisualX → Settings** if you want those families fetched from Google.

= Where do I configure AI? =

Enable AI in **WPVisualX → Settings**. On WordPress 7.0+, add a provider under **Settings → Connectors**. This plugin does not collect Gemini or Claude API keys.

== Changelog ==

= 1.0.0 =
* Initial WordPress.org release
* Visual editor for pages, posts, layouts, and popups
* Site and page header/footer layouts
* SEO tools, popups, SVG library, optional tracking
* Optional AI assistant via the WordPress AI Client (`wp_ai_client_prompt()`)

== Upgrade Notice ==

= 1.0.0 =
Initial public release.

== Development ==

Repository: https://github.com/akash5052/EP-Builder
Editor UI source: `editor/` (build with `npm install && npm run build` inside that folder).
Packaging helper: `./package.sh` (development only; not required on production sites).
