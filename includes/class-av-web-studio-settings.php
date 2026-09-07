<?php

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Central plugin settings for AI, images, editor, SEO, popups, and permissions.
 */
class Av_Web_Studio_Settings {

	const OPTION_KEY = 'av_web_studio_settings';

	const DEFAULT_GEMINI_MODEL = 'gemini-2.5-flash';
	const DEFAULT_CLAUDE_MODEL = 'claude-haiku-4-5-20251001';

	/**
	 * Full defaults for all plugin features.
	 *
	 * @return array<string,mixed>
	 */
	public static function defaults() {
		return [
			// AI (disabled until the site owner enables it)
			'ai_enabled'           => false,
			'gemini_api_key'       => '',
			'gemini_model'         => self::DEFAULT_GEMINI_MODEL,
			'claude_api_key'       => '',
			'claude_model'         => self::DEFAULT_CLAUDE_MODEL,
			'image_suggestions'    => 'auto', // auto | claude | gemini | local
			'ai_capability'        => 'edit_posts',
			'ai_history_turns'     => 40,
			'ai_panel_default_open'=> false,

			// Images
			'images_enabled'       => false,
			'image_width'          => 1200,
			'image_height'         => 700,
			'images_replace_broken'=> true,

			// Editor
			'default_list_status'  => 'publish',
			'default_preview'      => true,
			'default_theme'        => 'system', // light | dark | system
			'autosave_delay_ms'    => 1500,

			// SEO (site-wide defaults)
			'seo_defer_to_plugins' => true,
			'seo_meta_template'    => '',
			'seo_default_og_image' => '',

			// Popups
			'popups_enabled'       => true,

			// Layout
			'layout_header_default'=> true,
			'layout_footer_default'=> true,

			// Google Fonts (off until the site owner opts in)
			'google_fonts_enabled' => false,

			// Tracking link (actual tracking lives in av_web_studio_tracking)
			'tracking_enabled_hint'=> true,

			// Permissions
			'builder_capability'   => 'edit_posts',
			'settings_capability'  => 'manage_options',

			// SVG
			'svg_max_kb'           => 512,
		];
	}

	/**
	 * Raw option merged with defaults (no secrets resolved).
	 *
	 * @return array<string,mixed>
	 */
	public static function get_raw() {
		$stored = get_option(self::OPTION_KEY, []);
		if (!is_array($stored)) {
			$stored = [];
		}
		return wp_parse_args($stored, self::defaults());
	}

	/**
	 * AI settings with constant overrides for preferred models.
	 *
	 * @return array<string,mixed>
	 */
	public static function get_ai_settings() {
		$settings = self::get_raw();

		if (defined('AV_WEB_STUDIO_GEMINI_MODEL') && AV_WEB_STUDIO_GEMINI_MODEL) {
			$settings['gemini_model'] = AV_WEB_STUDIO_GEMINI_MODEL;
		}

		if (defined('AV_WEB_STUDIO_CLAUDE_MODEL') && AV_WEB_STUDIO_CLAUDE_MODEL) {
			$settings['claude_model'] = AV_WEB_STUDIO_CLAUDE_MODEL;
		}

		return $settings;
	}

	/**
	 * Whether plugin AI is on and the WordPress AI Client can generate text.
	 *
	 * @return bool
	 */
	public static function has_cloud_ai() {
		$s = self::get_raw();
		return !empty($s['ai_enabled']) && Av_Web_Studio_AI_Client::is_available();
	}

	public static function has_gemini_config() {
		return self::has_cloud_ai();
	}

	public static function has_claude_config() {
		return self::has_cloud_ai();
	}

	public static function has_any_ai_config() {
		return self::has_cloud_ai();
	}

	/**
	 * Whether the current user should see AI UI in the builder.
	 *
	 * @return bool
	 */
	public static function current_user_can_see_ai_panel() {
		$s = self::get_raw();
		$cap = !empty($s['ai_capability']) ? $s['ai_capability'] : 'edit_posts';
		return current_user_can($cap);
	}

	/**
	 * Whether current user can use the AI assistant.
	 *
	 * @return bool
	 */
	public static function current_user_can_use_ai() {
		if (!self::current_user_can_see_ai_panel()) {
			return false;
		}
		$s = self::get_raw();
		return !empty($s['ai_enabled']);
	}

	/**
	 * Whether AI generation/review requests can run (enabled).
	 * Local templates still work without a cloud provider.
	 *
	 * @return bool
	 */
	public static function current_user_can_run_ai() {
		return self::current_user_can_use_ai();
	}

	/**
	 * Whether current user can manage plugin settings.
	 *
	 * @return bool
	 */
	public static function current_user_can_manage() {
		$s = self::get_raw();
		$cap = !empty($s['settings_capability']) ? $s['settings_capability'] : 'manage_options';
		return current_user_can($cap);
	}

	/**
	 * Image suggestion provider preference.
	 *
	 * @return string auto|claude|gemini|local
	 */
	public static function image_suggestions_mode() {
		$s = self::get_raw();
		$mode = $s['image_suggestions'] ?? 'auto';
		$allowed = [ 'auto', 'claude', 'gemini', 'local' ];
		return in_array($mode, $allowed, true) ? $mode : 'auto';
	}

	/**
	 * Public (non-secret) settings for the editor.
	 *
	 * @return array<string,mixed>
	 */
	public static function public_settings() {
		$s = self::get_ai_settings();
		return [
			'ai_enabled'            => (bool) $s['ai_enabled'],
			'gemini_model'          => $s['gemini_model'],
			'claude_model'          => $s['claude_model'],
			'image_suggestions'     => self::image_suggestions_mode(),
			'is_configured'         => self::has_any_ai_config(),
			'has_gemini'            => self::has_cloud_ai(),
			'has_claude'            => self::has_cloud_ai(),
			'ai_client_available'   => Av_Web_Studio_AI_Client::is_available(),
			'ai_client_core'        => Av_Web_Studio_AI_Client::core_available(),
			'connectors_url'        => Av_Web_Studio_AI_Client::connectors_url(),
			'has_smart_engine'      => true,
			'images_enabled'        => (bool) $s['images_enabled'],
			'image_width'           => absint($s['image_width']),
			'image_height'          => absint($s['image_height']),
			'default_list_status'   => $s['default_list_status'],
			'default_preview'       => (bool) $s['default_preview'],
			'default_theme'         => $s['default_theme'],
			'autosave_delay_ms'     => absint($s['autosave_delay_ms']),
			'ai_history_turns'      => absint($s['ai_history_turns']),
			'ai_panel_default_open' => (bool) $s['ai_panel_default_open'],
			'seo_defer_to_plugins'  => (bool) $s['seo_defer_to_plugins'],
			'seo_meta_template'     => (string) $s['seo_meta_template'],
			'seo_default_og_image'  => (string) $s['seo_default_og_image'],
			'popups_enabled'        => (bool) $s['popups_enabled'],
			'layout_header_default' => (bool) $s['layout_header_default'],
			'layout_footer_default' => (bool) $s['layout_footer_default'],
			'google_fonts_enabled'  => (bool) $s['google_fonts_enabled'],
			'svg_max_kb'            => absint($s['svg_max_kb']),
			'can_manage_settings'   => self::current_user_can_manage(),
		];
	}

	/**
	 * Admin settings payload (includes masked keys, never raw secrets in list views).
	 *
	 * @return array<string,mixed>
	 */
	public static function admin_settings() {
		$s      = self::get_ai_settings();
		$public = self::public_settings();

		return array_merge($public, [
			'ai_capability'        => $s['ai_capability'],
			'builder_capability'   => $s['builder_capability'],
			'settings_capability'  => $s['settings_capability'],
			'images_replace_broken'=> (bool) $s['images_replace_broken'],
			'available_gemini_models' => self::gemini_models(),
			'available_claude_models' => self::claude_models(),
		]);
	}

	/**
	 * @return array<int,array{value:string,label:string}>
	 */
	public static function gemini_models() {
		return [
			[ 'value' => 'gemini-2.5-flash', 'label' => 'Gemini 2.5 Flash (recommended)' ],
			[ 'value' => 'gemini-2.5-pro', 'label' => 'Gemini 2.5 Pro' ],
			[ 'value' => 'gemini-2.0-flash', 'label' => 'Gemini 2.0 Flash' ],
			[ 'value' => 'gemini-1.5-flash', 'label' => 'Gemini 1.5 Flash' ],
			[ 'value' => 'gemini-1.5-pro', 'label' => 'Gemini 1.5 Pro' ],
		];
	}

	/**
	 * @return array<int,array{value:string,label:string}>
	 */
	public static function claude_models() {
		return [
			[ 'value' => 'claude-haiku-4-5-20251001', 'label' => 'Claude Haiku 4.5 (fast, recommended)' ],
			[ 'value' => 'claude-sonnet-4-5-20250929', 'label' => 'Claude Sonnet 4.5' ],
			[ 'value' => 'claude-sonnet-4-20250514', 'label' => 'Claude Sonnet 4' ],
			[ 'value' => 'claude-3-5-haiku-20241022', 'label' => 'Claude 3.5 Haiku' ],
			[ 'value' => 'claude-3-5-sonnet-20241022', 'label' => 'Claude 3.5 Sonnet' ],
		];
	}

	/**
	 * Save settings from admin request.
	 *
	 * @param array $input Raw input.
	 * @return array{success:bool,settings:array,message:string}
	 */
	public static function save($input) {
		if (!is_array($input)) {
			return [
				'success'  => false,
				'settings' => self::admin_settings(),
				'message'  => __('Invalid settings payload.', 'av-web-studio'),
			];
		}

		$current = self::get_raw();
		$next    = $current;

		$bools = [
			'ai_enabled', 'ai_panel_default_open', 'images_enabled', 'images_replace_broken',
			'default_preview', 'seo_defer_to_plugins', 'popups_enabled',
			'layout_header_default', 'layout_footer_default', 'google_fonts_enabled',
		];
		foreach ($bools as $key) {
			if (array_key_exists($key, $input)) {
				$next[ $key ] = (bool) $input[ $key ];
			}
		}

		$text = [
			'gemini_model', 'claude_model', 'image_suggestions', 'ai_capability',
			'default_list_status', 'default_theme', 'seo_meta_template', 'seo_default_og_image',
			'builder_capability', 'settings_capability',
		];
		foreach ($text as $key) {
			if (array_key_exists($key, $input)) {
				$next[ $key ] = sanitize_text_field((string) $input[ $key ]);
			}
		}

		$ints = [ 'image_width', 'image_height', 'autosave_delay_ms', 'ai_history_turns', 'svg_max_kb' ];
		foreach ($ints as $key) {
			if (array_key_exists($key, $input)) {
				$next[ $key ] = absint($input[ $key ]);
			}
		}

		// Validate enums.
		if (!in_array($next['image_suggestions'], [ 'auto', 'claude', 'gemini', 'local' ], true)) {
			$next['image_suggestions'] = 'auto';
		}
		if (!in_array($next['default_theme'], [ 'light', 'dark', 'system' ], true)) {
			$next['default_theme'] = 'system';
		}
		if (!in_array($next['default_list_status'], [ 'publish', 'draft', 'pending', 'private', 'any' ], true)) {
			$next['default_list_status'] = 'publish';
		}

		$next['image_width']       = max(200, min(2400, absint($next['image_width'])));
		$next['image_height']      = max(200, min(1800, absint($next['image_height'])));
		$next['autosave_delay_ms'] = max(500, min(30000, absint($next['autosave_delay_ms'])));
		$next['ai_history_turns']  = max(5, min(100, absint($next['ai_history_turns'])));
		$next['svg_max_kb']        = max(64, min(5120, absint($next['svg_max_kb'])));

		update_option(self::OPTION_KEY, $next);

		return [
			'success'  => true,
			'settings' => self::admin_settings(),
			'message'  => __('Settings saved.', 'av-web-studio'),
		];
	}

	/**
	 * Whether the site owner opted in to loading Google Fonts.
	 *
	 * @return bool
	 */
	public static function google_fonts_enabled() {
		$raw = self::get_raw();
		return !empty($raw['google_fonts_enabled']);
	}
}
