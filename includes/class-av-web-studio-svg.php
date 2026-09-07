<?php

if (!defined('ABSPATH')) {
	exit;
}

/**
 * SVG upload support and media library helpers.
 */
class Av_Web_Studio_SVG {

	const MIME_TYPE = 'image/svg+xml';

	public function __construct() {
		add_filter('upload_mimes', [ $this, 'allow_svg_mime' ]);
		add_filter('wp_check_filetype_and_ext', [ $this, 'fix_svg_filetype' ], 10, 4);
		add_filter('wp_prepare_attachment_for_js', [ $this, 'prepare_svg_for_js' ], 10, 3);
	}

	/**
	 * Allow SVG uploads for users who can upload files.
	 *
	 * @param array $mimes Mime types.
	 * @return array
	 */
	public function allow_svg_mime($mimes) {
		if (current_user_can('upload_files')) {
			$mimes['svg']  = self::MIME_TYPE;
			$mimes['svgz'] = self::MIME_TYPE;
		}
		return $mimes;
	}

	/**
	 * Fix SVG file type detection on some hosts.
	 *
	 * @param array  $data     File data.
	 * @param string $file     File path.
	 * @param string $filename Filename.
	 * @param array  $mimes    Mime types.
	 * @return array
	 */
	public function fix_svg_filetype($data, $file, $filename, $mimes) {
		$ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

		if ($ext === 'svg') {
			$data['ext']  = 'svg';
			$data['type'] = self::MIME_TYPE;
		}

		return $data;
	}

	/**
	 * Show SVG thumbnails in media modal.
	 *
	 * @param array      $response   Attachment data.
	 * @param WP_Post    $attachment Attachment post.
	 * @param array|bool $meta       Attachment meta.
	 * @return array
	 */
	public function prepare_svg_for_js($response, $attachment, $meta) {
		if (!empty($response['mime']) && $response['mime'] === self::MIME_TYPE && !empty($response['url'])) {
			$response['icon'] = $response['url'];
			$response['sizes'] = [
				'full' => [
					'url'         => $response['url'],
					'width'       => 300,
					'height'      => 300,
					'orientation' => 'portrait',
				],
			];
		}

		return $response;
	}

	/**
	 * Sanitize SVG file contents.
	 *
	 * @param string $svg Raw SVG.
	 * @return string
	 */
	public static function sanitize($svg) {
		if ($svg === '') {
			return '';
		}

		$svg = preg_replace('/<script[\s\S]*?<\/script>/i', '', $svg);
		$svg = preg_replace('/on\w+\s*=\s*(["\']).*?\1/i', '', $svg);
		$svg = preg_replace('/javascript:/i', '', $svg);
		$svg = preg_replace('/<\?php[\s\S]*?\?>/i', '', $svg);

		return trim($svg);
	}

	/**
	 * List SVG attachments.
	 *
	 * @return array
	 */
	public static function get_library() {
		$attachments = get_posts([
			'post_type'      => 'attachment',
			'post_mime_type' => self::MIME_TYPE,
			'post_status'    => 'inherit',
			'posts_per_page' => 200,
			'orderby'        => 'date',
			'order'          => 'DESC',
		]);

		$items = [];

		foreach ($attachments as $attachment) {
			$url = wp_get_attachment_url($attachment->ID);
			if (!$url) {
				continue;
			}

			$items[] = [
				'id'       => (int) $attachment->ID,
				'title'    => $attachment->post_title,
				'url'      => $url,
				'filename' => basename(get_attached_file($attachment->ID) ?: $url),
				'date'     => $attachment->post_date,
			];
		}

		return $items;
	}

	/**
	 * Upload an SVG from REST request.
	 *
	 * @param array $file Uploaded file array from $_FILES shape.
	 * @return array|WP_Error
	 */
	public static function upload($file) {
		if (empty($file['tmp_name']) || !file_exists($file['tmp_name'])) {
			return new WP_Error('av_web_studio_svg_missing', __('No SVG file uploaded.', 'av-web-studio'), [ 'status' => 400 ]);
		}

		$checked = wp_check_filetype_and_ext($file['tmp_name'], $file['name']);
		$ext     = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

		if ($ext !== 'svg' && ($checked['type'] ?? '') !== self::MIME_TYPE) {
			return new WP_Error('av_web_studio_svg_invalid', __('Only SVG files are allowed.', 'av-web-studio'), [ 'status' => 400 ]);
		}

		$raw  = file_get_contents($file['tmp_name']); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$safe = self::sanitize($raw);

		if ($safe === '') {
			return new WP_Error('av_web_studio_svg_empty', __('SVG file is empty or invalid.', 'av-web-studio'), [ 'status' => 400 ]);
		}

		file_put_contents($file['tmp_name'], $safe); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents

		require_once ABSPATH . 'wp-admin/includes/file.php';

		$uploaded = wp_handle_upload(
			$file,
			[
				'test_form' => false,
				'mimes'     => [
					'svg' => self::MIME_TYPE,
				],
			]
		);

		if (!empty($uploaded['error'])) {
			return new WP_Error('av_web_studio_svg_upload', $uploaded['error'], [ 'status' => 500 ]);
		}

		$attachment_id = wp_insert_attachment(
			[
				'post_mime_type' => self::MIME_TYPE,
				'post_title'     => sanitize_file_name(pathinfo($file['name'], PATHINFO_FILENAME)),
				'post_content'   => '',
				'post_status'    => 'inherit',
			],
			$uploaded['file']
		);

		if (is_wp_error($attachment_id) || !$attachment_id) {
			return new WP_Error('av_web_studio_svg_attachment', __('Could not save SVG to media library.', 'av-web-studio'), [ 'status' => 500 ]);
		}

		require_once ABSPATH . 'wp-admin/includes/image.php';
		wp_update_attachment_metadata($attachment_id, wp_generate_attachment_metadata($attachment_id, $uploaded['file']));

		$item = null;
		foreach (self::get_library() as $entry) {
			if ($entry['id'] === (int) $attachment_id) {
				$item = $entry;
				break;
			}
		}

		return [
			'success' => true,
			'svg'     => $item ?: [
				'id'       => (int) $attachment_id,
				'title'    => sanitize_text_field($file['name']),
				'url'      => $uploaded['url'],
				'filename' => basename($uploaded['file']),
				'date'     => current_time('mysql'),
			],
			'message' => __('SVG uploaded.', 'av-web-studio'),
		];
	}

	/**
	 * Delete an SVG attachment.
	 *
	 * @param int $id Attachment ID.
	 * @return bool|WP_Error
	 */
	public static function delete($id) {
		$post = get_post($id);
		if (!$post || $post->post_type !== 'attachment' || get_post_mime_type($id) !== self::MIME_TYPE) {
			return new WP_Error('av_web_studio_svg_not_found', __('SVG not found.', 'av-web-studio'), [ 'status' => 404 ]);
		}

		$result = wp_delete_attachment($id, true);
		return $result ? true : new WP_Error('av_web_studio_svg_delete_failed', __('Could not delete SVG.', 'av-web-studio'), [ 'status' => 500 ]);
	}
}
