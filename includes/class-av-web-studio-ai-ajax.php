<?php

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Secure admin-ajax.php proxy for AI generation requests.
 *
 * Relays builder prompts through the WordPress AI Client (or local templates),
 * verifying a security nonce and restricting access by capability.
 */
class Av_Web_Studio_AI_Ajax {

	const NONCE_ACTION = 'av_web_studio_ai_ajax';

	public function __construct() {
		add_action('wp_ajax_av_web_studio_ai_generate', [$this, 'handle_generate']);
	}

	/**
	 * Handle an AI generation request coming through admin-ajax.php.
	 */
	public function handle_generate() {
		// Step 3: verify the security nonce.
		check_ajax_referer(self::NONCE_ACTION, 'nonce');

		// Step 3: enforce AI permission from settings.
		if (!Av_Web_Studio_Settings::current_user_can_use_ai()) {
			wp_send_json_error(['message' => __('You are not allowed to use the AI assistant.', 'av-web-studio')], 403);
		}

		$raw = Av_Web_Studio_Settings::get_raw();
		if (empty($raw['ai_enabled'])) {
			wp_send_json_error(['message' => __('AI is disabled in plugin settings.', 'av-web-studio')], 403);
		}

		$prompt = isset($_POST['prompt']) ? sanitize_textarea_field(wp_unslash($_POST['prompt'])) : '';
		if ($prompt === '') {
			wp_send_json_error(['message' => __('Prompt is required.', 'av-web-studio')], 400);
		}

		$title = isset($_POST['title']) ? sanitize_text_field(wp_unslash($_POST['title'])) : '';

		$context = null;
		if (isset($_POST['code']) && is_array($_POST['code'])) {
			$context = Av_Web_Studio_Renderer::normalize_code([
				'html' => isset($_POST['code']['html'])
					? sanitize_textarea_field(wp_unslash($_POST['code']['html']))
					: '',
				'css'  => isset($_POST['code']['css'])
					? sanitize_textarea_field(wp_unslash($_POST['code']['css']))
					: '',
				'js'   => '',
			]);
		}

		$result = Av_Web_Studio_AI::generate($prompt, Av_Web_Studio_Settings::get_ai_settings(), $context, $title);

		// Step 4/5: return structured JSON for the builder canvas.
		wp_send_json_success([
			'html'        => $result['code']['html'] ?? '',
			'css'         => '',
			'js'          => '',
			'action'      => $result['action'] ?? 'append',
			'explanation' => $result['message'] ?? '',
			'source'      => $result['source'] ?? 'ai',
		]);
	}
}

