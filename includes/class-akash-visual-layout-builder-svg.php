<?php

if (!defined('ABSPATH')) {
	exit;
}

/**
 * SVG upload support and media library helpers.
 */
class Akash_Visual_Layout_Builder_SVG {

	const MIME_TYPE = 'image/svg+xml';

	/**
	 * Allowed SVG element names.
	 *
	 * @var string[]
	 */
	private static $allowed_tags = [
		'svg',
		'g',
		'path',
		'rect',
		'circle',
		'ellipse',
		'line',
		'polyline',
		'polygon',
		'text',
		'tspan',
		'defs',
		'clippath',
		'mask',
		'pattern',
		'lineargradient',
		'radialgradient',
		'stop',
		'use',
		'symbol',
		'title',
		'desc',
		'metadata',
		'style',
		'switch',
		'marker',
		'view',
		'filter',
		'fegaussianblur',
		'feoffset',
		'feblend',
		'fecolormatrix',
		'fecomponenttransfer',
		'fefunca',
		'fefuncb',
		'fefuncg',
		'fefuncr',
		'fecomposite',
		'feconvolvematrix',
		'fediffuselighting',
		'fedisplacementmap',
		'feflood',
		'feimage',
		'femerge',
		'femergenode',
		'femorphology',
		'fespecularlighting',
		'fetile',
		'feturbulence',
		'fedistantlight',
		'fepointlight',
		'fespotlight',
		'fedropshadow',
		'image',
	];

	/**
	 * Allowed attribute names (lowercase).
	 *
	 * @var string[]
	 */
	private static $allowed_attrs = [
		'id',
		'class',
		'clip-path',
		'clip-rule',
		'color',
		'cx',
		'cy',
		'd',
		'direction',
		'display',
		'dx',
		'dy',
		'fill',
		'fill-opacity',
		'fill-rule',
		'filter',
		'font-family',
		'font-size',
		'font-style',
		'font-weight',
		'fx',
		'fy',
		'gradienttransform',
		'gradientunits',
		'height',
		'href',
		'letter-spacing',
		'marker-end',
		'marker-mid',
		'marker-start',
		'mask',
		'offset',
		'opacity',
		'overflow',
		'pathlength',
		'patterncontentunits',
		'patterntransform',
		'patternunits',
		'points',
		'preserveaspectratio',
		'r',
		'rx',
		'ry',
		'spreadmethod',
		'stddeviation',
		'stop-color',
		'stop-opacity',
		'stroke',
		'stroke-dasharray',
		'stroke-dashoffset',
		'stroke-linecap',
		'stroke-linejoin',
		'stroke-miterlimit',
		'stroke-opacity',
		'stroke-width',
		'style',
		'text-anchor',
		'transform',
		'type',
		'viewbox',
		'visibility',
		'width',
		'x',
		'x1',
		'x2',
		'xlink:href',
		'xmlns',
		'xmlns:xlink',
		'xml:space',
		'y',
		'y1',
		'y2',
		'role',
		'aria-hidden',
		'aria-label',
		'aria-labelledby',
		'focusable',
	];

	public function __construct() {
		add_filter('upload_mimes', [ $this, 'allow_svg_mime' ]);
		add_filter('wp_check_filetype_and_ext', [ $this, 'fix_svg_filetype' ], 10, 4);
		add_filter('wp_prepare_attachment_for_js', [ $this, 'prepare_svg_for_js' ], 10, 3);
		add_filter('wp_handle_upload_prefilter', [ $this, 'sanitize_upload_prefilter' ]);
	}

	/**
	 * Allow SVG uploads for users who can upload files.
	 *
	 * @param array $mimes Mime types.
	 * @return array
	 */
	public function allow_svg_mime($mimes) {
		if (current_user_can('upload_files')) {
			$mimes['svg'] = self::MIME_TYPE;
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
	 * Sanitize SVG files uploaded through the media library.
	 *
	 * @param array $file Uploaded file array.
	 * @return array
	 */
	public function sanitize_upload_prefilter($file) {
		if (empty($file['tmp_name']) || !empty($file['error'])) {
			return $file;
		}

		$name = isset($file['name']) ? (string) $file['name'] : '';
		$type = isset($file['type']) ? (string) $file['type'] : '';
		$ext  = strtolower(pathinfo($name, PATHINFO_EXTENSION));

		if ($ext !== 'svg' && $type !== self::MIME_TYPE) {
			return $file;
		}

		$raw  = file_get_contents($file['tmp_name']); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$safe = self::sanitize($raw);

		if ($safe === '') {
			$file['error'] = __('This SVG file contains unsupported or unsafe content and was rejected.', 'akash-visual-layout-builder');
			return $file;
		}

		$written = file_put_contents($file['tmp_name'], $safe); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
		if ($written === false) {
			$file['error'] = __('Could not sanitize the SVG upload.', 'akash-visual-layout-builder');
		}

		return $file;
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
	 * Sanitize SVG markup by allowing only a safe subset of tags and attributes.
	 *
	 * @param string $svg Raw SVG.
	 * @return string
	 */
	public static function sanitize($svg) {
		if (!is_string($svg) || $svg === '') {
			return '';
		}

		// Strip PHP / ASP / XML processing instructions and BOM.
		$svg = preg_replace('/^\xEF\xBB\xBF/', '', $svg);
		$svg = preg_replace('/<\?(?:php|=)?[\s\S]*?\?>/i', '', $svg);
		$svg = preg_replace('/<%[\s\S]*?%>/', '', $svg);
		$svg = preg_replace('/<!ENTITY[\s\S]*?>/i', '', $svg);
		$svg = preg_replace('/<!DOCTYPE[\s\S]*?>/i', '', $svg);

		if (!class_exists('DOMDocument')) {
			return '';
		}

		$previous = libxml_use_internal_errors(true);
		$dom      = new DOMDocument();
		$loaded   = $dom->loadXML($svg, LIBXML_NONET | LIBXML_COMPACT);

		if (!$loaded || !$dom->documentElement) {
			// Fallback for HTML-ish SVG markup.
			$loaded = $dom->loadHTML('<?xml encoding="UTF-8">' . $svg, LIBXML_NONET | LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
		}

		libxml_clear_errors();
		libxml_use_internal_errors($previous);

		if (!$loaded || !$dom->documentElement) {
			return '';
		}

		$root = $dom->getElementsByTagName('svg')->item(0);
		if (!$root instanceof DOMElement) {
			return '';
		}

		self::sanitize_node($root);

		$clean = $dom->saveXML($root);
		if (!is_string($clean) || $clean === '') {
			return '';
		}

		// Final pass for leftover script/event patterns.
		$clean = preg_replace('/<script[\s\S]*?<\/script>/i', '', $clean);
		$clean = preg_replace('/\son[a-z]+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $clean);
		$clean = preg_replace('/javascript\s*:/i', '', $clean);

		$clean = trim($clean);
		if ($clean === '' || stripos($clean, '<svg') === false) {
			return '';
		}

		return $clean;
	}

	/**
	 * Recursively sanitize an SVG DOM node.
	 *
	 * @param DOMNode $node Node.
	 */
	private static function sanitize_node($node) {
		if (!$node instanceof DOMElement) {
			return;
		}

		$tag = strtolower($node->tagName);
		if (!in_array($tag, self::$allowed_tags, true)) {
			if ($node->parentNode) {
				$node->parentNode->removeChild($node);
			}
			return;
		}

		if ($node->hasAttributes()) {
			$remove = [];
			foreach ($node->attributes as $attr) {
				$name  = strtolower($attr->name);
				$value = (string) $attr->value;

				if (strpos($name, 'on') === 0) {
					$remove[] = $attr->name;
					continue;
				}

				if (!in_array($name, self::$allowed_attrs, true)) {
					$remove[] = $attr->name;
					continue;
				}

				if (in_array($name, [ 'href', 'xlink:href' ], true) && !self::is_safe_href($value, $tag)) {
					$remove[] = $attr->name;
					continue;
				}

				if ($name === 'style' && self::style_is_unsafe($value)) {
					$remove[] = $attr->name;
					continue;
				}
			}

			foreach ($remove as $attr_name) {
				$node->removeAttribute($attr_name);
			}
		}

		if ($tag === 'style' && $node->textContent !== '' && self::style_is_unsafe($node->textContent)) {
			$node->textContent = '';
		}

		// Copy children first because the live list mutates when removing nodes.
		$children = [];
		foreach ($node->childNodes as $child) {
			$children[] = $child;
		}

		foreach ($children as $child) {
			if ($child instanceof DOMElement) {
				self::sanitize_node($child);
			} elseif ($child instanceof DOMComment) {
				$node->removeChild($child);
			}
		}
	}

	/**
	 * Whether an href/xlink:href value is safe for SVG.
	 *
	 * @param string $value Attribute value.
	 * @param string $tag   Element tag.
	 * @return bool
	 */
	private static function is_safe_href($value, $tag) {
		$value = trim($value);
		if ($value === '') {
			return false;
		}

		// Fragment references within the same document.
		if (isset($value[0]) && $value[0] === '#') {
			return (bool) preg_match('/^#[A-Za-z][\w\-:.]{0,200}$/', $value);
		}

		$lower = strtolower($value);
		if (strpos($lower, 'javascript:') === 0 || strpos($lower, 'vbscript:') === 0) {
			return false;
		}

		// Allow only relative paths or safe raster data URIs for <image> — never remote URLs or nested SVG data.
		if ($tag === 'image') {
			if (strpos($lower, 'data:image/') === 0 && strpos($lower, 'data:image/svg') !== 0) {
				return true;
			}
			if (preg_match('#^(?:\.?/)?[A-Za-z0-9._\-/]+(?:\?[^\s]*)?$#', $value) && !preg_match('#^[a-z][a-z0-9+.-]*:#i', $value)) {
				return true;
			}
		}

		// Disallow external <use> and other remote references.
		return false;
	}

	/**
	 * Detect unsafe CSS constructs in style attributes/blocks.
	 *
	 * @param string $css CSS text.
	 * @return bool
	 */
	private static function style_is_unsafe($css) {
		$css = strtolower((string) $css);
		return (bool) preg_match('/expression\s*\(|javascript\s*:|import\s+|@import|behavior\s*:|binding\s*:|-moz-binding/i', $css);
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
			return new WP_Error('akash_visual_layout_builder_svg_missing', __('No SVG file uploaded.', 'akash-visual-layout-builder'), [ 'status' => 400 ]);
		}

		$checked = wp_check_filetype_and_ext($file['tmp_name'], $file['name']);
		$ext     = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

		if ($ext !== 'svg' && ($checked['type'] ?? '') !== self::MIME_TYPE) {
			return new WP_Error('akash_visual_layout_builder_svg_invalid', __('Only SVG files are allowed.', 'akash-visual-layout-builder'), [ 'status' => 400 ]);
		}

		$raw  = file_get_contents($file['tmp_name']); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$safe = self::sanitize($raw);

		if ($safe === '') {
			return new WP_Error('akash_visual_layout_builder_svg_empty', __('SVG file is empty or contains unsupported content.', 'akash-visual-layout-builder'), [ 'status' => 400 ]);
		}

		$written = file_put_contents($file['tmp_name'], $safe); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
		if ($written === false) {
			return new WP_Error('akash_visual_layout_builder_svg_write', __('Could not sanitize the SVG upload.', 'akash-visual-layout-builder'), [ 'status' => 500 ]);
		}

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
			return new WP_Error('akash_visual_layout_builder_svg_upload', $uploaded['error'], [ 'status' => 500 ]);
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
			return new WP_Error('akash_visual_layout_builder_svg_attachment', __('Could not save SVG to media library.', 'akash-visual-layout-builder'), [ 'status' => 500 ]);
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
			'message' => __('SVG uploaded.', 'akash-visual-layout-builder'),
		];
	}

	/**
	 * Delete an SVG attachment.
	 *
	 * @param int $id Attachment ID.
	 * @return bool|WP_Error
	 */
	public static function delete($id) {
		$id   = absint($id);
		$post = get_post($id);
		if (!$post || $post->post_type !== 'attachment' || get_post_mime_type($id) !== self::MIME_TYPE) {
			return new WP_Error('akash_visual_layout_builder_svg_not_found', __('SVG not found.', 'akash-visual-layout-builder'), [ 'status' => 404 ]);
		}

		if (!current_user_can('delete_post', $id)) {
			return new WP_Error('akash_visual_layout_builder_svg_forbidden', __('You cannot delete this SVG.', 'akash-visual-layout-builder'), [ 'status' => 403 ]);
		}

		$result = wp_delete_attachment($id, true);
		return $result ? true : new WP_Error('akash_visual_layout_builder_svg_delete_failed', __('Could not delete SVG.', 'akash-visual-layout-builder'), [ 'status' => 500 ]);
	}
}
