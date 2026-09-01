<?php

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Site-wide tracking integrations (GTM, GA4, Ads, Meta Pixel).
 *
 * Only account IDs are stored. Official vendor scripts are enqueued;
 * users cannot paste arbitrary CSS, JavaScript, or PHP.
 */
class EPB_Tracking {

	const OPTION_KEY = 'epb_tracking';

	public function __construct() {
		add_action('wp_enqueue_scripts', [ $this, 'enqueue_scripts' ], 1);
		add_action('wp_body_open', [ $this, 'output_body_open' ], 1);
		add_action('wp_footer', [ $this, 'output_footer' ], 1);
	}

	/**
	 * Register and enqueue third-party tracking scripts from account IDs.
	 *
	 * IDs are localized into a static plugin script. Users cannot paste JavaScript.
	 */
	public function enqueue_scripts() {
		if (!$this->should_output()) {
			return;
		}

		$settings = self::get();
		$ga_ids   = [];
		$gtm_id   = !empty($settings['gtm_id']) ? $settings['gtm_id'] : '';
		$pixel    = !empty($settings['facebook_pixel_id']) ? $settings['facebook_pixel_id'] : '';

		if ($gtm_id === '') {
			$ga_ids = array_values(array_filter([ $settings['ga4_id'], $settings['google_ads_id'] ]));
		}

		$js_file = EPB_PLUGIN_DIR . 'assets/js/tracking.js';
		wp_register_script(
			'wpvisualx-tracking',
			EPB_PLUGIN_URL . 'assets/js/tracking.js',
			[],
			file_exists($js_file) ? filemtime($js_file) : EPB_VERSION,
			false
		);
		wp_localize_script(
			'wpvisualx-tracking',
			'epbTracking',
			[
				'gtmId'   => $gtm_id,
				'gaIds'   => $ga_ids,
				'pixelId' => $pixel,
			]
		);
		wp_enqueue_script('wpvisualx-tracking');

		if ($gtm_id !== '') {
			wp_enqueue_script(
				'wpvisualx-gtm',
				'https://www.googletagmanager.com/gtm.js?id=' . rawurlencode($gtm_id),
				[ 'wpvisualx-tracking' ],
				EPB_VERSION,
				false
			);
			wp_script_add_data('wpvisualx-gtm', 'async', true);
		} elseif (!empty($ga_ids)) {
			wp_enqueue_script(
				'wpvisualx-google-gtag',
				'https://www.googletagmanager.com/gtag/js?id=' . rawurlencode($ga_ids[0]),
				[ 'wpvisualx-tracking' ],
				EPB_VERSION,
				false
			);
			wp_script_add_data('wpvisualx-google-gtag', 'async', true);
		}

		if ($pixel !== '') {
			wp_enqueue_script(
				'wpvisualx-meta-pixel',
				'https://connect.facebook.net/en_US/fbevents.js',
				[ 'wpvisualx-tracking' ],
				EPB_VERSION,
				false
			);
			wp_script_add_data('wpvisualx-meta-pixel', 'async', true);
		}
	}

	/**
	 * Default tracking settings.
	 *
	 * @return array
	 */
	public static function defaults() {
		return [
			'enabled'           => false,
			'scope'             => 'entire_site',
			'gtm_id'            => '',
			'ga4_id'            => '',
			'google_ads_id'     => '',
			'facebook_pixel_id' => '',
		];
	}

	/**
	 * Get tracking settings.
	 *
	 * @return array
	 */
	public static function get() {
		$stored = get_option(self::OPTION_KEY, []);
		if (!is_array($stored)) {
			$stored = [];
		}

		return wp_parse_args($stored, self::defaults());
	}

	/**
	 * Sanitize and save tracking settings.
	 *
	 * @param array $input Raw input.
	 * @return array
	 */
	public static function save($input) {
		$current = self::get();
		$scope   = sanitize_key($input['scope'] ?? $current['scope']);

		if (!in_array($scope, [ 'entire_site', 'epb_only' ], true)) {
			$scope = 'entire_site';
		}

		$settings = [
			'enabled'           => !empty($input['enabled']),
			'scope'             => $scope,
			'gtm_id'            => self::sanitize_gtm_id($input['gtm_id'] ?? ''),
			'ga4_id'            => self::sanitize_ga_id($input['ga4_id'] ?? ''),
			'google_ads_id'     => self::sanitize_ga_id($input['google_ads_id'] ?? ''),
			'facebook_pixel_id' => self::sanitize_pixel_id($input['facebook_pixel_id'] ?? ''),
		];

		update_option(self::OPTION_KEY, $settings);

		return $settings;
	}

	/**
	 * Format settings for the editor API.
	 *
	 * @return array
	 */
	public static function format() {
		return self::get();
	}

	/**
	 * @param string $id GTM container ID.
	 * @return string
	 */
	private static function sanitize_gtm_id($id) {
		$id = strtoupper(sanitize_text_field($id));
		if ($id === '') {
			return '';
		}
		return preg_match('/^GTM-[A-Z0-9]+$/', $id) ? $id : '';
	}

	/**
	 * @param string $id GA4 or Google Ads ID.
	 * @return string
	 */
	private static function sanitize_ga_id($id) {
		$id = strtoupper(sanitize_text_field($id));
		if ($id === '') {
			return '';
		}
		return preg_match('/^(G|AW|GT)-[A-Z0-9]+$/', $id) ? $id : '';
	}

	/**
	 * @param string $id Facebook pixel ID.
	 * @return string
	 */
	private static function sanitize_pixel_id($id) {
		$id = sanitize_text_field($id);
		if ($id === '') {
			return '';
		}
		return preg_match('/^\d+$/', $id) ? $id : '';
	}

	/**
	 * Whether tracking should load on the current request.
	 *
	 * @return bool
	 */
	private function should_output() {
		if (is_admin()) {
			return false;
		}

		$settings = self::get();

		if (empty($settings['enabled'])) {
			return false;
		}

		if ($settings['scope'] === 'epb_only') {
			$post_id = is_singular(EPB_Post_Types::types()) ? get_queried_object_id() : 0;
			if (!$post_id || get_post_meta($post_id, EPB_Renderer::META_ENABLED, true) !== '1') {
				return false;
			}
		}

		return self::has_any_tracking($settings);
	}

	/**
	 * @param array $settings Tracking settings.
	 * @return bool
	 */
	private static function has_any_tracking($settings) {
		return !empty($settings['gtm_id'])
			|| !empty($settings['ga4_id'])
			|| !empty($settings['google_ads_id'])
			|| !empty($settings['facebook_pixel_id']);
	}

	/**
	 * Output GTM noscript iframe after <body>.
	 */
	public function output_body_open() {
		if (!$this->should_output()) {
			return;
		}

		$settings = self::get();

		if (!empty($settings['gtm_id'])) {
			echo "<!-- Google Tag Manager (noscript) -->\n";
			echo '<noscript><iframe src="' . esc_url('https://www.googletagmanager.com/ns.html?id=' . rawurlencode($settings['gtm_id'])) . '" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>' . "\n";
			echo "<!-- End Google Tag Manager (noscript) -->\n";
		}
	}

	/**
	 * Output Meta Pixel noscript image.
	 */
	public function output_footer() {
		if (!$this->should_output()) {
			return;
		}

		$settings = self::get();

		if (!empty($settings['facebook_pixel_id'])) {
			echo '<noscript><img height="1" width="1" style="display:none" alt="" src="' . esc_url('https://www.facebook.com/tr?id=' . rawurlencode($settings['facebook_pixel_id']) . '&ev=PageView&noscript=1') . '" /></noscript>' . "\n";
		}
	}
}
