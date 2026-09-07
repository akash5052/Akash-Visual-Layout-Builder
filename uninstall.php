<?php
/**
 * Uninstall AV Web Studio.
 *
 * Removes plugin options and post meta. Does not delete pages/posts.
 *
 * @package Av_Web_Studio
 */

if (!defined('WP_UNINSTALL_PLUGIN')) {
	exit;
}

/**
 * Recursively remove a directory created by this plugin.
 *
 * @param string $dir Absolute path.
 */
function av_web_studio_uninstall_delete_dir($dir) {
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
			av_web_studio_uninstall_delete_dir($path);
		} else {
			wp_delete_file($path);
		}
	}

	rmdir($dir); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_rmdir -- uninstall cleanup of plugin upload dir.
}

/**
 * Delete plugin options, post meta, and uploaded template files.
 */
function av_web_studio_uninstall() {
	delete_option('av_web_studio_settings');
	delete_option('av_web_studio_tracking');
	delete_option('av_web_studio_global_layout');
	delete_option('av_web_studio_popups');
	delete_option('av_web_studio_custom_templates');

	$av_web_studio_meta_keys = [
		'_av_web_studio_html',
		'_av_web_studio_css',
		'_av_web_studio_js',
		'_av_web_studio_code',
		'_av_web_studio_enabled',
		'_av_web_studio_edit_mode',
		'_av_web_studio_visual',
		'_av_web_studio_page_layout',
		'_av_web_studio_layout',
		'_av_web_studio_seo',
		'_av_web_studio_page_template',
		'_av_web_studio_hide_title',
	];

	foreach ($av_web_studio_meta_keys as $av_web_studio_meta_key) {
		delete_post_meta_by_key($av_web_studio_meta_key);
	}

	$av_web_studio_uploads = wp_upload_dir();
	if (empty($av_web_studio_uploads['error']) && !empty($av_web_studio_uploads['basedir'])) {
		av_web_studio_uninstall_delete_dir(trailingslashit($av_web_studio_uploads['basedir']) . 'av-web-studio-templates');
	}
}

av_web_studio_uninstall();
