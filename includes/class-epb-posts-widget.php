<?php

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Server-side rendering for dynamic post listing widgets.
 */
class EPB_Posts_Widget {

	/**
	 * Replace post widget placeholders in stored HTML.
	 *
	 * @param string $html    Page HTML.
	 * @param int    $post_id Current post ID.
	 * @return string
	 */
	public static function hydrate_html($html, $post_id = 0) {
		if ($html === '' || strpos($html, 'data-epb-posts') === false) {
			return $html;
		}

		return (string) preg_replace_callback(
			'/<div([^>]*)\sdata-epb-posts=(["\'])(.*?)\2([^>]*)><\/div>/s',
			function ($matches) use ($post_id) {
				$config = json_decode(htmlspecialchars_decode($matches[3], ENT_QUOTES), true);
				if (!is_array($config)) {
					return $matches[0];
				}

				$inner = self::render($config, $post_id);

				return '<div' . $matches[1] . ' data-epb-posts=' . $matches[2] . $matches[3] . $matches[2] . $matches[4] . '>' . $inner . '</div>';
			},
			$html
		);
	}

	/**
	 * Render posts widget markup from config.
	 *
	 * @param array $config  Widget config (layout, query, display).
	 * @param int   $post_id Current post context.
	 * @return string
	 */
	public static function render($config, $post_id = 0) {
		$layout  = sanitize_key($config['layout'] ?? 'grid');
		$query   = isset($config['query']) && is_array($config['query']) ? $config['query'] : [];
		$display = isset($config['display']) && is_array($config['display']) ? $config['display'] : [];

		$args       = self::build_query_args($query);
		$limit      = $args['posts_per_page'];
		$exclude_id = (!empty($query['excludeCurrent']) && $post_id > 0) ? absint($post_id) : 0;

		if ($exclude_id > 0) {
			$args['posts_per_page'] = $limit + 1;
		}

		$posts = get_posts($args);

		if ($exclude_id > 0) {
			$posts = array_values(array_filter($posts, static function ($post) use ($exclude_id) {
				return (int) $post->ID !== $exclude_id;
			}));
			$posts = array_slice($posts, 0, $limit);
		}

		$style_vars = self::style_vars($display, $layout);
		$card_class = self::card_class($display);
		$layout_class = 'epb-posts--' . $layout;

		if (empty($posts)) {
			return '<div class="epb-posts ' . esc_attr($layout_class) . ' ' . esc_attr($card_class) . '" style="' . esc_attr($style_vars) . '"><div class="epb-posts__empty">' . esc_html__('No posts found.', 'wpvisualx') . '</div></div>';
		}

		$items = '';

		foreach ($posts as $index => $post) {
			$items .= self::render_item($post, $display, $layout, $index === 0);
		}

		if ($layout === 'carousel') {
			$items = '<div class="epb-posts epb-posts--carousel ' . esc_attr($card_class) . ' epb-carousel" style="' . esc_attr($style_vars) . '" data-epb-carousel>' . $items .
				'<button type="button" data-epb-carousel-prev aria-label="' . esc_attr__('Previous slide', 'wpvisualx') . '" style="position:absolute;left:8px;top:50%;transform:translateY(-50%);border:none;background:rgba(15,23,42,.7);color:#fff;width:32px;height:32px;cursor:pointer;">‹</button>' .
				'<button type="button" data-epb-carousel-next aria-label="' . esc_attr__('Next slide', 'wpvisualx') . '" style="position:absolute;right:8px;top:50%;transform:translateY(-50%);border:none;background:rgba(15,23,42,.7);color:#fff;width:32px;height:32px;cursor:pointer;">›</button>' .
				'</div>';

			return $items;
		}

		return '<div class="epb-posts ' . esc_attr($layout_class) . ' ' . esc_attr($card_class) . '" style="' . esc_attr($style_vars) . '">' . $items . '</div>';
	}

	/**
	 * Build WP_Query-compatible get_posts args.
	 *
	 * @param array $query Query config.
	 * @return array
	 */
	public static function build_query_args($query) {
		$post_type = sanitize_key($query['postType'] ?? 'post');
		if (!post_type_exists($post_type)) {
			$post_type = 'post';
		}

		$posts_per_page = max(1, min(24, absint($query['postsPerPage'] ?? 6)));
		$offset         = max(0, absint($query['offset'] ?? 0));
		$order          = strtoupper(sanitize_key($query['order'] ?? 'DESC')) === 'ASC' ? 'ASC' : 'DESC';
		$orderby        = sanitize_key($query['orderBy'] ?? 'date');
		$allowed_order  = [ 'date', 'title', 'modified', 'comment_count', 'rand', 'menu_order' ];

		if (!in_array($orderby, $allowed_order, true)) {
			$orderby = 'date';
		}

		$args = [
			'post_type'           => $post_type,
			'post_status'         => 'publish',
			'posts_per_page'      => $posts_per_page,
			'offset'              => $offset,
			'orderby'             => $orderby,
			'order'               => $order,
			'ignore_sticky_posts' => true,
			'suppress_filters'    => false,
		];

		$category_ids = [];
		if (!empty($query['categoryIds']) && is_array($query['categoryIds'])) {
			foreach ($query['categoryIds'] as $id) {
				$id = absint($id);
				if ($id > 0) {
					$category_ids[] = $id;
				}
			}
		}

		if (!empty($category_ids)) {
			if ($post_type === 'post') {
				$args['category__in'] = $category_ids;
			} else {
				$taxonomy = self::find_hierarchical_taxonomy($post_type);
				if ($taxonomy) {
					$args['tax_query'][] = [
						'taxonomy' => $taxonomy,
						'field'    => 'term_id',
						'terms'    => $category_ids,
					];
				}
			}
		}

		$tag_slugs = [];
		if (!empty($query['tagSlugs']) && is_array($query['tagSlugs'])) {
			foreach ($query['tagSlugs'] as $slug) {
				$slug = sanitize_title($slug);
				if ($slug !== '') {
					$tag_slugs[] = $slug;
				}
			}
		}

		if (!empty($tag_slugs)) {
			if ($post_type === 'post') {
				$args['tag_slug__in'] = $tag_slugs;
			} else {
				$taxonomy = self::find_flat_taxonomy($post_type);
				if ($taxonomy) {
					$args['tax_query'][] = [
						'taxonomy' => $taxonomy,
						'field'    => 'slug',
						'terms'    => $tag_slugs,
					];
				}
			}
		}

		if (!empty($args['tax_query']) && count($args['tax_query']) > 1) {
			$args['tax_query']['relation'] = 'AND';
		}

		return $args;
	}

	/**
	 * Render a single post item.
	 *
	 * @param WP_Post $post    Post object.
	 * @param array   $display Display config.
	 * @param string  $layout  Layout type.
	 * @param bool    $active  Active slide (carousel).
	 * @return string
	 */
	private static function render_item($post, $display, $layout, $active = false) {
		$show_image      = !empty($display['showImage']);
		$show_title      = !empty($display['showTitle']);
		$show_excerpt    = !empty($display['showExcerpt']);
		$show_meta       = !empty($display['showMeta']);
		$show_author     = !empty($display['showAuthor']);
		$show_date       = !empty($display['showDate']);
		$show_categories = !empty($display['showCategories']);
		$show_read_more  = !empty($display['showReadMore']);
		$read_more_text  = sanitize_text_field($display['readMoreText'] ?? __('Read more', 'wpvisualx'));
		$excerpt_length  = max(5, min(80, absint($display['excerptLength'] ?? 22)));
		$image_size      = sanitize_key($display['imageSize'] ?? 'medium');
		$title_tag       = in_array($display['titleTag'] ?? 'h3', [ 'h2', 'h3', 'h4' ], true) ? $display['titleTag'] : 'h3';
		$permalink       = get_permalink($post);

		$media = '';
		if ($show_image) {
			$thumb = get_the_post_thumbnail(
				$post,
				in_array($image_size, [ 'thumbnail', 'medium', 'large', 'full' ], true) ? $image_size : 'medium',
				[
					'class'   => 'epb-posts__image',
					'loading' => 'lazy',
					'alt'     => get_the_title($post),
				]
			);
			if ($thumb) {
				$media = '<a class="epb-posts__media" href="' . esc_url($permalink) . '">' . $thumb . '</a>';
			}
		}

		$meta_parts = [];
		if ($show_meta) {
			if ($show_author) {
				$meta_parts[] = esc_html(get_the_author_meta('display_name', $post->post_author));
			}
			if ($show_date) {
				$format = !empty($display['metaDateFormat']) ? $display['metaDateFormat'] : get_option('date_format');
				$meta_parts[] = esc_html(get_the_date($format, $post));
			}
			if ($show_categories) {
				$cats = get_the_category($post->ID);
				if (!empty($cats)) {
					$meta_parts[] = esc_html(implode(', ', wp_list_pluck($cats, 'name')));
				}
			}
		}

		$meta = $meta_parts ? '<div class="epb-posts__meta">' . implode('<span aria-hidden="true"> · </span>', $meta_parts) . '</div>' : '';

		$title = $show_title
			? '<' . $title_tag . ' class="epb-posts__title"><a href="' . esc_url($permalink) . '">' . esc_html(get_the_title($post)) . '</a></' . $title_tag . '>'
			: '';

		$excerpt = $show_excerpt
			? '<p class="epb-posts__excerpt">' . esc_html(wp_trim_words(get_the_excerpt($post), $excerpt_length, '…')) . '</p>'
			: '';

		$read_more = $show_read_more
			? '<a class="epb-posts__read-more" href="' . esc_url($permalink) . '">' . esc_html($read_more_text) . '</a>'
			: '';

		$body = '<div class="epb-posts__body">' . $meta . $title . $excerpt . $read_more . '</div>';
		$item = '<article class="epb-posts__item">' . $media . $body . '</article>';

		if ($layout === 'carousel') {
			$display_style = $active ? 'block' : 'none';
			return '<div class="epb-posts__slide' . ($active ? ' is-active' : '') . '" data-epb-slide style="display:' . esc_attr($display_style) . ';">' . $item . '</div>';
		}

		return $item;
	}

	/**
	 * Inline CSS custom properties for layout.
	 *
	 * @param array  $display Display config.
	 * @param string $layout  Layout type.
	 * @return string
	 */
	private static function style_vars($display, $layout) {
		$cols = max(1, min(6, absint($display['columns'] ?? 3)));
		$gap  = sanitize_text_field($display['gap'] ?? '24px');
		$ratio = sanitize_text_field($display['imageRatio'] ?? '16/9');
		$list_width = sanitize_text_field($display['listImageWidth'] ?? '140px');
		$carousel_height = sanitize_text_field($display['carouselHeight'] ?? '360px');

		$vars = [
			'--epb-posts-gap' => $gap,
			'--epb-posts-cols' => (string) $cols,
			'--epb-posts-image-ratio' => $ratio,
			'--epb-posts-list-image-width' => $list_width,
			'--epb-posts-carousel-height' => $carousel_height,
		];

		$parts = [];
		foreach ($vars as $key => $value) {
			if ($value === '') {
				continue;
			}
			$parts[] = $key . ':' . $value;
		}

		return implode(';', $parts);
	}

	/**
	 * Card style modifier class.
	 *
	 * @param array $display Display config.
	 * @return string
	 */
	private static function card_class($display) {
		$style = sanitize_key($display['cardStyle'] ?? 'card');
		$allowed = [ 'default', 'card', 'minimal', 'overlay' ];

		if (!in_array($style, $allowed, true)) {
			$style = 'card';
		}

		return 'epb-posts--' . $style;
	}

	/**
	 * First public hierarchical taxonomy for a post type.
	 *
	 * @param string $post_type Post type.
	 * @return string|null
	 */
	private static function find_hierarchical_taxonomy($post_type) {
		foreach (get_object_taxonomies($post_type, 'objects') as $taxonomy) {
			if (!empty($taxonomy->public) && !empty($taxonomy->hierarchical)) {
				return $taxonomy->name;
			}
		}

		return null;
	}

	/**
	 * First public flat taxonomy for a post type.
	 *
	 * @param string $post_type Post type.
	 * @return string|null
	 */
	private static function find_flat_taxonomy($post_type) {
		foreach (get_object_taxonomies($post_type, 'objects') as $taxonomy) {
			if (!empty($taxonomy->public) && empty($taxonomy->hierarchical)) {
				return $taxonomy->name;
			}
		}

		return null;
	}
}
