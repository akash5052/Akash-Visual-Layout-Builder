<?php

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Site-wide and per-page header/footer layouts.
 */
class Akash_Visual_Layout_Builder_Layout {

	const OPTION_GLOBAL = 'akash_visual_layout_builder_global_layout';
	const META_PAGE     = '_akash_visual_layout_builder_page_layout';

	const MODES = [ 'inherit', 'append', 'replace', 'none' ];

	/**
	 * Empty layout part.
	 *
	 * @return array
	 */
	public static function default_part() {
		return [
			'html'   => '',
			'css'    => '',
			'js'     => '',
			'visual' => null,
		];
	}

	/**
	 * Default global layout.
	 *
	 * @return array
	 */
	public static function default_global() {
		return [
			'header_enabled' => false,
			'footer_enabled' => false,
			'header'         => self::default_part(),
			'footer'         => self::default_part(),
		];
	}

	/**
	 * Default per-page layout settings.
	 *
	 * @return array
	 */
	public static function default_page() {
		return [
			'header_mode' => 'inherit',
			'footer_mode' => 'inherit',
			'header'      => self::default_part(),
			'footer'      => self::default_part(),
		];
	}

	/**
	 * Normalize a layout part.
	 *
	 * @param array|null $part Raw part.
	 * @return array
	 */
	public static function normalize_part($part) {
		if (!is_array($part)) {
			return self::default_part();
		}

		$visual = null;
		if (isset($part['visual']) && is_array($part['visual'])) {
			$visual = $part['visual'];
		}

		if (is_array($visual)) {
			$compiled = Akash_Visual_Layout_Builder_Output::compile_visual($visual);
			return [
				'html'   => $compiled['html'],
				'css'    => $compiled['css'],
				'js'     => '',
				'visual' => $visual,
			];
		}

		return self::default_part();
	}

	/**
	 * Visual documents used by a page's header/footer (for Google Fonts).
	 *
	 * @param int $post_id Post ID.
	 * @return array<int,array>
	 */
	public static function visual_docs_for_page($post_id) {
		$global = self::get_global();
		$page   = self::get_page($post_id);
		$docs   = [];

		$header_global = $global['header_enabled'] && in_array($page['header_mode'], [ 'inherit', 'append' ], true);
		$footer_global = $global['footer_enabled'] && in_array($page['footer_mode'], [ 'inherit', 'append' ], true);
		$header_page   = in_array($page['header_mode'], [ 'replace', 'append' ], true);
		$footer_page   = in_array($page['footer_mode'], [ 'replace', 'append' ], true);

		if ($header_global && !empty($global['header']['visual']) && is_array($global['header']['visual'])) {
			$docs[] = $global['header']['visual'];
		}
		if ($footer_global && !empty($global['footer']['visual']) && is_array($global['footer']['visual'])) {
			$docs[] = $global['footer']['visual'];
		}
		if ($header_page && !empty($page['header']['visual']) && is_array($page['header']['visual'])) {
			$docs[] = $page['header']['visual'];
		}
		if ($footer_page && !empty($page['footer']['visual']) && is_array($page['footer']['visual'])) {
			$docs[] = $page['footer']['visual'];
		}

		return $docs;
	}

	/**
	 * Normalize global layout from storage.
	 *
	 * @param array|null $layout Raw layout.
	 * @return array
	 */
	public static function normalize_global($layout) {
		$defaults = self::default_global();

		if (!is_array($layout)) {
			return $defaults;
		}

		return [
			'header_enabled' => !empty($layout['header_enabled']),
			'footer_enabled' => !empty($layout['footer_enabled']),
			'header'         => self::normalize_part($layout['header'] ?? []),
			'footer'         => self::normalize_part($layout['footer'] ?? []),
		];
	}

	/**
	 * Normalize page layout from storage.
	 *
	 * @param array|null $layout Raw layout.
	 * @return array
	 */
	public static function normalize_page($layout) {
		$defaults = self::default_page();

		if (!is_array($layout)) {
			return $defaults;
		}

		$header_mode = isset($layout['header_mode']) ? sanitize_key($layout['header_mode']) : 'inherit';
		$footer_mode = isset($layout['footer_mode']) ? sanitize_key($layout['footer_mode']) : 'inherit';

		if (!in_array($header_mode, self::MODES, true)) {
			$header_mode = 'inherit';
		}

		if (!in_array($footer_mode, self::MODES, true)) {
			$footer_mode = 'inherit';
		}

		return [
			'header_mode' => $header_mode,
			'footer_mode' => $footer_mode,
			'header'      => self::normalize_part($layout['header'] ?? []),
			'footer'      => self::normalize_part($layout['footer'] ?? []),
		];
	}

	/**
	 * Get site-wide layout.
	 *
	 * @return array
	 */
	public static function get_global() {
		return self::normalize_global(get_option(self::OPTION_GLOBAL, []));
	}

	/**
	 * Save site-wide layout.
	 *
	 * @param array $layout Layout data.
	 */
	public static function save_global($layout) {
		update_option(self::OPTION_GLOBAL, self::normalize_global($layout), false);
	}

	/**
	 * Get per-page layout.
	 *
	 * @param int $post_id Post ID.
	 * @return array
	 */
	public static function get_page($post_id) {
		$stored = get_post_meta($post_id, self::META_PAGE, true);
		return self::normalize_page(is_array($stored) ? $stored : []);
	}

	/**
	 * Save per-page layout.
	 *
	 * @param int   $post_id Post ID.
	 * @param array $layout  Layout data.
	 */
	public static function save_page($post_id, $layout) {
		update_post_meta($post_id, self::META_PAGE, self::normalize_page($layout));
	}

	/**
	 * Whether a part has any content.
	 *
	 * @param array $part Layout part.
	 * @return bool
	 */
	public static function part_has_content($part) {
		$part = self::normalize_part($part);
		return trim($part['html']) !== '' || trim($part['css']) !== '' || trim($part['js']) !== '';
	}

	/**
	 * Resolve header/footer for a page.
	 *
	 * @param int $post_id Post ID.
	 * @return array
	 */
	public static function resolve($post_id) {
		$global = self::get_global();
		$page   = self::get_page($post_id);

		$global_header = ($global['header_enabled'] && self::part_has_content($global['header']))
			? self::wrap_region('header', 'global', $global['header']['html'])
			: '';

		$global_footer = ($global['footer_enabled'] && self::part_has_content($global['footer']))
			? self::wrap_region('footer', 'global', $global['footer']['html'])
			: '';

		$page_header = self::part_has_content($page['header'])
			? self::wrap_region('header', 'page', $page['header']['html'])
			: '';

		$page_footer = self::part_has_content($page['footer'])
			? self::wrap_region('footer', 'page', $page['footer']['html'])
			: '';

		$header_html = self::combine_region_html($page['header_mode'], $global_header, $page_header);
		$footer_html = self::combine_region_html($page['footer_mode'], $global_footer, $page_footer);

		$header_css = self::combine_region_assets($page['header_mode'], $global['header_enabled'] ? $global['header'] : self::default_part(), $page['header']);
		$footer_css = self::combine_region_assets($page['footer_mode'], $global['footer_enabled'] ? $global['footer'] : self::default_part(), $page['footer']);

		$header_js = self::combine_region_assets($page['header_mode'], $global['header_enabled'] ? $global['header'] : self::default_part(), $page['header'], 'js');
		$footer_js = self::combine_region_assets($page['footer_mode'], $global['footer_enabled'] ? $global['footer'] : self::default_part(), $page['footer'], 'js');

		return [
			'header_html' => $header_html,
			'footer_html' => $footer_html,
			'header_css'  => $header_css,
			'footer_css'  => $footer_css,
			'header_js'   => $header_js,
			'footer_js'   => $footer_js,
		];
	}

	/**
	 * Combine global and page HTML for a region.
	 *
	 * @param string $mode    Layout mode.
	 * @param string $global  Global HTML.
	 * @param string $page    Page HTML.
	 * @return string
	 */
	private static function combine_region_html($mode, $global, $page) {
		switch ($mode) {
			case 'none':
				return '';
			case 'replace':
				return $page;
			case 'append':
				return $global . $page;
			case 'inherit':
			default:
				return $global;
		}
	}

	/**
	 * Combine CSS or JS assets for a region.
	 *
	 * @param string $mode   Layout mode.
	 * @param array  $global Global part.
	 * @param array  $page   Page part.
	 * @param string $field  css|js.
	 * @return string
	 */
	private static function combine_region_assets($mode, $global, $page, $field = 'css') {
		$global = self::normalize_part($global);
		$page   = self::normalize_part($page);
		$g      = trim($global[ $field ]);
		$p      = trim($page[ $field ]);

		switch ($mode) {
			case 'none':
				return '';
			case 'replace':
				return $p;
			case 'append':
				return self::join_assets($g, $p);
			case 'inherit':
			default:
				return $g;
		}
	}

	/**
	 * Join CSS/JS strings.
	 *
	 * @param string $a First value.
	 * @param string $b Second value.
	 * @return string
	 */
	private static function join_assets($a, $b) {
		if ($a === '') {
			return $b;
		}
		if ($b === '') {
			return $a;
		}
		return $a . "\n\n" . $b;
	}

	/**
	 * Wrap region HTML with identifiable container.
	 *
	 * @param string $region header|footer.
	 * @param string $scope  global|page.
	 * @param string $html   Inner HTML.
	 * @return string
	 */
	private static function wrap_region($region, $scope, $html) {
		$html = trim($html);
		if ($html === '') {
			return '';
		}

		return '<div class="akash-visual-layout-builder-region akash-visual-layout-builder-region--' . esc_attr($region) . ' akash-visual-layout-builder-region--' . esc_attr($scope) . '" data-akash-visual-layout-builder-region="' . esc_attr($region) . '">' . Akash_Visual_Layout_Builder_Output::kses_html($html) . '</div>';
	}

	/**
	 * Public format for API responses.
	 *
	 * @param array $layout Global layout.
	 * @return array
	 */
	public static function format_global($layout) {
		$layout = self::normalize_global($layout);
		return $layout;
	}

	/**
	 * Public format for API responses.
	 *
	 * @param array $layout Page layout.
	 * @return array
	 */
	public static function format_page($layout) {
		return self::normalize_page($layout);
	}
}
