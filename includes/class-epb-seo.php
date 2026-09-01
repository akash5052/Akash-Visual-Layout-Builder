<?php

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Per-page SEO settings and front-end meta output.
 */
class EPB_SEO {

	const META_KEY = '_epb_seo';

	public function __construct() {
		add_filter('pre_get_document_title', [ $this, 'filter_document_title' ]);
		add_action('wp_head', [ $this, 'output_head_meta' ], 5);
	}

	/**
	 * Default SEO structure.
	 *
	 * @return array
	 */
	public static function defaults() {
		return [
			'meta_title'       => '',
			'meta_description' => '',
			'focus_keyword'    => '',
			'social_image'     => '',
			'social_image_id'  => 0,
		];
	}

	/**
	 * Get SEO data for a post.
	 *
	 * @param int $post_id Post ID.
	 * @return array
	 */
	public static function get_page($post_id) {
		$stored = get_post_meta($post_id, self::META_KEY, true);
		return self::normalize(is_array($stored) ? $stored : []);
	}

	/**
	 * Save SEO data for a post.
	 *
	 * @param int   $post_id Post ID.
	 * @param array $seo     SEO fields.
	 * @return array
	 */
	public static function save_page($post_id, $seo) {
		$clean = self::normalize($seo);
		update_post_meta($post_id, self::META_KEY, $clean);
		return $clean;
	}

	/**
	 * Normalize SEO fields.
	 *
	 * @param array $seo Raw SEO.
	 * @return array
	 */
	public static function normalize($seo) {
		$defaults = self::defaults();

		$image_id = isset($seo['social_image_id']) ? absint($seo['social_image_id']) : 0;
		$image    = isset($seo['social_image']) ? esc_url_raw($seo['social_image']) : '';

		if ($image_id > 0) {
			$attachment_url = wp_get_attachment_image_url($image_id, 'large');
			if ($attachment_url) {
				$image = $attachment_url;
			}
		}

		return [
			'meta_title'       => sanitize_text_field($seo['meta_title'] ?? $defaults['meta_title']),
			'meta_description' => sanitize_textarea_field($seo['meta_description'] ?? $defaults['meta_description']),
			'focus_keyword'    => sanitize_text_field($seo['focus_keyword'] ?? $defaults['focus_keyword']),
			'social_image'     => $image,
			'social_image_id'  => $image_id,
		];
	}

	/**
	 * Format for REST/editor.
	 *
	 * @param array $seo SEO data.
	 * @return array
	 */
	public static function format($seo) {
		return self::normalize($seo);
	}

	/**
	 * Resolved document title for a post.
	 *
	 * @param int    $post_id Post ID.
	 * @param string $fallback Fallback title.
	 * @return string
	 */
	public static function get_title($post_id, $fallback = '') {
		$seo = self::get_page($post_id);
		if (!empty($seo['meta_title'])) {
			return $seo['meta_title'];
		}
		return $fallback;
	}

	/**
	 * Resolved social image URL.
	 *
	 * @param int $post_id Post ID.
	 * @return string
	 */
	public static function get_social_image_url($post_id) {
		$seo = self::get_page($post_id);
		if (!empty($seo['social_image'])) {
			return $seo['social_image'];
		}

		if (has_post_thumbnail($post_id)) {
			$thumb = get_the_post_thumbnail_url($post_id, 'large');
			if ($thumb) {
				return $thumb;
			}
		}

		return '';
	}

	/**
	 * Whether post is an EPB page with SEO output enabled.
	 *
	 * @param int|null $post_id Post ID.
	 * @return int|false
	 */
	private function get_epb_post_id($post_id = null) {
		if ($post_id === null) {
			if (!is_singular(EPB_Post_Types::types())) {
				return false;
			}
			$post_id = get_queried_object_id();
		}

		if (!$post_id || get_post_meta($post_id, EPB_Renderer::META_ENABLED, true) !== '1') {
			return false;
		}

		return (int) $post_id;
	}

	/**
	 * Filter document title for EPB pages.
	 *
	 * @param string $title Current title.
	 * @return string
	 */
	public function filter_document_title($title) {
		$post_id = $this->get_epb_post_id();
		if (!$post_id) {
			return $title;
		}

		$custom = self::get_title($post_id, '');
		return $custom !== '' ? $custom : $title;
	}

	/**
	 * Output SEO meta tags in wp_head.
	 */
	public function output_head_meta() {
		$post_id = $this->get_epb_post_id();
		if (!$post_id) {
			return;
		}

		// Let dedicated SEO plugins handle output when active.
		if (defined('WPSEO_VERSION') || defined('RANK_MATH_VERSION') || defined('AIOSEO_VERSION')) {
			return;
		}

		$seo       = self::get_page($post_id);
		$post      = get_post($post_id);
		$permalink = get_permalink($post_id);
		$title     = self::get_title($post_id, $post ? $post->post_title : '');
		$desc      = $seo['meta_description'];
		$image     = self::get_social_image_url($post_id);
		$keyword   = $seo['focus_keyword'];

		if ($desc !== '') {
			printf(
				'<meta name="description" content="%s" />' . "\n",
				esc_attr($desc)
			);
		}

		if ($keyword !== '') {
			printf(
				'<meta name="keywords" content="%s" />' . "\n",
				esc_attr($keyword)
			);
		}

		if ($permalink) {
			printf(
				'<link rel="canonical" href="%s" />' . "\n",
				esc_url($permalink)
			);
		}

		$og_title = $title !== '' ? $title : ($post ? $post->post_title : '');
		if ($og_title !== '') {
			printf('<meta property="og:title" content="%s" />' . "\n", esc_attr($og_title));
			printf('<meta name="twitter:title" content="%s" />' . "\n", esc_attr($og_title));
		}

		if ($desc !== '') {
			printf('<meta property="og:description" content="%s" />' . "\n", esc_attr($desc));
			printf('<meta name="twitter:description" content="%s" />' . "\n", esc_attr($desc));
		}

		if ($permalink) {
			printf('<meta property="og:url" content="%s" />' . "\n", esc_url($permalink));
		}

		printf('<meta property="og:type" content="%s" />' . "\n", esc_attr(is_singular('post') ? 'article' : 'website'));
		printf('<meta property="og:site_name" content="%s" />' . "\n", esc_attr(get_bloginfo('name')));

		if ($image !== '') {
			printf('<meta property="og:image" content="%s" />' . "\n", esc_url($image));
			printf('<meta name="twitter:image" content="%s" />' . "\n", esc_url($image));
			echo '<meta name="twitter:card" content="summary_large_image" />' . "\n";
		} else {
			echo '<meta name="twitter:card" content="summary" />' . "\n";
		}
	}

	/**
	 * Build preview head meta tags (for full-page preview).
	 *
	 * @param int    $post_id Post ID.
	 * @param string $fallback_title Fallback title.
	 * @return array{title: string, meta_html: string}
	 */
	public static function preview_head($post_id, $fallback_title = '') {
		$seo   = self::get_page($post_id);
		$title = self::get_title($post_id, $fallback_title);
		$desc  = $seo['meta_description'];
		$image = self::get_social_image_url($post_id);
		$tags  = '';

		if ($desc !== '') {
			$tags .= '<meta name="description" content="' . esc_attr($desc) . '">' . "\n  ";
		}

		if (!empty($seo['focus_keyword'])) {
			$tags .= '<meta name="keywords" content="' . esc_attr($seo['focus_keyword']) . '">' . "\n  ";
		}

		if ($image !== '') {
			$tags .= '<meta property="og:image" content="' . esc_url($image) . '">' . "\n  ";
		}

		return [
			'title'     => esc_html($title !== '' ? $title : $fallback_title),
			'meta_html' => $tags,
		];
	}
}
