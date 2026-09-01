<?php

if (!defined('ABSPATH')) {
	exit;
}

class EPB_Admin {

	const MENU_SLUG = 'wpvisualx';

	/**
	 * Admin screen slugs mapped to builder views.
	 *
	 * @var array<string, string>
	 */
	private $screens = [
		'wpvisualx' => 'dashboard',
		'wpvisualx-pages'         => 'pages',
		'wpvisualx-posts'         => 'posts',
		'wpvisualx-templates'     => 'templates',
		'wpvisualx-site-layout'   => 'site-layout',
		'wpvisualx-popups'        => 'popups',
		'wpvisualx-svg'           => 'svg',
		'wpvisualx-tracking'      => 'tracking',
		'wpvisualx-settings'      => 'settings',
	];

	public function __construct() {
		add_action('admin_menu', [$this, 'register_menu']);
		add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
		add_filter('page_row_actions', [$this, 'add_row_action'], 10, 2);
		add_filter('post_row_actions', [$this, 'add_row_action'], 10, 2);
		add_action('add_meta_boxes', [$this, 'register_builder_meta_box']);
		add_action('admin_action_epb_edit_with_builder', [$this, 'handle_edit_with_builder']);
		add_filter('admin_body_class', [$this, 'admin_body_class']);
	}

	/**
	 * Register admin menus.
	 */
	public function register_menu() {
		add_menu_page(
			__('WPVisualX', 'wpvisualx'),
			__('WPVisualX', 'wpvisualx'),
			'edit_posts',
			self::MENU_SLUG,
			[ $this, 'render_builder_page' ],
			$this->get_menu_icon(),
			30
		);

		add_submenu_page(
			self::MENU_SLUG,
			__('Dashboard', 'wpvisualx'),
			__('Dashboard', 'wpvisualx'),
			'edit_posts',
			self::MENU_SLUG,
			[ $this, 'render_builder_page' ]
		);

		add_submenu_page(
			self::MENU_SLUG,
			__('Pages', 'wpvisualx'),
			__('Pages', 'wpvisualx'),
			'edit_posts',
			'wpvisualx-pages',
			[ $this, 'render_builder_page' ]
		);

		add_submenu_page(
			self::MENU_SLUG,
			__('Posts', 'wpvisualx'),
			__('Posts', 'wpvisualx'),
			'edit_posts',
			'wpvisualx-posts',
			[ $this, 'render_builder_page' ]
		);

		add_submenu_page(
			self::MENU_SLUG,
			__('Templates', 'wpvisualx'),
			__('Templates', 'wpvisualx'),
			'edit_posts',
			'wpvisualx-templates',
			[ $this, 'render_builder_page' ]
		);

		add_submenu_page(
			self::MENU_SLUG,
			__('Site Layout', 'wpvisualx'),
			__('Site Layout', 'wpvisualx'),
			'edit_posts',
			'wpvisualx-site-layout',
			[ $this, 'render_builder_page' ]
		);

		add_submenu_page(
			self::MENU_SLUG,
			__('Popups', 'wpvisualx'),
			__('Popups', 'wpvisualx'),
			'edit_posts',
			'wpvisualx-popups',
			[ $this, 'render_builder_page' ]
		);

		add_submenu_page(
			self::MENU_SLUG,
			__('SVG Library', 'wpvisualx'),
			__('SVG Library', 'wpvisualx'),
			'edit_posts',
			'wpvisualx-svg',
			[ $this, 'render_builder_page' ]
		);

		add_submenu_page(
			self::MENU_SLUG,
			__('Tracking', 'wpvisualx'),
			__('Tracking', 'wpvisualx'),
			'edit_posts',
			'wpvisualx-tracking',
			[ $this, 'render_builder_page' ]
		);

		add_submenu_page(
			self::MENU_SLUG,
			__('Settings', 'wpvisualx'),
			__('Settings', 'wpvisualx'),
			'manage_options',
			'wpvisualx-settings',
			[ $this, 'render_builder_page' ]
		);
	}

	/**
	 * Inline SVG icon sized for the WordPress admin menu (20×20).
	 *
	 * @return string
	 */
	private function get_menu_icon() {
		$icon_file = EPB_PLUGIN_DIR . 'assets/images/icon.svg';

		if (!file_exists($icon_file)) {
			return 'dashicons-layout';
		}

		$svg = file_get_contents($icon_file); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents

		if ($svg === false || $svg === '') {
			return 'dashicons-layout';
		}

		$svg = preg_replace('/\s+/', ' ', trim($svg));

		return 'data:image/svg+xml;base64,' . base64_encode($svg);
	}

	/**
	 * Read the admin screen slug from the query string.
	 *
	 * @return string
	 */
	private function get_admin_screen_slug() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Admin menu routing only; no state change.
		if (!isset($_GET['page'])) {
			return '';
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return sanitize_key(wp_unslash($_GET['page']));
	}

	/**
	 * Read the optional page_id deep-link query arg.
	 *
	 * @return int
	 */
	private function get_admin_page_id_arg() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Deep-link to open editor; capability checked separately.
		if (empty($_GET['page_id'])) {
			return 0;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return absint(wp_unslash($_GET['page_id']));
	}

	/**
	 * Resolve a valid content ID from the page_id query arg for pages/posts screens.
	 *
	 * @param string $screen Admin screen slug.
	 * @return int
	 */
	private function get_valid_initial_page_id($screen) {
		if (!in_array($screen, [ 'wpvisualx-pages', 'wpvisualx-posts' ], true)) {
			return 0;
		}

		if ($this->get_admin_page_id_arg() === 0) {
			return 0;
		}

		$id = $this->get_admin_page_id_arg();
		if ($id < 1) {
			return 0;
		}

		$post = get_post($id);
		if (!$post || !EPB_Post_Types::is_supported($post) || !EPB_Post_Types::user_can_edit($id)) {
			return 0;
		}

		$expected_type = $screen === 'wpvisualx-posts' ? 'post' : 'page';
		if ($post->post_type !== $expected_type) {
			return 0;
		}

		return $id;
	}

	/**
	 * Redirect when page_id points at the wrong content type for this screen.
	 *
	 * @param string $screen Admin screen slug.
	 * @param int    $id     Requested content ID.
	 * @return bool True when a redirect was sent.
	 */
	private function maybe_redirect_mismatched_content($screen, $id) {
		if ($id < 1 || !in_array($screen, [ 'wpvisualx-pages', 'wpvisualx-posts' ], true)) {
			return false;
		}

		$post = get_post($id);
		if (!$post || !EPB_Post_Types::is_supported($post) || !EPB_Post_Types::user_can_edit($id)) {
			return false;
		}

		if ($screen === 'wpvisualx-pages' && $post->post_type === 'post') {
			wp_safe_redirect(admin_url('admin.php?page=wpvisualx-posts&page_id=' . $id));
			exit;
		}

		if ($screen === 'wpvisualx-posts' && $post->post_type === 'page') {
			wp_safe_redirect(admin_url('admin.php?page=wpvisualx-pages&page_id=' . $id));
			exit;
		}

		return false;
	}

	/**
	 * Render builder mount point.
	 */
	public function render_builder_page() {
		$screen  = $this->get_admin_screen_slug();
		$raw_id  = $this->get_admin_page_id_arg();
		$page_id = $this->get_valid_initial_page_id($screen);

		if ($raw_id > 0 && $page_id === 0 && in_array($screen, [ 'wpvisualx-pages', 'wpvisualx-posts' ], true)) {
			$this->maybe_redirect_mismatched_content($screen, $raw_id);
			wp_safe_redirect(admin_url('admin.php?page=' . $screen));
			exit;
		}
		?>
		<div class="wrap epb-admin-wrap">
			<div id="epb-root" data-page-id="<?php echo esc_attr($page_id); ?>"></div>
		</div>
		<?php
	}

	/**
	 * Enqueue builder assets on admin page.
	 *
	 * @param string $hook Current admin page hook.
	 */
	public function enqueue_assets($hook) {
		unset($hook);

		$admin_css = EPB_PLUGIN_DIR . 'assets/css/admin.css';
		if (file_exists($admin_css)) {
			wp_enqueue_style(
				'wpvisualx-admin',
				EPB_PLUGIN_URL . 'assets/css/admin.css',
				[],
				filemtime($admin_css)
			);
		}

		$screen = $this->get_admin_screen_slug();
		if (!isset($this->screens[ $screen ])) {
			return;
		}

		$css_file = EPB_PLUGIN_DIR . 'assets/build/index.css';
		$js_file  = EPB_PLUGIN_DIR . 'assets/build/index.js';

		if (file_exists($css_file)) {
			wp_enqueue_style(
				'wpvisualx',
				EPB_PLUGIN_URL . 'assets/build/index.css',
				[],
				filemtime($css_file)
			);
		}

		wp_enqueue_media();

		wp_enqueue_script(
			'wpvisualx',
			EPB_PLUGIN_URL . 'assets/build/index.js',
			[ 'jquery', 'media-upload', 'media-views' ],
			file_exists($js_file) ? filemtime($js_file) : EPB_VERSION,
			true
		);

		$initial_page_id = $this->get_valid_initial_page_id($screen);
		$initial_view    = $this->screens[ $screen ];

		$raw = EPB_Settings::get_raw();

		/**
		 * Filter localized builder bootstrap data.
		 *
		 * @param array $data Localized script data.
		 */
		$epb_data = apply_filters(
			'wpvisualx_data',
			[
				'restUrl'           => esc_url_raw(rest_url(EPB_REST::NAMESPACE . '/')),
				'nonce'             => wp_create_nonce('wp_rest'),
				'ajaxUrl'           => esc_url_raw(admin_url('admin-ajax.php')),
				'aiNonce'           => wp_create_nonce(EPB_AI_Ajax::NONCE_ACTION),
				'canUseAi'          => EPB_Settings::current_user_can_see_ai_panel(),
				'aiReady'           => EPB_Settings::current_user_can_run_ai(),
				'canManageSettings' => EPB_Settings::current_user_can_manage(),
				'adminUrl'          => admin_url(),
				'pluginUrl'         => EPB_PLUGIN_URL,
				'buildUrl'          => esc_url_raw(EPB_PLUGIN_URL . 'assets/build/'),
				'siteUrl'           => get_site_url(),
				'initialPageId'     => $initial_page_id,
				'initialView'       => $initial_view,
				'defaultListStatus' => $raw['default_list_status'] ?? 'publish',
				'pluginSettings'    => EPB_Settings::public_settings(),
				'adminUrls'         => [
					'dashboard'  => admin_url('admin.php?page=' . self::MENU_SLUG),
					'pages'      => admin_url('admin.php?page=wpvisualx-pages'),
					'posts'      => admin_url('admin.php?page=wpvisualx-posts'),
					'templates'  => admin_url('admin.php?page=wpvisualx-templates'),
					'siteLayout' => admin_url('admin.php?page=wpvisualx-site-layout'),
					'popups'     => admin_url('admin.php?page=wpvisualx-popups'),
					'svg'        => admin_url('admin.php?page=wpvisualx-svg'),
					'tracking'   => admin_url('admin.php?page=wpvisualx-tracking'),
					'settings'   => admin_url('admin.php?page=wpvisualx-settings'),
				],
				'user'              => [
					'name'  => wp_get_current_user()->display_name,
					'email' => wp_get_current_user()->user_email,
				],
				'version'           => EPB_VERSION,
			]
		);

		wp_localize_script('wpvisualx', 'epbBuilderData', $epb_data);
	}

	/**
	 * Add "Edit with Builder" link on pages and posts list.
	 *
	 * @param array   $actions Row actions.
	 * @param WP_Post $post    Post object.
	 * @return array
	 */
	public function add_row_action($actions, $post) {
		if (!EPB_Post_Types::is_supported($post) || !EPB_Post_Types::user_can_edit($post->ID)) {
			return $actions;
		}

		$actions['epb_edit'] = sprintf(
			'<a href="%s">%s</a>',
			esc_url($this->get_edit_with_builder_url($post->ID)),
			__('Edit with Builder', 'wpvisualx')
		);

		return $actions;
	}

	/**
	 * Register a side meta box on the native page/post editor.
	 */
	public function register_builder_meta_box() {
		foreach (EPB_Post_Types::types() as $type) {
			add_meta_box(
				'epb-edit-with-builder',
				__('WPVisualX', 'wpvisualx'),
				[$this, 'render_builder_meta_box'],
				$type,
				'side',
				'high'
			);
		}
	}

	/**
	 * Render the native editor CTA to open WPVisualX.
	 *
	 * @param WP_Post $post Post being edited.
	 */
	public function render_builder_meta_box($post) {
		if (!EPB_Post_Types::is_supported($post) || !EPB_Post_Types::user_can_edit($post->ID)) {
			echo '<p>' . esc_html__('You do not have permission to edit this with WPVisualX.', 'wpvisualx') . '</p>';
			return;
		}

		$enabled = get_post_meta($post->ID, EPB_Renderer::META_ENABLED, true) === '1';
		$url     = $this->get_edit_with_builder_url($post->ID);

		echo '<div class="epb-wp-editor-box">';
		echo '<p class="epb-wp-editor-box__text">' . esc_html__(
			'Design this page with WPVisualX’s visual editor.',
			'wpvisualx'
		) . '</p>';
		printf(
			'<a class="button button-primary button-hero epb-wp-editor-box__button" href="%s">%s</a>',
			esc_url($url),
			esc_html($enabled ? __('Edit with WPVisualX', 'wpvisualx') : __('Build with WPVisualX', 'wpvisualx'))
		);
		if ($enabled) {
			echo '<p class="epb-wp-editor-box__hint">' . esc_html__('This content is managed by WPVisualX.', 'wpvisualx') . '</p>';
		} else {
			echo '<p class="epb-wp-editor-box__hint">' . esc_html__('Opens the builder and enables it for this page.', 'wpvisualx') . '</p>';
		}
		echo '</div>';
	}

	/**
	 * Enable EPB for a native WP page/post and redirect into the builder.
	 */
	public function handle_edit_with_builder() {
		$post_id = isset($_GET['post']) ? absint($_GET['post']) : 0;
		$nonce   = isset($_GET['_wpnonce']) ? sanitize_text_field(wp_unslash($_GET['_wpnonce'])) : '';

		if (!$post_id || !wp_verify_nonce($nonce, 'epb_edit_with_builder_' . $post_id)) {
			wp_die(esc_html__('Invalid request.', 'wpvisualx'), 403);
		}

		$post = get_post($post_id);
		if (!$post || !EPB_Post_Types::is_supported($post) || !EPB_Post_Types::user_can_edit($post_id)) {
			wp_die(esc_html__('You cannot edit this content with WPVisualX.', 'wpvisualx'), 403);
		}

		$has_html = get_post_meta($post_id, EPB_Renderer::META_HTML, true);
		$has_css  = get_post_meta($post_id, EPB_Renderer::META_CSS, true);
		$has_js   = get_post_meta($post_id, EPB_Renderer::META_JS, true);
		$legacy   = get_post_meta($post_id, EPB_Renderer::META_LEGACY, true);

		$title = $post->post_title;
		if ($title === '' || $title === __('Auto Draft', 'wpvisualx')) {
			$title = EPB_Post_Types::default_title($post->post_type);
		}

		if ($post->post_status === 'auto-draft') {
			wp_update_post([
				'ID'          => $post_id,
				'post_status' => 'draft',
				'post_title'  => $title,
			]);
		}

		if ($has_html === '' && $has_css === '' && $has_js === '' && empty($legacy)) {
			EPB_Renderer::save_page_code($post_id, EPB_Renderer::default_code($title));
		} else {
			update_post_meta($post_id, EPB_Renderer::META_ENABLED, '1');
		}

		$menu_slug = $post->post_type === 'post' ? 'wpvisualx-posts' : 'wpvisualx-pages';
		wp_safe_redirect(admin_url('admin.php?page=' . $menu_slug . '&page_id=' . $post_id));
		exit;
	}

	/**
	 * URL that enables EPB (if needed) and opens the builder.
	 *
	 * @param int $post_id Post ID.
	 * @return string
	 */
	private function get_edit_with_builder_url($post_id) {
		return wp_nonce_url(
			admin_url('admin.php?action=epb_edit_with_builder&post=' . absint($post_id)),
			'epb_edit_with_builder_' . absint($post_id)
		);
	}

	/**
	 * Full-screen builder body class.
	 *
	 * @param string $classes Existing classes.
	 * @return string
	 */
	public function admin_body_class($classes) {
		if ($this->is_builder_screen()) {
			$classes .= ' epb-builder-fullscreen';
		}

		return $classes;
	}

	/**
	 * Whether the current admin screen is an EPB builder page.
	 *
	 * @return bool
	 */
	private function is_builder_screen() {
		$screen = $this->get_admin_screen_slug();
		return isset($this->screens[ $screen ]);
	}
}
