<?php

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Parses user intent from natural language AI prompts.
 */
class EPB_AI_Intent {

	/**
	 * Parse prompt into structured intent.
	 *
	 * @param string     $prompt  User prompt.
	 * @param array|null $context Current page code.
	 * @return array
	 */
	public static function parse($prompt, $context = null) {
		$p = strtolower(trim($prompt));

		if (preg_match('/\b(?:remove|delete|clear)\b/i', $p)) {
			return self::make_intent('remove', self::parse_target($p), '', true);
		}

		if (preg_match('/\b(?:animation|animations|animate|animated|fade[\s-]?in|slide[\s-]?in|scroll[\s-]?reveal|transition|motion|parallax)\b/i', $p)) {
			return self::make_intent('animate', self::parse_target($p) ?: 'all', '', true);
		}

		if (preg_match('/\b(?:image|images|photo|photos|picture|pictures|thumbnail|banner|illustration|generate\s+(?:an?\s+)?image|add\s+images?|claude\s+images?|images?\s+suggested\s+by\s+claude|suggest(?:ed)?\s+(?:relevant\s+)?(?:claude\s+)?images?)\b/i', $p)) {
			$target = self::parse_target($p);
			$type   = self::parse_section_type($p);
			if (!$target && $type) {
				$target = 'type:' . $type;
			}
			if (!$target && preg_match('/\ball\s+sections?\b/i', $p)) {
				$target = 'all';
			}
			return self::make_intent('images', $target ?: 'last', self::extract_image_topic($prompt), true, $type);
		}

		// Complaints / quality feedback — edit existing section, never append.
		if (self::is_complaint($p) || self::is_section_feedback($p, $context)) {
			$type = self::parse_section_type($p);
			return self::make_intent(
				'edit',
				$type ? 'type:' . $type : ( self::parse_target($p) ?: 'last' ),
				'',
				true,
				$type,
				true
			);
		}

		if (preg_match('/\b(?:edit|update|change|modify|fix|improve|restyle|redesign|make\s+(?:it|the)|adjust|tweak|rewrite)\b/i', $p)) {
			$type = self::parse_section_type($p);
			return self::make_intent(
				'edit',
				$type ? 'type:' . $type : ( self::parse_target($p) ?: 'last' ),
				self::extract_topic($prompt),
				(bool) preg_match('/\b(?:replace|rewrite|redesign)\b/i', $p),
				$type
			);
		}

		if (preg_match('/\b(?:replace)\b/i', $p)) {
			$type = self::parse_section_type($p);
			return self::make_intent('edit', $type ? 'type:' . $type : 'last', self::extract_topic($prompt), true, $type);
		}

		// Full page / SEO landing page from a single prompt.
		if (self::is_whole_page_prompt($prompt, $context)) {
			return self::make_intent(
				'seo_content',
				null,
				self::extract_content_topic($prompt) ?: self::extract_topic($prompt),
				true
			);
		}

		// "hero section" without add/create → likely referring to existing section.
		if ($context && self::parse_section_type($p) && !preg_match('/\b(?:add|create|generate|build|make|new)\b/i', $p)) {
			$type = self::parse_section_type($p);
			return self::make_intent('edit', 'type:' . $type, '', true, $type);
		}

		$type = self::parse_section_type($p);
		return self::make_intent('add', null, self::extract_topic($prompt), false, $type);
	}

	/**
	 * @param string      $type         Intent type.
	 * @param mixed       $target       Target section.
	 * @param string      $topic        Topic.
	 * @param bool        $replace      Replace flag.
	 * @param string|null $section_type Section type slug.
	 * @param bool        $is_complaint Complaint flag.
	 * @return array
	 */
	private static function make_intent($type, $target, $topic, $replace, $section_type = null, $is_complaint = false) {
		return [
			'type'          => $type,
			'target'        => $target,
			'topic'         => $topic,
			'replace'       => $replace,
			'section_type'  => $section_type,
			'is_complaint'  => $is_complaint,
		];
	}

	/**
	 * User is complaining about quality, not requesting new content.
	 *
	 * @param string $prompt Lowercased prompt.
	 * @return bool
	 */
	public static function is_complaint($prompt) {
		return (bool) preg_match(
			'/\b(?:not proper|not good|not right|not correct|is wrong|looks bad|look bad|looks wrong|doesn\'t look|does not look|isn\'t right|is not right|is not good|is not proper|is broken|broken|ugly|terrible|awful|horrible|bad|poor quality|doesn\'t work|does not work|fix this|fix it|please fix|make it better|improve this|redo this|try again)\b/i',
			$prompt
		);
	}

	/**
	 * Feedback about a named section type on an existing page.
	 *
	 * @param string     $prompt  Lowercased prompt.
	 * @param array|null $context Page code.
	 * @return bool
	 */
	public static function is_section_feedback($prompt, $context) {
		if (!$context || empty($context['html'])) {
			return false;
		}
		$type = self::parse_section_type($prompt);
		return $type && self::is_complaint($prompt);
	}

	/**
	 * Detect section type referenced in prompt.
	 *
	 * @param string $prompt Prompt.
	 * @return string|null hero|pricing|gallery|contact|team|menu|faq|features
	 */
	public static function parse_section_type($prompt) {
		$map = [
			'hero'     => ['hero section', 'hero banner', 'banner section', 'landing hero', '\bhero\b'],
			'pricing'  => ['pricing section', 'pricing table', 'plan section', '\bpricing\b'],
			'gallery'  => ['gallery section', 'portfolio section', '\bgallery\b', '\bportfolio\b'],
			'contact'  => ['contact section', 'contact form', '\bcontact\b'],
			'team'     => ['team section', '\bteam\b', 'about us section'],
			'menu'     => ['menu section', '\bmenu\b', 'restaurant section'],
			'faq'      => ['faq section', '\bfaq\b'],
			'features' => ['features section', '\bfeatures\b', 'services section'],
		];

		foreach ($map as $type => $patterns) {
			foreach ($patterns as $pattern) {
				if ($pattern[0] === '\\') {
					if (preg_match('/' . $pattern . '/i', $prompt)) {
						return $type;
					}
				} elseif (strpos(strtolower($prompt), $pattern) !== false) {
					return $type;
				}
			}
		}

		return null;
	}

	/**
	 * Which section the user refers to.
	 *
	 * @param string $prompt Lowercased prompt.
	 * @return string|int|null
	 */
	public static function parse_target($prompt) {
		if (preg_match('/\b(?:the\s+)?first\s+section\b/i', $prompt)) {
			return 'first';
		}
		if (preg_match('/\b(?:the\s+)?last\s+section\b/i', $prompt)) {
			return 'last';
		}
		if (preg_match('/\b(?:all|every)\s+sections?\b/i', $prompt)) {
			return 'all';
		}
		if (preg_match('/\bsection\s+(\d+)\b/i', $prompt, $m)) {
			return max(1, (int) $m[1]);
		}
		if (preg_match('/\bprevious\s+section\b/i', $prompt)) {
			return 'last';
		}
		return null;
	}

	public static function extract_image_topic($prompt) {
		if (preg_match('/(?:image|photo|picture|photos|images)\s+(?:of|for|showing|about|featuring)\s+(.+?)(?:\.|$)/i', $prompt, $m)) {
			$t = EPB_AI_Images::sanitize_topic(trim($m[1]));
			if ($t !== '') {
				return $t;
			}
		}
		if (preg_match('/(?:add|generate|insert|put|place)\s+(?:an?\s+)?images?\s+(?:of|for|to|into|on)\s+(.+?)(?:\.|$)/i', $prompt, $m)) {
			$t = EPB_AI_Images::sanitize_topic(trim($m[1]));
			if ($t !== '') {
				return $t;
			}
		}
		$topic = self::extract_topic($prompt);
		$topic = EPB_AI_Images::sanitize_topic($topic);
		// Empty / generic — let resolve_topic use page/section context instead of forcing "business".
		return $topic;
	}

	public static function extract_topic($prompt) {
		if (self::is_complaint(strtolower($prompt))) {
			return '';
		}

		$patterns = [
			'/(?:for|about|of)\s+(?:a\s+)?(.+?)(?:\s+section|\s+page|\.|$)/i',
			'/(?:create|build|generate|add|make)\s+(?:a\s+)?(.+?)(?:\s+section|\s+with|\.|$)/i',
		];
		foreach ($patterns as $pattern) {
			if (preg_match($pattern, $prompt, $m)) {
				$t = trim(preg_replace('/\s+(section|page)$/i', '', $m[1]));
				if (strlen($t) > 2 && strlen($t) < 100 && !self::is_bad_title($t)) {
					return $t;
				}
			}
		}
		return '';
	}

	/**
	 * Extract business/topic for full-page content generation.
	 *
	 * @param string $prompt User prompt.
	 * @return string
	 */
	public static function extract_content_topic($prompt) {
		$patterns = [
			'/(?:for|about|of)\s+(?:a\s+)?(.+?)(?:\s+(?:business|company|website|page|with|\.|$))/i',
			'/(?:generate|create|build)\s+(?:a\s+)?(?:seo\s+)?(?:friendly\s+)?(?:full\s+)?(?:landing\s+)?page\s+(?:for|about)\s+(.+?)$/i',
			'/(?:landing page for|content for|website for)\s+(.+?)$/i',
		];
		foreach ($patterns as $pattern) {
			if (preg_match($pattern, $prompt, $m)) {
				$t = trim($m[1]);
				if (strlen($t) > 2 && !self::is_bad_title($t)) {
					return $t;
				}
			}
		}
		return self::extract_topic($prompt);
	}

	/**
	 * Detect when the user wants a complete page from one prompt.
	 *
	 * @param string     $prompt  User prompt.
	 * @param array|null $context Current page code.
	 * @return bool
	 */
	public static function is_whole_page_prompt($prompt, $context = null) {
		$p = strtolower(trim($prompt));

		if (preg_match('/\b(?:seo|seo-friendly|seo friendly|search engine|meta description|full page|complete page|whole page|entire page|landing page with|generate content|content generation)\b/i', $p)) {
			return true;
		}

		if (preg_match('/\b(?:build|create|design|make|generate|develop)\s+(?:me\s+)?(?:a\s+)?(?:complete|full|entire|whole|seo)?\s*(?:website|web\s*site|landing\s*page|homepage|home\s*page|web\s*page|site|page)\b/i', $p)) {
			return ! self::is_single_section_request($p);
		}

		if (preg_match('/\b(?:website|landing\s*page|homepage|web\s*page)\s+(?:for|about|of)\b/i', $p)) {
			return true;
		}

		if (preg_match('/\b(?:one[-\s]?page|single[-\s]?page|full[-\s]?page)\s+(?:website|site|landing)\b/i', $p)) {
			return true;
		}

		// Rich descriptive prompt on a blank/minimal page → generate everything at once.
		if (self::is_minimal_page($context) && ! self::is_single_section_request($p)) {
			$topic = self::extract_content_topic($prompt) ?: self::extract_topic($prompt);
			if ($topic && strlen($topic) > 4) {
				return true;
			}
			if (strlen($prompt) > 30 && preg_match('/\b(?:with|including|featuring|offering|for)\b/i', $p)) {
				return true;
			}
		}

		return false;
	}

	/**
	 * User wants only one section type, not a full page.
	 *
	 * @param string $prompt Lowercased prompt.
	 * @return bool
	 */
	private static function is_single_section_request($prompt) {
		if (! self::parse_section_type($prompt)) {
			return false;
		}
		return (bool) preg_match('/\b(?:add|append|create|generate|make|new|insert)\b/i', $prompt);
	}

	/**
	 * Page is empty or only has a placeholder title.
	 *
	 * @param array|null $context Page code.
	 * @return bool
	 */
	public static function is_minimal_page($context) {
		if (!$context) {
			return true;
		}
		$html = trim($context['html'] ?? '');
		if ($html === '') {
			return true;
		}
		if (preg_match('/^<h1[^>]*>[\s\S]*?<\/h1>\s*$/i', $html)) {
			return true;
		}
		if (preg_match('/^<div[^>]*class="[^"]*epb-page[^"]*"[^>]*>\s*<h1[^>]*>[\s\S]*?<\/h1>\s*<\/div>\s*$/i', $html)) {
			return true;
		}
		return strlen(wp_strip_all_tags($html)) < 80;
	}

	/**
	 * Titles that must never appear on the page.
	 *
	 * @param string $text Text.
	 * @return bool
	 */
	public static function is_bad_title($text) {
		$t = strtolower(trim($text));
		if (self::is_complaint($t)) {
			return true;
		}
		$bad = ['hero', 'hero section', 'section', 'not proper', 'is not proper', 'is not good', 'wrong', 'bad'];
		return in_array($t, $bad, true) || preg_match('/\bis not\b|\bnot proper\b|\blooks bad\b/', $t);
	}

	public static function instruction_for_ai($intent) {
		switch ($intent['type']) {
			case 'remove':
				return 'INTENT: REMOVE sections from the page. Return action "replace" with the FULL updated html, css, and js.';
			case 'edit':
				$target = is_int($intent['target']) ? 'section ' . $intent['target'] : str_replace('type:', '', $intent['target'] ?? 'last') . ' section';
				$extra  = !empty($intent['is_complaint'])
					? ' The user is reporting a problem with quality — FIX and IMPROVE the section professionally. NEVER use their complaint text as heading/content. Use professional marketing copy.'
					: '';
				return 'INTENT: EDIT the ' . $target . '. Return action "replace" with the FULL updated page html/css/js. Keep unchanged parts intact.' . $extra;
			case 'animate':
				return 'INTENT: ADD animations (CSS @keyframes, transitions, scroll-reveal). Return action "replace" with FULL page html/css/js including animation styles and any needed JS.';
			case 'images':
				return 'INTENT: ADD or REPLACE images using Claude image suggestions — highly relevant photorealistic scenes for this business and section. '
					. 'Return action "replace" with FULL updated page. '
					. 'Every image must match the detected industry and section heading/copy. '
					. 'Label conceptually as images suggested by Claude. '
					. EPB_AI_Images::ai_image_instructions();
			case 'seo_content':
				return 'INTENT: GENERATE a COMPLETE landing page from this single prompt — hero, features, about, testimonials, FAQ, and CTA. '
					. 'Use action "replace" with FULL page html/css/js. Semantic HTML (one h1, h2 per section), meta description comment, JSON-LD schema, descriptive alt text. '
					. 'Images must match the business type in every section. '
					. EPB_AI_Images::ai_image_instructions();
			default:
				return 'INTENT: ADD a new section. Return action "append" with ONLY the new section html/css/js. Do NOT duplicate an existing section type already on the page.';
		}
	}
}

