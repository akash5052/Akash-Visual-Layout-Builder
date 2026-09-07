<?php

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Front-end popup rendering and scripts.
 */
class Av_Web_Studio_Popups_Frontend {

	public function __construct() {
		add_action('wp_enqueue_scripts', [ $this, 'enqueue_assets' ]);
		add_action('wp_footer', [ $this, 'output_popups' ], 5 );
	}

	/**
	 * Enqueue popup runtime and generated visual CSS.
	 */
	public function enqueue_assets() {
		$raw = Av_Web_Studio_Settings::get_raw();
		if (empty($raw['popups_enabled'])) {
			return;
		}

		$popups = Av_Web_Studio_Popups::get_for_current_page();
		if (empty($popups)) {
			return;
		}

		$css_file = AV_WEB_STUDIO_PLUGIN_DIR . 'assets/css/popups.css';
		$js_file  = AV_WEB_STUDIO_PLUGIN_DIR . 'assets/js/popups.js';

		wp_enqueue_style(
			'av-web-studio-popups',
			AV_WEB_STUDIO_PLUGIN_URL . 'assets/css/popups.css',
			[],
			file_exists($css_file) ? filemtime($css_file) : AV_WEB_STUDIO_VERSION
		);

		$css = '';
		$font_docs = [];
		foreach ($popups as $popup) {
			if (!empty($popup['visual']) && is_array($popup['visual'])) {
				$compiled    = Av_Web_Studio_Output::compile_visual($popup['visual']);
				$css        .= "\n" . $compiled['css'];
				$font_docs[] = $popup['visual'];
			}
		}

		$css = Av_Web_Studio_Output::sanitize_css($css);
		if ($css !== '') {
			wp_add_inline_style('av-web-studio-popups', $css);
		}

		$font_url = Av_Web_Studio_Visual_Compile::google_fonts_url_from_docs($font_docs);
		if ($font_url !== '' && Av_Web_Studio_Settings::google_fonts_enabled()) {
			wp_enqueue_style('av-web-studio-popups-google-fonts', esc_url_raw($font_url), [], AV_WEB_STUDIO_VERSION);
		}

		if (file_exists($js_file)) {
			wp_enqueue_script(
				'av-web-studio-popups',
				AV_WEB_STUDIO_PLUGIN_URL . 'assets/js/popups.js',
				[],
				filemtime($js_file),
				true
			);

			$config = array_map([ 'Av_Web_Studio_Popups', 'client_config' ], $popups);
			wp_localize_script('av-web-studio-popups', 'avWebStudioBuilderPopups', $config);
		}
	}

	/**
	 * Output popup HTML in footer.
	 */
	public function output_popups() {
		$raw = Av_Web_Studio_Settings::get_raw();
		if (empty($raw['popups_enabled'])) {
			return;
		}

		$popups = Av_Web_Studio_Popups::get_for_current_page();
		if (empty($popups)) {
			return;
		}

		foreach ($popups as $popup) {
			echo wp_kses(Av_Web_Studio_Popups::render_markup($popup), Av_Web_Studio_Output::allowed_html());
			echo "\n";
		}
	}
}
