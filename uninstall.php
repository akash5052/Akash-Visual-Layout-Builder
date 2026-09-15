<?php
/**
 * Uninstall Akash Visual Layout Builder.
 *
 * Removes plugin options and post meta. Does not delete pages/posts.
 *
 * @package Akash_Visual_Layout_Builder
 */

if (!defined('WP_UNINSTALL_PLUGIN')) {
	exit;
}

/**
 * Recursively remove a directory created by this plugin.
 *
 * @param string $dir Absolute path.
 */
function akash_visual_layout_builder_uninstall_delete_dir($dir) {
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
			akash_visual_layout_builder_uninstall_delete_dir($path);
		} else {
			wp_delete_file($path);
		}
	}

	rmdir($dir); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_rmdir -- uninstall cleanup of plugin upload dir.
}

/**
 * Delete plugin options, post meta, and uploaded template files.
 */
function akash_visual_layout_builder_uninstall() {
	delete_option('akash_visual_layout_builder_settings');
	delete_option('akash_visual_layout_builder_tracking');
	delete_option('akash_visual_layout_builder_global_layout');
	delete_option('akash_visual_layout_builder_popups');
	delete_option('akash_visual_layout_builder_custom_templates');

	$akash_visual_layout_builder_meta_keys = [
		'_akash_visual_layout_builder_html',
		'_akash_visual_layout_builder_css',
		'_akash_visual_layout_builder_js',
		'_akash_visual_layout_builder_code',
		'_akash_visual_layout_builder_enabled',
		'_akash_visual_layout_builder_edit_mode',
		'_akash_visual_layout_builder_visual',
		'_akash_visual_layout_builder_page_layout',
		'_akash_visual_layout_builder_layout',
		'_akash_visual_layout_builder_seo',
		'_akash_visual_layout_builder_page_template',
		'_akash_visual_layout_builder_hide_title',
	];

	foreach ($akash_visual_layout_builder_meta_keys as $akash_visual_layout_builder_meta_key) {
		delete_post_meta_by_key($akash_visual_layout_builder_meta_key);
	}

	$akash_visual_layout_builder_uploads = wp_upload_dir();
	if (empty($akash_visual_layout_builder_uploads['error']) && !empty($akash_visual_layout_builder_uploads['basedir'])) {
		akash_visual_layout_builder_uninstall_delete_dir(trailingslashit($akash_visual_layout_builder_uploads['basedir']) . 'akash-visual-layout-builder-templates');
	}
}

akash_visual_layout_builder_uninstall();
