<?php

if (!defined('ABSPATH')) {
	exit;
}

class EPB_REST {

	const NAMESPACE = 'wpvisualx/v1';

	public function __construct() {
		add_action('rest_api_init', [$this, 'register_routes']);
	}

	/**
	 * Register REST routes.
	 */
	public function register_routes() {
		register_rest_route(self::NAMESPACE, '/pages', [
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => [$this, 'get_pages'],
			'permission_callback' => [$this, 'can_edit'],
		]);

		register_rest_route(self::NAMESPACE, '/pages', [
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => [$this, 'create_page'],
			'permission_callback' => [$this, 'can_edit'],
		]);

		register_rest_route(self::NAMESPACE, '/pages/(?P<id>\d+)', [
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => [$this, 'get_page'],
			'permission_callback' => [$this, 'can_edit'],
		]);

		register_rest_route(self::NAMESPACE, '/pages/(?P<id>\d+)', [
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => [$this, 'save_page'],
			'permission_callback' => [$this, 'can_edit'],
		]);

		register_rest_route(self::NAMESPACE, '/pages/bulk', [
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => [ $this, 'bulk_pages' ],
			'permission_callback' => [ $this, 'can_edit' ],
		]);

		register_rest_route(self::NAMESPACE, '/pages/(?P<id>\d+)/restore', [
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => [ $this, 'restore_page' ],
			'permission_callback' => [ $this, 'can_edit' ],
		]);

		register_rest_route(self::NAMESPACE, '/pages/(?P<id>\d+)', [
			'methods'             => WP_REST_Server::DELETABLE,
			'callback'            => [ $this, 'delete_page' ],
			'permission_callback' => [ $this, 'can_edit' ],
		]);

		register_rest_route(self::NAMESPACE, '/pages/(?P<id>\d+)/quick', [
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => [ $this, 'quick_edit_page' ],
			'permission_callback' => [ $this, 'can_edit' ],
		]);

		register_rest_route(self::NAMESPACE, '/pages/(?P<id>\d+)/publish', [
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => [$this, 'publish_page'],
			'permission_callback' => [$this, 'can_edit'],
		]);

		register_rest_route(self::NAMESPACE, '/ai/generate', [
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => [$this, 'ai_generate'],
			'permission_callback' => [$this, 'can_use_ai'],
		]);

		register_rest_route(self::NAMESPACE, '/post-meta/lists', [
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => [$this, 'get_post_meta_lists'],
			'permission_callback' => [$this, 'can_edit'],
		]);

		register_rest_route(self::NAMESPACE, '/posts/render', [
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => [$this, 'render_posts_widget'],
			'permission_callback' => [$this, 'can_edit'],
		]);

		register_rest_route(self::NAMESPACE, '/settings', [
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => [$this, 'get_settings'],
			'permission_callback' => [$this, 'can_edit'],
		]);

		register_rest_route(self::NAMESPACE, '/settings', [
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => [$this, 'save_settings'],
			'permission_callback' => [$this, 'can_manage_settings'],
		]);

		register_rest_route(self::NAMESPACE, '/templates', [
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => [$this, 'get_templates'],
			'permission_callback' => [$this, 'can_edit'],
		]);

		register_rest_route(self::NAMESPACE, '/templates/apply', [
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => [$this, 'apply_template'],
			'permission_callback' => [$this, 'can_edit'],
		]);

		register_rest_route(self::NAMESPACE, '/templates/upload', [
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => [$this, 'upload_template'],
			'permission_callback' => [$this, 'can_upload'],
		]);

		register_rest_route(self::NAMESPACE, '/templates/(?P<id>[a-z0-9_-]+)', [
			'methods'             => WP_REST_Server::DELETABLE,
			'callback'            => [$this, 'delete_template'],
			'permission_callback' => [$this, 'can_upload'],
		]);

		register_rest_route(self::NAMESPACE, '/layout/global', [
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => [$this, 'get_global_layout'],
			'permission_callback' => [$this, 'can_edit'],
		]);

		register_rest_route(self::NAMESPACE, '/layout/global', [
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => [$this, 'save_global_layout'],
			'permission_callback' => [$this, 'can_edit'],
		]);

		register_rest_route(self::NAMESPACE, '/popups', [
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => [$this, 'get_popups'],
			'permission_callback' => [$this, 'can_edit'],
		]);

		register_rest_route(self::NAMESPACE, '/popups', [
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => [$this, 'create_popup'],
			'permission_callback' => [$this, 'can_edit'],
		]);

		register_rest_route(self::NAMESPACE, '/popups/(?P<id>[a-zA-Z0-9_]+)', [
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => [$this, 'save_popup'],
			'permission_callback' => [$this, 'can_edit'],
		]);

		register_rest_route(self::NAMESPACE, '/popups/bulk', [
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => [ $this, 'bulk_popups' ],
			'permission_callback' => [ $this, 'can_edit' ],
		]);

		register_rest_route(self::NAMESPACE, '/popups/(?P<id>[a-zA-Z0-9_]+)/quick', [
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => [ $this, 'quick_edit_popup' ],
			'permission_callback' => [ $this, 'can_edit' ],
		]);

		register_rest_route(self::NAMESPACE, '/popups/(?P<id>[a-zA-Z0-9_]+)/restore', [
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => [ $this, 'restore_popup' ],
			'permission_callback' => [ $this, 'can_edit' ],
		]);

		register_rest_route(self::NAMESPACE, '/popups/(?P<id>[a-zA-Z0-9_]+)', [
			'methods'             => WP_REST_Server::DELETABLE,
			'callback'            => [$this, 'delete_popup'],
			'permission_callback' => [$this, 'can_edit'],
		]);

		register_rest_route(self::NAMESPACE, '/svg', [
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => [ $this, 'get_svgs' ],
			'permission_callback' => [ $this, 'can_edit' ],
		]);

		register_rest_route(self::NAMESPACE, '/svg', [
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => [ $this, 'upload_svg' ],
			'permission_callback' => [ $this, 'can_upload' ],
		]);

		register_rest_route(self::NAMESPACE, '/svg/(?P<id>\d+)', [
			'methods'             => WP_REST_Server::DELETABLE,
			'callback'            => [ $this, 'delete_svg' ],
			'permission_callback' => [ $this, 'can_upload' ],
		]);

		register_rest_route(self::NAMESPACE, '/tracking', [
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => [ $this, 'get_tracking' ],
			'permission_callback' => [ $this, 'can_edit' ],
		]);

		register_rest_route(self::NAMESPACE, '/tracking', [
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => [ $this, 'save_tracking' ],
			'permission_callback' => [ $this, 'can_edit' ],
		]);
	}

	/**
	 * Check edit permission.
	 *
	 * @return bool
	 */
	public function can_edit() {
		return EPB_Post_Types::user_can_use_builder();
	}

	/**
	 * Restrict AI features based on settings capability.
	 *
	 * @return bool
	 */
	public function can_use_ai() {
		return EPB_Settings::current_user_can_use_ai() && EPB_Post_Types::user_can_use_builder();
	}

	/**
	 * Restrict plugin settings to configured capability.
	 *
	 * @return bool
	 */
	public function can_manage_settings() {
		return EPB_Settings::current_user_can_manage();
	}

	/**
	 * Check upload permission for SVG library.
	 *
	 * @return bool
	 */
	public function can_upload() {
		return current_user_can('upload_files') && EPB_Post_Types::user_can_use_builder();
	}

	/**
	 * Validate post access for REST handlers.
	 *
	 * @param WP_Post|null $post Post object.
	 * @return true|WP_Error
	 */
	private function ensure_editable_post($post) {
		if (!$post || !EPB_Post_Types::is_supported($post)) {
			return new WP_Error('epb_not_found', __('Content not found.', 'wpvisualx'), [ 'status' => 404 ]);
		}

		if (!EPB_Post_Types::user_can_edit($post->ID)) {
			return new WP_Error('epb_forbidden', __('You cannot edit this content.', 'wpvisualx'), [ 'status' => 403 ]);
		}

		return true;
	}

	/**
	 * Get pages list with filters and pagination.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response
	 */
	public function get_pages($request) {
		$post_type = sanitize_key($request->get_param('post_type') ?: 'all');
		$status    = sanitize_key($request->get_param('status') ?: 'all');
		$search    = sanitize_text_field((string) $request->get_param('search'));
		$page      = max(1, (int) $request->get_param('page'));
		$per_page  = min(100, max(1, (int) ($request->get_param('per_page') ?: 20)));

		$types = EPB_Post_Types::types();
		if ($post_type !== 'all' && EPB_Post_Types::is_supported_type($post_type)) {
			$types = [ $post_type ];
		}

		if ($status === 'trash') {
			$statuses = [ 'trash' ];
		} elseif ($status === 'publish') {
			$statuses = [ 'publish' ];
		} elseif ($status === 'draft') {
			$statuses = [ 'draft', 'pending', 'private' ];
		} else {
			$statuses = [ 'publish', 'draft', 'pending', 'private', 'trash' ];
		}

		$args = [
			'post_type'              => $types,
			'post_status'            => $statuses,
			'posts_per_page'         => $per_page,
			'paged'                  => $page,
			'orderby'                => 'modified',
			'order'                  => 'DESC',
			'ignore_sticky_posts'    => true,
			'no_found_rows'          => false,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		];

		if ($search !== '') {
			$args['s'] = $search;
		}

		$query = new WP_Query($args);

		$items = [];
		foreach ($query->posts as $post) {
			if (!EPB_Post_Types::user_can_edit($post->ID)) {
				continue;
			}
			$items[] = $this->format_page_summary($post);
		}

		return rest_ensure_response([
			'items'       => $items,
			'total'       => (int) $query->found_posts,
			'total_pages' => (int) max(1, $query->max_num_pages),
			'page'        => $page,
			'per_page'    => $per_page,
		]);
	}

	/**
	 * Create a new page.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response|WP_Error
	 */
	public function create_page($request) {
		$params = $request->get_json_params();
		$title  = sanitize_text_field($request->get_param('title') ?: ($params['title'] ?? ''));
		$type   = sanitize_key($request->get_param('post_type') ?: ($params['post_type'] ?? 'page'));

		if (!EPB_Post_Types::is_supported_type($type)) {
			return new WP_Error('epb_invalid_type', __('Unsupported content type.', 'wpvisualx'), [ 'status' => 400 ]);
		}

		if (!EPB_Post_Types::user_can_create($type)) {
			return new WP_Error('epb_forbidden', __('You cannot create this content type.', 'wpvisualx'), [ 'status' => 403 ]);
		}

		if (empty($title)) {
			$title = EPB_Post_Types::default_title($type);
		}

		$page_id = wp_insert_post([
			'post_title'  => $title,
			'post_type'   => $type,
			'post_status' => 'draft',
		], true);

		if (is_wp_error($page_id)) {
			return $page_id;
		}

		update_post_meta($page_id, '_epb_enabled', '1');
		EPB_Renderer::save_page_code($page_id, EPB_Renderer::default_code($title));

		$page = get_post($page_id);

		return rest_ensure_response($this->format_page_detail($page));
	}

	/**
	 * Get single page with layout.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_page($request) {
		$page = get_post((int) $request['id']);
		$check = $this->ensure_editable_post($page);

		if (is_wp_error($check)) {
			return $check;
		}

		return rest_ensure_response($this->format_page_detail($page));
	}

	/**
	 * Save page layout (draft).
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response|WP_Error
	 */
	public function save_page($request) {
		$page = get_post((int) $request['id']);
		$check = $this->ensure_editable_post($page);

		if (is_wp_error($check)) {
			return $check;
		}

		$params = $request->get_json_params();
		$visual = isset($params['visual']) && is_array($params['visual']) ? $params['visual'] : null;

		if (!is_array($visual) && !EPB_Renderer::get_visual_document($page->ID)) {
			return new WP_Error('epb_invalid_code', __('Invalid builder data.', 'wpvisualx'), ['status' => 400]);
		}

		$title = isset($params['title']) ? $params['title'] : $request->get_param('title');

		if (!empty($title)) {
			wp_update_post([
				'ID'         => $page->ID,
				'post_title' => sanitize_text_field($title),
			]);
		}

		if (is_array($visual)) {
			EPB_Renderer::save_visual_and_compile($page->ID, $visual);
		} else {
			EPB_Renderer::save_page_code($page->ID, []);
		}

		EPB_Renderer::save_edit_mode($page->ID);

		if (isset($params['layout']) && is_array($params['layout'])) {
			EPB_Layout::save_page($page->ID, $params['layout']);
		}

		if (isset($params['seo']) && is_array($params['seo'])) {
			EPB_SEO::save_page($page->ID, $params['seo']);
		}

		if (isset($params['post_options']) && is_array($params['post_options'])) {
			EPB_Post_Options::save($page->ID, $params['post_options']);
		}

		wp_cache_delete($page->ID, 'post_meta');
		clean_post_cache($page->ID);

		$page = get_post($page->ID);

		return rest_ensure_response([
			'success'          => true,
			'page'             => $this->format_page_detail($page),
			'full_preview_url' => EPB_Preview::get_url($page->ID),
			'message'          => __('Draft saved.', 'wpvisualx'),
		]);
	}

	/**
	 * Quick edit page/post metadata without opening the full builder.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response|WP_Error
	 */
	public function quick_edit_page($request) {
		$page = get_post((int) $request['id']);
		$check = $this->ensure_editable_post($page);

		if (is_wp_error($check)) {
			return $check;
		}

		$params = $request->get_json_params();
		$update = [ 'ID' => $page->ID ];
		$changed = false;

		if (array_key_exists('title', $params)) {
			$title = sanitize_text_field($params['title']);
			if ($title === '') {
				return new WP_Error('epb_invalid_title', __('Title cannot be empty.', 'wpvisualx'), [ 'status' => 400 ]);
			}
			$update['post_title'] = $title;
			$changed               = true;
		}

		if (array_key_exists('status', $params)) {
			$status = sanitize_key($params['status']);
			$allowed = [ 'publish', 'draft', 'pending', 'private' ];
			if (!in_array($status, $allowed, true)) {
				return new WP_Error('epb_invalid_status', __('Invalid status.', 'wpvisualx'), [ 'status' => 400 ]);
			}
			$update['post_status'] = $status;
			$changed               = true;
		}

		if (array_key_exists('slug', $params)) {
			$slug = sanitize_title($params['slug']);
			if ($slug === '') {
				return new WP_Error('epb_invalid_slug', __('Slug cannot be empty.', 'wpvisualx'), [ 'status' => 400 ]);
			}
			$update['post_name'] = $slug;
			$changed             = true;
		}

		if (!$changed) {
			return new WP_Error('epb_no_changes', __('No changes provided.', 'wpvisualx'), [ 'status' => 400 ]);
		}

		$result = wp_update_post($update, true);

		if (is_wp_error($result)) {
			return $result;
		}

		clean_post_cache($page->ID);
		$page = get_post($page->ID);

		return rest_ensure_response([
			'success' => true,
			'page'    => $this->format_page_summary($page),
			'message' => sprintf(
				/* translators: %s: content type label (Page or Post). */
				__('%s updated.', 'wpvisualx'),
				EPB_Post_Types::label($page->post_type)
			),
		]);
	}

	/**
	 * Move a page or post to the trash.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response|WP_Error
	 */
	public function delete_page($request) {
		$page = get_post((int) $request['id']);
		$check = $this->ensure_accessible_post($page, true);

		if (is_wp_error($check)) {
			return $check;
		}

		if (!EPB_Post_Types::user_can_delete($page->ID)) {
			return new WP_Error('epb_forbidden', __('You cannot delete this content.', 'wpvisualx'), [ 'status' => 403 ]);
		}

		$params = $request->get_json_params();
		$force  = !empty($params['force']) || $request->get_param('force');

		$result = wp_delete_post($page->ID, (bool) $force);

		if (!$result) {
			return new WP_Error('epb_delete_failed', __('Could not delete content.', 'wpvisualx'), [ 'status' => 500 ]);
		}

		$label = EPB_Post_Types::label($page->post_type);

		return rest_ensure_response([
			'success' => true,
			'id'      => (int) $page->ID,
			'message' => $force
				? sprintf(
					/* translators: %s: content type label (Page or Post). */
					__('%s permanently deleted.', 'wpvisualx'),
					$label
				)
				: sprintf(
					/* translators: %s: content type label (Page or Post). */
					__('%s moved to trash.', 'wpvisualx'),
					$label
				),
		]);
	}

	/**
	 * Restore a page or post from trash.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response|WP_Error
	 */
	public function restore_page($request) {
		$page = get_post((int) $request['id']);
		$check = $this->ensure_accessible_post($page, true);

		if (is_wp_error($check)) {
			return $check;
		}

		if ($page->post_status !== 'trash') {
			return new WP_Error('epb_not_trashed', __('Content is not in trash.', 'wpvisualx'), [ 'status' => 400 ]);
		}

		if (!EPB_Post_Types::user_can_delete($page->ID)) {
			return new WP_Error('epb_forbidden', __('You cannot restore this content.', 'wpvisualx'), [ 'status' => 403 ]);
		}

		$restored = wp_untrash_post($page->ID);

		if (!$restored) {
			return new WP_Error('epb_restore_failed', __('Could not restore content.', 'wpvisualx'), [ 'status' => 500 ]);
		}

		$label = EPB_Post_Types::label($page->post_type);

		return rest_ensure_response([
			'success' => true,
			'id'      => (int) $page->ID,
			'message' => sprintf(
				/* translators: %s: content type label (Page or Post). */
				__('%s restored from trash.', 'wpvisualx'),
				$label
			),
		]);
	}

	/**
	 * Bulk trash, restore, or delete pages/posts.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response|WP_Error
	 */
	public function bulk_pages($request) {
		$params = $request->get_json_params();
		$action = sanitize_key($params['action'] ?? $request->get_param('action') ?? '');
		$ids    = isset($params['ids']) && is_array($params['ids']) ? array_map('absint', $params['ids']) : [];
		$force  = !empty($params['force']) || $request->get_param('force');

		if (!in_array($action, [ 'trash', 'restore', 'delete' ], true)) {
			return new WP_Error('epb_invalid_action', __('Invalid bulk action.', 'wpvisualx'), [ 'status' => 400 ]);
		}

		if (empty($ids)) {
			return new WP_Error('epb_empty_ids', __('No items selected.', 'wpvisualx'), [ 'status' => 400 ]);
		}

		$processed = 0;
		$errors    = 0;

		foreach ($ids as $id) {
			$page = get_post($id);
			$check = $this->ensure_accessible_post($page, true);

			if (is_wp_error($check) || !EPB_Post_Types::user_can_delete($page->ID)) {
				$errors++;
				continue;
			}

			$result = false;

			if ($action === 'trash' && $page->post_status !== 'trash') {
				$result = (bool) wp_trash_post($page->ID);
			} elseif ($action === 'restore' && $page->post_status === 'trash') {
				$result = (bool) wp_untrash_post($page->ID);
			} elseif ($action === 'delete') {
				$result = (bool) wp_delete_post($page->ID, (bool) $force || $page->post_status === 'trash');
			}

			if ($result) {
				$processed++;
			} else {
				$errors++;
			}
		}

		return rest_ensure_response([
			'success'   => $processed > 0,
			'processed' => $processed,
			'errors'    => $errors,
			'message'   => sprintf(
				/* translators: %d: number of items. */
				_n('%d item updated.', '%d items updated.', $processed, 'wpvisualx'),
				$processed
			),
		]);
	}

	/**
	 * Validate post access for REST handlers.
	 *
	 * @param WP_Post|null $post          Post object.
	 * @param bool         $allow_trashed Allow trashed posts.
	 * @return true|WP_Error
	 */
	private function ensure_accessible_post($post, $allow_trashed = false) {
		if (!$post || !EPB_Post_Types::is_supported($post)) {
			return new WP_Error('epb_not_found', __('Content not found.', 'wpvisualx'), [ 'status' => 404 ]);
		}

		if ($post->post_status === 'trash' && !$allow_trashed) {
			return new WP_Error('epb_trashed', __('Content is in trash.', 'wpvisualx'), [ 'status' => 410 ]);
		}

		if (!EPB_Post_Types::user_can_edit($post->ID)) {
			return new WP_Error('epb_forbidden', __('You cannot edit this content.', 'wpvisualx'), [ 'status' => 403 ]);
		}

		return true;
	}

	/**
	 * Publish page with layout.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response|WP_Error
	 */
	public function publish_page($request) {
		$page = get_post((int) $request['id']);
		$check = $this->ensure_editable_post($page);

		if (is_wp_error($check)) {
			return $check;
		}

		$publish_cap = $page->post_type === 'post' ? 'publish_posts' : 'publish_pages';
		if (!current_user_can($publish_cap)) {
			return new WP_Error('epb_cannot_publish', __('You do not have permission to publish this content.', 'wpvisualx'), [ 'status' => 403 ]);
		}

		$params = $request->get_json_params();
		$visual = isset($params['visual']) && is_array($params['visual']) ? $params['visual'] : null;

		if (!is_array($visual) && !EPB_Renderer::get_visual_document($page->ID)) {
			return new WP_Error('epb_invalid_code', __('Invalid builder data.', 'wpvisualx'), ['status' => 400]);
		}

		$title = isset($params['title']) ? $params['title'] : $request->get_param('title');

		$update = [
			'ID'          => $page->ID,
			'post_status' => 'publish',
		];

		if (!empty($title)) {
			$update['post_title'] = sanitize_text_field($title);
		}

		$result = wp_update_post($update, true);

		if (is_wp_error($result)) {
			return $result;
		}

		if (is_array($visual)) {
			EPB_Renderer::save_visual_and_compile($page->ID, $visual);
		} else {
			EPB_Renderer::save_page_code($page->ID, []);
		}

		EPB_Renderer::save_edit_mode($page->ID);

		if (isset($params['layout']) && is_array($params['layout'])) {
			EPB_Layout::save_page($page->ID, $params['layout']);
		}

		if (isset($params['seo']) && is_array($params['seo'])) {
			EPB_SEO::save_page($page->ID, $params['seo']);
		}

		if (isset($params['post_options']) && is_array($params['post_options'])) {
			$opts            = $params['post_options'];
			$opts['status']  = 'publish';
			EPB_Post_Options::save($page->ID, $opts);
		}

		$stored = EPB_Renderer::get_page_code($page->ID);

		$result = wp_update_post([
			'ID'           => $page->ID,
			'post_status'  => 'publish',
			'post_content' => EPB_Renderer::render_for_post_content($stored, $page->ID),
		], true);

		if (is_wp_error($result)) {
			return $result;
		}

		wp_cache_delete($page->ID, 'post_meta');
		clean_post_cache($page->ID);

		$page = get_post($page->ID);

		return rest_ensure_response([
			'success'     => true,
			'page'        => $this->format_page_detail($page),
			'preview_url' => get_permalink($page->ID),
			'message'     => sprintf(
				/* translators: %s: content type label (Page or Post). */
				__('%s published successfully.', 'wpvisualx'),
				EPB_Post_Types::label($page->post_type)
			),
		]);
	}

	/**
	 * AI content generation.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response|WP_Error
	 */
	public function ai_generate($request) {
		$prompt = sanitize_textarea_field($request->get_param('prompt'));

		if (empty($prompt)) {
			return new WP_Error('epb_empty_prompt', __('Prompt is required.', 'wpvisualx'), ['status' => 400]);
		}

		$code_param = $request->get_param('code');
		$context    = is_array($code_param) ? EPB_Renderer::normalize_code($code_param) : null;
		$page_title = sanitize_text_field($request->get_param('title') ?? '');

		$result = EPB_AI::generate($prompt, EPB_Settings::get_ai_settings(), $context, $page_title);
		$code   = is_array($result['code'] ?? null) ? $result['code'] : [];

		return rest_ensure_response([
			'success'     => true,
			'code'        => [
				'html' => (string) ($code['html'] ?? ''),
				'css'  => '',
				'js'   => '',
			],
			'source'      => $result['source'],
			'message'     => $result['message'],
			'explanation' => $result['message'],
			'action'      => $result['action'] ?? 'append',
		]);
	}

	/**
	 * Get taxonomy and parent page lists for the editor.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response
	 */
	public function get_post_meta_lists($request) {
		$post_id = absint($request->get_param('post_id'));

		return rest_ensure_response(EPB_Post_Options::get_editor_lists($post_id));
	}

	/**
	 * Render posts widget HTML for editor preview.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response|WP_Error
	 */
	public function render_posts_widget($request) {
		$params  = $request->get_json_params();
		$config  = isset($params['config']) && is_array($params['config']) ? $params['config'] : [];
		$post_id = absint($params['post_id'] ?? 0);

		if (empty($config)) {
			return new WP_Error('epb_invalid_posts_config', __('Invalid posts widget config.', 'wpvisualx'), [ 'status' => 400 ]);
		}

		return rest_ensure_response([
			'html' => EPB_Posts_Widget::render($config, $post_id),
		]);
	}

	/**
	 * Get site-wide header/footer layout.
	 *
	 * @return WP_REST_Response
	 */
	public function get_global_layout() {
		return rest_ensure_response([
			'layout' => EPB_Layout::format_global(EPB_Layout::get_global()),
		]);
	}

	/**
	 * Save site-wide header/footer layout.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response|WP_Error
	 */
	public function save_global_layout($request) {
		$params = $request->get_json_params();
		$layout = isset($params['layout']) && is_array($params['layout']) ? $params['layout'] : $params;

		if (!is_array($layout)) {
			return new WP_Error('epb_invalid_layout', __('Invalid layout data.', 'wpvisualx'), [ 'status' => 400 ]);
		}

		EPB_Layout::save_global($layout);

		return rest_ensure_response([
			'success' => true,
			'layout'  => EPB_Layout::format_global(EPB_Layout::get_global()),
			'message' => __('Site layout saved.', 'wpvisualx'),
		]);
	}

	/**
	 * Get popups with filters and pagination.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response
	 */
	public function get_popups($request) {
		$status   = sanitize_key($request->get_param('status') ?: 'all');
		$page     = max(1, (int) $request->get_param('page'));
		$per_page = min(100, max(1, (int) ($request->get_param('per_page') ?: 20)));

		$result = EPB_Popups::query([
			'status'   => $status,
			'page'     => $page,
			'per_page' => $per_page,
		]);

		return rest_ensure_response($result);
	}

	/**
	 * Create a popup.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response
	 */
	public function create_popup($request) {
		$params = $request->get_json_params();
		$name   = sanitize_text_field($params['name'] ?? $request->get_param('name') ?? 'New Popup');
		$popup  = EPB_Popups::save(EPB_Popups::default_popup($name));

		return rest_ensure_response([
			'success' => true,
			'popup'   => $popup,
		]);
	}

	/**
	 * Save a popup.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response|WP_Error
	 */
	public function save_popup($request) {
		$params = $request->get_json_params();
		$popup  = isset($params['popup']) && is_array($params['popup']) ? $params['popup'] : $params;

		if (!is_array($popup)) {
			return new WP_Error('epb_invalid_popup', __('Invalid popup data.', 'wpvisualx'), [ 'status' => 400 ]);
		}

		$popup['id'] = sanitize_key($request['id']);
		$saved       = EPB_Popups::save($popup);

		return rest_ensure_response([
			'success' => true,
			'popup'   => $saved,
			'message' => __('Popup saved.', 'wpvisualx'),
		]);
	}

	/**
	 * Quick edit popup name and status.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response|WP_Error
	 */
	public function quick_edit_popup($request) {
		$id    = sanitize_key($request['id']);
		$popup = EPB_Popups::get($id);

		if (!$popup) {
			return new WP_Error('epb_not_found', __('Popup not found.', 'wpvisualx'), [ 'status' => 404 ]);
		}

		if (EPB_Popups::get_status($popup) === 'trash') {
			return new WP_Error('epb_trashed', __('Restore the popup before editing.', 'wpvisualx'), [ 'status' => 410 ]);
		}

		$params  = $request->get_json_params();
		$changed = false;

		if (array_key_exists('name', $params)) {
			$name = sanitize_text_field($params['name']);
			if ($name === '') {
				return new WP_Error('epb_invalid_name', __('Name cannot be empty.', 'wpvisualx'), [ 'status' => 400 ]);
			}
			$popup['name'] = $name;
			$changed       = true;
		}

		if (array_key_exists('status', $params)) {
			$status = sanitize_key($params['status']);
			if (!in_array($status, [ 'publish', 'draft' ], true)) {
				return new WP_Error('epb_invalid_status', __('Invalid status.', 'wpvisualx'), [ 'status' => 400 ]);
			}
			$popup['status']  = $status;
			$popup['enabled'] = $status === 'publish';
			$changed          = true;
		}

		if (!$changed) {
			return new WP_Error('epb_no_changes', __('No changes provided.', 'wpvisualx'), [ 'status' => 400 ]);
		}

		$saved = EPB_Popups::save($popup);

		return rest_ensure_response([
			'success' => true,
			'popup'   => $saved,
			'message' => __('Popup updated.', 'wpvisualx'),
		]);
	}

	/**
	 * Delete a popup.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response|WP_Error
	 */
	public function delete_popup($request) {
		$id     = sanitize_key($request['id']);
		$params = $request->get_json_params();
		$force  = !empty($params['force']) || $request->get_param('force');

		if (!EPB_Popups::delete($id, (bool) $force)) {
			return new WP_Error('epb_not_found', __('Popup not found.', 'wpvisualx'), [ 'status' => 404 ]);
		}

		return rest_ensure_response([
			'success' => true,
			'id'      => $id,
			'message' => $force ? __('Popup permanently deleted.', 'wpvisualx') : __('Popup moved to trash.', 'wpvisualx'),
		]);
	}

	/**
	 * Restore popup from trash.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response|WP_Error
	 */
	public function restore_popup($request) {
		$id = sanitize_key($request['id']);

		if (!EPB_Popups::restore($id)) {
			return new WP_Error('epb_restore_failed', __('Could not restore popup.', 'wpvisualx'), [ 'status' => 400 ]);
		}

		return rest_ensure_response([
			'success' => true,
			'id'      => $id,
			'message' => __('Popup restored from trash.', 'wpvisualx'),
		]);
	}

	/**
	 * Bulk trash, restore, or delete popups.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response|WP_Error
	 */
	public function bulk_popups($request) {
		$params = $request->get_json_params();
		$action = sanitize_key($params['action'] ?? $request->get_param('action') ?? '');
		$ids    = isset($params['ids']) && is_array($params['ids']) ? array_map('sanitize_key', $params['ids']) : [];
		$force  = !empty($params['force']) || $request->get_param('force');

		if (!in_array($action, [ 'trash', 'restore', 'delete' ], true)) {
			return new WP_Error('epb_invalid_action', __('Invalid bulk action.', 'wpvisualx'), [ 'status' => 400 ]);
		}

		if (empty($ids)) {
			return new WP_Error('epb_empty_ids', __('No items selected.', 'wpvisualx'), [ 'status' => 400 ]);
		}

		$processed = 0;
		$errors    = 0;

		foreach ($ids as $id) {
			$result = false;

			if ($action === 'trash') {
				$result = EPB_Popups::trash($id);
			} elseif ($action === 'restore') {
				$result = EPB_Popups::restore($id);
			} elseif ($action === 'delete') {
				$result = EPB_Popups::delete($id, (bool) $force);
			}

			if ($result) {
				$processed++;
			} else {
				$errors++;
			}
		}

		return rest_ensure_response([
			'success'   => $processed > 0,
			'processed' => $processed,
			'errors'    => $errors,
			'message'   => sprintf(
				/* translators: %d: number of popups. */
				_n('%d popup updated.', '%d popups updated.', $processed, 'wpvisualx'),
				$processed
			),
		]);
	}

	/**
	 * Get plugin settings.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_settings() {
		if (EPB_Settings::current_user_can_manage()) {
			return rest_ensure_response(EPB_Settings::admin_settings());
		}
		return rest_ensure_response(EPB_Settings::public_settings());
	}

	/**
	 * Save plugin settings.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response|WP_Error
	 */
	public function save_settings($request) {
		if (!EPB_Settings::current_user_can_manage()) {
			return new WP_Error('epb_forbidden', __('You are not allowed to manage plugin settings.', 'wpvisualx'), [ 'status' => 403 ]);
		}

		$params = $request->get_json_params();
		if (!is_array($params)) {
			$params = $request->get_params();
		}

		$result = EPB_Settings::save($params);
		return rest_ensure_response($result);
	}

	/**
	 * List polished starter templates plus uploaded packs.
	 *
	 * @return WP_REST_Response
	 */
	public function get_templates() {
		$built_in = [];
		foreach (EPB_Visual_Templates::catalog() as $item) {
			$item['source']     = 'builtin';
			$item['can_delete'] = false;
			$item['format']     = 'visual';
			$built_in[]         = $item;
		}

		return rest_ensure_response([
			'templates' => array_merge($built_in, EPB_Custom_Templates::catalog()),
		]);
	}

	/**
	 * Upload a template zip pack.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response|WP_Error
	 */
	public function upload_template($request) {
		$files = $request->get_file_params();
		$file  = $files['file'] ?? null;

		if (!$file || empty($file['name'])) {
			return new WP_Error('epb_template_missing', __('No template zip uploaded.', 'wpvisualx'), [ 'status' => 400 ]);
		}

		$result = EPB_Custom_Templates::import_zip($file);
		if (is_wp_error($result)) {
			return $result;
		}

		return rest_ensure_response($result);
	}

	/**
	 * Delete an uploaded template pack.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response|WP_Error
	 */
	public function delete_template($request) {
		$id     = sanitize_key($request['id'] ?? '');
		$result = EPB_Custom_Templates::delete($id);

		if (is_wp_error($result)) {
			return $result;
		}

		return rest_ensure_response([
			'success' => true,
			'message' => __('Template deleted.', 'wpvisualx'),
		]);
	}

	/**
	 * Create a draft page from a starter template.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response|WP_Error
	 */
	public function apply_template($request) {
		$params = $request->get_json_params();
		if (!is_array($params)) {
			$params = $request->get_params();
		}

		$id = sanitize_key($params['id'] ?? '');
		if ($id === '') {
			return new WP_Error('epb_invalid_template', __('Template id is required.', 'wpvisualx'), [ 'status' => 400 ]);
		}

		$pack = EPB_Visual_Templates::get($id);
		$visual = null;
		$title  = '';

		if ($pack) {
			$visual = $pack['visual'];
			$title  = $pack['title'];
		} else {
			$custom = EPB_Custom_Templates::get_pack($id);
			if ($custom) {
				$visual = $custom['visual'] ?? null;
				$title  = $custom['title'] ?? 'New Page';
			}
		}

		if (!$visual || empty($visual['sections'])) {
			return new WP_Error('epb_unknown_template', __('Template not found.', 'wpvisualx'), [ 'status' => 404 ]);
		}

		$type = sanitize_key($params['post_type'] ?? 'page');
		if (!EPB_Post_Types::is_supported_type($type)) {
			$type = 'page';
		}
		if (!EPB_Post_Types::user_can_create($type)) {
			return new WP_Error('epb_forbidden', __('You cannot create this content type.', 'wpvisualx'), [ 'status' => 403 ]);
		}

		$title   = sanitize_text_field($params['title'] ?? $title ?: 'New Page');
		$page_id = wp_insert_post([
			'post_title'  => $title,
			'post_type'   => $type,
			'post_status' => 'draft',
		], true);

		if (is_wp_error($page_id)) {
			return $page_id;
		}

		update_post_meta($page_id, '_epb_enabled', '1');
		EPB_Renderer::save_visual_and_compile($page_id, $visual);
		EPB_Renderer::save_edit_mode($page_id, 'visual');

		$page = get_post($page_id);
		return rest_ensure_response($this->format_page_detail($page));
	}

	/**
	 * Format page summary.
	 *
	 * @param WP_Post $page Page.
	 * @return array
	 */
	private function format_page_summary($page) {
		return [
			'id'               => $page->ID,
			'title'            => $page->post_title,
			'post_type'        => $page->post_type,
			'status'           => $page->post_status,
			'slug'             => $page->post_name,
			'modified'         => $page->post_modified,
			'preview_url'      => get_preview_post_link($page),
			'full_preview_url' => EPB_Preview::get_url($page->ID),
			'permalink'        => get_permalink($page->ID),
			'epb_enabled'      => get_post_meta($page->ID, '_epb_enabled', true) === '1',
		];
	}

	/**
	 * Format page with layout.
	 *
	 * @param WP_Post $page Page.
	 * @return array
	 */
	private function format_page_detail($page) {
		return [
			'id'               => $page->ID,
			'title'            => $page->post_title,
			'post_type'        => $page->post_type,
			'status'           => $page->post_status,
			'slug'             => $page->post_name,
			'modified'         => $page->post_modified,
			'preview_url'      => get_preview_post_link($page),
			'full_preview_url' => EPB_Preview::get_url($page->ID),
			'permalink'        => get_permalink($page->ID),
			'epb_enabled'      => get_post_meta($page->ID, '_epb_enabled', true) === '1',
			'code'             => EPB_Renderer::get_page_code($page->ID),
			'edit_mode'        => EPB_Renderer::get_edit_mode($page->ID),
			'visual'           => EPB_Renderer::get_visual_document($page->ID),
			'layout'           => EPB_Layout::format_page(EPB_Layout::get_page($page->ID)),
			'seo'              => EPB_SEO::format(EPB_SEO::get_page($page->ID)),
			'post_options'     => EPB_Post_Options::get($page->ID),
		];
	}

	/**
	 * List SVG library items.
	 *
	 * @return WP_REST_Response
	 */
	public function get_svgs() {
		return rest_ensure_response([
			'svgs' => EPB_SVG::get_library(),
		]);
	}

	/**
	 * Upload an SVG file.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response|WP_Error
	 */
	public function upload_svg($request) {
		$files = $request->get_file_params();
		$file  = $files['file'] ?? null;

		if (!$file || empty($file['name'])) {
			return new WP_Error('epb_svg_missing', __('No SVG file uploaded.', 'wpvisualx'), [ 'status' => 400 ]);
		}

		$result = EPB_SVG::upload($file);

		if (is_wp_error($result)) {
			return $result;
		}

		return rest_ensure_response($result);
	}

	/**
	 * Delete an SVG attachment.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response|WP_Error
	 */
	public function delete_svg($request) {
		$id     = (int) $request['id'];
		$result = EPB_SVG::delete($id);

		if (is_wp_error($result)) {
			return $result;
		}

		return rest_ensure_response([
			'success' => true,
			'message' => __('SVG deleted.', 'wpvisualx'),
		]);
	}

	/**
	 * Get tracking integration settings.
	 *
	 * @return WP_REST_Response
	 */
	public function get_tracking() {
		return rest_ensure_response([
			'tracking' => EPB_Tracking::format(),
		]);
	}

	/**
	 * Save tracking integration settings.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response
	 */
	public function save_tracking($request) {
		$params   = $request->get_json_params();
		$tracking = isset($params['tracking']) && is_array($params['tracking']) ? $params['tracking'] : $params;
		$saved    = EPB_Tracking::save($tracking);

		return rest_ensure_response([
			'success'  => true,
			'tracking' => $saved,
			'message'  => __('Tracking settings saved.', 'wpvisualx'),
		]);
	}
}
