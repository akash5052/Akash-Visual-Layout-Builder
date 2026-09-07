# AV Web Studio — WordPress.org submission

Follow [WordPress.org developer information](https://wordpress.org/plugins/developers/) and the [detailed plugin guidelines](https://developer.wordpress.org/plugins/wordpress-org/detailed-plugin-guidelines/).

## Before you submit

1. Create / confirm your [WordPress.org account](https://login.wordpress.org/register).
2. Edit `readme.txt` → **Contributors** so it matches your exact wordpress.org username (not display name).
3. Edit `Author URI` / `Contributors` in `av-web-studio.php` / `readme.txt` if needed.
4. Build a clean zip:

```bash
./package.sh
```

Upload **`av-web-studio.zip`** (plugin root folder inside the zip must be `av-web-studio/`).

5. Submit at [Add your plugin](https://wordpress.org/plugins/developers/add/).

## What reviewers expect

| Requirement | Status in this plugin |
|-------------|------------------------|
| GPLv2 or later | `LICENSE` + headers |
| `readme.txt` | Included |
| No hardcoded API secrets | Keys only via Settings / `wp-config.php` |
| No forced “powered by” front-end credits | None |
| Tracking opt-in | Off until enabled |
| AI opt-in | Off by default; needs user keys |
| No locked / trialware features | Visual builder is fully usable; no Pro gates |
| Human-readable source for built JS | Link + `editor/` in [github.com/akash5052/AV-Web-Studio](https://github.com/akash5052/AV-Web-Studio) |
| Stable version | `1.0.0` |

## After approval (SVN)

1. You get an SVN repo (e.g. `https://plugins.svn.wordpress.org/av-web-studio`).
2. Put the plugin files in `/trunk`.
3. Copy trunk to `/tags/1.0.0` for the stable release.
4. Optional marketing assets (not inside the plugin zip) go in SVN `/assets`:
   - `icon-128x128.png`, `icon-256x256.png`
   - `banner-772x250.png`, `banner-1544x500.png`
   - `screenshot-1.png` … matching `readme.txt` Screenshots

Validate the readme: [readme validator](https://wordpress.org/plugins/developers/readme-validator/).

## Security note

If API keys were ever committed to git history, **rotate them** in Google AI Studio / Anthropic before publishing.
