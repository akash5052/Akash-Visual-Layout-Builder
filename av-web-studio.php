<?php
/**
 * Plugin Name:       AV Web Studio
 * Description:       Build WordPress pages with a visual drag-and-drop editor. Live preview, SEO, layouts, popups, and optional AI.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Akash Kate
 * Author URI:        https://profiles.wordpress.org/akash5052/
 * Plugin URI:        https://github.com/akash5052/AV-Web-Studio
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       av-web-studio
 * Domain Path:       /languages
 */

if (!defined('ABSPATH')) {
	exit;
}

define('AV_WEB_STUDIO_VERSION', '1.0.0'); // Keep in sync with the Version header above.
define('AV_WEB_STUDIO_PLUGIN_FILE', __FILE__);
define('AV_WEB_STUDIO_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('AV_WEB_STUDIO_PLUGIN_URL', plugin_dir_url(__FILE__));
define('AV_WEB_STUDIO_PLUGIN_BASENAME', plugin_basename(__FILE__));

/*
 * Optional model preference overrides (wp-config.php). API keys are stored in
 * Settings → Connectors, not in this plugin.
 *
 *   define( 'AV_WEB_STUDIO_GEMINI_MODEL', 'gemini-2.5-flash' );
 *   define( 'AV_WEB_STUDIO_CLAUDE_MODEL', 'claude-haiku-4-5-20251001' );
 */

if (!defined('AV_WEB_STUDIO_GEMINI_MODEL')) {
	define('AV_WEB_STUDIO_GEMINI_MODEL', 'gemini-2.5-flash');
}
if (!defined('AV_WEB_STUDIO_CLAUDE_MODEL')) {
	define('AV_WEB_STUDIO_CLAUDE_MODEL', 'claude-haiku-4-5-20251001');
}

require_once AV_WEB_STUDIO_PLUGIN_DIR . 'includes/class-av-web-studio-visual-compile.php';
require_once AV_WEB_STUDIO_PLUGIN_DIR . 'includes/class-av-web-studio-output.php';
require_once AV_WEB_STUDIO_PLUGIN_DIR . 'includes/class-av-web-studio-renderer.php';
require_once AV_WEB_STUDIO_PLUGIN_DIR . 'includes/class-av-web-studio-post-types.php';
require_once AV_WEB_STUDIO_PLUGIN_DIR . 'includes/class-av-web-studio-layout.php';
require_once AV_WEB_STUDIO_PLUGIN_DIR . 'includes/class-av-web-studio-seo.php';
require_once AV_WEB_STUDIO_PLUGIN_DIR . 'includes/class-av-web-studio-post-options.php';
require_once AV_WEB_STUDIO_PLUGIN_DIR . 'includes/class-av-web-studio-popups.php';
require_once AV_WEB_STUDIO_PLUGIN_DIR . 'includes/class-av-web-studio-popups-frontend.php';
require_once AV_WEB_STUDIO_PLUGIN_DIR . 'includes/class-av-web-studio-preview.php';
require_once AV_WEB_STUDIO_PLUGIN_DIR . 'includes/class-av-web-studio-settings.php';
require_once AV_WEB_STUDIO_PLUGIN_DIR . 'includes/class-av-web-studio-ai-client.php';
require_once AV_WEB_STUDIO_PLUGIN_DIR . 'includes/class-av-web-studio-ai-intent.php';
require_once AV_WEB_STUDIO_PLUGIN_DIR . 'includes/class-av-web-studio-ai-images.php';
require_once AV_WEB_STUDIO_PLUGIN_DIR . 'includes/class-av-web-studio-ai-profiles.php';
require_once AV_WEB_STUDIO_PLUGIN_DIR . 'includes/class-av-web-studio-ai-content.php';
require_once AV_WEB_STUDIO_PLUGIN_DIR . 'includes/class-av-web-studio-ai-industrial.php';
require_once AV_WEB_STUDIO_PLUGIN_DIR . 'includes/class-av-web-studio-posts-widget.php';
require_once AV_WEB_STUDIO_PLUGIN_DIR . 'includes/class-av-web-studio-visual-templates.php';
require_once AV_WEB_STUDIO_PLUGIN_DIR . 'includes/class-av-web-studio-custom-templates.php';
require_once AV_WEB_STUDIO_PLUGIN_DIR . 'includes/class-av-web-studio-ai-templates.php';
require_once AV_WEB_STUDIO_PLUGIN_DIR . 'includes/class-av-web-studio-ai-editor.php';
require_once AV_WEB_STUDIO_PLUGIN_DIR . 'includes/class-av-web-studio-ai-claude.php';
require_once AV_WEB_STUDIO_PLUGIN_DIR . 'includes/class-av-web-studio-ai.php';
require_once AV_WEB_STUDIO_PLUGIN_DIR . 'includes/class-av-web-studio-ai-ajax.php';
require_once AV_WEB_STUDIO_PLUGIN_DIR . 'includes/class-av-web-studio-svg.php';
require_once AV_WEB_STUDIO_PLUGIN_DIR . 'includes/class-av-web-studio-tracking.php';
require_once AV_WEB_STUDIO_PLUGIN_DIR . 'includes/class-av-web-studio-rest.php';
require_once AV_WEB_STUDIO_PLUGIN_DIR . 'includes/class-av-web-studio-frontend.php';
require_once AV_WEB_STUDIO_PLUGIN_DIR . 'includes/class-av-web-studio-admin.php';

register_activation_hook(__FILE__, 'av_web_studio_activate');
register_deactivation_hook(__FILE__, 'av_web_studio_deactivate');

/**
 * Plugin activation.
 */
function av_web_studio_activate() {
	$defaults = Av_Web_Studio_Settings::defaults();
	$existing = get_option('av_web_studio_settings', []);
	if (!is_array($existing)) {
		$existing = [];
	}
	update_option('av_web_studio_settings', wp_parse_args($existing, $defaults));
	flush_rewrite_rules();
}

/**
 * Plugin deactivation.
 */
function av_web_studio_deactivate() {
	flush_rewrite_rules();
}

/**
 * Boot plugin classes after WordPress is loaded.
 */
function av_web_studio_bootstrap() {
	new Av_Web_Studio_SVG();
	new Av_Web_Studio_Tracking();
	new Av_Web_Studio_REST();
	new Av_Web_Studio_AI_Ajax();
	new Av_Web_Studio_Frontend();
	new Av_Web_Studio_SEO();
	new Av_Web_Studio_Popups_Frontend();
	new Av_Web_Studio_Preview();
	new Av_Web_Studio_Admin();
}
add_action('plugins_loaded', 'av_web_studio_bootstrap');
