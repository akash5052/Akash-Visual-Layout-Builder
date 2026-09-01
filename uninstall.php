<?php
/**
 * Uninstall WPVisualX.
 *
 * Removes plugin options and post meta. Does not delete pages/posts.
 *
 * @package WPVisualX
 */

if (!defined('WP_UNINSTALL_PLUGIN')) {
	exit;
}

/**
 * Recursively remove a directory created by this plugin.
 *
 * @param string $dir Absolute path.
 */
function ep_builder_uninstall_delete_dir($dir) {
	if (!is_dir($dir)) {
		return;
	}

	$entries = scandir($dir);
	if ($entries === false) {
		return;
	}

	foreach ($entries as $entry) {
		if ($entry === '.' || $entry === '..') {
			continue;
		}
		$path = $dir . DIRECTORY_SEPARATOR . $entry;
		if (is_dir($path)) {
			ep_builder_uninstall_delete_dir($path);
		} else {
			wp_delete_file($path);
		}
	}

	rmdir($dir); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_rmdir -- uninstall cleanup of plugin upload dir.
}

/**
 * Delete plugin options, post meta, and uploaded template files.
 */
function ep_builder_uninstall() {
	delete_option('epb_settings');
	delete_option('epb_tracking');
	delete_option('epb_global_layout');
	delete_option('epb_popups');
	delete_option('epb_custom_templates');

	$ep_builder_meta_keys = [
		'_epb_html',
		'_epb_css',
		'_epb_js',
		'_epb_code',
		'_epb_enabled',
		'_epb_edit_mode',
		'_epb_visual',
		'_epb_page_layout',
		'_epb_layout',
		'_epb_seo',
		'_epb_page_template',
		'_epb_hide_title',
	];

	foreach ($ep_builder_meta_keys as $ep_builder_meta_key) {
		delete_post_meta_by_key($ep_builder_meta_key);
	}

	$ep_builder_uploads = wp_upload_dir();
	if (empty($ep_builder_uploads['error']) && !empty($ep_builder_uploads['basedir'])) {
		ep_builder_uninstall_delete_dir(trailingslashit($ep_builder_uploads['basedir']) . 'epb-templates');
	}
}

ep_builder_uninstall();
