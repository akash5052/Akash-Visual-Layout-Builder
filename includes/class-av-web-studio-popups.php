<?php

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Site popups with triggers and display conditions.
 */
class Av_Web_Studio_Popups {

	const OPTION_KEY = 'av_web_studio_popups';

	const TRIGGERS = [ 'load', 'scroll', 'exit_intent', 'click', 'inactivity' ];

	const SCOPES = [ 'entire_site', 'specific', 'exclude', 'homepage' ];

	const FREQUENCIES = [ 'always', 'session', 'once', 'days' ];

	const STATUSES = [ 'publish', 'draft', 'trash' ];

	/**
	 * Default popup template.
	 *
	 * @param string $name Popup name.
	 * @return array
	 */
	public static function default_popup($name = 'New Popup') {
		return [
			'id'            => self::generate_id(),
			'name'          => $name,
			'status'        => 'draft',
			'modified'      => current_time('mysql'),
			'enabled'       => false,
			'edit_mode'     => 'visual',
			'visual'        => null,
			'html'          => '',
			'css'           => '',
			'js'            => '',
			'trigger'       => [
				'type'           => 'load',
				'delay'          => 3,
				'scroll_percent' => 50,
				'click_selector' => '',
			],
			'conditions'    => [
				'scope'      => 'entire_site',
				'page_ids'   => [],
				'post_types' => [],
			],
			'frequency'     => [
				'type' => 'session',
				'days' => 7,
			],
			'overlay_close' => true,
			'esc_close'     => true,
		];
	}

	/**
	 * Generate unique popup ID.
	 *
	 * @return string
	 */
	public static function generate_id() {
		return 'av_web_studio_' . substr(wp_generate_password(10, false, false), 0, 10);
	}

	/**
	 * Resolve popup list status.
	 *
	 * @param array $popup Popup data.
	 * @return string
	 */
	public static function get_status($popup) {
		$status = isset($popup['status']) ? sanitize_key($popup['status']) : '';

		if ($status === 'trash') {
			return 'trash';
		}

		if (in_array($status, [ 'publish', 'draft' ], true)) {
			return $status;
		}

		return !empty($popup['enabled']) ? 'publish' : 'draft';
	}

	/**
	 * Get all popups.
	 *
	 * @param array $args Optional filters: status (all|publish|draft|trash).
	 * @return array
	 */
	public static function get_all($args = []) {
		$stored = get_option(self::OPTION_KEY, []);
		if (!is_array($stored)) {
			return [];
		}

		$status_filter = isset($args['status']) ? sanitize_key($args['status']) : 'all';
		$popups        = [];

		foreach ($stored as $popup) {
			if (!is_array($popup)) {
				continue;
			}

			$normalized = self::normalize_popup($popup);

			if ($status_filter !== 'all' && self::get_status($normalized) !== $status_filter) {
				continue;
			}

			$popups[] = $normalized;
		}

		usort($popups, function ($a, $b) {
			return strcmp($b['modified'] ?? '', $a['modified'] ?? '');
		});

		return $popups;
	}

	/**
	 * Paginate popup list.
	 *
	 * @param array $args Query args.
	 * @return array{items: array, total: int, total_pages: int, page: int, per_page: int}
	 */
	public static function query($args = []) {
		$page     = max(1, (int) ($args['page'] ?? 1));
		$per_page = min(100, max(1, (int) ($args['per_page'] ?? 20)));
		$all      = self::get_all($args);
		$total    = count($all);
		$offset   = ($page - 1) * $per_page;

		return [
			'items'       => array_slice($all, $offset, $per_page),
			'total'       => $total,
			'total_pages' => (int) max(1, ceil($total / $per_page)),
			'page'        => $page,
			'per_page'    => $per_page,
		];
	}

	/**
	 * Get popup by ID.
	 *
	 * @param string $id Popup ID.
	 * @return array|null
	 */
	public static function get($id) {
		foreach (self::get_all() as $popup) {
			if ($popup['id'] === $id) {
				return $popup;
			}
		}
		return null;
	}

	/**
	 * Save all popups.
	 *
	 * @param array $popups Popups list.
	 */
	public static function save_all($popups) {
		$clean = [];
		foreach ((array) $popups as $popup) {
			if (is_array($popup)) {
				$clean[] = self::normalize_popup($popup);
			}
		}
		update_option(self::OPTION_KEY, $clean, false);
	}

	/**
	 * Save single popup (upsert).
	 *
	 * @param array $popup Popup data.
	 * @return array
	 */
	public static function save($popup) {
		$popup['modified'] = current_time('mysql');
		$popup             = self::normalize_popup($popup);
		$all    = self::get_all();
		$found  = false;

		foreach ($all as $i => $item) {
			if ($item['id'] === $popup['id']) {
				$all[ $i ] = $popup;
				$found     = true;
				break;
			}
		}

		if (!$found) {
			$all[] = $popup;
		}

		self::save_all($all);
		return $popup;
	}

	/**
	 * Move popup to trash.
	 *
	 * @param string $id Popup ID.
	 * @return bool
	 */
	public static function trash($id) {
		$popup = self::get($id);

		if (!$popup) {
			return false;
		}

		$popup['status']  = 'trash';
		$popup['enabled'] = false;
		$popup['modified'] = current_time('mysql');

		self::save($popup);
		return true;
	}

	/**
	 * Restore popup from trash.
	 *
	 * @param string $id Popup ID.
	 * @return bool
	 */
	public static function restore($id) {
		$popup = self::get($id);

		if (!$popup || self::get_status($popup) !== 'trash') {
			return false;
		}

		$popup['status']   = 'draft';
		$popup['enabled']  = false;
		$popup['modified'] = current_time('mysql');

		self::save($popup);
		return true;
	}

	/**
	 * Delete popup (trash by default, permanent when forced).
	 *
	 * @param string $id    Popup ID.
	 * @param bool   $force Permanently delete.
	 * @return bool
	 */
	public static function delete($id, $force = false) {
		$popup = self::get($id);

		if (!$popup) {
			return false;
		}

		if (!$force && self::get_status($popup) !== 'trash') {
			return self::trash($id);
		}

		$all  = self::get_all([ 'status' => 'all' ]);
		$next = array_values(array_filter($all, function ($item) use ($id) {
			return $item['id'] !== $id;
		}));

		if (count($next) === count($all)) {
			return false;
		}

		self::save_all($next);
		return true;
	}

	/**
	 * Normalize popup structure.
	 *
	 * @param array $popup Raw popup.
	 * @return array
	 */
	public static function normalize_popup($popup) {
		$defaults = self::default_popup();

		$id = !empty($popup['id']) ? sanitize_key($popup['id']) : self::generate_id();

		$trigger_type = isset($popup['trigger']['type']) ? sanitize_key($popup['trigger']['type']) : 'load';
		if (!in_array($trigger_type, self::TRIGGERS, true)) {
			$trigger_type = 'load';
		}

		$scope = isset($popup['conditions']['scope']) ? sanitize_key($popup['conditions']['scope']) : 'entire_site';
		if (!in_array($scope, self::SCOPES, true)) {
			$scope = 'entire_site';
		}

		$freq_type = isset($popup['frequency']['type']) ? sanitize_key($popup['frequency']['type']) : 'session';
		if (!in_array($freq_type, self::FREQUENCIES, true)) {
			$freq_type = 'session';
		}

		$page_ids = [];
		if (!empty($popup['conditions']['page_ids']) && is_array($popup['conditions']['page_ids'])) {
			$page_ids = array_map('absint', $popup['conditions']['page_ids']);
		}

		$post_types = [];
		if (!empty($popup['conditions']['post_types']) && is_array($popup['conditions']['post_types'])) {
			foreach ($popup['conditions']['post_types'] as $type) {
				if (Av_Web_Studio_Post_Types::is_supported_type($type)) {
					$post_types[] = $type;
				}
			}
		}

		$status = isset($popup['status']) ? sanitize_key($popup['status']) : '';
		if (!in_array($status, self::STATUSES, true)) {
			$status = !empty($popup['enabled']) ? 'publish' : 'draft';
		}

		if ($status === 'trash') {
			$enabled = false;
		} else {
			$enabled = $status === 'publish';
		}

		$edit_mode = 'visual';

		$visual = null;
		if (isset($popup['visual']) && is_array($popup['visual'])) {
			$visual = $popup['visual'];
		}

		if (is_array($visual)) {
			$compiled = Av_Web_Studio_Output::compile_visual($visual);
			$html     = $compiled['html'];
			$css      = $compiled['css'];
		} else {
			$html = '';
			$css  = '';
		}

		return [
			'id'            => $id,
			'name'          => sanitize_text_field($popup['name'] ?? $defaults['name']),
			'status'        => $status,
			'modified'      => sanitize_text_field($popup['modified'] ?? current_time('mysql')),
			'enabled'       => $enabled,
			'edit_mode'     => $edit_mode,
			'visual'        => $visual,
			'html'          => $html,
			'css'           => $css,
			'js'            => '',
			'trigger'       => [
				'type'           => $trigger_type,
				'delay'          => max(0, (int) ($popup['trigger']['delay'] ?? 3)),
				'scroll_percent' => min(100, max(1, (int) ($popup['trigger']['scroll_percent'] ?? 50))),
				'click_selector' => sanitize_text_field($popup['trigger']['click_selector'] ?? ''),
			],
			'conditions'    => [
				'scope'      => $scope,
				'page_ids'   => $page_ids,
				'post_types' => $post_types,
			],
			'frequency'     => [
				'type' => $freq_type,
				'days' => max(1, (int) ($popup['frequency']['days'] ?? 7)),
			],
			'overlay_close' => !isset($popup['overlay_close']) || !empty($popup['overlay_close']),
			'esc_close'     => !isset($popup['esc_close']) || !empty($popup['esc_close']),
		];
	}

	/**
	 * Check if popup should load on current page.
	 *
	 * @param array    $popup   Popup config.
	 * @param int|null $post_id Current post ID.
	 * @return bool
	 */
	public static function matches_page($popup, $post_id = null) {
		if (empty($popup['enabled'])) {
			return false;
		}

		$conditions = $popup['conditions'];
		$scope      = $conditions['scope'] ?? 'entire_site';

		if ($scope === 'homepage') {
			return is_front_page() || is_home();
		}

		if ($post_id === null) {
			$post_id = is_singular() ? (int) get_queried_object_id() : 0;
		}

		if (!empty($conditions['post_types'])) {
			if (!$post_id) {
				return false;
			}
			$type = get_post_type($post_id);
			if (!in_array($type, $conditions['post_types'], true)) {
				return false;
			}
		}

		$page_ids = array_map('intval', $conditions['page_ids'] ?? []);

		if ($scope === 'entire_site') {
			return true;
		}

		if ($scope === 'specific') {
			return $post_id > 0 && in_array($post_id, $page_ids, true);
		}

		if ($scope === 'exclude') {
			return $post_id === 0 || !in_array($post_id, $page_ids, true);
		}

		return false;
	}

	/**
	 * Get popups for current front-end page.
	 *
	 * @return array
	 */
	public static function get_for_current_page() {
		$post_id = is_singular() ? (int) get_queried_object_id() : 0;
		$matched = [];

		foreach (self::get_all([ 'status' => 'publish' ]) as $popup) {
			if (self::matches_page($popup, $post_id)) {
				$matched[] = $popup;
			}
		}

		return $matched;
	}

	/**
	 * Render popup markup.
	 *
	 * @param array $popup Popup config.
	 * @return string
	 */
	public static function render_markup($popup) {
		$id = esc_attr($popup['id']);

		$html = '';
		if (!empty($popup['visual']) && is_array($popup['visual'])) {
			$compiled = Av_Web_Studio_Output::compile_visual($popup['visual']);
			$html     = $compiled['html'];
		}

		return '<div id="av-web-studio-popup-' . $id . '" class="av-web-studio-popup" data-av-web-studio-popup="' . $id . '" aria-hidden="true" role="dialog" aria-modal="true">'
			. '<div class="av-web-studio-popup__overlay" data-av-web-studio-close></div>'
			. '<div class="av-web-studio-popup__dialog" role="document">'
			. '<button type="button" class="av-web-studio-popup__close" data-av-web-studio-close aria-label="' . esc_attr__('Close', 'av-web-studio') . '">&times;</button>'
			. '<div class="av-web-studio-popup__content">' . $html . '</div>'
			. '</div></div>';
	}

	/**
	 * Client-side config for a popup.
	 *
	 * @param array $popup Popup config.
	 * @return array
	 */
	public static function client_config($popup) {
		return [
			'id'            => $popup['id'],
			'trigger'       => $popup['trigger'],
			'frequency'     => $popup['frequency'],
			'overlay_close' => (bool) $popup['overlay_close'],
			'esc_close'     => (bool) $popup['esc_close'],
		];
	}
}
