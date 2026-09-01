<?php
/**
 * Plugin Name:       WPVisualX
 * Description:       Build WordPress pages with a visual drag-and-drop editor. Live preview, SEO, layouts, popups, and optional AI.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Akash Kate
 * Author URI:        https://profiles.wordpress.org/akash5052/
 * Plugin URI:        https://github.com/akash5052/EP-Builder
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       wpvisualx
 * Domain Path:       /languages
 */

if (!defined('ABSPATH')) {
	exit;
}

define('EPB_VERSION', '1.0.0'); // Keep in sync with the Version header above.
define('EPB_PLUGIN_FILE', __FILE__);
define('EPB_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('EPB_PLUGIN_URL', plugin_dir_url(__FILE__));
define('EPB_PLUGIN_BASENAME', plugin_basename(__FILE__));

/*
 * Optional model preference overrides (wp-config.php). API keys are stored in
 * Settings → Connectors, not in this plugin.
 *
 *   define( 'EPB_GEMINI_MODEL', 'gemini-2.5-flash' );
 *   define( 'EPB_CLAUDE_MODEL', 'claude-haiku-4-5-20251001' );
 */

if (!defined('EPB_GEMINI_MODEL')) {
	define('EPB_GEMINI_MODEL', 'gemini-2.5-flash');
}
if (!defined('EPB_CLAUDE_MODEL')) {
	define('EPB_CLAUDE_MODEL', 'claude-haiku-4-5-20251001');
}

require_once EPB_PLUGIN_DIR . 'includes/class-epb-visual-compile.php';
require_once EPB_PLUGIN_DIR . 'includes/class-epb-output.php';
require_once EPB_PLUGIN_DIR . 'includes/class-epb-renderer.php';
require_once EPB_PLUGIN_DIR . 'includes/class-epb-post-types.php';
require_once EPB_PLUGIN_DIR . 'includes/class-epb-layout.php';
require_once EPB_PLUGIN_DIR . 'includes/class-epb-seo.php';
require_once EPB_PLUGIN_DIR . 'includes/class-epb-post-options.php';
require_once EPB_PLUGIN_DIR . 'includes/class-epb-popups.php';
require_once EPB_PLUGIN_DIR . 'includes/class-epb-popups-frontend.php';
require_once EPB_PLUGIN_DIR . 'includes/class-epb-preview.php';
require_once EPB_PLUGIN_DIR . 'includes/class-epb-settings.php';
require_once EPB_PLUGIN_DIR . 'includes/class-epb-ai-client.php';
require_once EPB_PLUGIN_DIR . 'includes/class-epb-ai-intent.php';
require_once EPB_PLUGIN_DIR . 'includes/class-epb-ai-images.php';
require_once EPB_PLUGIN_DIR . 'includes/class-epb-ai-profiles.php';
require_once EPB_PLUGIN_DIR . 'includes/class-epb-ai-content.php';
require_once EPB_PLUGIN_DIR . 'includes/class-epb-ai-industrial.php';
require_once EPB_PLUGIN_DIR . 'includes/class-epb-posts-widget.php';
require_once EPB_PLUGIN_DIR . 'includes/class-epb-visual-templates.php';
require_once EPB_PLUGIN_DIR . 'includes/class-epb-custom-templates.php';
require_once EPB_PLUGIN_DIR . 'includes/class-epb-ai-templates.php';
require_once EPB_PLUGIN_DIR . 'includes/class-epb-ai-editor.php';
require_once EPB_PLUGIN_DIR . 'includes/class-epb-ai-claude.php';
require_once EPB_PLUGIN_DIR . 'includes/class-epb-ai.php';
require_once EPB_PLUGIN_DIR . 'includes/class-epb-ai-ajax.php';
require_once EPB_PLUGIN_DIR . 'includes/class-epb-svg.php';
require_once EPB_PLUGIN_DIR . 'includes/class-epb-tracking.php';
require_once EPB_PLUGIN_DIR . 'includes/class-epb-rest.php';
require_once EPB_PLUGIN_DIR . 'includes/class-epb-frontend.php';
require_once EPB_PLUGIN_DIR . 'includes/class-epb-admin.php';

register_activation_hook(__FILE__, 'ep_builder_activate');
register_deactivation_hook(__FILE__, 'ep_builder_deactivate');

/**
 * Plugin activation.
 */
function ep_builder_activate() {
	$defaults = EPB_Settings::defaults();
	$existing = get_option('epb_settings', []);
	if (!is_array($existing)) {
		$existing = [];
	}
	update_option('epb_settings', wp_parse_args($existing, $defaults));
	flush_rewrite_rules();
}

/**
 * Plugin deactivation.
 */
function ep_builder_deactivate() {
	flush_rewrite_rules();
}

/**
 * Boot plugin classes after WordPress is loaded.
 */
function ep_builder_bootstrap() {
	new EPB_SVG();
	new EPB_Tracking();
	new EPB_REST();
	new EPB_AI_Ajax();
	new EPB_Frontend();
	new EPB_SEO();
	new EPB_Popups_Frontend();
	new EPB_Preview();
	new EPB_Admin();
}
add_action('plugins_loaded', 'ep_builder_bootstrap');
