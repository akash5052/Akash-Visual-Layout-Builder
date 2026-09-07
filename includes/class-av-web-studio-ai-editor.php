<?php

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Applies AI edits to existing page code (sections, animations, images).
 */
class Av_Web_Studio_AI_Editor {

	/**
	 * Apply a contextual edit based on parsed intent.
	 *
	 * @param string     $prompt  User prompt.
	 * @param array      $context Current page code.
	 * @param array      $intent  Parsed intent.
	 * @return array|null Wrap-ready result or null if not handled.
	 */
	public static function apply($prompt, $context, $intent) {
		if (empty($context['html']) && in_array($intent['type'], ['edit', 'animate', 'images'], true)) {
			return [
				'code'    => $context,
				'source'  => 'smart',
				'message' => __('Add a section first, then you can edit it.', 'av-web-studio'),
				'action'  => 'none',
			];
		}

		switch ($intent['type']) {
			case 'animate':
				return self::wrap_result(
					self::add_animations($context, $intent['target'] ?? 'all'),
					__('Animations added to your page.', 'av-web-studio')
				);

			case 'images':
				$topic = $intent['topic'] ?: Av_Web_Studio_AI_Intent::extract_image_topic($prompt);
				$page_title = $intent['page_title'] ?? self::extract_page_title($context['html'] ?? '');
				$result = self::inject_images($context, $topic, $intent['target'] ?? 'last', $prompt, $page_title);
				if ($result) {
					return self::wrap_result($result, __('Relevant images added to match your page content.', 'av-web-studio'));
				}
				break;

			case 'edit':
				$result = self::edit_page($context, $prompt, $intent);
				if ($result) {
					$msg = !empty($intent['is_complaint']) ? __('Section fixed and improved.', 'av-web-studio') : __('Section updated.', 'av-web-studio');
					return self::wrap_result($result, $msg);
				}
				break;
		}

		return null;
	}

	/**
	 * @param array  $code    Updated code.
	 * @param string $message Success message.
	 * @return array
	 */
	private static function wrap_result($code, $message) {
		return [
			'code'    => $code,
			'source'  => 'smart',
			'message' => $message,
			'action'  => 'replace',
		];
	}

	/**
	 * Extract all section HTML blocks.
	 *
	 * @param string $html Page HTML.
	 * @return array<int,string>
	 */
	public static function get_sections($html) {
		if (!preg_match_all('/<section\b[\s\S]*?<\/section>/i', $html, $matches)) {
			return [];
		}
		return $matches[0];
	}

	/**
	 * Find section by semantic type (hero, pricing, etc.).
	 *
	 * @param string $html Page HTML.
	 * @param string $type Section type.
	 * @return string|null
	 */
	public static function get_section_by_type($html, $type) {
		$sections = self::get_sections($html);
		if (empty($sections)) {
			return null;
		}

		$needles = [
			'hero'     => ['-hero', 'hero-'],
			'pricing'  => ['-pricing', 'pricing-'],
			'gallery'  => ['-gallery', 'gallery-'],
			'contact'  => ['-contact', 'contact-', '-form'],
			'team'     => ['-team', 'team-'],
			'menu'     => ['-menu', 'menu-'],
			'faq'      => ['-faq', 'faq-'],
			'features' => ['-features', 'features-', '-block'],
		];

		$keys = $needles[ $type ] ?? [ '-' . $type ];

		foreach ($sections as $section) {
			foreach ($keys as $needle) {
				if (stripos($section, $needle) !== false) {
					return $section;
				}
			}
		}

		if ($type === 'hero') {
			return $sections[0];
		}

		return null;
	}

	/**
	 * Extract h1/h2 text from HTML fragment.
	 *
	 * @param string $html HTML fragment.
	 * @return string
	 */
	public static function extract_heading($html) {
		if (preg_match('/<h1[^>]*>(.*?)<\/h1>/is', $html, $m)) {
			return trim(wp_strip_all_tags($m[1]));
		}
		if (preg_match('/<h2[^>]*>(.*?)<\/h2>/is', $html, $m)) {
			return trim(wp_strip_all_tags($m[1]));
		}
		return '';
	}

	/**
	 * Get page-level h1 title.
	 *
	 * @param string $html Full page HTML.
	 * @return string
	 */
	public static function extract_page_title($html) {
		if (preg_match('/<h1[^>]*>(.*?)<\/h1>/is', $html, $m)) {
			$title = trim(wp_strip_all_tags($m[1]));
			if (!Av_Web_Studio_AI_Intent::is_bad_title($title)) {
				return $title;
			}
		}
		return '';
	}

	/**
	 * Regenerate a section by type with a safe title (never the user's complaint).
	 *
	 * @param array  $context Page code.
	 * @param array  $intent  Intent.
	 * @param string $prompt  Original prompt.
	 * @return array|null
	 */
	public static function regenerate_section($context, $intent, $prompt) {
		$type   = $intent['section_type'] ?? 'hero';
		$target = $intent['target'] ?? ( 'type:' . $type );
		$old    = self::get_section($context['html'], $target);

		if (!$old) {
			return null;
		}

		$title = self::extract_heading($old);
		if (!$title || Av_Web_Studio_AI_Intent::is_bad_title($title)) {
			$title = self::extract_page_title($context['html']);
		}
		if (!$title || Av_Web_Studio_AI_Intent::is_bad_title($title)) {
			$defaults = [
				'hero'     => 'Welcome',
				'pricing'  => 'Simple Pricing',
				'gallery'  => 'Our Work',
				'contact'  => 'Get In Touch',
				'team'     => 'Our Team',
				'menu'     => 'Our Menu',
				'faq'      => 'FAQ',
				'features' => 'Why Choose Us',
			];
			$title = $defaults[ $type ] ?? 'Welcome';
		}

		$slug = 'fixed-' . $type . '-' . wp_rand(100, 999);
		$new  = Av_Web_Studio_AI_Templates::section_by_type($type, $title, $slug, $prompt);

		return [
			'html' => self::replace_section($context['html'], $target, $new['html']),
			'css'  => trim($context['css'] . "\n\n/* --- Fixed {$type} section --- */\n" . $new['css']),
			'js'   => trim($context['js'] . "\n" . $new['js']),
		];
	}

	/**
	 * Get one section by target selector.
	 *
	 * @param string       $html   Page HTML.
	 * @param string|int|null $target first|last|all|section number.
	 * @return string|null
	 */
	public static function get_section($html, $target) {
		if (is_string($target) && strpos($target, 'type:') === 0) {
			$typed = self::get_section_by_type($html, substr($target, 5));
			if ($typed) {
				return $typed;
			}
		}

		$sections = self::get_sections($html);
		if (empty($sections)) {
			return null;
		}

		if ($target === 'first') {
			return $sections[0];
		}
		if ($target === 'last') {
			return $sections[ count($sections) - 1 ];
		}
		if (is_int($target) && isset($sections[ $target - 1 ])) {
			return $sections[ $target - 1 ];
		}

		return $sections[ count($sections) - 1 ];
	}

	/**
	 * Replace a targeted section with new HTML.
	 *
	 * @param string $html         Full page HTML.
	 * @param mixed  $target       Section target.
	 * @param string $new_section  Replacement section HTML.
	 * @return string
	 */
	public static function replace_section($html, $target, $new_section) {
		if (is_string($target) && strpos($target, 'type:') === 0) {
			$old = self::get_section_by_type($html, substr($target, 5));
			if ($old) {
				return str_replace($old, $new_section, $html);
			}
		}

		$sections = self::get_sections($html);
		if (empty($sections)) {
			return $html . "\n\n" . $new_section;
		}

		$index = count($sections) - 1;
		if ($target === 'first') {
			$index = 0;
		} elseif (is_int($target) && $target > 0) {
			$index = min($target - 1, count($sections) - 1);
		}

		$old = $sections[ $index ];
		return str_replace($old, $new_section, $html);
	}

	/**
	 * Add CSS animations and scroll-reveal to page.
	 *
	 * @param array      $context Page code.
	 * @param string|int $target  Section target or all.
	 * @return array
	 */
	public static function add_animations($context, $target = 'all') {
		$html = $context['html'];
		$css  = $context['css'];
		$js   = $context['js'];

		$anim_css = "
/* --- AI Animations --- */
@keyframes av-web-studio-fade-up {
  from { opacity: 0; transform: translateY(36px); }
  to { opacity: 1; transform: translateY(0); }
}
@keyframes av-web-studio-fade-in {
  from { opacity: 0; }
  to { opacity: 1; }
}
@keyframes av-web-studio-scale-in {
  from { opacity: 0; transform: scale(0.94); }
  to { opacity: 1; transform: scale(1); }
}
@keyframes av-web-studio-slide-left {
  from { opacity: 0; transform: translateX(-40px); }
  to { opacity: 1; transform: translateX(0); }
}

.av-web-studio-anim-enter {
  animation: av-web-studio-fade-up 0.75s cubic-bezier(0.22, 1, 0.36, 1) forwards;
  opacity: 0;
}
.av-web-studio-anim-delay-1 { animation-delay: 0.12s; }
.av-web-studio-anim-delay-2 { animation-delay: 0.24s; }
.av-web-studio-anim-delay-3 { animation-delay: 0.36s; }
.av-web-studio-anim-delay-4 { animation-delay: 0.48s; }

.av-web-studio-scroll-reveal {
  opacity: 0;
  transform: translateY(28px);
  transition: opacity 0.65s ease, transform 0.65s ease;
}
.av-web-studio-scroll-reveal.av-web-studio-revealed {
  opacity: 1;
  transform: translateY(0);
}

section:hover {
  transition: transform 0.3s ease;
}";

		$anim_js = "
/* --- AI Scroll Reveal --- */
document.addEventListener('DOMContentLoaded', function () {
  var els = document.querySelectorAll('.av-web-studio-scroll-reveal');
  if (!els.length) return;
  if (!('IntersectionObserver' in window)) {
    els.forEach(function (el) { el.classList.add('av-web-studio-revealed'); });
    return;
  }
  var io = new IntersectionObserver(function (entries) {
    entries.forEach(function (entry) {
      if (entry.isIntersecting) {
        entry.target.classList.add('av-web-studio-revealed');
        io.unobserve(entry.target);
      }
    });
  }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });
  els.forEach(function (el) { io.observe(el); });
});";

		if ($target === 'all') {
			$html = preg_replace('/<section\b/i', '<section class="av-web-studio-scroll-reveal"', $html);
			$html = preg_replace('/class="av-web-studio-scroll-reveal"\s+class="/i', 'class="av-web-studio-scroll-reveal ', $html);
		} else {
			$section = self::get_section($html, $target);
			if ($section) {
				$updated = preg_replace('/<section\b/i', '<section class="av-web-studio-scroll-reveal av-web-studio-anim-enter"', $section, 1);
				$html    = str_replace($section, $updated, $html);
			}
		}

		// Stagger child elements inside sections.
		$html = preg_replace_callback(
			'/<section\b[\s\S]*?<\/section>/i',
			function ($m) {
				$block = $m[0];
				$i     = 0;
				return preg_replace_callback(
					'/<(h[1-6]|p|article|figure|img|button|a)\b/i',
					function ($tag) use (&$i) {
						$delay = min($i, 4);
						$i++;
						return '<' . $tag[1] . ' class="av-web-studio-anim-enter av-web-studio-anim-delay-' . $delay . '"';
					},
					$block,
					6
				);
			},
			$html
		);

		return [
			'html' => $html,
			'css'  => trim($css . $anim_css),
			'js'   => trim($js . "\n" . $anim_js),
		];
	}

	/**
	 * Inject topic-matched stock images into a section (or all sections).
	 *
	 * @param array      $context    Page code.
	 * @param string     $topic      Image topic from prompt.
	 * @param string|int $target     Section target.
	 * @param string     $prompt     Original prompt.
	 * @param string     $page_title Page / WP title for industry detection.
	 * @return array|null
	 */
	public static function inject_images($context, $topic, $target = 'last', $prompt = '', $page_title = '') {
		$html = $context['html'];
		if ($page_title === '') {
			$page_title = self::extract_page_title($html);
		}

		$section_type = Av_Web_Studio_AI_Intent::parse_section_type(strtolower($prompt));
		if (is_string($target) && strpos($target, 'type:') === 0) {
			$section_type = substr($target, 5) ?: $section_type;
		}

		// "all sections" — align every section to page/section context.
		if ($target === 'all') {
			$sections = self::get_sections($html);
			if (empty($sections)) {
				$target = 'last';
			} else {
				$new_html = $html;
				foreach ($sections as $sec) {
					$resolved = Av_Web_Studio_AI_Images::resolve_topic($topic, $page_title, $sec, $html);
					$type     = Av_Web_Studio_AI_Images::detect_section_type_from_html($sec);
					$updated  = Av_Web_Studio_AI_Images::align_section_images($sec, $resolved, $page_title, $type);
					if (stripos($updated, '<img') === false && stripos($updated, 'background-image') === false) {
						$industry = Av_Web_Studio_AI_Images::industry_for_topic($resolved, $page_title);
						$heading  = Av_Web_Studio_AI_Editor::extract_heading($sec);
						$alt      = Av_Web_Studio_AI_Images::descriptive_alt($resolved, $industry, 0, $heading);
						$img      = Av_Web_Studio_AI_Images::img_tag($alt, $resolved, 'av-web-studio-ai-injected-img', 1000, 560, 0, $page_title, $type, $heading);
						$updated  = preg_replace(
							'/(<section[^>]*>)(\s*)/i',
							'$1$2<div class="av-web-studio-ai-img-wrap">' . $img . '</div>',
							$updated,
							1
						);
					}
					$new_html = str_replace($sec, $updated, $new_html);
				}
				return [
					'html' => $new_html,
					'css'  => self::injected_image_css($context['css']),
					'js'   => $context['js'],
				];
			}
		}

		$section = self::get_section($html, $target);

		if (!$section) {
			$resolved = Av_Web_Studio_AI_Images::resolve_topic($topic, $page_title, null, $html);
			$industry = Av_Web_Studio_AI_Images::industry_for_topic($resolved, $page_title);
			$alt      = Av_Web_Studio_AI_Images::descriptive_alt($resolved, $industry);
			$heading  = Av_Web_Studio_AI_Images::is_generic_topic($resolved)
				? ucfirst(Av_Web_Studio_AI_Images::industry_label($industry))
				: ucfirst($resolved);

			$new_section = '<section class="av-web-studio-image-sec">' . "\n"
				. '  <div class="av-web-studio-image-sec__inner">' . "\n"
				. '    ' . Av_Web_Studio_AI_Images::img_tag($alt, $resolved, 'av-web-studio-image-sec__hero', 1200, 640, 0, $page_title, 'hero', $heading) . "\n"
				. '    <h2>' . esc_html($heading) . '</h2>' . "\n"
				. '  </div>' . "\n"
				. '</section>';

			$css = $context['css'] . "/* --- AI Image Section --- */
.av-web-studio-image-sec { padding: 60px 20px; width: 100%; text-align: center; }
.av-web-studio-image-sec__inner { max-width: 1100px; margin: 0 auto; }
.av-web-studio-image-sec__hero { width: 100%; max-height: 480px; object-fit: cover; border-radius: 16px; box-shadow: 0 8px 32px rgba(0,0,0,.12); margin-bottom: 24px; }
.av-web-studio-image-sec h2 { font-size: 2rem; color: #0f172a; margin: 0; }";

			return [
				'html' => trim($html . "\n\n" . $new_section),
				'css'  => trim($css),
				'js'   => $context['js'],
			];
		}

		$resolved = Av_Web_Studio_AI_Images::resolve_topic($topic, $page_title, $section, $html);
		$industry = Av_Web_Studio_AI_Images::industry_for_topic($resolved, $page_title);
		$heading  = self::extract_heading($section);
		$body     = Av_Web_Studio_AI_Images::extract_section_text($section);
		$type     = $section_type ?: Av_Web_Studio_AI_Images::detect_section_type_from_html($section);
		$updated  = $section;

		if (stripos($updated, '<img') === false && stripos($updated, 'background-image') === false) {
			$alt = Av_Web_Studio_AI_Images::descriptive_alt($resolved, $industry, 0, $heading);
			$img = Av_Web_Studio_AI_Images::img_tag($alt, $resolved, 'av-web-studio-ai-injected-img', 1000, 560, 0, $page_title, $type, $heading);
			$updated = preg_replace(
				'/(<section[^>]*>)(\s*)/i',
				'$1$2<div class="av-web-studio-ai-img-wrap">' . $img . '</div>',
				$updated,
				1
			);
		} else {
			$count = 0;
			$updated = preg_replace_callback(
				'/<img\b[^>]*>/i',
				function ($m) use ($resolved, $page_title, $type, $heading, $body, &$count) {
					$count++;
					$w   = 800;
					$h   = 500;
					$alt = Av_Web_Studio_AI_Images::descriptive_alt($resolved, '', $count - 1, $heading);
					$src = esc_url(Av_Web_Studio_AI_Images::relevant_url($resolved, $w, $h, $count - 1, $page_title, $type, $heading, $body));
					return '<img class="av-web-studio-ai-injected-img" src="' . $src . '" alt="' . esc_attr($alt) . '" width="' . $w . '" height="' . $h . '" loading="lazy" decoding="async" />';
				},
				$updated
			);
			$updated = Av_Web_Studio_AI_Images::align_images_in_markup($updated, $resolved, $page_title, $type, $heading);
		}

		$new_html = str_replace($section, $updated, $html);

		return [
			'html' => $new_html,
			'css'  => self::injected_image_css($context['css']),
			'js'   => $context['js'],
		];
	}

	/**
	 * Shared CSS for injected images.
	 *
	 * @param string $existing Existing CSS.
	 * @return string
	 */
	private static function injected_image_css($existing) {
		if (strpos($existing, 'av-web-studio-ai-injected-img') !== false) {
			return trim($existing);
		}

		return trim($existing . "/* --- AI Injected Images --- */
.av-web-studio-ai-img-wrap { margin-bottom: 24px; }
.av-web-studio-ai-injected-img {
  width: 100%;
  max-height: 420px;
  object-fit: cover;
  border-radius: 12px;
  box-shadow: 0 4px 20px rgba(0,0,0,.1);
  transition: transform 0.35s ease, box-shadow 0.35s ease;
}
.av-web-studio-ai-injected-img:hover {
  transform: scale(1.02);
  box-shadow: 0 8px 28px rgba(0,0,0,.15);
}"
		);
	}

	/**
	 * Edit existing page — style tweaks or section replacement.
	 *
	 * @param array  $context Page code.
	 * @param string $prompt  User prompt.
	 * @param array  $intent  Parsed intent.
	 * @return array|null
	 */
	public static function edit_page($context, $prompt, $intent) {
		$p = strtolower($prompt);

		// Background color change.
		if (preg_match('/background(?:\s+color)?\s+(?:to\s+)?(#?[a-z0-9]{3,8}|(?:dark|light)\s+\w+|(?:red|blue|green|purple|black|white|gray|grey|orange|pink|yellow|teal|navy|gold|silver|beige|brown|cyan|indigo|violet|maroon|olive|lime|aqua|fuchsia|salmon|coral|crimson|turquoise|magenta|lavender|mint|cream|charcoal|ivory|peach|plum|rose|sky|slate|stone|zinc|amber|emerald|ruby|sapphire|amber|bronze|copper|tan|khaki|wine|forest|ocean|midnight|sunset|sunrise|sand|snow|ash|coal|steel|iron|chrome|neon|pastel|gradient))/i', $prompt, $m)) {
			return self::apply_background_edit($context, trim($m[1]), $intent['target'] ?? 'last');
		}

		// Text/color/style keywords (without full regenerate).
		if (preg_match('/\b(darker|lighter|bigger|smaller|bold|center|rounded|shadow|gradient|modern|minimal|padding|spacing)\b/i', $p)
			&& empty($intent['is_complaint'])) {
			return self::apply_style_enhancements($context, $prompt, $intent['target'] ?? 'last');
		}

		// Fix/regenerate when user complains or names a section type to fix.
		if (!empty($intent['is_complaint']) || !empty($intent['section_type'])) {
			$regenerated = self::regenerate_section($context, $intent, $prompt);
			if ($regenerated) {
				return $regenerated;
			}
		}

		// Replace section with newly generated content matching prompt topic.
		if (!empty($intent['replace']) || preg_match('/\b(?:replace|rewrite|redesign)\b/i', $p)) {
			$regenerated = self::regenerate_section($context, $intent, $prompt);
			if ($regenerated) {
				return $regenerated;
			}

			$new_section = Av_Web_Studio_AI_Templates::generate($prompt, $context);
			$html        = self::replace_section($context['html'], $intent['target'] ?? 'last', $new_section['html']);

			return [
				'html' => $html,
				'css'  => trim($context['css'] . "\n\n/* --- Updated Section --- */\n" . $new_section['css']),
				'js'   => trim($context['js'] . "\n" . $new_section['js']),
			];
		}

		// Default edit: enhance last section styling + add animations.
		$enhanced = self::apply_style_enhancements($context, $prompt, $intent['target'] ?? 'last');
		return self::add_animations($enhanced, $intent['target'] ?? 'last');
	}

	/**
	 * Apply background color to targeted section.
	 *
	 * @param array      $context Page code.
	 * @param string     $color   Color value/word.
	 * @param string|int $target  Section target.
	 * @return array
	 */
	private static function apply_background_edit($context, $color, $target) {
		$section = self::get_section($context['html'], $target);
		$slug    = 'av-web-studio-edit-' . wp_rand(100, 999);

		if ($section && preg_match('/class="([^"]*)"/i', $section, $m)) {
			$class   = $m[1];
			$css_val = self::resolve_color($color);
			$rule    = '.' . explode(' ', trim($class))[0] . ' { background: ' . $css_val . ' !important; }';
		} else {
			$rule = 'section:last-of-type { background: ' . self::resolve_color($color) . ' !important; }';
		}

		return [
			'html' => $context['html'],
			'css'  => trim($context['css'] . "\n\n/* --- AI Edit --- */\n" . $rule),
			'js'   => $context['js'],
		];
	}

	/**
	 * Add general style enhancements to a section.
	 *
	 * @param array      $context Page code.
	 * @param string     $prompt  Prompt.
	 * @param string|int $target  Target section.
	 * @return array
	 */
	private static function apply_style_enhancements($context, $prompt, $target) {
		$section = self::get_section($context['html'], $target);
		$class   = 'section';

		if ($section && preg_match('/class="([^"]*)"/i', $section, $m)) {
			$class = explode(' ', trim($m[1]))[0];
		}

		$extra_css = "
/* --- AI Style Enhance --- */
.{$class} {
  border-radius: 16px;
  box-shadow: 0 4px 24px rgba(0,0,0,.08);
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.{$class}:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 32px rgba(0,0,0,.12);
}
.{$class} h1, .{$class} h2, .{$class} h3 {
  letter-spacing: -0.02em;
}
.{$class} img {
  border-radius: 12px;
}";

		return [
			'html' => $context['html'],
			'css'  => trim($context['css'] . $extra_css),
			'js'   => $context['js'],
		];
	}

	/**
	 * Map color words to CSS values.
	 *
	 * @param string $color Color input.
	 * @return string
	 */
	private static function resolve_color($color) {
		$map = [
			'dark blue'   => '#1e3a5f',
			'light blue'  => '#dbeafe',
			'dark'        => '#0f172a',
			'light'       => '#f8fafc',
			'red'         => '#ef4444',
			'blue'        => '#3b82f6',
			'green'       => '#22c55e',
			'purple'      => '#8b5cf6',
			'black'       => '#0f172a',
			'white'       => '#ffffff',
			'gray'        => '#64748b',
			'grey'        => '#64748b',
			'orange'      => '#f97316',
			'pink'        => '#ec4899',
			'yellow'      => '#eab308',
			'gradient'    => 'linear-gradient(135deg, #6b5ce7 0%, #a855f7 100%)',
			'dark purple' => '#4c1d95',
			'navy'        => '#1e3a8a',
			'teal'        => '#14b8a6',
		];

		$key = strtolower(trim($color));
		if (isset($map[ $key ])) {
			return $map[ $key ];
		}
		if (preg_match('/^#?[a-f0-9]{3,8}$/i', $color)) {
			return strpos($color, '#') === 0 ? $color : '#' . $color;
		}

		return sanitize_text_field($color);
	}
}



