<?php

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Supported content types for the page builder.
 */
class EPB_Post_Types {

	/**
	 * Post types editable with WPVisualX.
	 *
	 * @return string[]
	 */
	public static function types() {
		return [ 'page', 'post' ];
	}

	/**
	 * Check if a post type string is supported.
	 *
	 * @param string $type Post type.
	 * @return bool
	 */
	public static function is_supported_type($type) {
		return in_array($type, self::types(), true);
	}

	/**
	 * Check if a post object is a supported type.
	 *
	 * @param WP_Post|null $post Post object.
	 * @return bool
	 */
	public static function is_supported($post) {
		return $post instanceof WP_Post && self::is_supported_type($post->post_type);
	}

	/**
	 * Whether the current user can use the builder at all.
	 *
	 * @return bool
	 */
	public static function user_can_use_builder() {
		return current_user_can('edit_pages') || current_user_can('edit_posts');
	}

	/**
	 * Whether the current user can edit a specific item.
	 *
	 * @param int $post_id Post ID.
	 * @return bool
	 */
	public static function user_can_edit($post_id) {
		return current_user_can('edit_post', $post_id);
	}

	/**
	 * Whether the current user can create a post type.
	 *
	 * @param string $type Post type.
	 * @return bool
	 */
	public static function user_can_create($type) {
		if ($type === 'page') {
			return current_user_can('edit_pages');
		}

		if ($type === 'post') {
			return current_user_can('edit_posts');
		}

		return false;
	}

	/**
	 * Whether the current user can delete a specific item.
	 *
	 * @param int $post_id Post ID.
	 * @return bool
	 */
	public static function user_can_delete($post_id) {
		return current_user_can('delete_post', $post_id);
	}

	/**
	 * Default title for a new item.
	 *
	 * @param string $type Post type.
	 * @return string
	 */
	public static function default_title($type) {
		return $type === 'post' ? 'Untitled Post' : 'Untitled Page';
	}

	/**
	 * Human-readable singular label.
	 *
	 * @param string $type Post type.
	 * @return string
	 */
	public static function label($type) {
		return $type === 'post' ? 'Post' : 'Page';
	}
}
