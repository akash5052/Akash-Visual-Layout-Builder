<?php

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Compile a visual builder document to HTML / CSS / JS (mirrors editor compile).
 */
class Akash_Visual_Layout_Builder_Visual_Compile {

	const BREAKPOINT_TABLET = 1024;
	const BREAKPOINT_MOBILE = 767;

	/**
	 * Compile a visual document.
	 *
	 * @param array $doc Visual document.
	 * @return array{html:string,css:string,js:string}
	 */
	public static function compile($doc) {
		$sections = isset($doc['sections']) && is_array($doc['sections']) ? $doc['sections'] : [];
		$html_parts = [];
		foreach ($sections as $section) {
			$html_parts[] = self::compile_section($section);
		}

		$html = "<div class=\"akash-visual-layout-builder-visual-root\">\n" . implode("\n", $html_parts) . "\n</div>";
		$css   = "/* Akash Visual Layout Builder — Visual mode */\n"
			. ".akash-visual-layout-builder-visual-root { width: 100%; }\n"
			. ".akash-visual-layout-builder-section { box-sizing: border-box; }\n"
			. ".akash-visual-layout-builder-col { min-width: 0; }\n"
			. ".akash-visual-layout-builder-w-heading, .akash-visual-layout-builder-w-text, .akash-visual-layout-builder-w-button, .akash-visual-layout-builder-w-image, .akash-visual-layout-builder-w-iconbox { word-break: break-word; }\n"
			. self::widget_css()
			. "@media (max-width: 768px) {\n"
			. "  .akash-visual-layout-builder-container { flex-direction: column !important; }\n"
			. "  .akash-visual-layout-builder-col { flex: 1 1 100% !important; max-width: 100% !important; }\n"
			. "}\n"
			. self::collect_responsive_css($doc);

		return [
			'html' => $html,
			'css'  => $css,
			'js'   => '',
		];
	}

	/**
	 * Escape HTML text.
	 *
	 * @param string $value Raw.
	 * @return string
	 */
	private static function esc($value) {
		return esc_html((string) $value);
	}

	/**
	 * Escape attribute.
	 *
	 * @param string $value Raw.
	 * @return string
	 */
	private static function esc_attr_val($value) {
		return esc_attr((string) $value);
	}

	/**
	 * Escape a URL for href attributes.
	 *
	 * @param string $value Raw URL.
	 * @return string
	 */
	private static function esc_href($value) {
		$url = trim((string) $value);
		if ($url === '') {
			return '#';
		}
		$safe = esc_url($url);
		return $safe !== '' ? $safe : '#';
	}

	/**
	 * Escape an image URL, swapping known stock hosts for a local placeholder.
	 *
	 * @param string $value Raw URL.
	 * @return string
	 */
	private static function esc_src($value) {
		return esc_url(self::local_media_url((string) $value));
	}

	/**
	 * Build inline style string from widget style + extras.
	 *
	 * @param array $style  Style map.
	 * @param array $extra  Extra CSS props.
	 * @return string
	 */
	private static function style_css($style, $extra = []) {
		$s = is_array($style) ? $style : [];
		$parts = [];
		$push = function ($key, $value) use (&$parts) {
			if ($value === null || $value === '') {
				return;
			}
			$value = Akash_Visual_Layout_Builder_Output::sanitize_css_value($value);
			if ($value === '') {
				return;
			}
			$key = preg_replace('/[^a-z0-9\-]/i', '', (string) $key);
			if ($key === '') {
				return;
			}
			$parts[] = $key . ':' . $value;
		};

		$push('margin-top', $s['marginTop'] ?? '');
		$push('margin-right', $s['marginRight'] ?? '');
		$push('margin-bottom', $s['marginBottom'] ?? '');
		$push('margin-left', $s['marginLeft'] ?? '');
		$push('padding-top', $s['paddingTop'] ?? '');
		$push('padding-right', $s['paddingRight'] ?? '');
		$push('padding-bottom', $s['paddingBottom'] ?? '');
		$push('padding-left', $s['paddingLeft'] ?? '');
		$push('font-family', $s['fontFamily'] ?? '');
		$push('font-size', $s['fontSize'] ?? '');
		$push('font-weight', $s['fontWeight'] ?? '');
		$push('line-height', $s['lineHeight'] ?? '');
		$push('letter-spacing', $s['letterSpacing'] ?? '');
		if (!empty($s['textTransform']) && $s['textTransform'] !== 'none') {
			$push('text-transform', $s['textTransform']);
		}
		$push('border-radius', $s['borderRadius'] ?? '');
		$border_style = $s['borderStyle'] ?? 'none';
		$border_width = $s['borderWidth'] ?? '0px';
		if ($border_style !== 'none' && $border_width && $border_width !== '0px') {
			$push('border', $border_width . ' ' . $border_style . ' ' . ($s['borderColor'] ?? '#e2e8f0'));
		}
		if (!empty($s['boxShadow']) && $s['boxShadow'] !== 'none') {
			$push('box-shadow', $s['boxShadow']);
		}
		if (isset($s['opacity']) && (float) $s['opacity'] !== 1.0) {
			$push('opacity', $s['opacity']);
		}
		if (!empty($s['background']) && $s['background'] !== 'transparent') {
			$push('background', $s['background']);
		}
		if (!empty($s['maxWidth']) && $s['maxWidth'] !== '100%') {
			$push('max-width', $s['maxWidth']);
		}
		if (isset($s['zIndex']) && trim((string) $s['zIndex']) !== '') {
			$push('z-index', $s['zIndex']);
			$push('position', 'relative');
		}

		foreach ($extra as $key => $value) {
			$push($key, $value);
		}

		return implode(';', $parts);
	}

	/**
	 * Section outer style.
	 *
	 * @param array $settings Section settings.
	 * @return string
	 */
	private static function section_style($settings) {
		$settings = is_array($settings) ? $settings : [];
		$parts    = array_filter([
			self::css_decl('padding', $settings['padding'] ?? '60px 24px'),
			self::css_decl('background', $settings['background'] ?? 'transparent'),
			self::css_decl('color', $settings['textColor'] ?? 'inherit'),
			'width:100%',
			'box-sizing:border-box',
		]);
		if (!empty($settings['minHeight']) && $settings['minHeight'] !== 0 && $settings['minHeight'] !== '0' && $settings['minHeight'] !== '0px') {
			$min = self::css_length($settings['minHeight'], '');
			if ($min !== '') {
				$parts[] = 'min-height:' . $min;
			}
		}
		if (!empty($settings['backgroundImage'])) {
			$raw = Akash_Visual_Layout_Builder_Output::sanitize_css_value((string) $settings['backgroundImage']);
			$raw = self::local_media_url($raw);
			if (strpos($raw, 'gradient(') !== false || strpos($raw, 'url(') !== false) {
				$parts[] = 'background-image:' . $raw;
			} elseif ($raw !== '') {
				$url = esc_url($raw);
				if ($url) {
					$parts[] = "background-image:linear-gradient(rgba(15,23,42,.55),rgba(15,23,42,.35)),url('" . esc_url($url) . "')";
				}
			}
			$parts[] = 'background-size:cover';
			$parts[] = 'background-position:center';
		}
		if (isset($settings['zIndex']) && trim((string) $settings['zIndex']) !== '') {
			$z = Akash_Visual_Layout_Builder_Output::sanitize_css_value($settings['zIndex']);
			if ($z !== '') {
				$parts[] = 'z-index:' . $z;
				$parts[] = 'position:relative';
			}
		}
		return implode(';', $parts);
	}

	/**
	 * Compile one section.
	 *
	 * @param array $section Section.
	 * @return string
	 */
	private static function compile_section($section) {
		$settings = $section['settings'] ?? [];
		$gap      = self::css_length($settings['gap'] ?? 0, '0px');
		$max      = self::css_length($settings['contentWidth'] ?? 1140, '1140px');
		$cols     = [];
		foreach (($section['columns'] ?? []) as $column) {
			$cols[] = self::compile_column($column);
		}
		$container = '<div class="akash-visual-layout-builder-container" style="max-width:' . esc_attr($max) . ';margin:0 auto;display:flex;flex-wrap:wrap;gap:' . esc_attr($gap) . ';width:100%;">'
			. implode("\n", $cols)
			. '</div>';
		return '<section class="akash-visual-layout-builder-section" data-akash-visual-layout-builder-id="' . esc_attr($section['id'] ?? '') . '" style="' . esc_attr(self::section_style($settings)) . '">' . $container . '</section>';
	}

	/**
	 * Compile column.
	 *
	 * @param array $column Column.
	 * @return string
	 */
	private static function compile_column($column) {
		$settings = $column['settings'] ?? [];
		$width    = isset($settings['width']) ? (float) $settings['width'] : 100;
		$align    = $settings['verticalAlign'] ?? 'top';
		$justify  = $align === 'middle' ? 'center' : ($align === 'bottom' ? 'flex-end' : 'flex-start');
		$style    = array_filter([
			'flex:0 0 ' . $width . '%',
			'max-width:' . $width . '%',
			self::css_decl('padding', $settings['padding'] ?? '12px'),
			self::css_decl('background', $settings['background'] ?? 'transparent'),
			'box-sizing:border-box',
			'display:flex',
			'flex-direction:column',
			'justify-content:' . Akash_Visual_Layout_Builder_Output::sanitize_css_value($justify),
		]);
		$body = [];
		foreach (($column['children'] ?? []) as $child) {
			if (($child['type'] ?? '') === 'inner-section') {
				$body[] = self::compile_section($child);
			} else {
				$body[] = self::compile_widget($child);
			}
		}
		return '<div class="akash-visual-layout-builder-col" data-akash-visual-layout-builder-id="' . esc_attr($column['id'] ?? '') . '" style="' . esc_attr(implode(';', $style)) . '">' . implode("\n", $body) . '</div>';
	}

	/**
	 * CSS length helper.
	 *
	 * @param mixed  $value    Value.
	 * @param string $fallback Fallback.
	 * @return string
	 */
	private static function css_length($value, $fallback) {
		if ($value === null || $value === '') {
			return $fallback;
		}
		if (is_numeric($value)) {
			return ((float) $value) . 'px';
		}
		$value = Akash_Visual_Layout_Builder_Output::sanitize_css_value((string) $value);
		if ($value === '' || !preg_match('/^-?[0-9.]+(px|em|rem|%|vh|vw|pt)?$/i', $value)) {
			return $fallback;
		}
		return $value;
	}

	/**
	 * CSS declaration or empty.
	 *
	 * @param string $property Property name.
	 * @param mixed  $value    Value.
	 * @return string
	 */
	private static function css_decl($property, $value) {
		$value = Akash_Visual_Layout_Builder_Output::sanitize_css_value($value);
		if ($value === '') {
			return '';
		}
		$property = preg_replace('/[^a-z0-9\-]/i', '', (string) $property);
		if ($property === '') {
			return '';
		}
		return $property . ':' . $value;
	}

	/**
	 * Replace bundled stock-photo hosts with a local placeholder.
	 *
	 * @param string $url URL.
	 * @return string
	 */
	private static function local_media_url($url) {
		return Akash_Visual_Layout_Builder_Output::sanitize_media_src($url);
	}

	/**
	 * Normalize soft line breaks then escape for HTML text nodes.
	 *
	 * @param string $value Raw content.
	 * @return string
	 */
	private static function esc_text($value) {
		$value = (string) $value;
		$value = preg_replace('/<br\s*\/?>/i', "\n", $value);
		// Convert real newlines and literal "\n" sequences from single-quoted PHP strings.
		$value = str_replace([ "\r\n", "\r", '\\n' ], "\n", $value);
		$value = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
		return self::esc($value);
	}

	/**
	 * Compile widget HTML.
	 *
	 * @param array $widget Widget.
	 * @return string
	 */
	private static function compile_widget($widget) {
		return self::inject_data_id(self::compile_widget_inner($widget), $widget['id'] ?? '');
	}

	/**
	 * Compile widget HTML without data attribute wrapper.
	 *
	 * @param array $widget Widget.
	 * @return string
	 */
	private static function compile_widget_inner($widget) {
		$type  = $widget['type'] ?? 'text';
		$style = $widget['style'] ?? [];

		switch ($type) {
			case 'heading':
				$tag = in_array($widget['tag'] ?? 'h2', [ 'h1', 'h2', 'h3', 'h4' ], true) ? $widget['tag'] : 'h2';
				$css = self::style_css($style, [
					'text-align'   => $widget['align'] ?? 'left',
					'color'        => $widget['color'] ?? '#0f172a',
					'display'      => 'block',
					'white-space'  => 'pre-line',
				]);
				return '<' . $tag . ' class="akash-visual-layout-builder-w akash-visual-layout-builder-w-heading" style="' . esc_attr($css) . '">' . self::esc_text($widget['content'] ?? '') . '</' . $tag . '>';

			case 'text':
				$css = self::style_css($style, [
					'text-align'  => $widget['align'] ?? 'left',
					'color'       => $widget['color'] ?? '#475569',
					'display'     => 'block',
					'white-space' => 'pre-line',
				]);
				return '<p class="akash-visual-layout-builder-w akash-visual-layout-builder-w-text" style="' . esc_attr($css) . '">' . self::esc_text($widget['content'] ?? '') . '</p>';

			case 'button':
				$wrap = self::style_css(
					array_merge($style, [
						'paddingTop'    => '0px',
						'paddingRight'  => '0px',
						'paddingBottom' => '0px',
						'paddingLeft'   => '0px',
						'background'    => 'transparent',
						'borderStyle'   => 'none',
						'boxShadow'     => 'none',
					]),
					[ 'text-align' => $widget['align'] ?? 'left' ]
				);
				$btn = [
					'display:inline-flex',
					'align-items:center',
					'justify-content:center',
					'padding:' . Akash_Visual_Layout_Builder_Output::sanitize_css_value($widget['paddingY'] ?? '12px') . ' ' . Akash_Visual_Layout_Builder_Output::sanitize_css_value($widget['paddingX'] ?? '24px'),
					'border-radius:' . Akash_Visual_Layout_Builder_Output::sanitize_css_value($style['borderRadius'] ?? '0px'),
					'background:' . Akash_Visual_Layout_Builder_Output::sanitize_css_value($widget['background'] ?? '#6366f1'),
					'color:' . Akash_Visual_Layout_Builder_Output::sanitize_css_value($widget['textColor'] ?? '#ffffff'),
					'text-decoration:none',
					'font-weight:' . Akash_Visual_Layout_Builder_Output::sanitize_css_value($style['fontWeight'] ?? '700'),
					'font-size:' . Akash_Visual_Layout_Builder_Output::sanitize_css_value($style['fontSize'] ?? '15px'),
				];
				if (!empty($widget['fullWidth'])) {
					$btn[] = 'width:100%';
				}
				return '<div class="akash-visual-layout-builder-w akash-visual-layout-builder-w-button" style="' . esc_attr($wrap) . '"><a href="' . self::esc_href($widget['url'] ?? '#') . '" style="' . esc_attr(implode(';', $btn)) . '">' . self::esc($widget['label'] ?? 'Button') . '</a></div>';

			case 'image':
				$wrap = self::style_css($style, [ 'text-align' => $widget['align'] ?? 'center' ]);
				$img  = [
					'display:inline-block',
					'width:' . self::esc_attr_val($widget['width'] ?? '100%'),
					'max-width:100%',
					'height:auto',
					'border-radius:' . self::esc_attr_val($widget['borderRadius'] ?? ($style['borderRadius'] ?? '0')),
					'object-fit:' . self::esc_attr_val($widget['objectFit'] ?? 'cover'),
				];
				return '<figure class="akash-visual-layout-builder-w akash-visual-layout-builder-w-image" style="' . esc_attr($wrap) . '"><img src="' . self::esc_src($widget['src'] ?? '') . '" alt="' . self::esc_attr_val($widget['alt'] ?? '') . '" style="' . esc_attr(implode(';', $img)) . '" loading="lazy" /></figure>';

			case 'spacer':
				$h = is_numeric($widget['height'] ?? 40) ? ((int) $widget['height']) . 'px' : (string) ($widget['height'] ?? '40px');
				return '<div class="akash-visual-layout-builder-w akash-visual-layout-builder-w-spacer" style="' . esc_attr(self::style_css($style, [ 'height' => $h, 'margin-bottom' => '0' ])) . '" aria-hidden="true"></div>';

			case 'divider':
				$wrap  = self::style_css($style, [ 'text-align' => $widget['align'] ?? 'center' ]);
				$thick = is_numeric($widget['thickness'] ?? 1) ? ((int) $widget['thickness']) . 'px' : (string) ($widget['thickness'] ?? '1px');
				return '<div class="akash-visual-layout-builder-w akash-visual-layout-builder-w-divider" style="' . esc_attr($wrap) . '"><hr style="border:none;border-top:' . esc_attr($thick) . ' solid ' . self::esc_attr_val($widget['color'] ?? '#e2e8f0') . ';width:' . self::esc_attr_val($widget['width'] ?? '100%') . ';margin:0 auto;display:inline-block;" /></div>';

			case 'icon-box':
				$css = self::style_css($style, [ 'text-align' => $widget['align'] ?? 'center' ]);
				return '<div class="akash-visual-layout-builder-w akash-visual-layout-builder-w-iconbox" style="' . esc_attr($css) . '"><div style="font-size:' . self::esc_attr_val($widget['iconSize'] ?? '28px') . ';margin-bottom:10px;line-height:1;">' . self::esc($widget['icon'] ?? '◆') . '</div><h3 style="margin:0 0 8px;font-size:1.1rem;color:' . self::esc_attr_val($widget['titleColor'] ?? '#0f172a') . ';">' . self::esc($widget['title'] ?? '') . '</h3><p style="margin:0;color:' . self::esc_attr_val($widget['textColor'] ?? '#64748b') . ';line-height:1.6;">' . self::esc($widget['text'] ?? '') . '</p></div>';

			case 'counter':
				$css = self::style_css($style, [ 'text-align' => $widget['align'] ?? 'center' ]);
				return '<div class="akash-visual-layout-builder-w akash-visual-layout-builder-w-counter" data-akash-visual-layout-builder-counter data-end="' . esc_attr((string) ($widget['end'] ?? 0)) . '" data-duration="' . esc_attr((string) ($widget['duration'] ?? 1500)) . '" style="' . esc_attr($css) . '"><div style="font-size:2.4rem;font-weight:800;line-height:1;color:' . self::esc_attr_val($widget['numberColor'] ?? '#0f172a') . ';">' . self::esc($widget['prefix'] ?? '') . '<span data-akash-visual-layout-builder-counter-value>0</span>' . self::esc($widget['suffix'] ?? '') . '</div><div style="margin-top:8px;color:' . self::esc_attr_val($widget['titleColor'] ?? '#64748b') . ';">' . self::esc($widget['title'] ?? '') . '</div></div>';

			case 'testimonial':
				$css = self::style_css($style, [ 'text-align' => $widget['align'] ?? 'left' ]);
				$avatar = !empty($widget['avatar'])
					? '<img src="' . self::esc_src($widget['avatar']) . '" alt="" style="width:48px;height:48px;border-radius:50%;object-fit:cover;" loading="lazy" />'
					: '';
				return '<blockquote class="akash-visual-layout-builder-w akash-visual-layout-builder-w-testimonial" style="' . esc_attr($css) . '"><p style="margin:0 0 16px;font-size:1.05rem;line-height:1.7;">“' . self::esc($widget['content'] ?? '') . '”</p><footer style="display:flex;gap:12px;align-items:center;">' . $avatar . '<div><strong>' . self::esc($widget['name'] ?? '') . '</strong><div style="color:#64748b;font-size:0.9rem;">' . self::esc($widget['role'] ?? '') . '</div></div></footer></blockquote>';

			case 'price-table':
				$css = self::style_css($style, [
					'background'    => $widget['background'] ?? '#ffffff',
					'padding-top'   => '28px',
					'padding-right' => '24px',
					'padding-bottom'=> '28px',
					'padding-left'  => '24px',
					'border-radius' => $style['borderRadius'] ?? '16px',
					'text-align'    => 'center',
				]);
				$features = array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', (string) ($widget['features'] ?? ''))));
				$lis = '';
				foreach ($features as $f) {
					$lis .= '<li style="padding:8px 0;border-bottom:1px solid rgba(15,23,42,.08);">' . self::esc($f) . '</li>';
				}
				return '<div class="akash-visual-layout-builder-w akash-visual-layout-builder-w-pricetable" style="' . esc_attr($css) . '"><h3 style="margin:0 0 8px;">' . self::esc($widget['title'] ?? '') . '</h3><div style="font-size:2.4rem;font-weight:800;margin:8px 0;">' . self::esc($widget['price'] ?? '') . '<span style="font-size:0.95rem;font-weight:500;color:#64748b;"> ' . self::esc($widget['period'] ?? '') . '</span></div><ul style="list-style:none;margin:16px 0 24px;padding:0;text-align:left;">' . $lis . '</ul><a href="' . self::esc_href($widget['buttonUrl'] ?? '#') . '" style="display:inline-flex;padding:12px 22px;background:' . self::esc_attr_val($widget['buttonBackground'] ?? '#0f172a') . ';color:#fff;text-decoration:none;font-weight:700;border-radius:8px;">' . self::esc($widget['buttonLabel'] ?? 'Choose') . '</a></div>';

			case 'call-to-action':
				$css = self::style_css($style, [
					'background'     => $widget['background'] ?? '#0f172a',
					'padding-top'    => '40px',
					'padding-right'  => '32px',
					'padding-bottom' => '40px',
					'padding-left'   => '32px',
					'border-radius'  => $style['borderRadius'] ?? '16px',
					'color'          => '#ffffff',
				]);
				return '<div class="akash-visual-layout-builder-w akash-visual-layout-builder-w-cta" style="' . esc_attr($css) . '"><h3 style="margin:0 0 10px;font-size:1.6rem;">' . self::esc($widget['title'] ?? '') . '</h3><p style="margin:0 0 20px;opacity:.9;line-height:1.6;">' . self::esc($widget['text'] ?? '') . '</p><a href="' . self::esc_href($widget['buttonUrl'] ?? '#') . '" style="display:inline-flex;padding:12px 22px;background:#fff;color:#0f172a;text-decoration:none;font-weight:700;border-radius:8px;">' . self::esc($widget['buttonLabel'] ?? 'Get Started') . '</a></div>';

			case 'team':
				$css = self::style_css($style, [ 'text-align' => $widget['align'] ?? 'center' ]);
				$img = !empty($widget['image'])
					? '<img src="' . self::esc_src($widget['image']) . '" alt="' . self::esc_attr_val($widget['name'] ?? '') . '" style="width:120px;height:120px;border-radius:50%;object-fit:cover;margin:0 auto 14px;display:block;" loading="lazy" />'
					: '';
				return '<div class="akash-visual-layout-builder-w akash-visual-layout-builder-w-team" style="' . esc_attr($css) . '">' . $img . '<h3 style="margin:0 0 4px;">' . self::esc($widget['name'] ?? '') . '</h3><div style="color:#64748b;margin-bottom:10px;">' . self::esc($widget['role'] ?? '') . '</div><p style="margin:0;line-height:1.6;color:#475569;">' . self::esc($widget['bio'] ?? '') . '</p></div>';

			case 'dual-button':
				$css = self::style_css($style, [ 'text-align' => $widget['align'] ?? 'center' ]);
				$left = 'display:inline-flex;padding:12px 20px;background:' . self::esc_attr_val($widget['leftBackground'] ?? '#6366f1') . ';color:#fff;text-decoration:none;font-weight:700;border-radius:8px;margin:4px;';
				$right = 'display:inline-flex;padding:12px 20px;background:' . self::esc_attr_val($widget['rightBackground'] ?? '#0f172a') . ';color:#fff;text-decoration:none;font-weight:700;border-radius:8px;margin:4px;';
				return '<div class="akash-visual-layout-builder-w akash-visual-layout-builder-w-dualbutton" style="' . esc_attr($css) . '"><a href="' . self::esc_href($widget['leftUrl'] ?? '#') . '" style="' . esc_attr($left) . '">' . self::esc($widget['leftLabel'] ?? '') . '</a><a href="' . self::esc_href($widget['rightUrl'] ?? '#') . '" style="' . esc_attr($right) . '">' . self::esc($widget['rightLabel'] ?? '') . '</a></div>';

			case 'accordion':
				$css   = self::style_css($style);
				$items = '';
				foreach (($widget['items'] ?? []) as $i => $item) {
					$id = 'acc_' . $i;
					$items .= '<div style="border-bottom:1px solid #e2e8f0;"><button type="button" data-akash-visual-layout-builder-acc="' . esc_attr($id) . '" style="width:100%;display:flex;justify-content:space-between;gap:12px;padding:14px 0;background:none;border:0;font:inherit;font-weight:600;cursor:pointer;text-align:left;"><span>' . self::esc($item['title'] ?? '') . '</span><span>+</span></button><div data-akash-visual-layout-builder-acc-panel="' . esc_attr($id) . '" style="display:none;padding:0 0 14px;color:#475569;line-height:1.6;">' . self::esc($item['content'] ?? '') . '</div></div>';
				}
				return '<div class="akash-visual-layout-builder-w akash-visual-layout-builder-w-accordion" data-akash-visual-layout-builder-accordion style="' . esc_attr($css) . '">' . $items . '</div>';

			case 'gallery':
				$css = self::style_css($style);
				$cols = max(1, (int) ($widget['columns'] ?? 3));
				$gap  = $widget['gap'] ?? '12px';
				$imgs = '';
				foreach (($widget['images'] ?? []) as $image) {
					$imgs .= '<img src="' . self::esc_src($image['src'] ?? '') . '" alt="' . self::esc_attr_val($image['alt'] ?? '') . '" style="width:100%;height:180px;object-fit:cover;display:block;" loading="lazy" />';
				}
				return '<div class="akash-visual-layout-builder-w akash-visual-layout-builder-w-gallery" style="' . esc_attr($css) . '"><div style="display:grid;grid-template-columns:repeat(' . $cols . ',1fr);gap:' . esc_attr($gap) . ';">' . $imgs . '</div></div>';

			case 'price-list':
				$css  = self::style_css($style);
				$rows = '';
				foreach (($widget['items'] ?? []) as $item) {
					$rows .= '<div style="display:flex;justify-content:space-between;align-items:flex-start;gap:20px;padding:14px 0;border-bottom:1px solid rgba(15,23,42,.1);">'
						. '<div style="min-width:0;flex:1 1 auto;"><div style="font-weight:700;line-height:1.35;">' . self::esc_text($item['title'] ?? '') . '</div>'
						. '<div style="opacity:.72;font-size:13px;margin-top:4px;line-height:1.45;">' . self::esc_text($item['description'] ?? '') . '</div></div>'
						. '<div style="font-weight:800;white-space:nowrap;flex:0 0 auto;padding-left:12px;">' . self::esc_text($item['price'] ?? '') . '</div></div>';
				}
				return '<div class="akash-visual-layout-builder-w akash-visual-layout-builder-w-price-list" style="' . esc_attr($css) . '">' . $rows . '</div>';

			case 'icon-list':
				$css = self::style_css($style, [ 'color' => $widget['color'] ?? '#0f172a' ]);
				$lis = '';
				foreach (($widget['items'] ?? []) as $item) {
					$lis .= '<li style="display:flex;gap:10px;align-items:flex-start;margin:0 0 10px;"><span>' . self::esc($item['icon'] ?? '•') . '</span><span>' . self::esc($item['text'] ?? '') . '</span></li>';
				}
				return '<ul class="akash-visual-layout-builder-w akash-visual-layout-builder-w-iconlist" style="' . esc_attr($css) . 'list-style:none;margin:0;padding:0;">' . $lis . '</ul>';

			case 'star-rating':
				$css = self::style_css($style, [ 'text-align' => $widget['align'] ?? 'left' ]);
				$rating = (float) ($widget['rating'] ?? 5);
				$max    = max(1, (int) ($widget['max'] ?? 5));
				$color  = Akash_Visual_Layout_Builder_Output::sanitize_css_value($widget['color'] ?? '#f59e0b');
				$stars  = '';
				for ($i = 1; $i <= $max; $i++) {
					$fill = $i <= $rating ? $color : '#cbd5e1';
					$stars .= '<span style="color:' . esc_attr($fill) . '">★</span>';
				}
				return '<div class="akash-visual-layout-builder-w akash-visual-layout-builder-w-stars" style="' . esc_attr($css) . '"><div style="font-size:1.4rem;letter-spacing:2px;">' . $stars . '</div><div style="margin-top:6px;color:#64748b;">' . self::esc($widget['title'] ?? '') . '</div></div>';

			case 'business-hours':
				$css = self::style_css($style);
				$rows = '';
				foreach (($widget['items'] ?? []) as $item) {
					$rows .= '<div style="display:flex;justify-content:space-between;gap:16px;padding:10px 0;border-bottom:1px solid #e2e8f0;"><strong>' . self::esc($item['day'] ?? '') . '</strong><span>' . self::esc($item['hours'] ?? '') . '</span></div>';
				}
				return '<div class="akash-visual-layout-builder-w akash-visual-layout-builder-w-hours" style="' . esc_attr($css) . '">' . $rows . '</div>';

			case 'html':
				$css = self::style_css($style, [
					'text-align'  => $widget['align'] ?? 'left',
					'color'       => $widget['color'] ?? '#475569',
					'display'     => 'block',
					'white-space' => 'pre-line',
				]);
				return '<p class="akash-visual-layout-builder-w akash-visual-layout-builder-w-text" style="' . esc_attr($css) . '">' . self::esc_text(wp_strip_all_tags((string) ($widget['content'] ?? ''))) . '</p>';

			default:
				$css = self::style_css($style);
				return '<div class="akash-visual-layout-builder-w" style="' . esc_attr($css) . '">' . self::esc($widget['content'] ?? $widget['title'] ?? $widget['label'] ?? ucfirst($type)) . '</div>';
		}
	}

	/**
	 * Inject stable element id for responsive CSS selectors.
	 *
	 * @param string $html HTML fragment.
	 * @param string $id   Element id.
	 * @return string
	 */
	private static function inject_data_id($html, $id) {
		$id = (string) $id;
		if ($id === '' || $html === '') {
			return $html;
		}
		return preg_replace('/^(<\w+)/', '$1 data-akash-visual-layout-builder-id="' . esc_attr($id) . '"', $html, 1);
	}

	/**
	 * Build responsive override CSS from visual document.
	 *
	 * @param array $doc Document.
	 * @return string
	 */
	private static function collect_responsive_css($doc) {
		$rules = [];
		$append = function ($selector, $device, $props) use (&$rules) {
			if (!is_array($props) || !$props) {
				return;
			}
			$css = implode(';', array_map(function ($key, $value) {
				return $key . ':' . $value;
			}, array_keys($props), array_values($props)));
			if ($css === '') {
				return;
			}
			$max = $device === 'mobile' ? self::BREAKPOINT_MOBILE : self::BREAKPOINT_TABLET;
			$rules[] = '@media (max-width: ' . $max . 'px) { ' . $selector . ' { ' . $css . ' } }';
		};

		$visit_widget = function ($widget) use (&$append) {
			if (!is_array($widget) || empty($widget['id'])) {
				return;
			}
			$selector = '[data-akash-visual-layout-builder-id="' . esc_attr($widget['id']) . '"]';
			$overrides = $widget['styleOverrides'] ?? [];
			if (!empty($overrides['tablet']) && is_array($overrides['tablet'])) {
				$append($selector, 'tablet', self::widget_style_props($overrides['tablet']));
			}
			if (!empty($overrides['mobile']) && is_array($overrides['mobile'])) {
				$append($selector, 'mobile', self::widget_style_props($overrides['mobile']));
			}
		};

		$visit_column = function ($column) use (&$visit_widget, &$append) {
			if (!is_array($column) || empty($column['id'])) {
				return;
			}
			$selector = '[data-akash-visual-layout-builder-id="' . esc_attr($column['id']) . '"]';
			$overrides = $column['settingsOverrides'] ?? [];
			if (!empty($overrides['tablet']) && is_array($overrides['tablet'])) {
				$append($selector, 'tablet', self::column_settings_props($overrides['tablet']));
			}
			if (!empty($overrides['mobile']) && is_array($overrides['mobile'])) {
				$append($selector, 'mobile', self::column_settings_props($overrides['mobile']));
			}
			foreach (($column['children'] ?? []) as $child) {
				if (($child['type'] ?? '') === 'inner-section') {
					self::visit_section_responsive($child, $append, $visit_widget, $visit_column);
				} else {
					$visit_widget($child);
				}
			}
		};

		foreach (($doc['sections'] ?? []) as $section) {
			self::visit_section_responsive($section, $append, $visit_widget, $visit_column);
		}

		return $rules ? "\n" . implode("\n", $rules) . "\n" : '';
	}

	/**
	 * Walk a section for responsive overrides.
	 *
	 * @param array    $section      Section node.
	 * @param callable $append       Rule appender.
	 * @param callable $visit_widget Widget visitor.
	 * @param callable $visit_column Column visitor.
	 */
	private static function visit_section_responsive($section, $append, $visit_widget, $visit_column) {
		if (!is_array($section) || empty($section['id'])) {
			return;
		}
		$selector = '[data-akash-visual-layout-builder-id="' . esc_attr($section['id']) . '"]';
		$overrides = $section['settingsOverrides'] ?? [];
		if (!empty($overrides['tablet']) && is_array($overrides['tablet'])) {
			$append($selector, 'tablet', self::section_settings_props($overrides['tablet']));
		}
		if (!empty($overrides['mobile']) && is_array($overrides['mobile'])) {
			$append($selector, 'mobile', self::section_settings_props($overrides['mobile']));
		}
		foreach (($section['columns'] ?? []) as $column) {
			$visit_column($column);
		}
	}

	/**
	 * CSS properties from widget style override partial.
	 *
	 * @param array $style Style partial.
	 * @return array
	 */
	private static function widget_style_props($style) {
		$css = self::style_css($style);
		if ($css === '') {
			return [];
		}
		$props = [];
		foreach (explode(';', $css) as $part) {
			if (strpos($part, ':') === false) {
				continue;
			}
			[ $key, $value ] = explode(':', $part, 2);
			$props[ trim($key) ] = trim($value);
		}
		return $props;
	}

	/**
	 * CSS properties from section settings override partial.
	 *
	 * @param array $settings Settings partial.
	 * @return array
	 */
	private static function section_settings_props($settings) {
		$props = [];
		if (isset($settings['padding'])) {
			$props['padding'] = (string) $settings['padding'];
		}
		if (isset($settings['background'])) {
			$props['background'] = (string) $settings['background'];
		}
		if (isset($settings['textColor'])) {
			$props['color'] = (string) $settings['textColor'];
		}
		if (isset($settings['minHeight'])) {
			$min = $settings['minHeight'];
			$props['min-height'] = is_numeric($min) ? $min . 'px' : (string) $min;
		}
		if (isset($settings['contentWidth'])) {
			$width = $settings['contentWidth'];
			$props['max-width'] = is_numeric($width) ? $width . 'px' : (string) $width;
		}
		if (isset($settings['gap'])) {
			$gap = $settings['gap'];
			$props['gap'] = is_numeric($gap) ? $gap . 'px' : (string) $gap;
		}
		if (isset($settings['zIndex']) && trim((string) $settings['zIndex']) !== '') {
			$props['z-index'] = (string) $settings['zIndex'];
			$props['position'] = 'relative';
		}
		return self::sanitize_css_prop_map($props);
	}

	/**
	 * CSS properties from column settings override partial.
	 *
	 * @param array $settings Settings partial.
	 * @return array
	 */
	private static function column_settings_props($settings) {
		$props = [];
		if (isset($settings['width'])) {
			$width = (float) $settings['width'];
			$props['flex'] = '0 0 ' . $width . '%';
			$props['max-width'] = $width . '%';
		}
		if (isset($settings['padding'])) {
			$props['padding'] = (string) $settings['padding'];
		}
		if (isset($settings['background'])) {
			$props['background'] = (string) $settings['background'];
		}
		if (isset($settings['zIndex']) && trim((string) $settings['zIndex']) !== '') {
			$props['z-index'] = (string) $settings['zIndex'];
			$props['position'] = 'relative';
		}
		return self::sanitize_css_prop_map($props);
	}

	/**
	 * Sanitize a CSS property map.
	 *
	 * @param array $props Property map.
	 * @return array
	 */
	private static function sanitize_css_prop_map($props) {
		$out = [];
		foreach ($props as $key => $value) {
			$key   = preg_replace('/[^a-z0-9\-]/i', '', (string) $key);
			$value = Akash_Visual_Layout_Builder_Output::sanitize_css_value($value);
			if ($key === '' || $value === '') {
				continue;
			}
			$out[ $key ] = $value;
		}
		return $out;
	}

	/**
	 * Collect font families from document.
	 *
	 * @param array $doc Document.
	 * @return array
	 */
	private static function collect_fonts($doc) {
		$fonts = [];
		$walk  = function ($nodes) use (&$walk, &$fonts) {
			foreach ((array) $nodes as $node) {
				if (!is_array($node)) {
					continue;
				}
				if (($node['type'] ?? '') === 'section' || ($node['type'] ?? '') === 'inner-section') {
					foreach (($node['columns'] ?? []) as $col) {
						$walk($col['children'] ?? []);
					}
					continue;
				}
				$family = trim((string) ($node['style']['fontFamily'] ?? ''));
				if ($family !== '') {
					$fonts[ $family ] = true;
				}
			}
		};
		$walk($doc['sections'] ?? []);
		return array_keys($fonts);
	}

	/**
	 * Google Fonts stylesheet URL for a visual document, or empty.
	 *
	 * @param array $doc Visual document.
	 * @return string
	 */
	public static function google_fonts_url($doc) {
		$families = self::collect_fonts(is_array($doc) ? $doc : []);
		$map      = [
			'Outfit'           => 'Outfit:wght@400;500;600;700;800',
			'IBM Plex Sans'    => 'IBM+Plex+Sans:wght@400;500;600;700',
			'IBM Plex Mono'    => 'IBM+Plex+Mono:wght@400;500;600;700',
			'Fraunces'         => 'Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700',
			'Playfair Display' => 'Playfair+Display:wght@400;500;600;700',
			'Source Serif 4'   => 'Source+Serif+4:opsz,wght@8..60,400;8..60,600;8..60,700',
			'DM Sans'          => 'DM+Sans:wght@400;500;600;700',
			'Space Grotesk'    => 'Space+Grotesk:wght@400;500;600;700',
			'Sora'             => 'Sora:wght@400;500;600;700;800',
			'Manrope'          => 'Manrope:wght@400;500;600;700;800',
		];
		$specs = [];
		foreach ($families as $family) {
			$name = trim(explode(',', (string) $family)[0], " '\"");
			if (isset($map[ $name ])) {
				$specs[] = $map[ $name ];
			}
		}
		$specs = array_values(array_unique($specs));
		if (!$specs) {
			return '';
		}
		$query = implode('&', array_map(function ($s) {
			return 'family=' . $s;
		}, $specs));

		return 'https://fonts.googleapis.com/css2?' . $query . '&display=swap';
	}

	/**
	 * Google Fonts URL for one or more visual documents.
	 *
	 * @param array<int,array> $docs Visual documents.
	 * @return string
	 */
	public static function google_fonts_url_from_docs($docs) {
		$merged = [ 'sections' => [] ];
		if (!is_array($docs)) {
			return '';
		}
		foreach ($docs as $doc) {
			if (!is_array($doc) || empty($doc['sections']) || !is_array($doc['sections'])) {
				continue;
			}
			$merged['sections'] = array_merge($merged['sections'], $doc['sections']);
		}
		return self::google_fonts_url($merged);
	}

	/**
	 * Shared visual widget CSS.
	 *
	 * @return string
	 */
	private static function widget_css() {
		return ".akash-visual-layout-builder-flipbox:hover .akash-visual-layout-builder-flipbox__inner, .akash-visual-layout-builder-flipbox.is-flipped .akash-visual-layout-builder-flipbox__inner { transform: rotateY(180deg); }\n"
			. ".akash-visual-layout-builder-headline-highlight { animation: akash-visual-layout-builder-headline-pulse 2.4s ease-in-out infinite; }\n"
			. "@keyframes akash-visual-layout-builder-headline-pulse { 0%,100% { opacity: 1; } 50% { opacity: .72; } }\n";
	}
}
