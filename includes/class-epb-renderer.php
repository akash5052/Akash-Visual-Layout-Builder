<?php

if (!defined('ABSPATH')) {
	exit;
}

class EPB_Renderer {

	const META_HTML    = '_epb_html';
	const META_CSS     = '_epb_css';
	const META_JS      = '_epb_js';
	const META_ENABLED = '_epb_enabled';
	const META_LEGACY  = '_epb_code';
	const META_EDIT_MODE = '_epb_edit_mode';
	const META_VISUAL    = '_epb_visual';

	/**
	 * Get editor mode for a page.
	 *
	 * The plugin is visual-only. Legacy `_epb_edit_mode` values are ignored.
	 *
	 * @param int $post_id Post ID.
	 * @return string
	 */
	public static function get_edit_mode($post_id) {
		unset($post_id);
		return 'visual';
	}

	/**
	 * Save editor mode (always visual).
	 *
	 * @param int    $post_id Post ID.
	 * @param string $mode    Ignored; visual is the only mode.
	 */
	public static function save_edit_mode($post_id, $mode = 'visual') {
		unset($mode);
		update_post_meta($post_id, self::META_EDIT_MODE, 'visual');
	}

	/**
	 * Get visual builder document JSON.
	 *
	 * @param int $post_id Post ID.
	 * @return array|null
	 */
	public static function get_visual_document($post_id) {
		$raw = get_post_meta($post_id, self::META_VISUAL, true);
		if (!is_string($raw) || $raw === '') {
			return null;
		}
		$decoded = json_decode($raw, true);
		return is_array($decoded) ? $decoded : null;
	}

	/**
	 * Save visual builder document.
	 *
	 * @param int   $post_id Post ID.
	 * @param array $doc     Visual document.
	 */
	public static function save_visual_document($post_id, $doc) {
		if (!is_array($doc)) {
			delete_post_meta($post_id, self::META_VISUAL);
			return;
		}
		$doc = EPB_Output::scrub_visual($doc);
		update_post_meta($post_id, self::META_VISUAL, wp_json_encode($doc));
	}

	/**
	 * Default starter code for a new page.
	 *
	 * @param string $title Page title.
	 * @return array
	 */
	public static function default_code($title = 'Untitled Page') {
		return self::normalize_code([]);
	}

	/**
	 * Normalize code array from request or storage.
	 *
	 * @param array $code Raw code array.
	 * @return array
	 */
	public static function normalize_code($code) {
		return [
			'html' => isset($code['html']) ? (string) $code['html'] : '',
			'css'  => isset($code['css']) ? (string) $code['css'] : '',
			'js'   => isset($code['js']) ? (string) $code['js'] : '',
		];
	}

	/**
	 * Sanitize editor code from POST/REST payloads.
	 *
	 * @param mixed $code Raw code array.
	 * @return array
	 */
	public static function sanitize_code_input($code) {
		if (!is_array($code)) {
			return self::normalize_code([]);
		}

		$code = map_deep($code, 'wp_unslash');

		return self::normalize_code([
			'html' => self::sanitize_code_field($code['html'] ?? ''),
			'css'  => self::sanitize_code_field($code['css'] ?? ''),
			'js'   => self::sanitize_code_field($code['js'] ?? ''),
		]);
	}

	/**
	 * Sanitize a single editor code field (HTML, CSS, or JS).
	 *
	 * @param mixed $value Raw field value.
	 * @return string
	 */
	public static function sanitize_code_field($value) {
		if (!is_string($value) && !is_numeric($value)) {
			return '';
		}

		$value = (string) $value;
		$value = wp_unslash($value);

		return str_replace("\0", '', $value);
	}

	/**
	 * Get page code from meta, with defaults.
	 *
	 * @param int $post_id Post ID.
	 * @return array
	 */
	public static function get_page_code($post_id) {
		$html = get_post_meta($post_id, self::META_HTML, true);
		$css  = get_post_meta($post_id, self::META_CSS, true);
		$js   = get_post_meta($post_id, self::META_JS, true);

		if ($html !== '' || $css !== '' || $js !== '') {
			return [
				'html' => is_string($html) ? $html : '',
				'css'  => is_string($css) ? $css : '',
				'js'   => '',
			];
		}

		$legacy = get_post_meta($post_id, self::META_LEGACY, true);

		if (!empty($legacy)) {
			$decoded = json_decode($legacy, true);

			if (!is_array($decoded)) {
				$decoded = json_decode(wp_unslash($legacy), true);
			}

			if (is_array($decoded)) {
				return self::normalize_code($decoded);
			}
		}

		return self::default_code(get_the_title($post_id) ?: 'Untitled Page');
	}

	/**
	 * Save compiled visual output. Client HTML/CSS/JS is ignored.
	 *
	 * @param int   $post_id Post ID.
	 * @param array $code    Ignored.
	 */
	public static function save_page_code($post_id, $code = []) {
		unset($code);
		$visual = self::get_visual_document($post_id);

		if (is_array($visual)) {
			$clean = EPB_Output::compile_visual($visual);
		} else {
			$clean = [
				'html' => '',
				'css'  => '',
				'js'   => '',
			];
		}

		update_post_meta($post_id, self::META_ENABLED, '1');
		update_post_meta($post_id, self::META_HTML, $clean['html']);
		update_post_meta($post_id, self::META_CSS, $clean['css']);
		update_post_meta($post_id, self::META_JS, '');

		delete_post_meta($post_id, self::META_LEGACY);
	}

	/**
	 * Save visual document and compile HTML/CSS from it.
	 *
	 * @param int   $post_id Post ID.
	 * @param array $doc     Visual document.
	 */
	public static function save_visual_and_compile($post_id, $doc) {
		self::save_visual_document($post_id, $doc);
		self::save_page_code($post_id, []);
	}

	/**
	 * Render HTML output for the_content.
	 *
	 * @param array $code    Page code.
	 * @param int   $post_id Post ID.
	 * @return string
	 */
	public static function render_html($code, $post_id) {
		$html = isset($code['html']) ? trim($code['html']) : '';

		if ($html === '') {
			return '';
		}

		$html = EPB_Output::kses_html($html);

		$html = EPB_Posts_Widget::hydrate_html($html, (int) $post_id);

		if (preg_match('/^<div[^>]*class="[^"]*epb-page[^"]*"/i', $html)) {
			return $html;
		}

		$attrs = 'class="epb-page epb-page-' . esc_attr($post_id) . '" data-epb-page="' . esc_attr($post_id) . '"';

		return '<div ' . $attrs . '>' . $html . '</div>';
	}

	/**
	 * Get CSS for output.
	 *
	 * @param array $code Page code.
	 * @return string
	 */
	public static function render_css($code) {
		return EPB_Output::sanitize_css(isset($code['css']) ? $code['css'] : '');
	}

	/**
	 * Get JS for output.
	 *
	 * @param array $code Page code.
	 * @return string
	 */
	public static function render_js($code) {
		unset($code);
		return '';
	}

	/**
	 * Build HTML for post_content on publish.
	 *
	 * @param array $code    Page code.
	 * @param int   $post_id Post ID.
	 * @return string
	 */
	public static function render_for_post_content($code, $post_id) {
		return self::render_html($code, $post_id);
	}
}
