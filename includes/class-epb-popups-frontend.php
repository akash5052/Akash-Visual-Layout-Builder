<?php

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Front-end popup rendering and scripts.
 */
class EPB_Popups_Frontend {

	public function __construct() {
		add_action('wp_enqueue_scripts', [ $this, 'enqueue_assets' ]);
		add_action('wp_footer', [ $this, 'output_popups' ], 5 );
	}

	/**
	 * Enqueue popup runtime and generated visual CSS.
	 */
	public function enqueue_assets() {
		$raw = EPB_Settings::get_raw();
		if (empty($raw['popups_enabled'])) {
			return;
		}

		$popups = EPB_Popups::get_for_current_page();
		if (empty($popups)) {
			return;
		}

		$css_file = EPB_PLUGIN_DIR . 'assets/css/popups.css';
		$js_file  = EPB_PLUGIN_DIR . 'assets/js/popups.js';

		wp_enqueue_style(
			'wpvisualx-popups',
			EPB_PLUGIN_URL . 'assets/css/popups.css',
			[],
			file_exists($css_file) ? filemtime($css_file) : EPB_VERSION
		);

		$css = '';
		$font_docs = [];
		foreach ($popups as $popup) {
			if (!empty($popup['visual']) && is_array($popup['visual'])) {
				$compiled    = EPB_Output::compile_visual($popup['visual']);
				$css        .= "\n" . $compiled['css'];
				$font_docs[] = $popup['visual'];
			}
		}

		$css = EPB_Output::sanitize_css($css);
		if ($css !== '') {
			wp_add_inline_style('wpvisualx-popups', $css);
		}

		$font_url = EPB_Visual_Compile::google_fonts_url_from_docs($font_docs);
		if ($font_url !== '' && EPB_Settings::google_fonts_enabled()) {
			wp_enqueue_style('wpvisualx-popups-google-fonts', esc_url_raw($font_url), [], EPB_VERSION);
		}

		if (file_exists($js_file)) {
			wp_enqueue_script(
				'wpvisualx-popups',
				EPB_PLUGIN_URL . 'assets/js/popups.js',
				[],
				filemtime($js_file),
				true
			);

			$config = array_map([ 'EPB_Popups', 'client_config' ], $popups);
			wp_localize_script('wpvisualx-popups', 'epbBuilderPopups', $config);
		}
	}

	/**
	 * Output popup HTML in footer.
	 */
	public function output_popups() {
		$raw = EPB_Settings::get_raw();
		if (empty($raw['popups_enabled'])) {
			return;
		}

		$popups = EPB_Popups::get_for_current_page();
		if (empty($popups)) {
			return;
		}

		foreach ($popups as $popup) {
			echo wp_kses(EPB_Popups::render_markup($popup), EPB_Output::allowed_html());
			echo "\n";
		}
	}
}
