<?php

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Front-end preview URLs. Preview uses the normal WordPress request so styles
 * and scripts are enqueued instead of printing a standalone HTML document.
 */
class EPB_Preview {

	public function __construct() {
		add_action('template_redirect', [ $this, 'maybe_redirect_legacy' ], 0);
	}

	/**
	 * Pretty front-end style base URL: domain.com/page-name/
	 * Works for drafts via get_sample_permalink().
	 *
	 * @param int $page_id Page ID.
	 * @return string
	 */
	public static function get_pretty_base_url($page_id) {
		$page_id = absint($page_id);
		$post    = get_post($page_id);

		if (!$post) {
			return home_url('/');
		}

		if ($post->post_status === 'publish') {
			$url = get_permalink($page_id);
			return $url ? $url : home_url('/');
		}

		if (function_exists('get_sample_permalink')) {
			$sample = get_sample_permalink($page_id);
			if (is_array($sample) && !empty($sample[0]) && isset($sample[1]) && $sample[1] !== '') {
				$url = str_replace([ '%pagename%', '%postname%' ], (string) $sample[1], (string) $sample[0]);
				$url = preg_replace('#%[^%/]+%#', '', $url);
				if (is_string($url) && $url !== '' && strpos($url, '%') === false) {
					return $url;
				}
			}
		}

		$slug = $post->post_name ? $post->post_name : sanitize_title($post->post_title);
		if ($slug) {
			$structure = get_option('permalink_structure');
			if ($structure) {
				return trailingslashit(home_url('/' . $slug));
			}
		}

		$url = get_permalink($page_id);
		return $url ? $url : home_url('/');
	}

	/**
	 * Preview URL that loads the page through WordPress (theme + enqueued assets).
	 *
	 * @param int $page_id Page ID.
	 * @return string
	 */
	public static function get_url($page_id) {
		$page_id = absint($page_id);
		$post    = get_post($page_id);

		if (!$post) {
			return home_url('/');
		}

		if ($post->post_status === 'publish') {
			$url = get_permalink($page_id);
			return $url ? $url : home_url('/');
		}

		$url = get_preview_post_link($post);
		return $url ? $url : home_url('/');
	}

	/**
	 * Redirect legacy ?epb_preview= URLs to the WordPress preview/permalink.
	 */
	public function maybe_redirect_legacy() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if (!isset($_GET['epb_preview'])) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$page_id = absint(wp_unslash($_GET['epb_preview']));

		if ($page_id < 1) {
			wp_die(esc_html__('Invalid preview.', 'wpvisualx'), '', [ 'response' => 400 ]);
		}

		$page = get_post($page_id);

		if (!$page || !EPB_Post_Types::is_supported($page)) {
			wp_die(esc_html__('Content not found.', 'wpvisualx'), '', [ 'response' => 404 ]);
		}

		if (!EPB_Post_Types::user_can_edit($page_id)) {
			wp_die(esc_html__('You do not have permission to preview this content.', 'wpvisualx'), '', [ 'response' => 403 ]);
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$nonce = isset($_GET['epb_nonce']) ? sanitize_text_field(wp_unslash($_GET['epb_nonce'])) : '';

		if (!wp_verify_nonce($nonce, 'epb_preview_' . $page_id)) {
			wp_die(esc_html__('Preview link expired. Open Full Page preview again from the editor.', 'wpvisualx'), '', [ 'response' => 403 ]);
		}

		wp_safe_redirect(self::get_url($page_id));
		exit;
	}
}
