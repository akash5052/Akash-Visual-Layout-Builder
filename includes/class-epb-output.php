<?php

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Sanitize visual-builder HTML and generated CSS. Users cannot paste CSS/JS.
 */
class EPB_Output {

	/**
	 * Allowed HTML for compiled visual markup.
	 *
	 * @return array
	 */
	public static function allowed_html() {
		$allowed = wp_kses_allowed_html('post');

		$allowed['iframe'] = [
			'src'             => true,
			'width'           => true,
			'height'          => true,
			'frameborder'     => true,
			'allow'           => true,
			'allowfullscreen' => true,
			'title'           => true,
			'loading'         => true,
			'referrerpolicy'  => true,
		];

		$allowed['button'] = [
			'type'       => true,
			'name'       => true,
			'value'      => true,
			'disabled'   => true,
			'aria-label' => true,
			'aria-hidden'=> true,
			'aria-modal' => true,
			'role'       => true,
		];

		foreach ($allowed as $tag => $attrs) {
			if (!is_array($attrs)) {
				$attrs = [];
			}
			$attrs['data-*']      = true;
			$attrs['style']       = true;
			$attrs['class']       = true;
			$attrs['id']          = true;
			$attrs['role']        = true;
			$attrs['aria-hidden'] = true;
			$attrs['aria-modal']  = true;
			$attrs['aria-label']  = true;
			$allowed[ $tag ]      = $attrs;
		}

		return $allowed;
	}

	/**
	 * KSES compiled visual HTML. Scripts and event handlers are stripped.
	 *
	 * @param string $html Raw HTML.
	 * @return string
	 */
	public static function kses_html($html) {
		if (!is_string($html) || $html === '') {
			return '';
		}

		$html = wp_kses($html, self::allowed_html());
		$html = preg_replace('/<script\b[^>]*>.*?<\/script>/is', '', $html);

		return is_string($html) ? $html : '';
	}

	/**
	 * Sanitize CSS generated from visual widget settings.
	 *
	 * @param string $css Generated CSS.
	 * @return string
	 */
	public static function sanitize_css($css) {
		if (!is_string($css) || $css === '') {
			return '';
		}

		$css = str_replace("\0", '', $css);
		$css = preg_replace('/<\/style/i', '', $css);
		$css = preg_replace('/<script/i', '', $css);
		$css = preg_replace('/expression\s*\(/i', '', $css);
		$css = preg_replace('/javascript\s*:/i', '', $css);
		$css = preg_replace('/@import\b/i', '', $css);

		return trim($css);
	}

	/**
	 * Sanitize a single CSS property value used in compiled visual styles.
	 *
	 * @param mixed $value Raw value.
	 * @return string
	 */
	public static function sanitize_css_value($value) {
		if (!is_scalar($value)) {
			return '';
		}

		$value = trim((string) $value);
		if ($value === '') {
			return '';
		}

		$value = str_replace([ "\0", "\n", "\r", "\t", '{', '}', '<', '>' ], '', $value);
		$value = preg_replace('/expression\s*\(/i', '', $value);
		$value = preg_replace('/javascript\s*:/i', '', $value);
		$value = preg_replace('/@import\b/i', '', $value);
		$value = preg_replace('/behavior\s*:/i', '', $value);
		$value = preg_replace('/-moz-binding/i', '', $value);
		$value = preg_replace('/url\s*\(\s*[\'"]?\s*javascript/i', '', $value);

		return is_string($value) ? trim($value) : '';
	}

	/**
	 * True when a URL is a third-party stock-photo host (not shipped with the plugin).
	 *
	 * @param string $url URL.
	 * @return bool
	 */
	public static function is_remote_stock_src($url) {
		$host = strtolower((string) wp_parse_url($url, PHP_URL_HOST));
		if ($host === '') {
			return false;
		}
		$parts   = explode('.', $host);
		$blocked = [ 'pexels', 'unsplash', 'loremflickr', 'pollinations' ];
		foreach ($parts as $part) {
			if (in_array($part, $blocked, true)) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Keep local/site media; swap known stock CDNs for the bundled placeholder.
	 *
	 * @param string $url URL.
	 * @return string
	 */
	public static function sanitize_media_src($url) {
		$url = trim((string) $url);
		if ($url === '') {
			return '';
		}
		if (self::is_remote_stock_src($url)) {
			return EPB_PLUGIN_URL . 'assets/images/placeholder.svg';
		}
		return $url;
	}

	/**
	 * Compile a visual document to safe HTML + CSS. JavaScript is never stored.
	 *
	 * @param array $doc Visual document.
	 * @return array{html:string,css:string,js:string}
	 */
	public static function compile_visual($doc) {
		if (!is_array($doc)) {
			return [
				'html' => '',
				'css'  => '',
				'js'   => '',
			];
		}

		$compiled = EPB_Visual_Compile::compile(self::scrub_visual($doc));

		return [
			'html' => self::kses_html($compiled['html'] ?? ''),
			'css'  => self::sanitize_css($compiled['css'] ?? ''),
			'js'   => '',
		];
	}

	/**
	 * Convert legacy HTML widgets into escaped text widgets.
	 *
	 * @param array $doc Visual document.
	 * @return array
	 */
	public static function scrub_visual($doc) {
		if (!is_array($doc) || empty($doc['sections']) || !is_array($doc['sections'])) {
			return $doc;
		}

		foreach ($doc['sections'] as $s => $section) {
			if (!is_array($section) || empty($section['columns']) || !is_array($section['columns'])) {
				continue;
			}
			$doc['sections'][ $s ] = self::scrub_section($section);
		}

		return $doc;
	}

	/**
	 * @param array $section Section.
	 * @return array
	 */
	private static function scrub_section($section) {
		foreach ($section['columns'] as $c => $column) {
			if (!is_array($column) || empty($column['children']) || !is_array($column['children'])) {
				continue;
			}
			foreach ($column['children'] as $i => $child) {
				$section['columns'][ $c ]['children'][ $i ] = self::scrub_child($child);
			}
		}
		return $section;
	}

	/**
	 * @param mixed $child Column child.
	 * @return mixed
	 */
	private static function scrub_child($child) {
		if (!is_array($child)) {
			return $child;
		}
		if (($child['type'] ?? '') === 'inner-section') {
			return self::scrub_section($child);
		}
		if (($child['type'] ?? '') === 'html') {
			$child['type']    = 'text';
			$child['content'] = wp_strip_all_tags((string) ($child['content'] ?? ''));
		}
		return $child;
	}

	/**
	 * Turn markup into visual widgets (headings, text, images, buttons).
	 * Scripts, styles, and raw HTML widgets are not created.
	 *
	 * @param string $html Markup.
	 * @return array
	 */
	public static function visual_from_html($html) {
		$html = is_string($html) ? $html : '';
		$html = preg_replace('/<script\b[^>]*>.*?<\/script>/is', '', $html);
		$html = preg_replace('/<style\b[^>]*>.*?<\/style>/is', '', $html);
		$html = (string) $html;

		$widgets = [];
		$pattern = '/<(h[1-6]|p|img|a|blockquote|li)(\s[^>]*)?(?:\/>|>(.*?)<\/\1>)/is';
		if (preg_match_all($pattern, $html, $matches, PREG_SET_ORDER)) {
			foreach ($matches as $m) {
				if (count($widgets) >= 80) {
					break;
				}
				$tag = strtolower($m[1]);
				$attrs = $m[2] ?? '';
				$inner = isset($m[3]) ? wp_strip_all_tags($m[3]) : '';
				$inner = trim(html_entity_decode($inner, ENT_QUOTES | ENT_HTML5, 'UTF-8'));

				if ($tag === 'img') {
					$src = '';
					$alt = '';
					if (preg_match('/src\s*=\s*[\'"]([^\'"]+)[\'"]/i', $attrs, $sm)) {
						$src = esc_url_raw(self::sanitize_media_src($sm[1]));
					}
					if (preg_match('/alt\s*=\s*[\'"]([^\'"]*)[\'"]/i', $attrs, $am)) {
						$alt = sanitize_text_field($am[1]);
					}
					if ($src !== '') {
						$widgets[] = self::visual_widget('image', [ 'src' => $src, 'alt' => $alt ]);
					}
					continue;
				}

				if ($tag === 'a') {
					$href = '#';
					if (preg_match('/href\s*=\s*[\'"]([^\'"]+)[\'"]/i', $attrs, $hm)) {
						$href = esc_url_raw($hm[1]) ?: '#';
					}
					if ($inner !== '') {
						$widgets[] = self::visual_widget('button', [ 'label' => $inner, 'url' => $href ]);
					}
					continue;
				}

				if ($inner === '') {
					continue;
				}

				if (preg_match('/^h[1-6]$/', $tag)) {
					$widgets[] = self::visual_widget('heading', [
						'tag'     => in_array($tag, [ 'h1', 'h2', 'h3', 'h4' ], true) ? $tag : 'h2',
						'content' => $inner,
					]);
					continue;
				}

				$widgets[] = self::visual_widget('text', [ 'content' => $inner ]);
			}
		}

		if (!$widgets) {
			$text = trim(wp_strip_all_tags($html));
			if ($text !== '') {
				$widgets[] = self::visual_widget('text', [ 'content' => $text ]);
			}
		}

		if (!$widgets) {
			return [
				'version'  => 1,
				'sections' => [],
			];
		}

		$children = [];
		foreach ($widgets as $i => $widget) {
			$widget['id'] = 'w_import_' . $i;
			$children[]   = $widget;
		}

		return [
			'version'  => 1,
			'sections' => [
				[
					'id'       => 'sec_imported',
					'type'     => 'section',
					'settings' => [
						'fullWidth'       => true,
						'contentWidth'    => 1140,
						'minHeight'       => 0,
						'padding'         => '40px 24px',
						'background'      => '#ffffff',
						'backgroundImage' => '',
						'textColor'       => '#0f172a',
						'gap'             => 0,
						'zIndex'          => '',
					],
					'columns'  => [
						[
							'id'       => 'col_imported',
							'type'     => 'column',
							'settings' => [
								'width'         => 100,
								'padding'       => '12px',
								'background'    => 'transparent',
								'verticalAlign' => 'top',
								'zIndex'        => '',
							],
							'children' => $children,
						],
					],
				],
			],
		];
	}

	/**
	 * Minimal visual widget.
	 *
	 * @param string $type Widget type.
	 * @param array  $props Extra properties.
	 * @return array
	 */
	private static function visual_widget($type, $props = []) {
		$base = [
			'id'         => '',
			'type'       => $type,
			'linkUrl'    => '',
			'linkNewTab' => false,
			'style'      => [
				'marginTop'     => '0px',
				'marginRight'   => '0px',
				'marginBottom'  => '20px',
				'marginLeft'    => '0px',
				'paddingTop'    => '0px',
				'paddingRight'  => '0px',
				'paddingBottom' => '0px',
				'paddingLeft'   => '0px',
				'fontFamily'    => '',
				'fontSize'      => '',
				'fontWeight'    => '',
				'lineHeight'    => '',
				'letterSpacing' => '0',
				'textTransform' => 'none',
				'borderRadius'  => '0px',
				'borderWidth'   => '0px',
				'borderColor'   => '#e2e8f0',
				'borderStyle'   => 'none',
				'boxShadow'     => 'none',
				'opacity'       => 1,
				'background'    => 'transparent',
				'maxWidth'      => '100%',
				'zIndex'        => '',
			],
		];
		return array_merge($base, $props);
	}
}
