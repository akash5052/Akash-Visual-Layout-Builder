<?php

if (!defined('ABSPATH')) {
	exit;
}

/**
 * WordPress post/page options (categories, tags, excerpt, etc.).
 */
class Akash_Visual_Layout_Builder_Post_Options {

	const META_PAGE_TEMPLATE = '_akash_visual_layout_builder_page_template';
	const META_HIDE_TITLE    = '_akash_visual_layout_builder_hide_title';

	/**
	 * Allowed page template values.
	 *
	 * @return string[]
	 */
	public static function allowed_page_templates() {
		return [ 'default', 'akash-visual-layout-builder-full-width', 'akash-visual-layout-builder-canvas', 'theme' ];
	}

	/**
	 * Get page template for a post.
	 *
	 * @param int $post_id Post ID.
	 * @return string
	 */
	public static function get_page_template($post_id) {
		$value = (string) get_post_meta($post_id, self::META_PAGE_TEMPLATE, true);
		if ($value === '' || !in_array($value, self::allowed_page_templates(), true)) {
			return 'akash-visual-layout-builder-full-width';
		}
		return $value;
	}

	/**
	 * Whether the theme title should be hidden on the front end.
	 *
	 * @param int $post_id Post ID.
	 * @return bool
	 */
	public static function get_hide_title($post_id) {
		$stored = get_post_meta($post_id, self::META_HIDE_TITLE, true);
		if ($stored === '') {
			return true;
		}
		return $stored === '1';
	}

	/**
	 * Get options for a post.
	 *
	 * @param int $post_id Post ID.
	 * @return array
	 */
	public static function get($post_id) {
		$post = get_post($post_id);

		if (!$post) {
			return self::defaults();
		}

		$thumb_id  = (int) get_post_thumbnail_id($post_id);
		$thumb_url = $thumb_id ? (string) wp_get_attachment_image_url($thumb_id, 'medium') : '';

		$category_ids = [];
		$tag_names    = [];

		if ($post->post_type === 'post') {
			$category_ids = array_map('intval', wp_get_post_categories($post_id));
			$tags         = wp_get_post_tags($post_id, [ 'fields' => 'names' ]);
			$tag_names    = is_array($tags) ? array_values($tags) : [];
		}

		$page_template = self::get_page_template($post_id);

		return [
			'excerpt'             => $post->post_excerpt,
			'slug'                => $post->post_name,
			'status'              => $post->post_status,
			'featured_image_id'   => $thumb_id,
			'featured_image_url'  => $thumb_url ?: '',
			'category_ids'        => $category_ids,
			'tag_names'           => $tag_names,
			'parent_id'           => (int) $post->post_parent,
			'page_template'       => $page_template,
			'comment_status'      => $post->comment_status === 'open' ? 'open' : 'closed',
			'hide_title'          => self::get_hide_title($post_id),
			'menu_order'          => (int) $post->menu_order,
		];
	}

	/**
	 * Default options shape.
	 *
	 * @return array
	 */
	public static function defaults() {
		return [
			'excerpt'            => '',
			'slug'               => '',
			'status'             => 'draft',
			'featured_image_id'  => 0,
			'featured_image_url' => '',
			'category_ids'       => [],
			'tag_names'          => [],
			'parent_id'          => 0,
			'page_template'      => 'akash-visual-layout-builder-full-width',
			'comment_status'     => 'closed',
			'hide_title'         => true,
			'menu_order'         => 0,
		];
	}

	/**
	 * Save post/page options.
	 *
	 * @param int   $post_id Post ID.
	 * @param array $options Options payload.
	 * @return array|WP_Error
	 */
	public static function save($post_id, $options) {
		$post = get_post($post_id);

		if (!$post || !Akash_Visual_Layout_Builder_Post_Types::is_supported($post)) {
			return new WP_Error('akash_visual_layout_builder_invalid_post', __('Invalid post.', 'akash-visual-layout-builder'), [ 'status' => 400 ]);
		}

		$update = [ 'ID' => $post_id ];

		if (array_key_exists('excerpt', $options)) {
			$update['post_excerpt'] = sanitize_textarea_field($options['excerpt']);
		}

		if (!empty($options['slug'])) {
			$update['post_name'] = sanitize_title($options['slug']);
		}

		if (!empty($options['status'])) {
			$status  = sanitize_key($options['status']);
			$allowed = [ 'draft', 'publish', 'pending', 'private' ];
			if (in_array($status, $allowed, true)) {
				$update['post_status'] = $status;
			}
		}

		if ($post->post_type === 'page' && array_key_exists('parent_id', $options)) {
			$parent_id = absint($options['parent_id']);
			if ($parent_id === $post_id) {
				$parent_id = 0;
			}
			$update['post_parent'] = $parent_id;
		}

		if (array_key_exists('comment_status', $options)) {
			$update['comment_status'] = $options['comment_status'] === 'open' ? 'open' : 'closed';
		}

		if (array_key_exists('menu_order', $options)) {
			$update['menu_order'] = (int) $options['menu_order'];
		}

		$result = wp_update_post($update, true);

		if (is_wp_error($result)) {
			return $result;
		}

		if ($post->post_type === 'post' && isset($options['category_ids']) && is_array($options['category_ids'])) {
			$cat_ids = array_map('absint', $options['category_ids']);
			wp_set_post_categories($post_id, $cat_ids, false);
		}

		if ($post->post_type === 'post' && isset($options['tag_names']) && is_array($options['tag_names'])) {
			$tags = [];
			foreach ($options['tag_names'] as $tag) {
				$tag = sanitize_text_field($tag);
				if ($tag !== '') {
					$tags[] = $tag;
				}
			}
			wp_set_post_tags($post_id, $tags, false);
		}

		if (array_key_exists('featured_image_id', $options)) {
			$thumb_id = absint($options['featured_image_id']);
			if ($thumb_id > 0) {
				set_post_thumbnail($post_id, $thumb_id);
			} else {
				delete_post_thumbnail($post_id);
			}
		}

		if ($post->post_type === 'page' && array_key_exists('page_template', $options)) {
			$template = sanitize_key((string) $options['page_template']);
			if (in_array($template, self::allowed_page_templates(), true)) {
				update_post_meta($post_id, self::META_PAGE_TEMPLATE, $template);
			}
		}

		if ($post->post_type === 'page' && array_key_exists('hide_title', $options)) {
			update_post_meta($post_id, self::META_HIDE_TITLE, !empty($options['hide_title']) ? '1' : '0');
		}

		clean_post_cache($post_id);

		return self::get($post_id);
	}

	public static function get_tags() {
		$terms = get_tags([
			'hide_empty' => false,
			'orderby'    => 'name',
			'order'      => 'ASC',
		]);

		if (is_wp_error($terms)) {
			return [];
		}

		$list = [];

		foreach ($terms as $term) {
			$list[] = [
				'id'   => (int) $term->term_id,
				'name' => $term->name,
				'slug' => $term->slug,
			];
		}

		return $list;
	}

	/**
	 * Public post types for the posts widget picker.
	 *
	 * @return array
	 */
	public static function get_post_types_for_widget() {
		$types = get_post_types(
			[
				'public'  => true,
				'show_ui' => true,
			],
			'objects'
		);

		$exclude = [ 'attachment', 'akash_visual_layout_builder_popup' ];
		$list    = [];

		foreach ($types as $type) {
			if (in_array($type->name, $exclude, true)) {
				continue;
			}
			$list[] = [
				'name'  => $type->name,
				'label' => $type->labels->singular_name ? $type->labels->singular_name : $type->label,
			];
		}

		usort(
			$list,
			function ($a, $b) {
				return strcasecmp($a['label'], $b['label']);
			}
		);

		return $list;
	}

	/**
	 * Categories for the editor picker.
	 *
	 * @return array
	 */
	public static function get_categories() {
		$terms = get_categories([
			'hide_empty' => false,
			'orderby'    => 'name',
			'order'      => 'ASC',
		]);

		$list = [];

		foreach ($terms as $term) {
			$list[] = [
				'id'     => (int) $term->term_id,
				'name'   => $term->name,
				'slug'   => $term->slug,
				'parent' => (int) $term->parent,
			];
		}

		return $list;
	}

	/**
	 * Pages available as parent (excludes current).
	 *
	 * @param int $exclude_id Post ID to exclude.
	 * @return array
	 */
	public static function get_parent_pages($exclude_id = 0) {
		$exclude_id = absint($exclude_id);
		$pages      = get_posts([
			'post_type'      => 'page',
			'post_status'    => [ 'publish', 'draft', 'pending', 'private' ],
			'posts_per_page' => 200,
			'orderby'        => 'title',
			'order'          => 'ASC',
		]);

		$list = [ [ 'id' => 0, 'title' => '(No parent)' ] ];

		foreach ($pages as $page) {
			if ($exclude_id > 0 && (int) $page->ID === $exclude_id) {
				continue;
			}
			$list[] = [
				'id'    => (int) $page->ID,
				'title' => $page->post_title,
			];
		}

		return $list;
	}

	/**
	 * Editor meta lists.
	 *
	 * @param int $post_id Current post ID.
	 * @return array
	 */
	public static function get_editor_lists($post_id = 0) {
		return [
			'categories'   => self::get_categories(),
			'tags'         => self::get_tags(),
			'post_types'   => self::get_post_types_for_widget(),
			'parent_pages' => self::get_parent_pages($post_id),
		];
	}
}
