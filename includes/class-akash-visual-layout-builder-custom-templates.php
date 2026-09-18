<?php

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Uploaded page template packs (zip import).
 *
 * Expected zip layout:
 *   manifest.json   (required)
 *   document.json   (required — visual builder document)
 *   preview.jpg|png|webp (optional)
 *
 * manifest.json:
 * {
 *   "id": "my-template",
 *   "title": "Café Landing",
 *   "description": "…",
 *   "brand": "Daily Grind",
 *   "topic": "coffee shop",
 *   "accent": "#6F4E37"
 * }
 */
class Akash_Visual_Layout_Builder_Custom_Templates {

	const OPTION_KEY = 'akash_visual_layout_builder_custom_templates';
	const MAX_ZIP_BYTES = 5242880; // 5 MB
	const ALLOWED_EXTENSIONS = [ 'json', 'jpg', 'jpeg', 'png', 'webp', 'gif', 'svg' ];

	/**
	 * Built-in starter ids that cannot be overwritten by uploads.
	 *
	 * @return string[]
	 */
	public static function reserved_ids() {
		if (class_exists('Akash_Visual_Layout_Builder_Visual_Templates')) {
			return Akash_Visual_Layout_Builder_Visual_Templates::reserved_ids();
		}
		$ids = [];
		foreach (Akash_Visual_Layout_Builder_AI_Content::starter_catalog() as $item) {
			$ids[] = $item['id'];
		}
		return $ids;
	}

	/**
	 * Uploads base directory for template packs.
	 *
	 * @return array{basedir:string,baseurl:string}|WP_Error
	 */
	public static function upload_dir() {
		$uploads = wp_upload_dir();
		if (!empty($uploads['error'])) {
			return new WP_Error('akash_visual_layout_builder_upload_dir', $uploads['error'], [ 'status' => 500 ]);
		}

		$basedir = trailingslashit($uploads['basedir']) . 'akash-visual-layout-builder-templates';
		$baseurl = trailingslashit($uploads['baseurl']) . 'akash-visual-layout-builder-templates';

		if (!wp_mkdir_p($basedir)) {
			return new WP_Error('akash_visual_layout_builder_upload_dir', __('Could not create template uploads folder.', 'akash-visual-layout-builder'), [ 'status' => 500 ]);
		}

		return [
			'basedir' => $basedir,
			'baseurl' => $baseurl,
		];
	}

	/**
	 * All stored custom templates (option index).
	 *
	 * @return array<string,array<string,mixed>>
	 */
	public static function all() {
		$stored = get_option(self::OPTION_KEY, []);
		return is_array($stored) ? $stored : [];
	}

	/**
	 * Catalog rows for the Templates UI (merged with built-ins separately).
	 *
	 * @return array<int,array<string,mixed>>
	 */
	public static function catalog() {
		$items = [];
		$dir   = self::upload_dir();
		if (is_wp_error($dir)) {
			return $items;
		}

		foreach (self::all() as $id => $meta) {
			if (!is_array($meta)) {
				continue;
			}
			$preview = '';
			if (!empty($meta['preview'])) {
				$preview = trailingslashit($dir['baseurl']) . $id . '/' . ltrim((string) $meta['preview'], '/');
			} elseif (!empty($meta['topic'])) {
				$preview = Akash_Visual_Layout_Builder_AI_Images::relevant_url((string) $meta['topic'], 960, 640, 0, (string) ($meta['brand'] ?? ''), 'hero', (string) ($meta['title'] ?? ''));
			}

			$items[] = [
				'id'          => $id,
				'title'       => (string) ($meta['title'] ?? $id),
				'description' => (string) ($meta['description'] ?? 'Uploaded template pack.'),
				'topic'       => (string) ($meta['topic'] ?? ''),
				'brand'       => (string) ($meta['brand'] ?? ($meta['title'] ?? $id)),
				'preview'     => $preview,
				'accent'      => (string) ($meta['accent'] ?? '#2563eb'),
				'source'      => 'uploaded',
				'can_delete'  => self::current_user_can_delete($id),
				'author_id'   => isset($meta['author_id']) ? (int) $meta['author_id'] : 0,
				'format'      => !empty($meta['has_visual']) ? 'visual' : 'code',
			];
		}

		return $items;
	}

	/**
	 * Whether a custom template exists.
	 *
	 * @param string $id Template id.
	 * @return bool
	 */
	public static function exists($id) {
		$id  = sanitize_key($id);
		$all = self::all();
		return $id !== '' && isset($all[ $id ]);
	}

	/**
	 * Load a custom template pack (visual + compiled/code).
	 *
	 * @param string $id Template id.
	 * @return array{title:string,visual:?array,code:array}|null
	 */
	public static function get_pack($id) {
		$code = self::get_code($id);
		if (!$code) {
			return null;
		}

		$id  = sanitize_key($id);
		$all = self::all();
		$dir = self::upload_dir();
		if (is_wp_error($dir) || empty($all[ $id ])) {
			return [
				'title'  => $code['title'],
				'visual' => null,
				'code'   => $code,
			];
		}

		$folder   = trailingslashit($dir['basedir']) . $id;
		$visual   = null;
		$doc_raw  = self::read_first($folder, [ 'document.json', 'visual.json' ]);
		if ($doc_raw !== null) {
			$decoded = json_decode($doc_raw, true);
			if (is_array($decoded) && !empty($decoded['sections'])) {
				$visual = $decoded;
				// Prefer compiled output when a visual doc is present.
				$code = array_merge($code, Akash_Visual_Layout_Builder_Output::compile_visual($visual));
			}
		}

		return [
			'title'  => $code['title'],
			'visual' => $visual,
			'code'   => $code,
		];
	}

	/**
	 * Load code for a custom template.
	 *
	 * @param string $id Template id.
	 * @return array{html:string,css:string,js:string,title:string}|null
	 */
	public static function get_code($id) {
		$id = sanitize_key($id);
		$all = self::all();
		if ($id === '' || empty($all[ $id ]) || !is_array($all[ $id ])) {
			return null;
		}

		$dir = self::upload_dir();
		if (is_wp_error($dir)) {
			return null;
		}

		$meta    = $all[ $id ];
		$folder  = trailingslashit($dir['basedir']) . $id;
		$doc_raw = self::read_first($folder, [ 'document.json', 'visual.json' ]);

		if ($doc_raw === null) {
			return null;
		}

		return [
			'html'  => '',
			'css'   => '',
			'js'    => '',
			'title' => (string) ($meta['brand'] ?? $meta['title'] ?? $id),
		];
	}

	/**
	 * Import a template zip upload.
	 *
	 * @param array $file $_FILES-style array.
	 * @return array{success:bool,template:array<string,mixed>,message:string}|WP_Error
	 */
	public static function import_zip($file) {
		if (!class_exists('ZipArchive')) {
			return new WP_Error('akash_visual_layout_builder_zip_unsupported', __('ZipArchive is not available on this server.', 'akash-visual-layout-builder'), [ 'status' => 500 ]);
		}

		if (empty($file['tmp_name']) || !file_exists($file['tmp_name'])) {
			return new WP_Error('akash_visual_layout_builder_zip_missing', __('No zip file uploaded.', 'akash-visual-layout-builder'), [ 'status' => 400 ]);
		}

		$name = (string) ($file['name'] ?? '');
		$ext  = strtolower(pathinfo($name, PATHINFO_EXTENSION));
		if ($ext !== 'zip') {
			return new WP_Error('akash_visual_layout_builder_zip_type', __('Please upload a .zip template pack.', 'akash-visual-layout-builder'), [ 'status' => 400 ]);
		}

		$size = (int) ($file['size'] ?? 0);
		if ($size <= 0 || $size > self::MAX_ZIP_BYTES) {
			return new WP_Error('akash_visual_layout_builder_zip_size', __('Zip must be under 5 MB.', 'akash-visual-layout-builder'), [ 'status' => 400 ]);
		}

		$tmp_zip = $file['tmp_name'];
		$extract = wp_normalize_path(trailingslashit(get_temp_dir()) . 'akash-visual-layout-builder-tpl-' . wp_generate_password(8, false));
		if (!wp_mkdir_p($extract)) {
			return new WP_Error('akash_visual_layout_builder_zip_tmp', __('Could not prepare temp folder.', 'akash-visual-layout-builder'), [ 'status' => 500 ]);
		}

		$zip = new ZipArchive();
		$opened = $zip->open($tmp_zip);
		if ($opened !== true) {
			self::rrmdir($extract);
			return new WP_Error('akash_visual_layout_builder_zip_open', __('Could not open the zip archive.', 'akash-visual-layout-builder'), [ 'status' => 400 ]);
		}

		for ($i = 0; $i < $zip->numFiles; $i++) {
			$entry = $zip->getNameIndex($i);
			if ($entry === false) {
				continue;
			}
			$check = self::validate_zip_entry($entry);
			if (is_wp_error($check)) {
				$zip->close();
				self::rrmdir($extract);
				return $check;
			}
		}

		if (!$zip->extractTo($extract)) {
			$zip->close();
			self::rrmdir($extract);
			return new WP_Error('akash_visual_layout_builder_zip_extract', __('Could not extract the zip archive.', 'akash-visual-layout-builder'), [ 'status' => 400 ]);
		}
		$zip->close();

		$pack_root = self::find_pack_root($extract);
		if (!$pack_root) {
			self::rrmdir($extract);
			return new WP_Error(
				'akash_visual_layout_builder_zip_manifest',
				__('Zip must include a manifest.json plus document.json (visual builder document).', 'akash-visual-layout-builder'),
				[ 'status' => 400 ]
			);
		}

		$manifest_raw = file_get_contents(trailingslashit($pack_root) . 'manifest.json');
		$manifest     = json_decode((string) $manifest_raw, true);
		if (!is_array($manifest)) {
			self::rrmdir($extract);
			return new WP_Error('akash_visual_layout_builder_zip_manifest', __('manifest.json is invalid.', 'akash-visual-layout-builder'), [ 'status' => 400 ]);
		}

		$id = sanitize_key($manifest['id'] ?? pathinfo($name, PATHINFO_FILENAME));
		if ($id === '') {
			$id = 'template-' . wp_generate_password(6, false, false);
		}
		if (in_array($id, self::reserved_ids(), true)) {
			self::rrmdir($extract);
			return new WP_Error('akash_visual_layout_builder_zip_reserved', __('That template id is reserved for a built-in template. Change "id" in manifest.json.', 'akash-visual-layout-builder'), [ 'status' => 400 ]);
		}

		$visual_raw = self::read_first($pack_root, [ 'document.json', 'visual.json' ]);
		$has_visual = false;
		if ($visual_raw !== null) {
			$decoded = json_decode($visual_raw, true);
			$has_visual = is_array($decoded) && !empty($decoded['sections']);
		}
		if (!$has_visual) {
			self::rrmdir($extract);
			return new WP_Error('akash_visual_layout_builder_zip_empty', __('Zip must include document.json (visual builder document).', 'akash-visual-layout-builder'), [ 'status' => 400 ]);
		}

		$dir = self::upload_dir();
		if (is_wp_error($dir)) {
			self::rrmdir($extract);
			return $dir;
		}

		$dest = trailingslashit($dir['basedir']) . $id;
		if (is_dir($dest)) {
			self::rrmdir($dest);
		}
		if (!wp_mkdir_p($dest)) {
			self::rrmdir($extract);
			return new WP_Error('akash_visual_layout_builder_zip_dest', __('Could not create template folder.', 'akash-visual-layout-builder'), [ 'status' => 500 ]);
		}

		$preview_name = self::copy_pack_files($pack_root, $dest);
		self::rrmdir($extract);

		$meta = [
			'id'          => $id,
			'title'       => sanitize_text_field($manifest['title'] ?? ucwords(str_replace('-', ' ', $id))),
			'description' => sanitize_text_field($manifest['description'] ?? 'Uploaded template pack.'),
			'brand'       => sanitize_text_field($manifest['brand'] ?? ($manifest['title'] ?? $id)),
			'topic'       => sanitize_text_field($manifest['topic'] ?? ''),
			'accent'      => self::sanitize_accent($manifest['accent'] ?? '#2563eb'),
			'preview'     => $preview_name,
			'has_visual'  => $has_visual,
			'author_id'   => get_current_user_id(),
			'updated'     => current_time('mysql'),
		];

		$all        = self::all();
		$all[ $id ] = $meta;
		update_option(self::OPTION_KEY, $all, false);

		$catalog = self::catalog();
		$template = null;
		foreach ($catalog as $row) {
			if ($row['id'] === $id) {
				$template = $row;
				break;
			}
		}

		return [
			'success'  => true,
			'template' => $template ?: [
				'id'          => $id,
				'title'       => $meta['title'],
				'description' => $meta['description'],
				'topic'       => $meta['topic'],
				'brand'       => $meta['brand'],
				'preview'     => '',
				'accent'      => $meta['accent'],
				'source'      => 'uploaded',
				'can_delete'  => self::current_user_can_delete($id),
				'author_id'   => (int) $meta['author_id'],
			],
			'message'  => __('Template uploaded.', 'akash-visual-layout-builder'),
		];
	}

	/**
	 * Whether the current user may delete an uploaded template pack.
	 *
	 * Owners can delete their own packs. Users with the plugin settings capability
	 * can delete any uploaded pack (including legacy packs without an author).
	 *
	 * @param string $id Template id.
	 * @return bool
	 */
	public static function current_user_can_delete($id) {
		$id  = sanitize_key($id);
		$all = self::all();
		if ($id === '' || empty($all[ $id ]) || !is_array($all[ $id ])) {
			return false;
		}

		if (Akash_Visual_Layout_Builder_Settings::current_user_can_manage()) {
			return true;
		}

		$author_id = isset($all[ $id ]['author_id']) ? (int) $all[ $id ]['author_id'] : 0;
		$user_id   = get_current_user_id();

		return $author_id > 0 && $user_id > 0 && $author_id === $user_id;
	}

	/**
	 * Delete an uploaded template pack.
	 *
	 * @param string $id Template id.
	 * @return true|WP_Error
	 */
	public static function delete($id) {
		$id = sanitize_key($id);
		$all = self::all();
		if ($id === '' || empty($all[ $id ])) {
			return new WP_Error('akash_visual_layout_builder_template_missing', __('Uploaded template not found.', 'akash-visual-layout-builder'), [ 'status' => 404 ]);
		}

		if (!self::current_user_can_delete($id)) {
			return new WP_Error('akash_visual_layout_builder_template_forbidden', __('You cannot delete this template.', 'akash-visual-layout-builder'), [ 'status' => 403 ]);
		}

		$dir = self::upload_dir();
		if (!is_wp_error($dir)) {
			$folder = trailingslashit($dir['basedir']) . $id;
			if (is_dir($folder)) {
				self::rrmdir($folder);
			}
		}

		unset($all[ $id ]);
		update_option(self::OPTION_KEY, $all, false);
		return true;
	}

	/**
	 * Validate a zip entry path.
	 *
	 * @param string $entry Zip entry name.
	 * @return true|WP_Error
	 */
	private static function validate_zip_entry($entry) {
		$entry = str_replace('\\', '/', (string) $entry);
		if ($entry === '' || substr($entry, -1) === '/') {
			return true;
		}
		if (strpos($entry, '..') !== false || strpos($entry, "\0") !== false) {
			return new WP_Error('akash_visual_layout_builder_zip_unsafe', __('Zip contains an unsafe path.', 'akash-visual-layout-builder'), [ 'status' => 400 ]);
		}
		$ext = strtolower(pathinfo($entry, PATHINFO_EXTENSION));
		if ($ext === '') {
			return true;
		}
		if (in_array($ext, [ 'js', 'css', 'php', 'html', 'htm' ], true)) {
			return new WP_Error(
				'akash_visual_layout_builder_zip_filetype',
				sprintf(
					/* translators: %s: file extension. */
					__('Zip contains a disallowed file type (.%s).', 'akash-visual-layout-builder'),
					$ext
				),
				[ 'status' => 400 ]
			);
		}
		if (!in_array($ext, self::ALLOWED_EXTENSIONS, true)) {
			return new WP_Error(
				'akash_visual_layout_builder_zip_filetype',
				sprintf(
					/* translators: %s: file extension. */
					__('Zip contains a disallowed file type (.%s).', 'akash-visual-layout-builder'),
					$ext
				),
				[ 'status' => 400 ]
			);
		}
		return true;
	}

	/**
	 * Find folder that contains manifest.json (supports a single top-level directory).
	 *
	 * @param string $extract Extracted temp root.
	 * @return string|null
	 */
	private static function find_pack_root($extract) {
		$direct = trailingslashit($extract) . 'manifest.json';
		if (is_file($direct)) {
			return $extract;
		}

		$entries = scandir($extract);
		if (!$entries) {
			return null;
		}

		$dirs = [];
		foreach ($entries as $entry) {
			if ($entry === '.' || $entry === '..') {
				continue;
			}
			$path = trailingslashit($extract) . $entry;
			if (is_dir($path)) {
				$dirs[] = $path;
			}
		}

		if (count($dirs) === 1 && is_file(trailingslashit($dirs[0]) . 'manifest.json')) {
			return $dirs[0];
		}

		return null;
	}

	/**
	 * Copy allowed pack files into the destination folder.
	 *
	 * @param string $src Source pack root.
	 * @param string $dest Destination folder.
	 * @return string Preview filename or empty.
	 */
	private static function copy_pack_files($src, $dest) {
		$preview = '';
		$iterator = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator($src, FilesystemIterator::SKIP_DOTS)
		);

		/** @var SplFileInfo $file */
		foreach ($iterator as $file) {
			if (!$file->isFile()) {
				continue;
			}
			$rel = ltrim(str_replace('\\', '/', substr($file->getPathname(), strlen($src))), '/');
			if ($rel === '' || strpos($rel, '..') !== false) {
				continue;
			}
			// Flat pack only — skip nested paths for safety/simplicity.
			if (strpos($rel, '/') !== false) {
				continue;
			}
			$ext = strtolower(pathinfo($rel, PATHINFO_EXTENSION));
			if (!in_array($ext, self::ALLOWED_EXTENSIONS, true)) {
				continue;
			}

			$target = trailingslashit($dest) . basename($rel);
			copy($file->getPathname(), $target);

			if (in_array($ext, [ 'jpg', 'jpeg', 'png', 'webp', 'gif' ], true)) {
				$base = strtolower(pathinfo($rel, PATHINFO_FILENAME));
				if ($base === 'preview' || $base === 'thumbnail' || $base === 'cover') {
					$preview = basename($rel);
				} elseif ($preview === '') {
					$preview = basename($rel);
				}
			}
		}

		return $preview;
	}

	/**
	 * Read the first existing file from a list of names.
	 *
	 * @param string   $folder Folder path.
	 * @param string[] $names  Candidate filenames.
	 * @return string|null
	 */
	private static function read_first($folder, $names) {
		foreach ($names as $name) {
			$path = trailingslashit($folder) . $name;
			if (is_file($path) && is_readable($path)) {
				$contents = file_get_contents($path);
				return is_string($contents) ? $contents : '';
			}
		}
		return null;
	}

	/**
	 * Sanitize accent color.
	 *
	 * @param string $color Color.
	 * @return string
	 */
	private static function sanitize_accent($color) {
		$color = trim((string) $color);
		if (preg_match('/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/', $color)) {
			return $color;
		}
		return '#2563eb';
	}

	/**
	 * Recursively remove a directory.
	 *
	 * @param string $dir Directory path.
	 */
	private static function rrmdir($dir) {
		if (!is_dir($dir)) {
			return;
		}

		global $wp_filesystem;

		if (!$wp_filesystem) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
			WP_Filesystem();
		}

		if ($wp_filesystem) {
			$wp_filesystem->delete(trailingslashit($dir), true, 'd');
		}
	}
}
