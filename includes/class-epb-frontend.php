<?php

if (!defined('ABSPATH')) {
	exit;
}

class EPB_Frontend {

	public function __construct() {
		add_filter('body_class', [$this, 'add_body_class']);
		add_filter('template_include', [$this, 'maybe_canvas_template'], 99);
		add_filter('the_content', [$this, 'render_page_content'], 999);
		add_filter('render_block', [$this, 'render_block_content'], 10, 2);
		add_action('wp_enqueue_scripts', [$this, 'enqueue_frontend_assets']);
	}

	/**
	 * Check if a post uses EPB.
	 *
	 * @param int|null $post_id Post ID.
	 * @return int|false
	 */
	private function get_epb_page_id($post_id = null) {
		if ($post_id === null) {
			if (!is_singular(EPB_Post_Types::types())) {
				return false;
			}
			$post_id = get_queried_object_id();
		}

		if (!$post_id || !EPB_Post_Types::is_supported_type(get_post_type($post_id))) {
			return false;
		}

		if (get_post_meta($post_id, EPB_Renderer::META_ENABLED, true) !== '1') {
			return false;
		}

		return (int) $post_id;
	}

	/**
	 * Add body classes for EPB pages.
	 *
	 * @param array $classes Body classes.
	 * @return array
	 */
	public function add_body_class($classes) {
		$post_id = $this->get_epb_page_id();

		if (!$post_id) {
			return $classes;
		}

		$template = EPB_Post_Options::get_page_template($post_id);

		$classes[] = 'epb-page';
		$classes[] = 'epb-page-template-' . sanitize_html_class(str_replace('_', '-', $template));

		if ($template === 'epb-full-width' || $template === 'default') {
			$classes[] = 'epb-full-width-page';
		}

		if (EPB_Post_Options::get_hide_title($post_id)) {
			$classes[] = 'epb-hide-title';
		}

		return $classes;
	}

	/**
	 * Use the canvas template when EPB Canvas layout is selected.
	 *
	 * @param string $template Current template path.
	 * @return string
	 */
	public function maybe_canvas_template($template) {
		$post_id = $this->get_epb_page_id();

		if (!$post_id) {
			return $template;
		}

		if (EPB_Post_Options::get_page_template($post_id) !== 'epb-canvas') {
			return $template;
		}

		$canvas = EPB_PLUGIN_DIR . 'templates/canvas.php';

		return file_exists($canvas) ? $canvas : $template;
	}

	/**
	 * Enqueue frontend CSS/JS and generated visual styles.
	 */
	public function enqueue_frontend_assets() {
		$post_id = $this->get_epb_page_id();

		if (!$post_id) {
			return;
		}

		$css_file = EPB_PLUGIN_DIR . 'assets/css/frontend.css';
		$js_file  = EPB_PLUGIN_DIR . 'assets/js/frontend.js';

		wp_enqueue_style(
			'wpvisualx-frontend',
			EPB_PLUGIN_URL . 'assets/css/frontend.css',
			[],
			file_exists($css_file) ? filemtime($css_file) : EPB_VERSION
		);

		$code   = EPB_Renderer::get_page_code($post_id);
		$layout = EPB_Layout::resolve($post_id);
		$css    = EPB_Output::sanitize_css(
			trim($layout['header_css'] . "\n\n" . EPB_Renderer::render_css($code) . "\n\n" . $layout['footer_css'])
		);

		if ($css !== '') {
			wp_add_inline_style('wpvisualx-frontend', $css);
		}

		$font_docs = EPB_Layout::visual_docs_for_page($post_id);
		$page_visual = EPB_Renderer::get_visual_document($post_id);
		if (is_array($page_visual)) {
			array_unshift($font_docs, $page_visual);
		}

		$font_url = EPB_Visual_Compile::google_fonts_url_from_docs($font_docs);
		if ($font_url !== '' && EPB_Settings::google_fonts_enabled()) {
			wp_enqueue_style('wpvisualx-google-fonts', esc_url_raw($font_url), [], EPB_VERSION);
		}

		if (file_exists($js_file)) {
			wp_enqueue_script(
				'wpvisualx-frontend',
				EPB_PLUGIN_URL . 'assets/js/frontend.js',
				[],
				filemtime($js_file),
				true
			);
		}
	}

	/**
	 * Get rendered EPB HTML for a page.
	 *
	 * @param int $post_id Post ID.
	 * @return string
	 */
	private function get_rendered_html($post_id) {
		$code   = EPB_Renderer::get_page_code($post_id);
		$layout = EPB_Layout::resolve($post_id);

		if (empty(trim($code['html'])) && $layout['header_html'] === '' && $layout['footer_html'] === '') {
			return '';
		}

		$body = EPB_Renderer::render_html($code, $post_id);

		return EPB_Output::kses_html($layout['header_html'] . $body . $layout['footer_html']);
	}

	/**
	 * Replace classic theme content.
	 *
	 * @param string $content Post content.
	 * @return string
	 */
	public function render_page_content($content) {
		if (!is_singular(EPB_Post_Types::types()) || !in_the_loop() || !is_main_query()) {
			return $content;
		}

		$post_id = $this->get_epb_page_id(get_the_ID());

		if (!$post_id) {
			return $content;
		}

		$rendered = $this->get_rendered_html($post_id);

		return $rendered ? $rendered : $content;
	}

	/**
	 * Replace block theme post-content block output.
	 *
	 * @param string $block_content Block HTML.
	 * @param array  $block         Block data.
	 * @return string
	 */
	public function render_block_content($block_content, $block) {
		if (!isset($block['blockName']) || $block['blockName'] !== 'core/post-content') {
			return $block_content;
		}

		if (!is_singular(EPB_Post_Types::types())) {
			return $block_content;
		}

		$post_id = $this->get_epb_page_id(get_the_ID());

		if (!$post_id) {
			return $block_content;
		}

		$rendered = $this->get_rendered_html($post_id);

		return $rendered ? $rendered : $block_content;
	}
}
