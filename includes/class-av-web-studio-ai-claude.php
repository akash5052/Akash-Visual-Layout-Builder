<?php

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Image suggestions via the WordPress AI Client, with a local fallback.
 *
 * The AI Client does not generate pixels; it suggests visual prompts.
 * Image slots use bundled local files from this plugin.
 */
class Av_Web_Studio_AI_Claude {

	/**
	 * Suggest images for a page / section from context.
	 *
	 * @param string     $user_prompt User request.
	 * @param array|null $context     Page code.
	 * @param string     $page_title  Page title.
	 * @param int        $count       Desired suggestion count.
	 * @return array{suggestions:array<int,array>,source:string,error?:string}|null
	 */
	public static function suggest_images($user_prompt, $context = null, $page_title = '', $count = 4) {
		$count   = max(1, min(8, absint($count)));
		$payload = self::build_suggestion_request($user_prompt, $context, $page_title, $count);
		$mode    = Av_Web_Studio_Settings::image_suggestions_mode();

		if ($mode === 'local') {
			return [
				'suggestions' => self::local_suggestions($user_prompt, $context, $page_title, $count),
				'source'      => 'claude',
				'via'         => 'local',
			];
		}

		// Prefer a cloud suggestion when the WordPress AI Client has a provider.
		if (($mode === 'claude' || $mode === 'gemini' || $mode === 'auto') && Av_Web_Studio_Settings::has_cloud_ai()) {
			$result = self::call_ai_client($payload['system'], $payload['user'], $mode);
			if (!is_wp_error($result)) {
				$suggestions = self::parse_suggestions($result, $count);
				if (!empty($suggestions)) {
					return [
						'suggestions' => $suggestions,
						'source'      => 'claude',
						'via'         => 'ai-client',
					];
				}
			}
		}

		return [
			'suggestions' => self::local_suggestions($user_prompt, $context, $page_title, $count),
			'source'      => 'claude',
			'via'         => 'local',
		];
	}

	/**
	 * Apply Claude suggestions onto page HTML (replace or inject images).
	 *
	 * @param array  $context     Page code.
	 * @param array  $suggestions Suggestion list.
	 * @param string $target      Section target (last|first|all|type:hero|…).
	 * @return array Updated code.
	 */
	public static function apply_suggestions($context, $suggestions, $target = 'last') {
		$html = $context['html'] ?? '';
		$css  = $context['css'] ?? '';
		$js   = $context['js'] ?? '';

		if (empty($suggestions) || !is_array($suggestions)) {
			return $context;
		}

		if ($target === 'all') {
			$sections = Av_Web_Studio_AI_Editor::get_sections($html);
			if (!empty($sections)) {
				$i = 0;
				foreach ($sections as $sec) {
					$sug = $suggestions[ $i % count($suggestions) ];
					$updated = self::apply_one_to_section($sec, $sug, $i);
					$html = str_replace($sec, $updated, $html);
					$i++;
				}
				return [
					'html' => $html,
					'css'  => self::ensure_css($css),
					'js'   => $js,
				];
			}
		}

		$section = Av_Web_Studio_AI_Editor::get_section($html, $target);
		if (!$section) {
			// Build a dedicated Claude image gallery section.
			$cards = '';
			foreach ($suggestions as $i => $sug) {
				$url  = Av_Web_Studio_AI_Images::url_from_suggestion($sug['prompt'], $sug['width'], $sug['height'], $i, $sug['section'] ?? '', '');
				$alt  = esc_attr($sug['alt']);
				$cap  = esc_html($sug['alt']);
				$cards .= '<figure class="av-web-studio-claude-imgs__item">'
					. '<img class="av-web-studio-claude-imgs__img" src="' . esc_url($url) . '" alt="' . $alt . '" width="' . absint($sug['width']) . '" height="' . absint($sug['height']) . '" loading="lazy" decoding="async" />'
					. '<figcaption>' . $cap . '</figcaption></figure>';
			}
			$new = '<section class="av-web-studio-claude-imgs" data-av-web-studio-source="claude">'
				. '<div class="av-web-studio-claude-imgs__inner">'
				. '<h2>Images suggested by Claude</h2>'
				. '<p class="av-web-studio-claude-imgs__sub">Curated visual concepts matched to your page</p>'
				. '<div class="av-web-studio-claude-imgs__grid">' . $cards . '</div>'
				. '</div></section>';

			return [
				'html' => trim($html . "\n\n" . $new),
				'css'  => self::ensure_css($css),
				'js'   => $js,
			];
		}

		// Replace existing images in the target section, or inject the first suggestion.
		$updated = $section;
		$img_count = preg_match_all('/<img\b/i', $section);
		$bg_count  = preg_match_all('/background-image\s*:/i', $section);

		if ($img_count > 0 || $bg_count > 0) {
			$i = 0;
			$updated = preg_replace_callback(
				'/<img\b[^>]*>/i',
				function () use ($suggestions, &$i) {
					$sug = $suggestions[ $i % count($suggestions) ];
					$url = esc_url(Av_Web_Studio_AI_Images::placeholder_url());
					$alt = esc_attr($sug['alt']);
					$tag = '<img class="av-web-studio-claude-suggested" src="' . $url . '" alt="' . $alt . '" width="' . absint($sug['width']) . '" height="' . absint($sug['height']) . '" loading="lazy" decoding="async" data-av-web-studio-source="claude" />';
					$i++;
					return $tag;
				},
				$updated
			);
			$updated = Av_Web_Studio_AI_Images::replace_remote_stock_urls(
				$updated,
				function () use ($suggestions, &$i) {
					$sug = $suggestions[ $i % count($suggestions) ];
					$url = Av_Web_Studio_AI_Images::url_from_suggestion($sug['prompt'] ?? '', $sug['width'] ?? 1200, $sug['height'] ?? 700, $i, $sug['section'] ?? '');
					$i++;
					return $url;
				}
			);
		} else {
			$sug = $suggestions[0];
			$url = esc_url(Av_Web_Studio_AI_Images::placeholder_url());
			$alt = esc_attr($sug['alt']);
			$img = '<div class="av-web-studio-ai-img-wrap"><img class="av-web-studio-claude-suggested" src="' . $url . '" alt="' . $alt . '" width="' . absint($sug['width']) . '" height="' . absint($sug['height']) . '" loading="lazy" decoding="async" data-av-web-studio-source="claude" /></div>';
			$updated = preg_replace('/(<section[^>]*>)(\s*)/i', '$1$2' . $img, $updated, 1);
		}

		return [
			'html' => str_replace($section, $updated, $html),
			'css'  => self::ensure_css($css),
			'js'   => $js,
		];
	}

	/**
	 * High-level: suggest + apply Claude images for an images intent.
	 *
	 * @param string     $prompt     User prompt.
	 * @param array|null $context    Page code.
	 * @param string     $page_title Page title.
	 * @param array      $intent     Parsed intent.
	 * @return array|null Wrap-ready result.
	 */
	public static function enhance_page_images($prompt, $context, $page_title, $intent) {
		if (!$context || empty($context['html'])) {
			$context = [
				'html' => '',
				'css'  => '',
				'js'   => '',
			];
		}

		$target = $intent['target'] ?? 'last';
		$count  = ( $target === 'all' ) ? 6 : 4;
		if (preg_match('/\b(\d+)\s+images?\b/i', $prompt, $m)) {
			$count = max(1, min(8, (int) $m[1]));
		}

		// Prefer fast local suggestions first so images always appear; enrich with cloud when available.
		$pack = [
			'suggestions' => self::local_suggestions($prompt, $context, $page_title, $count),
			'source'      => 'claude',
			'via'         => 'local',
		];

		if (Av_Web_Studio_Settings::has_cloud_ai()) {
			$cloud = self::suggest_images($prompt, $context, $page_title, $count);
			if ($cloud && !empty($cloud['suggestions']) && ( $cloud['via'] ?? '' ) !== 'local') {
				$pack = $cloud;
			}
		}

		$code = self::apply_suggestions($context, $pack['suggestions'], $target);
		$topic = $intent['topic'] ?: Av_Web_Studio_AI_Intent::extract_image_topic($prompt) ?: $page_title;
		$code  = Av_Web_Studio_AI_Images::align_code_images($code, $topic, $page_title);

		$n   = count($pack['suggestions']);
		$msg = sprintf(
			'Added %d relevant image%s suggested by Claude for your page.',
			$n,
			$n === 1 ? '' : 's'
		);

		return [
			'code'    => $code,
			'source'  => 'claude',
			'message' => $msg,
			'action'  => 'replace',
		];
	}

	/**
	 * Enrich page images using fast local Claude-style suggestions (no extra cloud round-trip).
	 *
	 * @param array  $code       Page code.
	 * @param string $topic      Topic.
	 * @param string $page_title Page title.
	 * @param string $prompt     User prompt.
	 * @return array
	 */
	public static function align_with_suggestions($code, $topic, $page_title, $prompt) {
		// Fast path: local scene recipes mapped to bundled plugin images.
		$pack = [
			'suggestions' => self::local_suggestions(
				$prompt ?: ( 'Relevant images for ' . ( $topic ?: $page_title ) ),
				$code,
				$page_title,
				6
			),
			'source' => 'claude',
			'via'    => 'local',
		];

		$html = $code['html'] ?? '';
		if (preg_match_all('/<section\b[\s\S]*?<\/section>/i', $html, $matches)) {
			$i = 0;
			foreach ($matches[0] as $section) {
				$sug  = $pack['suggestions'][ $i % count($pack['suggestions']) ];
				$type = Av_Web_Studio_AI_Images::detect_section_type_from_html($section);
				if ($type) {
					$sug['section'] = $type;
				}
				if ($type === 'team') {
					$sug['prompt'] = 'professional headshot portrait team member soft studio lighting';
					$sug['section'] = 'team';
				}
				$updated = self::apply_one_to_section($section, $sug, $i);
				$html    = str_replace($section, $updated, $html);
				$i++;
			}
			$code['html'] = $html;
			$code['css']  = self::ensure_css($code['css'] ?? '');
			return $code;
		}

		return self::apply_suggestions($code, $pack['suggestions'], 'last');
	}

	/**
	 * @param string $section Section HTML.
	 * @param array  $sug     Suggestion.
	 * @param int    $index   Index.
	 * @return string
	 */
	private static function apply_one_to_section($section, $sug, $index) {
		$prompt = $sug['prompt'] ?? '';
		$alt    = $sug['alt'] ?? 'Claude suggested image';
		$type   = $sug['section'] ?? '';
		$url    = Av_Web_Studio_AI_Images::url_from_suggestion($prompt, $sug['width'] ?? 1200, $sug['height'] ?? 700, $index, $type);
		$n      = 0;

		if (stripos($section, '<img') !== false) {
			$section = preg_replace_callback(
				'/<img\b[^>]*>/i',
				function () use ($prompt, $alt, $index, $type, &$n) {
					$src = Av_Web_Studio_AI_Images::url_from_suggestion($prompt, 800, 500, $index + $n, $type);
					$n++;
					return '<img class="av-web-studio-claude-suggested" src="' . esc_url($src) . '" alt="' . esc_attr($alt) . '" loading="lazy" decoding="async" data-av-web-studio-source="claude" />';
				},
				$section
			);
		}

		$section = Av_Web_Studio_AI_Images::replace_remote_stock_urls(
			$section,
			function () use ($prompt, $index, $type, &$n) {
				$src = Av_Web_Studio_AI_Images::url_from_suggestion($prompt, 1200, 700, $index + $n, $type);
				$n++;
				return $src;
			}
		);

		if (stripos($section, '<img') === false && stripos($section, 'background-image') === false) {
			$img = '<div class="av-web-studio-ai-img-wrap"><img class="av-web-studio-claude-suggested" src="' . esc_url($url) . '" alt="' . esc_attr($alt) . '" loading="lazy" decoding="async" data-av-web-studio-source="claude" /></div>';
			$section = preg_replace('/(<section[^>]*>)(\s*)/i', '$1$2' . $img, $section, 1);
		}

		return $section;
	}

	/**
	 * @param string     $user_prompt Prompt.
	 * @param array|null $context     Context.
	 * @param string     $page_title  Title.
	 * @param int        $count       Count.
	 * @return array{system:string,user:string}
	 */
	private static function build_suggestion_request($user_prompt, $context, $page_title, $count) {
		$topic    = Av_Web_Studio_AI_Images::resolve_topic(
			Av_Web_Studio_AI_Intent::extract_image_topic($user_prompt) ?: Av_Web_Studio_AI_Intent::extract_topic($user_prompt),
			$page_title,
			null,
			$context['html'] ?? ''
		);
		$industry = Av_Web_Studio_AI_Images::industry_for_topic($topic, $page_title);
		$label    = Av_Web_Studio_AI_Images::industry_label($industry);

		$sections_brief = '';
		if (!empty($context['html'])) {
			$sections = Av_Web_Studio_AI_Editor::get_sections($context['html']);
			foreach (array_slice($sections, 0, 8) as $i => $sec) {
				$h = Av_Web_Studio_AI_Editor::extract_heading($sec);
				$t = Av_Web_Studio_AI_Images::detect_section_type_from_html($sec) ?: 'section';
				$b = Av_Web_Studio_AI_Images::extract_section_text($sec);
				$sections_brief .= ( $i + 1 ) . ". [{$t}] {$h} — " . mb_substr($b, 0, 120) . "\n";
			}
		}

		$system = 'You are Claude, an expert art director for websites. '
			. 'Suggest photorealistic image concepts that perfectly match the business and each section. '
			. 'Return ONLY valid JSON (no markdown) with this shape: '
			. '{"images":[{"prompt":"detailed photorealistic scene description, no text/watermark/logo","alt":"short alt text","section":"hero|features|gallery|team|about|menu","width":1200,"height":700}]} '
			. 'Prompts must be specific to the business type, never generic office stock. Team = headshot portraits.';

		$user = "Page title: {$page_title}\n"
			. "Business topic: {$topic}\n"
			. "Industry: {$industry} ({$label})\n"
			. "User request: {$user_prompt}\n"
			. "Suggest exactly {$count} images.\n";

		if ($sections_brief !== '') {
			$user .= "Existing sections:\n{$sections_brief}\n";
		}

		return compact('system', 'user');
	}

	/**
	 * Image-layout suggestions through the WordPress AI Client.
	 *
	 * @param string $system System prompt.
	 * @param string $user   User prompt.
	 * @param string $mode   auto|claude|gemini.
	 * @return string|WP_Error Raw text.
	 */
	private static function call_ai_client($system, $user, $mode = 'auto') {
		return Av_Web_Studio_AI_Client::generate_text(
			$user,
			$system,
			[
				'temperature' => 0.4,
				'max_tokens'  => 2048,
				'timeout'     => 60,
				'models'      => Av_Web_Studio_AI_Client::preferred_models($mode === 'local' ? 'auto' : $mode),
				'json_schema' => Av_Web_Studio_AI_Client::image_suggestion_schema(),
			]
		);
	}

	/**
	 * @param string $text  Raw JSON/text.
	 * @param int    $count Max items.
	 * @return array<int,array{prompt:string,alt:string,section:string,width:int,height:int}>
	 */
	private static function parse_suggestions($text, $count) {
		$text = trim($text);
		$text = preg_replace('/^```(?:json)?\s*/i', '', $text);
		$text = preg_replace('/\s*```\s*$/', '', $text);

		$json = $text;
		if (!preg_match('/^\s*\{/', $text)) {
			if (preg_match('/\{[\s\S]*"images"[\s\S]*\}/', $text, $m)) {
				$json = $m[0];
			}
		}

		$data = json_decode($json, true);
		if (!is_array($data) || empty($data['images']) || !is_array($data['images'])) {
			return [];
		}

		$out = [];
		foreach ($data['images'] as $img) {
			$prompt = sanitize_text_field($img['prompt'] ?? '');
			if (strlen($prompt) < 12) {
				continue;
			}
			// Keep richer prompt text (sanitize_text_field strips too much for long prompts).
			$prompt = trim(wp_strip_all_tags($img['prompt'] ?? ''));
			$prompt = preg_replace('/\s+/', ' ', $prompt);
			$prompt .= ', photorealistic, high resolution, no text, no watermark, no logo';

			$out[] = [
				'prompt'  => mb_substr($prompt, 0, 400),
				'alt'     => sanitize_text_field($img['alt'] ?? 'Suggested image'),
				'section' => sanitize_key($img['section'] ?? 'hero'),
				'width'   => absint($img['width'] ?? 1200) ?: 1200,
				'height'  => absint($img['height'] ?? 700) ?: 700,
			];
			if (count($out) >= $count) {
				break;
			}
		}

		return $out;
	}

	/**
	 * Local Claude-style suggestions without cloud.
	 *
	 * @param string     $user_prompt Prompt.
	 * @param array|null $context     Context.
	 * @param string     $page_title  Title.
	 * @param int        $count       Count.
	 * @return array
	 */
	private static function local_suggestions($user_prompt, $context, $page_title, $count) {
		$topic = Av_Web_Studio_AI_Images::resolve_topic(
			Av_Web_Studio_AI_Intent::extract_image_topic($user_prompt) ?: Av_Web_Studio_AI_Intent::extract_topic($user_prompt),
			$page_title,
			null,
			$context['html'] ?? ''
		);

		$roles = [ 'hero', 'features', 'gallery', 'about', 'menu', 'team' ];
		$out   = [];
		for ($i = 0; $i < $count; $i++) {
			$role = $roles[ $i % count($roles) ];
			$heading = '';
			$body    = '';
			if (!empty($context['html'])) {
				$sections = Av_Web_Studio_AI_Editor::get_sections($context['html']);
				if (!empty($sections[ $i % max(1, count($sections)) ])) {
					$sec     = $sections[ $i % count($sections) ];
					$heading = Av_Web_Studio_AI_Editor::extract_heading($sec);
					$body    = Av_Web_Studio_AI_Images::extract_section_text($sec);
					$detected = Av_Web_Studio_AI_Images::detect_section_type_from_html($sec);
					if ($detected) {
						$role = $detected;
					}
				}
			}
			$prompt = Av_Web_Studio_AI_Images::build_visual_prompt($topic, $page_title, $role, $heading, $body, $i);
			$out[]  = [
				'prompt'  => $prompt,
				'alt'     => Av_Web_Studio_AI_Images::descriptive_alt($topic, '', $i, $heading ?: $role),
				'section' => $role,
				'width'   => $role === 'team' ? 400 : 1200,
				'height'  => $role === 'team' ? 400 : 700,
			];
		}
		return $out;
	}

	/**
	 * @param string $css Existing CSS.
	 * @return string
	 */
	private static function ensure_css($css) {
		if (strpos($css, 'av-web-studio-claude-imgs') !== false) {
			return trim($css);
		}
		return trim($css . "/* --- Claude suggested images --- */
.av-web-studio-claude-imgs { padding: 64px 20px; background: #0f172a; width: 100%; color: #fff; }
.av-web-studio-claude-imgs__inner { max-width: 1100px; margin: 0 auto; }
.av-web-studio-claude-imgs h2 { margin: 0 0 8px; font-size: 2rem; text-align: center; }
.av-web-studio-claude-imgs__sub { margin: 0 0 36px; text-align: center; color: #94a3b8; }
.av-web-studio-claude-imgs__grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 18px; }
.av-web-studio-claude-imgs__item { margin: 0; border-radius: 14px; overflow: hidden; background: #1e293b; }
.av-web-studio-claude-imgs__img { width: 100%; height: 200px; object-fit: cover; display: block; }
.av-web-studio-claude-imgs__item figcaption { padding: 12px 14px; font-size: .9rem; color: #cbd5e1; }
.av-web-studio-claude-suggested { width: 100%; max-height: 480px; object-fit: cover; border-radius: 12px; }
.av-web-studio-ai-img-wrap { margin-bottom: 24px; }"
		);
	}
}

