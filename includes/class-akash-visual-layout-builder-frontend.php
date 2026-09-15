<?php

if (!defined('ABSPATH')) {
	exit;
}

class Akash_Visual_Layout_Builder_Frontend {

	public function __construct() {
		add_filter('body_class', [$this, 'add_body_class']);
		add_filter('template_include', [$this, 'maybe_canvas_template'], 99);
		add_filter('the_content', [$this, 'render_page_content'], 999);
		add_filter('render_block', [$this, 'render_block_content'], 10, 2);
		add_action('wp_enqueue_scripts', [$this, 'enqueue_frontend_assets']);
	}

	/**
	 * Check if a post uses Akash Visual Layout Builder.
	 *
	 * @param int|null $post_id Post ID.
	 * @return int|false
	 */
	private function get_akash_visual_layout_builder_page_id($post_id = null) {
		if ($post_id === null) {
			if (!is_singular(Akash_Visual_Layout_Builder_Post_Types::types())) {
				return false;
			}
			$post_id = get_queried_object_id();
		}

		if (!$post_id || !Akash_Visual_Layout_Builder_Post_Types::is_supported_type(get_post_type($post_id))) {
			return false;
		}

		if (get_post_meta($post_id, Akash_Visual_Layout_Builder_Renderer::META_ENABLED, true) !== '1') {
			return false;
		}

		return (int) $post_id;
	}

	/**
	 * Add body classes for Akash Visual Layout Builder pages.
	 *
	 * @param array $classes Body classes.
	 * @return array
	 */
	public function add_body_class($classes) {
		$post_id = $this->get_akash_visual_layout_builder_page_id();

		if (!$post_id) {
			return $classes;
		}

		$template = Akash_Visual_Layout_Builder_Post_Options::get_page_template($post_id);

		$classes[] = 'akash-visual-layout-builder-page';
		$classes[] = 'akash-visual-layout-builder-page-template-' . sanitize_html_class(str_replace('_', '-', $template));

		if ($template === 'akash-visual-layout-builder-full-width' || $template === 'default') {
			$classes[] = 'akash-visual-layout-builder-full-width-page';
		}

		if (Akash_Visual_Layout_Builder_Post_Options::get_hide_title($post_id)) {
			$classes[] = 'akash-visual-layout-builder-hide-title';
		}

		return $classes;
	}

	/**
	 * Use the canvas template when Akash Visual Layout Builder Canvas layout is selected.
	 *
	 * @param string $template Current template path.
	 * @return string
	 */
	public function maybe_canvas_template($template) {
		$post_id = $this->get_akash_visual_layout_builder_page_id();

		if (!$post_id) {
			return $template;
		}

		if (Akash_Visual_Layout_Builder_Post_Options::get_page_template($post_id) !== 'akash-visual-layout-builder-canvas') {
			return $template;
		}

		$canvas = AKASH_VISUAL_LAYOUT_BUILDER_PLUGIN_DIR . 'templates/canvas.php';

		return file_exists($canvas) ? $canvas : $template;
	}

	/**
	 * Enqueue frontend CSS/JS and generated visual styles.
	 */
	public function enqueue_frontend_assets() {
		$post_id = $this->get_akash_visual_layout_builder_page_id();

		if (!$post_id) {
			return;
		}

		$css_file = AKASH_VISUAL_LAYOUT_BUILDER_PLUGIN_DIR . 'assets/css/frontend.css';
		$js_file  = AKASH_VISUAL_LAYOUT_BUILDER_PLUGIN_DIR . 'assets/js/frontend.js';

		wp_enqueue_style(
			'akash-visual-layout-builder-frontend',
			AKASH_VISUAL_LAYOUT_BUILDER_PLUGIN_URL . 'assets/css/frontend.css',
			[],
			file_exists($css_file) ? filemtime($css_file) : AKASH_VISUAL_LAYOUT_BUILDER_VERSION
		);

		$code   = Akash_Visual_Layout_Builder_Renderer::get_page_code($post_id);
		$layout = Akash_Visual_Layout_Builder_Layout::resolve($post_id);
		$css    = Akash_Visual_Layout_Builder_Output::sanitize_css(
			trim($layout['header_css'] . "\n\n" . Akash_Visual_Layout_Builder_Renderer::render_css($code) . "\n\n" . $layout['footer_css'])
		);

		if ($css !== '') {
			wp_add_inline_style('akash-visual-layout-builder-frontend', $css);
		}

		$font_docs = Akash_Visual_Layout_Builder_Layout::visual_docs_for_page($post_id);
		$page_visual = Akash_Visual_Layout_Builder_Renderer::get_visual_document($post_id);
		if (is_array($page_visual)) {
			array_unshift($font_docs, $page_visual);
		}

		$font_url = Akash_Visual_Layout_Builder_Visual_Compile::google_fonts_url_from_docs($font_docs);
		if ($font_url !== '' && Akash_Visual_Layout_Builder_Settings::google_fonts_enabled()) {
			wp_enqueue_style('akash-visual-layout-builder-google-fonts', esc_url_raw($font_url), [], AKASH_VISUAL_LAYOUT_BUILDER_VERSION);
		}

		if (file_exists($js_file)) {
			wp_enqueue_script(
				'akash-visual-layout-builder-frontend',
				AKASH_VISUAL_LAYOUT_BUILDER_PLUGIN_URL . 'assets/js/frontend.js',
				[],
				filemtime($js_file),
				true
			);
		}
	}

	/**
	 * Get rendered Akash Visual Layout Builder HTML for a page.
	 *
	 * @param int $post_id Post ID.
	 * @return string
	 */
	private function get_rendered_html($post_id) {
		$code   = Akash_Visual_Layout_Builder_Renderer::get_page_code($post_id);
		$layout = Akash_Visual_Layout_Builder_Layout::resolve($post_id);

		if (empty(trim($code['html'])) && $layout['header_html'] === '' && $layout['footer_html'] === '') {
			return '';
		}

		$body = Akash_Visual_Layout_Builder_Renderer::render_html($code, $post_id);

		return Akash_Visual_Layout_Builder_Output::kses_html($layout['header_html'] . $body . $layout['footer_html']);
	}

	/**
	 * Replace classic theme content.
	 *
	 * @param string $content Post content.
	 * @return string
	 */
	public function render_page_content($content) {
		if (!is_singular(Akash_Visual_Layout_Builder_Post_Types::types()) || !in_the_loop() || !is_main_query()) {
			return $content;
		}

		$post_id = $this->get_akash_visual_layout_builder_page_id(get_the_ID());

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

		if (!is_singular(Akash_Visual_Layout_Builder_Post_Types::types())) {
			return $block_content;
		}

		$post_id = $this->get_akash_visual_layout_builder_page_id(get_the_ID());

		if (!$post_id) {
			return $block_content;
		}

		$rendered = $this->get_rendered_html($post_id);

		return $rendered ? $rendered : $block_content;
	}
}
