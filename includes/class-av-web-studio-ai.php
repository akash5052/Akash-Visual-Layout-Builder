<?php

if (!defined('ABSPATH')) {
	exit;
}

class Av_Web_Studio_AI {

	const SYSTEM_PROMPT = "You are an expert web developer assistant for a WordPress page builder. The user manages a page with separate HTML, CSS, and vanilla JavaScript files.

Return ONLY a raw JSON object (no markdown fences) with these keys:
- \"action\": \"append\" OR \"replace\"
- \"html\": HTML content
- \"css\": CSS styles
- \"js\": vanilla JavaScript (empty string if none). Use DOM APIs (querySelector, addEventListener, etc.). No React, JSX, or frameworks.
- \"explanation\": a short, plain-language description of what you generated or changed.

WHEN TO USE EACH ACTION:
- \"append\" — user wants a NEW section added. Return ONLY the new <section> html and its css/js.
- \"replace\" — user wants to EDIT, UPDATE, REMOVE, ANIMATE, ADD IMAGES, or BUILD A FULL PAGE. Return the FULL updated page html, css, and js.

CRITICAL RULES:
1. NEVER put the user's command/instruction as visible text on the page
2. Understand intent: \"edit last section\", \"change background\", \"add animations\", \"add images\" = action \"replace\" with full page
3. \"generate/create/add a [type] section\" = action \"append\" with new section only
4. For animations: include @keyframes, transition, transform, scroll-reveal classes in CSS; add IntersectionObserver JS if needed
5. For images: do not insert remote stock-photo URLs. Omit images or use empty src; the site owner adds photos from the WordPress Media Library.
6. When user describes a business, website, or landing page in one prompt → action \"replace\" with a COMPLETE multi-section page (hero, features, about, testimonials, FAQ, CTA)
7. When editing, preserve sections/content the user did not ask to change
8. Use unique CSS class prefixes per section
9. Make designs modern: gradients, shadows, hover effects, responsive layout

Example append:
{\"action\":\"append\",\"html\":\"<section class=\\\"pricing-sec\\\">...</section>\",\"css\":\".pricing-sec{padding:80px 20px}\",\"js\":\"\",\"explanation\":\"Added a pricing section with three plan cards.\"}

Example replace (edit):
{\"action\":\"replace\",\"html\":\"<h1>Title</h1>\\n<section>...</section>\",\"css\":\"...all css...\",\"js\":\"...all js...\",\"explanation\":\"Rebuilt the full landing page with a hero, features, and CTA.\"}";

	/**
	 * Generate HTML, CSS, JS from a prompt.
	 *
	 * @param string     $prompt   User prompt.
	 * @param array      $settings Plugin settings.
	 * @param array|null $context    Current page code for edit operations.
	 * @param string     $page_title WordPress page title.
	 * @return array
	 */
	public static function generate($prompt, $settings = [], $context = null, $page_title = '') {
		$settings = wp_parse_args($settings, Av_Web_Studio_Settings::defaults());
		$errors   = [];
		$context  = self::normalize_context($context);
		$intent   = Av_Web_Studio_AI_Intent::parse($prompt, $context);
		$intent   = self::prevent_duplicate_sections($prompt, $context, $intent);
		$intent['page_title'] = $page_title;

		$edit = self::try_edit_command($prompt, $context);
		if ($edit !== null) {
			return $edit;
		}

		// Image requests: Claude suggests relevant scenes, then we render them.
		$raw = Av_Web_Studio_Settings::get_raw();
		if ($intent['type'] === 'images' && $context && !empty($raw['images_enabled'])) {
			$claude = Av_Web_Studio_AI_Claude::enhance_page_images($prompt, $context, $page_title, $intent);
			if ($claude !== null) {
				return $claude;
			}
			$local = Av_Web_Studio_AI_Editor::apply($prompt, $context, $intent);
			if ($local !== null && ( $local['action'] ?? '' ) !== 'none') {
				return $local;
			}
		}

		$enhanced_prompt = self::build_context_prompt($prompt, $context, $intent, $page_title);

		if (Av_Web_Studio_Settings::has_cloud_ai()) {
			$cloud = self::try_cloud_ai($enhanced_prompt, $prompt, $context, $intent, $errors, $page_title);
			if ($cloud !== null) {
				return $cloud;
			}
		}

		// Handle edit / animate / images locally when cloud is unavailable.
		if ($context && ! in_array($intent['type'], ['add', 'seo_content'], true)) {
			$local = Av_Web_Studio_AI_Editor::apply($prompt, $context, $intent);
			if ($local !== null) {
				if (!empty($errors)) {
					$local['message'] .= ' (Cloud AI unavailable.)';
				}
				return $local;
			}
		}

		// Full page from smart templates (no cloud AI).
		if ($intent['type'] === 'seo_content' || Av_Web_Studio_AI_Intent::is_whole_page_prompt($prompt, $context)) {
			$topic = $intent['topic'] ?: Av_Web_Studio_AI_Intent::extract_content_topic($prompt) ?: Av_Web_Studio_AI_Intent::extract_topic($prompt) ?: $page_title ?: 'professional services';
			$code  = Av_Web_Studio_AI_Content::generate_full_page($topic, $page_title);
			$code  = Av_Web_Studio_AI_Claude::align_with_suggestions($code, $topic, $page_title, $prompt);
			$msg   = 'Industry-tailored page with images suggested by Claude.';
			if (!empty($errors)) {
				$msg .= ' (Cloud AI unavailable.)';
			}
			return self::wrap($code, 'claude', $msg, 'replace');
		}

		// Smart template fallback.
		if ($context && $intent['type'] !== 'add') {
			$local = Av_Web_Studio_AI_Editor::apply($prompt, $context, $intent);
			if ($local !== null) {
				if (!empty($errors)) {
					$local['message'] .= ' (Cloud AI unavailable.)';
				}
				return $local;
			}
		}

		$code = Av_Web_Studio_AI_Templates::generate($prompt, $context);
		$msg  = $intent['type'] === 'add' ? 'Modern section added with industry-matched content and images.' : 'Page updated.';
		if (!empty($errors)) {
			$msg .= ' (Cloud AI unavailable.)';
		}

		$action = $intent['type'] === 'add' ? 'append' : 'replace';
		if ($intent['type'] === 'add') {
			return self::wrap($code, 'smart', $msg, 'append');
		}

		$merged = [
			'html' => trim(($context['html'] ?? '') . "\n\n" . $code['html']),
			'css'  => trim(($context['css'] ?? '') . "\n\n" . $code['css']),
			'js'   => trim(($context['js'] ?? '') . "\n" . $code['js']),
		];

		return self::wrap($merged, 'smart', $msg, 'replace');
	}

	/**
	 * Call the WordPress AI Client for page generation.
	 *
	 * @param string     $enhanced_prompt Built prompt.
	 * @param string     $prompt          Original user prompt.
	 * @param array|null $context         Page code.
	 * @param array      $intent          Parsed intent.
	 * @param array      $errors          Collected error messages.
	 * @param string     $page_title      Page title.
	 * @return array|null
	 */
	private static function try_cloud_ai($enhanced_prompt, $prompt, $context, $intent, &$errors, $page_title = '') {
		$result = Av_Web_Studio_AI_Client::generate_text(
			$enhanced_prompt,
			self::SYSTEM_PROMPT,
			[
				'temperature'  => 0.35,
				'max_tokens'   => 8192,
				'timeout'      => 120,
				'models'       => Av_Web_Studio_AI_Client::preferred_models('auto'),
				'json_schema'  => Av_Web_Studio_AI_Client::page_response_schema(),
			]
		);
		if (!is_wp_error($result)) {
			return self::wrap_from_text($result, 'ai', __('Generated with the WordPress AI Client.', 'av-web-studio'), $prompt, $context, $intent, $page_title);
		}
		$errors[] = $result->get_error_message();
		return null;
	}

	/**
	 * Normalize page context from request.
	 *
	 * @param array|null $context Raw context.
	 * @return array|null
	 */
	private static function normalize_context($context) {
		if (!is_array($context)) {
			return null;
		}
		return Av_Web_Studio_Renderer::normalize_code($context);
	}

	/**
	 * Build prompt with current page context for cloud AI.
	 *
	 * @param string     $prompt     User prompt.
	 * @param array|null $context    Page code.
	 * @param array      $intent     Parsed intent.
	 * @param string     $page_title Page title.
	 * @return string
	 */
	private static function build_context_prompt($prompt, $context, $intent, $page_title = '') {
		$parts = [ Av_Web_Studio_AI_Intent::instruction_for_ai($intent) ];

		if ($page_title !== '') {
			$parts[] = 'Page title: ' . $page_title;
		}

		$section_html    = null;
		$section_heading = '';
		$section_type    = $intent['section_type'] ?? Av_Web_Studio_AI_Intent::parse_section_type(strtolower($prompt));

		if ($context && !empty($context['html'])) {
			$target = $intent['target'] ?? null;
			if ($section_type && !$target) {
				$target = 'type:' . $section_type;
			}
			if ($target && $target !== 'all') {
				$section_html = Av_Web_Studio_AI_Editor::get_section($context['html'], $target ?: 'last');
				if ($section_html) {
					$section_heading = Av_Web_Studio_AI_Editor::extract_heading($section_html);
				}
			}
		}

		$topic = $intent['topic']
			?: Av_Web_Studio_AI_Intent::extract_image_topic($prompt)
			?: Av_Web_Studio_AI_Intent::extract_content_topic($prompt)
			?: Av_Web_Studio_AI_Intent::extract_topic($prompt)
			?: $page_title;

		$resolved = Av_Web_Studio_AI_Images::resolve_topic(
			$topic,
			$page_title,
			$section_html,
			$context['html'] ?? ''
		);

		// Always attach a concrete image brief so the model cannot invent unrelated stock IDs.
		if (in_array($intent['type'], [ 'images', 'seo_content', 'add', 'edit' ], true)) {
			$parts[] = Av_Web_Studio_AI_Images::image_brief_for_ai(
				$resolved,
				$page_title,
				$section_type ?: '',
				$section_heading
			);
		}

		if ($section_html && $section_heading) {
			$parts[] = "Target section heading: {$section_heading}";
			$body = Av_Web_Studio_AI_Images::extract_section_text($section_html);
			if ($body !== '') {
				$parts[] = "Target section copy (for image relevance):\n" . $body;
			}
		}

		if ($context && ($context['html'] !== '' || $context['css'] !== '' || $context['js'] !== '')) {
			$parts[] = "Current page HTML:\n" . $context['html'];
			$parts[] = "Current page CSS:\n" . $context['css'];
			$parts[] = "Current page JS:\n" . $context['js'];
		}

		$parts[] = 'User request: ' . $prompt;

		return implode("\n\n", $parts);
	}

	/**
	 * If user asks to add a section type that already exists, edit it instead.
	 *
	 * @param string     $prompt  User prompt.
	 * @param array|null $context Page code.
	 * @param array      $intent  Parsed intent.
	 * @return array
	 */
	private static function prevent_duplicate_sections($prompt, $context, $intent) {
		if (!$context || empty($context['html']) || $intent['type'] !== 'add') {
			return $intent;
		}

		$type = $intent['section_type'] ?? Av_Web_Studio_AI_Intent::parse_section_type(strtolower($prompt));
		if (!$type || !Av_Web_Studio_AI_Templates::page_has_section_type($context['html'], $type)) {
			return $intent;
		}

		$intent['type']         = 'edit';
		$intent['target']       = 'type:' . $type;
		$intent['replace']      = true;
		$intent['section_type'] = $type;

		return $intent;
	}

	/**
	 * Handle remove/delete/clear edit commands locally.
	 *
	 * @param string     $prompt  User prompt.
	 * @param array|null $context Current page code.
	 * @return array|null
	 */
	private static function try_edit_command($prompt, $context) {
		if (!$context) {
			return null;
		}

		$p = strtolower(trim($prompt));

		if (preg_match('/\b(?:remove|delete|clear)\s+(?:all|every)\s+sections?\b/i', $p)) {
			$stripped = self::strip_all_sections($context['html']);
			if ($stripped === $context['html']) {
				return self::wrap($context, 'smart', 'No sections found to remove.', 'none');
			}
			return self::wrap(
				['html' => $stripped, 'css' => $context['css'], 'js' => $context['js']],
				'smart',
				'All sections removed.',
				'replace'
			);
		}

		if (preg_match('/\b(?:remove|delete)\s+(?:the\s+)?first\s+(\d+)\s+sections?\b/i', $prompt, $m)) {
			$count    = max(1, (int) $m[1]);
			$new_html = self::remove_sections($context['html'], $count, 'start');
			if ($new_html === $context['html']) {
				return self::wrap($context, 'smart', 'No sections found to remove.', 'none');
			}
			return self::wrap(
				['html' => $new_html, 'css' => $context['css'], 'js' => $context['js']],
				'smart',
				'Removed first ' . $count . ' section(s).',
				'replace'
			);
		}

		if (preg_match('/\b(?:remove|delete)\s+(?:the\s+)?last\s+(\d+)\s+sections?\b/i', $prompt, $m)) {
			$count    = max(1, (int) $m[1]);
			$new_html = self::remove_sections($context['html'], $count, 'end');
			if ($new_html === $context['html']) {
				return self::wrap($context, 'smart', 'No sections found to remove.', 'none');
			}
			return self::wrap(
				['html' => $new_html, 'css' => $context['css'], 'js' => $context['js']],
				'smart',
				'Removed last ' . $count . ' section(s).',
				'replace'
			);
		}

		if (preg_match('/\b(?:remove|delete)\s+(?:the\s+)?last\s+section\b/i', $p)) {
			$new_html = self::remove_sections($context['html'], 1, 'end');
			if ($new_html === $context['html']) {
				return self::wrap($context, 'smart', 'No sections found to remove.', 'none');
			}
			return self::wrap(
				['html' => $new_html, 'css' => $context['css'], 'js' => $context['js']],
				'smart',
				'Removed last section.',
				'replace'
			);
		}

		return null;
	}

	private static function remove_sections($html, $count, $from = 'start') {
		if (!preg_match_all('/<section\b[\s\S]*?<\/section>/i', $html, $matches)) {
			return $html;
		}

		$sections = $matches[0];
		if (empty($sections)) {
			return $html;
		}

		$to_remove = $from === 'end' ? array_slice($sections, -$count) : array_slice($sections, 0, $count);
		$result    = $html;

		foreach ($to_remove as $section) {
			$result = str_replace($section, '', $result);
		}

		return trim(preg_replace("/\n{3,}/", "\n\n", $result));
	}

	private static function strip_all_sections($html) {
		$stripped = preg_replace('/<section\b[\s\S]*?<\/section>\s*/i', '', $html);
		return trim(preg_replace("/\n{3,}/", "\n\n", $stripped));
	}

	private static function wrap($code, $source, $message, $action = 'append') {
		return ['code' => $code, 'source' => $source, 'message' => $message, 'action' => $action];
	}

	private static function wrap_from_text($text, $source, $message, $prompt, $context = null, $intent = null, $page_title = '') {
		$parsed = self::parse_ai_code($text, $prompt);
		if ($parsed['source'] === 'smart') {
			return self::wrap(
				$parsed['code'],
				'smart',
				$message . ' (Response could not be parsed — used smart template.)',
				self::resolve_action($intent, $parsed, $context)
			);
		}

		$action = !empty($parsed['action'])
			? $parsed['action']
			: self::resolve_action($intent, $parsed, $context);

		// Prefer the model's own explanation as the user-facing message.
		if (!empty($parsed['explanation'])) {
			$message = $parsed['explanation'];
		}

		// Force stock/AI images to match page — prefer Claude suggestions when available.
		$topic = ( $intent['topic'] ?? '' )
			?: Av_Web_Studio_AI_Intent::extract_image_topic($prompt)
			?: Av_Web_Studio_AI_Intent::extract_content_topic($prompt)
			?: Av_Web_Studio_AI_Intent::extract_topic($prompt)
			?: $page_title
			?: ( $intent['page_title'] ?? '' );

		$title = $page_title ?: ( $intent['page_title'] ?? '' );
		// Swap leftover remote stock URLs for bundled local images.
		$parsed['code'] = Av_Web_Studio_AI_Images::align_code_images($parsed['code'], $topic, $title);
		if (in_array($intent['type'] ?? '', [ 'images', 'seo_content', 'add' ], true)) {
			$parsed['code'] = Av_Web_Studio_AI_Claude::align_with_suggestions($parsed['code'], $topic, $title, $prompt);
		}

		return self::wrap($parsed['code'], $source, $message, $action);
	}

	private static function resolve_action($intent, $parsed, $context) {
		if (!empty($parsed['action'])) {
			return $parsed['action'];
		}
		if ($intent && $intent['type'] !== 'add') {
			return 'replace';
		}
		return self::detect_response_action($parsed['code'], $context);
	}

	private static function detect_response_action($code, $context) {
		if (!$context || empty($code['html'])) {
			return 'append';
		}

		$existing_count = preg_match_all('/<section\b/i', $context['html'], $m) ? count($m[0]) : 0;
		$new_count      = preg_match_all('/<section\b/i', $code['html'], $m2) ? count($m2[0]) : 0;

		if ($existing_count > 0 && $new_count >= $existing_count) {
			return 'replace';
		}

		return 'append';
	}

	public static function parse_ai_code($response, $fallback_prompt = '') {
		$cleaned = trim($response);
		$cleaned = preg_replace('/^```(?:json)?\s*/i', '', $cleaned);
		$cleaned = preg_replace('/\s*```\s*$/', '', $cleaned);

		$json = self::extract_json($cleaned);
		if ($json) {
			$decoded = json_decode($json, true);
			if (is_array($decoded) && !empty($decoded['html'])) {
				$result = [
					'code' => [
						'html' => $decoded['html'],
						'css'  => $decoded['css'] ?? '',
						'js'   => $decoded['js'] ?? '',
					],
					'source' => 'ai',
				];
				if (!empty($decoded['action'])) {
					$result['action'] = sanitize_key($decoded['action']);
				}
				if (!empty($decoded['explanation'])) {
					$result['explanation'] = sanitize_text_field($decoded['explanation']);
				}
				return $result;
			}
		}

		$html = $css = $js = '';
		if (preg_match('/```html\s*([\s\S]*?)```/i', $response, $m)) {
			$html = trim($m[1]);
		}
		if (preg_match('/```css\s*([\s\S]*?)```/i', $response, $m)) {
			$css = trim($m[1]);
		}
		if (preg_match('/```(?:js|javascript)\s*([\s\S]*?)```/i', $response, $m)) {
			$js = trim($m[1]);
		}

		if ($html) {
			return [
				'code'   => compact('html', 'css', 'js'),
				'source' => 'ai',
			];
		}

		if (preg_match('/<section[\s\S]*<\/section>/i', $response, $m)) {
			return [
				'code'   => ['html' => trim($m[0]), 'css' => '', 'js' => ''],
				'source' => 'ai',
			];
		}

		$prompt = $fallback_prompt ?: $response;
		return [
			'code'   => Av_Web_Studio_AI_Templates::generate($prompt),
			'source' => 'smart',
		];
	}

	private static function extract_json($text) {
		if (preg_match('/\{[\s\S]*"html"[\s\S]*\}/', $text, $matches)) {
			return $matches[0];
		}
		if (!empty($text) && $text[0] === '{') {
			return $text;
		}
		return null;
	}
}




