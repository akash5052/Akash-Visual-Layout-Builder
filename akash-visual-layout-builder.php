<?php
/**
 * Plugin Name:       Akash Visual Layout Builder
 * Description:       Build WordPress pages with a visual drag-and-drop editor. Live preview, SEO, layouts, popups, and optional AI.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Akash Kate
 * Author URI:        https://profiles.wordpress.org/akash5052/
 * Plugin URI:        https://github.com/akash5052/Akash-Visual-Layout-Builder
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       akash-visual-layout-builder
 * Domain Path:       /languages
 */

if (!defined('ABSPATH')) {
	exit;
}

define('AKASH_VISUAL_LAYOUT_BUILDER_VERSION', '1.0.0'); // Keep in sync with the Version header above.
define('AKASH_VISUAL_LAYOUT_BUILDER_PLUGIN_FILE', __FILE__);
define('AKASH_VISUAL_LAYOUT_BUILDER_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('AKASH_VISUAL_LAYOUT_BUILDER_PLUGIN_URL', plugin_dir_url(__FILE__));
define('AKASH_VISUAL_LAYOUT_BUILDER_PLUGIN_BASENAME', plugin_basename(__FILE__));

/*
 * Optional model preference overrides (wp-config.php). API keys are stored in
 * Settings → Connectors, not in this plugin.
 *
 *   define( 'AKASH_VISUAL_LAYOUT_BUILDER_GEMINI_MODEL', 'gemini-2.5-flash' );
 *   define( 'AKASH_VISUAL_LAYOUT_BUILDER_CLAUDE_MODEL', 'claude-haiku-4-5-20251001' );
 */

if (!defined('AKASH_VISUAL_LAYOUT_BUILDER_GEMINI_MODEL')) {
	define('AKASH_VISUAL_LAYOUT_BUILDER_GEMINI_MODEL', 'gemini-2.5-flash');
}
if (!defined('AKASH_VISUAL_LAYOUT_BUILDER_CLAUDE_MODEL')) {
	define('AKASH_VISUAL_LAYOUT_BUILDER_CLAUDE_MODEL', 'claude-haiku-4-5-20251001');
}

require_once AKASH_VISUAL_LAYOUT_BUILDER_PLUGIN_DIR . 'includes/class-akash-visual-layout-builder-visual-compile.php';
require_once AKASH_VISUAL_LAYOUT_BUILDER_PLUGIN_DIR . 'includes/class-akash-visual-layout-builder-output.php';
require_once AKASH_VISUAL_LAYOUT_BUILDER_PLUGIN_DIR . 'includes/class-akash-visual-layout-builder-renderer.php';
require_once AKASH_VISUAL_LAYOUT_BUILDER_PLUGIN_DIR . 'includes/class-akash-visual-layout-builder-post-types.php';
require_once AKASH_VISUAL_LAYOUT_BUILDER_PLUGIN_DIR . 'includes/class-akash-visual-layout-builder-layout.php';
require_once AKASH_VISUAL_LAYOUT_BUILDER_PLUGIN_DIR . 'includes/class-akash-visual-layout-builder-seo.php';
require_once AKASH_VISUAL_LAYOUT_BUILDER_PLUGIN_DIR . 'includes/class-akash-visual-layout-builder-post-options.php';
require_once AKASH_VISUAL_LAYOUT_BUILDER_PLUGIN_DIR . 'includes/class-akash-visual-layout-builder-popups.php';
require_once AKASH_VISUAL_LAYOUT_BUILDER_PLUGIN_DIR . 'includes/class-akash-visual-layout-builder-popups-frontend.php';
require_once AKASH_VISUAL_LAYOUT_BUILDER_PLUGIN_DIR . 'includes/class-akash-visual-layout-builder-preview.php';
require_once AKASH_VISUAL_LAYOUT_BUILDER_PLUGIN_DIR . 'includes/class-akash-visual-layout-builder-settings.php';
require_once AKASH_VISUAL_LAYOUT_BUILDER_PLUGIN_DIR . 'includes/class-akash-visual-layout-builder-ai-client.php';
require_once AKASH_VISUAL_LAYOUT_BUILDER_PLUGIN_DIR . 'includes/class-akash-visual-layout-builder-ai-intent.php';
require_once AKASH_VISUAL_LAYOUT_BUILDER_PLUGIN_DIR . 'includes/class-akash-visual-layout-builder-ai-images.php';
require_once AKASH_VISUAL_LAYOUT_BUILDER_PLUGIN_DIR . 'includes/class-akash-visual-layout-builder-ai-profiles.php';
require_once AKASH_VISUAL_LAYOUT_BUILDER_PLUGIN_DIR . 'includes/class-akash-visual-layout-builder-ai-content.php';
require_once AKASH_VISUAL_LAYOUT_BUILDER_PLUGIN_DIR . 'includes/class-akash-visual-layout-builder-ai-industrial.php';
require_once AKASH_VISUAL_LAYOUT_BUILDER_PLUGIN_DIR . 'includes/class-akash-visual-layout-builder-posts-widget.php';
require_once AKASH_VISUAL_LAYOUT_BUILDER_PLUGIN_DIR . 'includes/class-akash-visual-layout-builder-visual-templates.php';
require_once AKASH_VISUAL_LAYOUT_BUILDER_PLUGIN_DIR . 'includes/class-akash-visual-layout-builder-custom-templates.php';
require_once AKASH_VISUAL_LAYOUT_BUILDER_PLUGIN_DIR . 'includes/class-akash-visual-layout-builder-ai-templates.php';
require_once AKASH_VISUAL_LAYOUT_BUILDER_PLUGIN_DIR . 'includes/class-akash-visual-layout-builder-ai-editor.php';
require_once AKASH_VISUAL_LAYOUT_BUILDER_PLUGIN_DIR . 'includes/class-akash-visual-layout-builder-ai-claude.php';
require_once AKASH_VISUAL_LAYOUT_BUILDER_PLUGIN_DIR . 'includes/class-akash-visual-layout-builder-ai.php';
require_once AKASH_VISUAL_LAYOUT_BUILDER_PLUGIN_DIR . 'includes/class-akash-visual-layout-builder-ai-ajax.php';
require_once AKASH_VISUAL_LAYOUT_BUILDER_PLUGIN_DIR . 'includes/class-akash-visual-layout-builder-svg.php';
require_once AKASH_VISUAL_LAYOUT_BUILDER_PLUGIN_DIR . 'includes/class-akash-visual-layout-builder-tracking.php';
require_once AKASH_VISUAL_LAYOUT_BUILDER_PLUGIN_DIR . 'includes/class-akash-visual-layout-builder-rest.php';
require_once AKASH_VISUAL_LAYOUT_BUILDER_PLUGIN_DIR . 'includes/class-akash-visual-layout-builder-frontend.php';
require_once AKASH_VISUAL_LAYOUT_BUILDER_PLUGIN_DIR . 'includes/class-akash-visual-layout-builder-admin.php';

register_activation_hook(__FILE__, 'akash_visual_layout_builder_activate');
register_deactivation_hook(__FILE__, 'akash_visual_layout_builder_deactivate');

/**
 * Plugin activation.
 */
function akash_visual_layout_builder_activate() {
	$defaults = Akash_Visual_Layout_Builder_Settings::defaults();
	$existing = get_option('akash_visual_layout_builder_settings', []);
	if (!is_array($existing)) {
		$existing = [];
	}
	update_option('akash_visual_layout_builder_settings', wp_parse_args($existing, $defaults));
	flush_rewrite_rules();
}

/**
 * Plugin deactivation.
 */
function akash_visual_layout_builder_deactivate() {
	flush_rewrite_rules();
}

/**
 * Boot plugin classes after WordPress is loaded.
 */
function akash_visual_layout_builder_bootstrap() {
	new Akash_Visual_Layout_Builder_SVG();
	new Akash_Visual_Layout_Builder_Tracking();
	new Akash_Visual_Layout_Builder_REST();
	new Akash_Visual_Layout_Builder_AI_Ajax();
	new Akash_Visual_Layout_Builder_Frontend();
	new Akash_Visual_Layout_Builder_SEO();
	new Akash_Visual_Layout_Builder_Popups_Frontend();
	new Akash_Visual_Layout_Builder_Preview();
	new Akash_Visual_Layout_Builder_Admin();
}
add_action('plugins_loaded', 'akash_visual_layout_builder_bootstrap');
