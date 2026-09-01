<?php

if (!defined('ABSPATH')) {
	exit;
}

/**
 * WordPress AI Client adapter (wp_ai_client_prompt).
 *
 * Cloud AI goes through Core's provider-agnostic client so site owners
 * configure keys once under Settings → Connectors.
 */
class EPB_AI_Client {

	/**
	 * Whether Core exposes the AI Client API (WordPress 7.0+).
	 *
	 * @return bool
	 */
	public static function core_available() {
		return function_exists('wp_ai_client_prompt');
	}

	/**
	 * Whether a configured provider can generate text.
	 *
	 * @return bool
	 */
	public static function is_available() {
		if (!self::core_available()) {
			return false;
		}

		$builder = wp_ai_client_prompt('test');
		if (!is_object($builder) || !method_exists($builder, 'is_supported_for_text_generation')) {
			return false;
		}

		return (bool) $builder->is_supported_for_text_generation();
	}

	/**
	 * Admin URL for Settings → Connectors.
	 *
	 * @return string
	 */
	public static function connectors_url() {
		if (!self::core_available()) {
			return '';
		}

		return admin_url('options-connectors.php');
	}

	/**
	 * Generate a text completion via the WordPress AI Client.
	 *
	 * @param string $user   User prompt.
	 * @param string $system System instruction.
	 * @param array  $args   Optional. temperature, max_tokens, timeout, models, json_schema.
	 * @return string|WP_Error
	 */
	public static function generate_text($user, $system = '', $args = []) {
		if (!self::core_available()) {
			return new WP_Error(
				'epb_ai_client_missing',
				__('Cloud AI requires WordPress 7.0 or later with an AI provider configured under Settings → Connectors.', 'wpvisualx')
			);
		}

		$args     = is_array($args) ? $args : [];
		$timeout  = isset($args['timeout']) ? (float) $args['timeout'] : 120.0;
		$result   = self::request($user, $system, $args, $timeout);

		if (is_wp_error($result) && !empty($args['json_schema'])) {
			unset($args['json_schema']);
			$result = self::request($user, $system, $args, $timeout);
		}

		return $result;
	}

	/**
	 * Preferred model IDs for using_model_preference().
	 *
	 * @param string $mode auto|claude|gemini
	 * @return array<int,string>
	 */
	public static function preferred_models($mode = 'auto') {
		$settings = EPB_Settings::get_ai_settings();
		$gemini   = !empty($settings['gemini_model']) ? (string) $settings['gemini_model'] : EPB_Settings::DEFAULT_GEMINI_MODEL;
		$claude   = !empty($settings['claude_model']) ? (string) $settings['claude_model'] : EPB_Settings::DEFAULT_CLAUDE_MODEL;

		if ($mode === 'claude') {
			return array_values(array_unique([ $claude, $gemini ]));
		}
		if ($mode === 'gemini') {
			return array_values(array_unique([ $gemini, $claude ]));
		}

		return array_values(array_unique([ $gemini, $claude ]));
	}

	/**
	 * JSON schema for page generate/edit responses.
	 *
	 * @return array<string,mixed>
	 */
	public static function page_response_schema() {
		return [
			'type'       => 'object',
			'properties' => [
				'action'      => [
					'type' => 'string',
					'enum' => [ 'append', 'replace' ],
				],
				'html'        => [ 'type' => 'string' ],
				'css'         => [ 'type' => 'string' ],
				'js'          => [ 'type' => 'string' ],
				'explanation' => [ 'type' => 'string' ],
			],
			'required'   => [ 'action', 'html', 'css', 'js', 'explanation' ],
		];
	}

	/**
	 * JSON schema for image-suggestion responses.
	 *
	 * @return array<string,mixed>
	 */
	public static function image_suggestion_schema() {
		return [
			'type'       => 'object',
			'properties' => [
				'images' => [
					'type'  => 'array',
					'items' => [
						'type'       => 'object',
						'properties' => [
							'prompt'  => [ 'type' => 'string' ],
							'alt'     => [ 'type' => 'string' ],
							'section' => [ 'type' => 'string' ],
							'width'   => [ 'type' => 'integer' ],
							'height'  => [ 'type' => 'integer' ],
						],
						'required'   => [ 'prompt', 'alt' ],
					],
				],
			],
			'required'   => [ 'images' ],
		];
	}

	/**
	 * @param string $user     User prompt.
	 * @param string $system   System instruction.
	 * @param array  $args     Builder options.
	 * @param float  $timeout  HTTP timeout in seconds.
	 * @return string|WP_Error
	 */
	private static function request($user, $system, $args, $timeout) {
		$timeout_filter = static function () use ($timeout) {
			return $timeout;
		};
		add_filter('wp_ai_client_default_request_timeout', $timeout_filter);

		$builder = wp_ai_client_prompt($user);
		remove_filter('wp_ai_client_default_request_timeout', $timeout_filter);

		if (!is_object($builder) || !method_exists($builder, 'generate_text')) {
			return new WP_Error(
				'epb_ai_client_missing',
				__('The WordPress AI Client is not available.', 'wpvisualx')
			);
		}

		if ($system !== '' && method_exists($builder, 'using_system_instruction')) {
			$builder = $builder->using_system_instruction($system);
		}

		if (isset($args['temperature']) && method_exists($builder, 'using_temperature')) {
			$builder = $builder->using_temperature((float) $args['temperature']);
		}

		if (!empty($args['max_tokens']) && method_exists($builder, 'using_max_tokens')) {
			$builder = $builder->using_max_tokens((int) $args['max_tokens']);
		}

		if (!empty($args['models']) && is_array($args['models']) && method_exists($builder, 'using_model_preference')) {
			$models = array_values(array_filter(array_map('strval', $args['models'])));
			if (!empty($models)) {
				$builder = $builder->using_model_preference(...$models);
			}
		}

		if (!empty($args['json_schema']) && is_array($args['json_schema']) && method_exists($builder, 'as_json_response')) {
			$builder = $builder->as_json_response($args['json_schema']);
		}

		$text = $builder->generate_text();
		if (is_wp_error($text)) {
			return $text;
		}

		$text = is_string($text) ? trim($text) : '';
		if ($text === '') {
			return new WP_Error(
				'epb_ai_client_empty',
				__('The AI provider returned an empty response.', 'wpvisualx')
			);
		}

		return $text;
	}
}
