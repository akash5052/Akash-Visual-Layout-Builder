<?php

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Built-in visual starter templates (sections / columns / widgets).
 */
class EPB_Visual_Templates {

	/**
	 * Active template id while assembling inner images.
	 *
	 * @var string
	 */
	private static $tpl_id = '';

	/**
	 * Next inner image slot (1–3). Slot 0 is always the hero/preview.
	 *
	 * @var int
	 */
	private static $tpl_slot = 1;

	/**
	 * Catalog rows for Templates UI.
	 *
	 * @return array<int,array<string,mixed>>
	 */
	public static function catalog() {
		$items = [
			[
				'id'          => 'coffee',
				'title'       => 'Artisan Coffee Shop',
				'description' => 'Warm Fraunces landing: photo hero, menu price-list, story split, specialty cards, counters, hours, brown footer.',
				'topic'       => 'artisan coffee shop cafe',
				'brand'       => 'Roast & Bloom',
				'accent'      => '#8B5E3C',
			],
			[
				'id'          => 'fitness',
				'title'       => 'Fitness Studio',
				'description' => 'Dark Space Grotesk gym: intense hero, class cards, coaches, membership tables, FAQ, rose CTA.',
				'topic'       => 'fitness gym studio',
				'brand'       => 'Pulse Fitness',
				'accent'      => '#E11D48',
			],
			[
				'id'          => 'saas',
				'title'       => 'SaaS Product',
				'description' => 'Indigo SaaS: split product hero, social-proof counters, 2x2 features, screenshot band, pricing, FAQ, demo CTA.',
				'topic'       => 'saas software startup platform',
				'brand'       => 'LaunchPad',
				'accent'      => '#4F46E5',
			],
			[
				'id'          => 'agency',
				'title'       => 'Creative Agency',
				'description' => 'Charcoal agency: asymmetric hero, services list, 6-image case gallery, 4-step process, team, dark testimonial.',
				'topic'       => 'creative design marketing agency',
				'brand'       => 'Northstar Agency',
				'accent'      => '#0F172A',
			],
			[
				'id'          => 'restaurant',
				'title'       => 'Fine Dining Restaurant',
				'description' => 'Forest Playfair dining: photo hero, story, tasting menu, wine cards, gallery, chef, hours+reserve.',
				'topic'       => 'fine dining restaurant',
				'brand'       => 'Maison Verde',
				'accent'      => '#166534',
			],
			[
				'id'          => 'realestate',
				'title'       => 'Real Estate',
				'description' => 'Sky-blue realty: search hero, 3 priced listings, neighborhoods, why-us split, agents, inquiry CTA.',
				'topic'       => 'real estate luxury homes',
				'brand'       => 'Harbor Homes',
				'accent'      => '#0369A1',
			],
			[
				'id'          => 'beauty',
				'title'       => 'Beauty Salon',
				'description' => 'Blush salon: soft hero, services price-list, before/after gallery, stars+review, team, book CTA.',
				'topic'       => 'beauty salon spa',
				'brand'       => 'Luxe Glow',
				'accent'      => '#BE185D',
			],
			[
				'id'          => 'yoga',
				'title'       => 'Yoga & Wellness',
				'description' => 'Sage yoga: calm hero, alternating class splits, teachers, FAQ, soft pricing, signup, teal footer.',
				'topic'       => 'yoga wellness studio',
				'brand'       => 'Stillpoint Yoga',
				'accent'      => '#0D9488',
			],
			[
				'id'          => 'manufacturing',
				'title'       => 'Precision Manufacturing',
				'description' => 'Industrial amber: dark hero, process counters, capabilities, certifications strip, case, plant CTA.',
				'topic'       => 'precision manufacturing CNC factory',
				'brand'       => 'Apex Precision',
				'accent'      => '#B45309',
				'badge'       => 'Industrial',
			],
			[
				'id'          => 'steel',
				'title'       => 'Steel Manufacturing',
				'description' => 'Black/red steel: photo hero, capabilities split, 4-step timeline, projects gallery, quote CTA.',
				'topic'       => 'steel manufacturing mill fabrication',
				'brand'       => 'Forge & Beam',
				'accent'      => '#DC2626',
				'badge'       => 'Industrial',
			],
			[
				'id'          => 'photographer',
				'title'       => 'Photography Studio',
				'description' => 'Minimal gallery-first: 6 images up top, about, 2 packages, process, book CTA, black footer.',
				'topic'       => 'photography studio portrait',
				'brand'       => 'Lens & Light',
				'accent'      => '#1E293B',
			],
			[
				'id'          => 'bakery',
				'title'       => 'Artisan Bakery',
				'description' => 'Warm bakery: pastry hero, 3 featured goods, story, hours, price-list, catering CTA, brown footer.',
				'topic'       => 'bakery pastry bread shop',
				'brand'       => 'Crumb & Crust',
				'accent'      => '#C2410C',
			],
			[
				'id'          => 'law',
				'title'       => 'Law Firm',
				'description' => 'Navy law firm: conservative hero, practice accordion, attorneys, stats, FAQ, consult CTA.',
				'topic'       => 'law firm attorney office',
				'brand'       => 'Sterling & Hale',
				'accent'      => '#1E3A5F',
			],
			[
				'id'          => 'dentist',
				'title'       => 'Dental Clinic',
				'description' => 'Clinical blue dental: friendly hero, services, star reviews, doctor, hours, appointment CTA.',
				'topic'       => 'dental clinic dentist smile',
				'brand'       => 'BrightBite Dental',
				'accent'      => '#0284C7',
			],
			[
				'id'          => 'hotel',
				'title'       => 'Boutique Hotel',
				'description' => 'Gold luxury hotel: room hero, amenities, room packages, gallery, location, reserve, brown footer.',
				'topic'       => 'boutique hotel luxury stay',
				'brand'       => 'The Oak & Anchor',
				'accent'      => '#854D0E',
			],
			[
				'id'          => 'nonprofit',
				'title'       => 'Nonprofit Cause',
				'description' => 'Green nonprofit: impact counters first, mission story, programs, stories, donate CTA.',
				'topic'       => 'nonprofit charity community',
				'brand'       => 'Horizon Collective',
				'accent'      => '#15803D',
			],
			[
				'id'          => 'portfolio',
				'title'       => 'Personal Portfolio',
				'description' => 'Personal portfolio: giant name hero, skills list, project gallery, about, stats, dual contact.',
				'topic'       => 'personal portfolio designer',
				'brand'       => 'Alex Rivera',
				'accent'      => '#7C3AED',
			],
			[
				'id'          => 'education',
				'title'       => 'Online Courses',
				'description' => 'Blue education: hero, course price-tables, curriculum accordion, mentors, stats, enroll CTA.',
				'topic'       => 'online courses education learning',
				'brand'       => 'BrightPath Academy',
				'accent'      => '#2563EB',
			],
			[
				'id'          => 'consulting',
				'title'       => 'Business Consulting',
				'description' => 'Teal consulting: process strip first, hero, services, case testimonial, FAQ, discovery CTA.',
				'topic'       => 'business consulting strategy',
				'brand'       => 'Meridian Advisors',
				'accent'      => '#0F766E',
			],
			[
				'id'          => 'ecommerce',
				'title'       => 'Product Storefront',
				'description' => 'Amber storefront: promo banner, product grid, benefits, shipping/returns, reviews, shop CTA.',
				'topic'       => 'ecommerce product store',
				'brand'       => 'Field & Co.',
				'accent'      => '#B45309',
			],
		];

		foreach ($items as &$item) {
			$item['preview'] = self::media($item['id'], 0);
		}
		unset($item);

		return $items;
	}

	/**
	 * Reserved built-in ids.
	 *
	 * @return string[]
	 */
	public static function reserved_ids() {
		return array_map(function ($item) {
			return $item['id'];
		}, self::catalog());
	}

	/**
	 * Get template pack by id (visual + compiled code + title).
	 *
	 * @param string $id Template id.
	 * @return array{title:string,visual:array,code:array{html:string,css:string,js:string}}|null
	 */
	public static function get($id) {
		$meta = null;
		foreach (self::catalog() as $item) {
			if ($item['id'] === $id) {
				$meta = $item;
				break;
			}
		}
		if (!$meta) {
			return null;
		}

		$visual = self::build_document($id, $meta);
		$code   = EPB_Visual_Compile::compile($visual);

		return [
			'title'  => $meta['brand'],
			'visual' => $visual,
			'code'   => $code,
			'meta'   => $meta,
		];
	}

	/**
	 * Build visual document for a template id.
	 *
	 * @param string $id   Template id.
	 * @param array  $meta Catalog meta.
	 * @return array
	 */
	private static function build_document($id, $meta) {
		$brand  = $meta['brand'];
		$accent = $meta['accent'];
		self::$tpl_id   = $id;
		self::$tpl_slot = 1;
		$hero           = self::media($id, 0);

		switch ($id) {
			case 'coffee':
				return self::page_coffee($brand, $accent, $hero);
			case 'fitness':
				return self::page_fitness($brand, $accent, $hero);
			case 'saas':
				return self::page_saas($brand, $accent, $hero);
			case 'agency':
				return self::page_agency($brand, $accent, $hero);
			case 'restaurant':
				return self::page_restaurant($brand, $accent, $hero);
			case 'realestate':
				return self::page_realestate($brand, $accent, $hero);
			case 'beauty':
				return self::page_beauty($brand, $accent, $hero);
			case 'yoga':
				return self::page_yoga($brand, $accent, $hero);
			case 'manufacturing':
				return self::page_manufacturing($brand, $accent, $hero);
			case 'steel':
				return self::page_steel($brand, $accent, $hero);
			case 'photographer':
				return self::page_photographer($brand, $accent, $hero);
			case 'bakery':
				return self::page_bakery($brand, $accent, $hero);
			case 'law':
				return self::page_law($brand, $accent, $hero);
			case 'dentist':
				return self::page_dentist($brand, $accent, $hero);
			case 'hotel':
				return self::page_hotel($brand, $accent, $hero);
			case 'nonprofit':
				return self::page_nonprofit($brand, $accent, $hero);
			case 'portfolio':
				return self::page_portfolio($brand, $accent, $hero);
			case 'education':
				return self::page_education($brand, $accent, $hero);
			case 'consulting':
				return self::page_consulting($brand, $accent, $hero);
			case 'ecommerce':
				return self::page_ecommerce($brand, $accent, $hero);
			default:
				return self::page_generic($brand, $accent, $hero, $meta['title']);
		}
	}

	/**
	 * Local template image URL. Slot 0 is the card preview / hero.
	 * Called with no args, cycles inner slots 1–3 for the active template.
	 *
	 * @param string|null $id   Template id.
	 * @param int|null    $slot Image slot.
	 * @return string
	 */
	private static function media($id = null, $slot = null) {
		if ($id === null) {
			$id   = self::$tpl_id;
			$slot = self::$tpl_slot;
			self::$tpl_slot = self::$tpl_slot >= 3 ? 1 : self::$tpl_slot + 1;
		}

		$id   = sanitize_key((string) $id);
		$slot = absint($slot);
		if ($id === '') {
			return EPB_PLUGIN_URL . 'assets/images/placeholder.svg';
		}

		$candidates = [
			sprintf('assets/images/templates/%s-%d.jpg', $id, $slot),
			sprintf('assets/images/templates/%s-%d.webp', $id, $slot),
			sprintf('assets/images/templates/%s-%d.png', $id, $slot),
			sprintf('assets/images/templates/%s-0.jpg', $id),
			sprintf('assets/images/templates/%s-0.webp', $id),
			sprintf('assets/images/templates/%s-0.png', $id),
		];

		foreach ($candidates as $rel) {
			if (file_exists(EPB_PLUGIN_DIR . $rel)) {
				return EPB_PLUGIN_URL . $rel;
			}
		}

		return EPB_PLUGIN_URL . 'assets/images/placeholder.svg';
	}

	private static function uid($prefix = 'epb') {
		return $prefix . '_' . substr(md5(uniqid((string) wp_rand(), true)), 0, 8);
	}

	private static function style($overrides = []) {
		$base = [
			'marginTop'      => '0px',
			'marginRight'    => '0px',
			'marginBottom'   => '20px',
			'marginLeft'     => '0px',
			'paddingTop'     => '0px',
			'paddingRight'   => '0px',
			'paddingBottom'  => '0px',
			'paddingLeft'    => '0px',
			'fontFamily'     => '',
			'fontSize'       => '',
			'fontWeight'     => '',
			'lineHeight'     => '',
			'letterSpacing'  => '0',
			'textTransform'  => 'none',
			'borderRadius'   => '0px',
			'borderWidth'    => '0px',
			'borderColor'    => '#e2e8f0',
			'borderStyle'    => 'none',
			'boxShadow'      => 'none',
			'opacity'        => 1,
			'background'     => 'transparent',
			'maxWidth'       => '100%',
			'zIndex'         => '',
		];
		return array_merge($base, $overrides);
	}

	private static function widget($type, $props = []) {
		$base = [
			'id'        => self::uid('w'),
			'type'      => $type,
			'linkUrl'   => '',
			'linkNewTab'=> false,
			'style'     => self::style(),
		];
		if (isset($props['style']) && is_array($props['style'])) {
			$props['style'] = self::style($props['style']);
		}
		return array_merge($base, $props);
	}

	private static function column($width, $children = [], $settings = []) {
		return [
			'id'       => self::uid('col'),
			'type'     => 'column',
			'settings' => array_merge(
				[
					'width'         => $width,
					'padding'       => '12px',
					'background'    => 'transparent',
					'verticalAlign' => 'top',
					'zIndex'        => '',
				],
				$settings
			),
			'children' => $children,
		];
	}

	private static function section($columns, $settings = []) {
		return [
			'id'       => self::uid('sec'),
			'type'     => 'section',
			'settings' => array_merge(
				[
					'fullWidth'       => true,
					'contentWidth'    => 1140,
					'minHeight'       => 0,
					'padding'         => '72px 24px',
					'background'      => '#ffffff',
					'backgroundImage' => '',
					'textColor'       => '#0f172a',
					'gap'             => 24,
					'zIndex'          => '',
				],
				$settings
			),
			'columns'  => $columns,
		];
	}

	private static function doc($sections) {
		return [ 'version' => 1, 'sections' => $sections ];
	}

	private static function heading($text, $tag = 'h2', $align = 'left', $color = '#0f172a', $style = []) {
		return self::widget('heading', [
			'content' => $text,
			'tag'     => $tag,
			'align'   => $align,
			'color'   => $color,
			'style'   => array_merge([ 'fontSize' => $tag === 'h1' ? '48px' : '32px', 'fontWeight' => '700', 'lineHeight' => '1.15', 'marginBottom' => '16px' ], $style),
		]);
	}

	private static function text($content, $align = 'left', $color = '#475569', $style = []) {
		return self::widget('text', [
			'content' => $content,
			'align'   => $align,
			'color'   => $color,
			'style'   => array_merge([ 'fontSize' => '17px', 'lineHeight' => '1.7', 'marginBottom' => '20px' ], $style),
		]);
	}

	private static function button($label, $bg, $align = 'left', $style = []) {
		return self::widget('button', [
			'label'      => $label,
			'url'        => '#',
			'align'      => $align,
			'background' => $bg,
			'textColor'  => '#ffffff',
			'fullWidth'  => false,
			'paddingX'   => '26px',
			'paddingY'   => '14px',
			'style'      => array_merge([ 'fontSize' => '15px', 'fontWeight' => '700', 'borderRadius' => '999px', 'marginBottom' => '12px' ], $style),
		]);
	}

	private static function image($src, $alt = 'Image', $style = []) {
		return self::widget('image', [
			'src'          => $src,
			'alt'          => $alt,
			'width'        => '100%',
			'borderRadius' => '16px',
			'align'        => 'center',
			'objectFit'    => 'cover',
			'style'        => array_merge([ 'marginBottom' => '0px', 'borderRadius' => '16px' ], $style),
		]);
	}

	private static function font($name) {
		$map = [
			'Outfit'           => "'Outfit', system-ui, sans-serif",
			'DM Sans'          => "'DM Sans', system-ui, sans-serif",
			'Space Grotesk'    => "'Space Grotesk', system-ui, sans-serif",
			'Fraunces'         => "'Fraunces', Georgia, serif",
			'Playfair Display' => "'Playfair Display', Georgia, serif",
			'IBM Plex Sans'    => "'IBM Plex Sans', system-ui, sans-serif",
			'Manrope'          => "'Manrope', system-ui, sans-serif",
			'Sora'             => "'Sora', system-ui, sans-serif",
		];
		return $map[ $name ] ?? ("'" . $name . "', system-ui, sans-serif");
	}

	private static function icon_box($icon, $title, $text, $align = 'center', $dark = false) {
		return self::widget('icon-box', [
			'icon'       => $icon,
			'title'      => $title,
			'text'       => $text,
			'align'      => $align,
			'iconSize'   => '34px',
			'titleColor' => $dark ? '#ffffff' : '#0f172a',
			'textColor'  => $dark ? 'rgba(255,255,255,.78)' : '#64748b',
			'style'      => self::style([
				'paddingTop'    => '28px',
				'paddingRight'  => '22px',
				'paddingBottom' => '28px',
				'paddingLeft'   => '22px',
				'background'    => $dark ? 'rgba(255,255,255,.08)' : '#ffffff',
				'borderRadius'  => '18px',
				'boxShadow'     => $dark ? 'none' : '0 14px 40px rgba(15,23,42,.08)',
				'borderWidth'   => $dark ? '1px' : '0px',
				'borderStyle'   => $dark ? 'solid' : 'none',
				'borderColor'   => $dark ? 'rgba(255,255,255,.12)' : '#e2e8f0',
				'marginBottom'  => '0px',
			]),
		]);
	}

	private static function counter($end, $title, $suffix = '', $color = '#0f172a') {
		return self::widget('counter', [
			'prefix'      => '',
			'end'         => $end,
			'suffix'      => $suffix,
			'title'       => $title,
			'duration'    => 1600,
			'align'       => 'center',
			'numberColor' => $color,
			'titleColor'  => '#64748b',
			'style'       => self::style([ 'marginBottom' => '0px' ]),
		]);
	}

	private static function testimonial($content, $name, $role, $avatar = '') {
		return self::widget('testimonial', [
			'content' => $content,
			'name'    => $name,
			'role'    => $role,
			'avatar'  => $avatar,
			'align'   => 'left',
			'style'   => self::style([
				'paddingTop'    => '24px',
				'paddingRight'  => '24px',
				'paddingBottom' => '24px',
				'paddingLeft'   => '24px',
				'background'    => '#ffffff',
				'borderRadius'  => '16px',
				'boxShadow'     => '0 10px 30px rgba(15,23,42,.06)',
				'marginBottom'  => '0px',
			]),
		]);
	}

	private static function price_table($title, $price, $period, $features, $btn, $bg, $featured = false) {
		return self::widget('price-table', [
			'title'            => $title,
			'price'            => $price,
			'period'           => $period,
			'features'         => implode("\n", $features),
			'buttonLabel'      => $btn,
			'buttonUrl'        => '#',
			'featured'         => $featured,
			'background'       => $bg,
			'buttonBackground' => '#0f172a',
			'style'            => self::style([
				'borderRadius' => '18px',
				'boxShadow'    => $featured ? '0 18px 40px rgba(15,23,42,.12)' : '0 10px 30px rgba(15,23,42,.06)',
				'marginBottom' => '0px',
			]),
		]);
	}

	private static function cta($title, $text, $label, $bg) {
		return self::widget('call-to-action', [
			'title'       => $title,
			'text'        => $text,
			'buttonLabel' => $label,
			'buttonUrl'   => '#',
			'background'  => $bg,
			'image'       => '',
			'style'       => self::style([ 'borderRadius' => '20px', 'marginBottom' => '0px' ]),
		]);
	}

	private static function team($name, $role, $bio, $image) {
		return self::widget('team', [
			'name'  => $name,
			'role'  => $role,
			'bio'   => $bio,
			'image' => $image,
			'align' => 'center',
			'style' => self::style([
				'paddingTop'    => '20px',
				'paddingRight'  => '16px',
				'paddingBottom' => '20px',
				'paddingLeft'   => '16px',
				'background'    => '#ffffff',
				'borderRadius'  => '16px',
				'marginBottom'  => '0px',
			]),
		]);
	}

	private static function accordion($items) {
		return self::widget('accordion', [
			'items' => $items,
			'style' => self::style([ 'marginBottom' => '0px' ]),
		]);
	}

	private static function gallery($images, $columns = 3) {
		return self::widget('gallery', [
			'images'  => $images,
			'columns' => $columns,
			'gap'     => '14px',
			'style'   => self::style([ 'marginBottom' => '0px' ]),
		]);
	}

	private static function dual($left, $right, $leftBg, $rightBg) {
		return self::widget('dual-button', [
			'leftLabel'       => $left,
			'leftUrl'         => '#',
			'leftBackground'  => $leftBg,
			'rightLabel'      => $right,
			'rightUrl'        => '#',
			'rightBackground' => $rightBg,
			'align'           => 'left',
			'style'           => self::style([ 'marginBottom' => '0px' ]),
		]);
	}

	private static function hours($items) {
		return self::widget('business-hours', [
			'items' => $items,
			'style' => self::style([
				'paddingTop'    => '8px',
				'paddingRight'  => '8px',
				'paddingBottom' => '8px',
				'paddingLeft'   => '8px',
				'marginBottom'  => '0px',
			]),
		]);
	}

	/**
	 * Editable top navigation bar (logo + links + CTA).
	 *
	 * @param string $brand  Brand name.
	 * @param string $accent Accent color.
	 * @param array  $links  Link labels.
	 * @param string $cta    CTA label.
	 * @param string $bg     Background.
	 * @param string $color  Text color.
	 * @param string $font   Font key.
	 * @return array
	 */
	private static function site_header($brand, $accent, $links, $cta = 'Get Started', $bg = '#ffffff', $color = '#0f172a', $font = 'Outfit') {
		$f   = self::font($font);
		$nav = implode('   ·   ', $links);
		return self::section(
			[
				self::column(26, [
					self::heading($brand, 'h3', 'left', $color, [
						'fontFamily' => $f, 'fontSize' => '20px', 'fontWeight' => '800', 'marginBottom' => '0px',
					]),
				], [ 'verticalAlign' => 'middle', 'padding' => '4px 8px' ]),
				self::column(49, [
					self::text($nav, 'center', $color, [
						'fontFamily' => $f, 'fontSize' => '14px', 'fontWeight' => '600', 'marginBottom' => '0px', 'letterSpacing' => '0.02em',
					]),
				], [ 'verticalAlign' => 'middle', 'padding' => '4px 8px' ]),
				self::column(25, [
					self::button($cta, $accent, 'right', [
						'fontFamily' => $f, 'borderRadius' => '999px', 'marginBottom' => '0px', 'fontSize' => '13px',
					]),
				], [ 'verticalAlign' => 'middle', 'padding' => '4px 8px' ]),
			],
			[
				'padding'      => '16px 24px',
				'background'   => $bg,
				'textColor'    => $color,
				'contentWidth' => 1140,
				'gap'          => 8,
				'minHeight'    => 0,
			]
		);
	}

	/**
	 * Editable multi-column footer.
	 *
	 * @param string $brand   Brand.
	 * @param string $accent  Accent.
	 * @param string $blurb   About blurb.
	 * @param array  $cols    Extra columns [title => [lines...]].
	 * @param string $bg      Background.
	 * @param string $color   Text color.
	 * @param string $muted   Muted text.
	 * @param string $font    Font key.
	 * @return array
	 */
	private static function site_footer($brand, $accent, $blurb, $cols = [], $bg = '#0f172a', $color = '#ffffff', $muted = 'rgba(255,255,255,.72)', $font = 'Outfit') {
		$f        = self::font($font);
		$columns  = [];
		$columns[] = self::column(34, [
			self::heading($brand, 'h3', 'left', $color, [ 'fontFamily' => $f, 'fontSize' => '22px', 'fontWeight' => '800', 'marginBottom' => '12px' ]),
			self::text($blurb, 'left', $muted, [ 'fontFamily' => $f, 'fontSize' => '15px', 'lineHeight' => '1.7', 'marginBottom' => '16px' ]),
			self::button('Contact us', $accent, 'left', [ 'fontFamily' => $f, 'borderRadius' => '999px', 'marginBottom' => '0px' ]),
		], [ 'padding' => '12px' ]);

		$col_items = array_slice($cols, 0, 3, true);
		$remain    = max(1, count($col_items));
		$w         = (int) floor(66 / $remain);
		$i         = 0;
		foreach ($col_items as $title => $lines) {
			$i++;
			$width = $i === $remain ? 66 - ($w * ($remain - 1)) : $w;
			$list  = [];
			foreach ((array) $lines as $line) {
				$list[] = [ 'icon' => '›', 'text' => $line ];
			}
			$columns[] = self::column($width, [
				self::heading($title, 'h4', 'left', $color, [ 'fontFamily' => $f, 'fontSize' => '15px', 'fontWeight' => '700', 'marginBottom' => '14px', 'textTransform' => 'uppercase', 'letterSpacing' => '0.06em' ]),
				self::widget('icon-list', [
					'items' => $list,
					'color' => $muted,
					'style' => self::style([ 'marginBottom' => '0px', 'fontFamily' => $f, 'fontSize' => '14px' ]),
				]),
			], [ 'padding' => '12px' ]);
		}

		$legal = self::section(
			[ self::column(100, [
				self::divider('rgba(255,255,255,.15)', 1),
				self::text('© ' . gmdate('Y') . ' ' . $brand . ' · Built with WPVisualX · All content is editable', 'center', $muted, [
					'fontFamily' => $f, 'fontSize' => '13px', 'marginBottom' => '0px',
				]),
			]) ],
			[ 'padding' => '8px 24px 28px', 'background' => $bg, 'textColor' => $color, 'gap' => 8 ]
		);

		return [
			self::section($columns, [
				'padding'      => '64px 24px 28px',
				'background'   => $bg,
				'textColor'    => $color,
				'contentWidth' => 1140,
				'gap'          => 20,
			]),
			$legal,
		];
	}

	private static function divider($color = '#e2e8f0', $thickness = 1) {
		return self::widget('divider', [
			'color'     => $color,
			'thickness' => $thickness,
			'width'     => '100%',
			'align'     => 'center',
			'style'     => self::style([ 'marginTop' => '8px', 'marginBottom' => '16px' ]),
		]);
	}

	private static function hero_section($brand, $headline, $sub, $cta, $accent, $image, $font = 'Outfit') {
		$family = self::font($font);
		return self::section(
			[
				self::column(58, [
					self::heading($brand, 'h1', 'left', 'rgba(255,255,255,.78)', [
						'fontFamily'     => $family,
						'fontSize'       => '13px',
						'fontWeight'     => '700',
						'letterSpacing'  => '0.14em',
						'textTransform'  => 'uppercase',
						'marginBottom'   => '20px',
					]),
					self::heading($headline, 'h1', 'left', '#ffffff', [
						'fontFamily'   => $family,
						'fontSize'     => '58px',
						'fontWeight'   => '800',
						'lineHeight'   => '1.05',
						'marginBottom' => '20px',
					]),
					self::text($sub, 'left', 'rgba(255,255,255,.9)', [
						'fontFamily' => $family,
						'fontSize'   => '18px',
						'maxWidth'   => '540px',
						'lineHeight' => '1.7',
					]),
					self::dual($cta, 'Learn more', $accent, 'rgba(255,255,255,.14)'),
				], [ 'verticalAlign' => 'middle', 'padding' => '24px 16px' ]),
				self::column(42, [
					self::image($image, $brand, [
						'borderRadius' => '24px',
						'boxShadow'    => '0 28px 70px rgba(0,0,0,.45)',
					]),
				], [ 'verticalAlign' => 'middle', 'padding' => '24px 12px' ]),
			],
			[
				'padding'         => '96px 24px',
				'background'      => 'linear-gradient(135deg, #0b1220 0%, #1e293b 55%, #334155 100%)',
				'backgroundImage' => $image,
				'textColor'       => '#ffffff',
				'minHeight'       => '640px',
				'gap'             => 36,
			]
		);
	}

	private static function features_section($title, $boxes, $bg = '#f8fafc', $title_color = '#0f172a', $text_color = '#64748b', $dark_cards = false) {
		$children = [
			self::heading($title, 'h2', 'center', $title_color, [ 'fontFamily' => self::font('Outfit'), 'fontSize' => '36px', 'marginBottom' => '10px' ]),
			self::text('Every block is editable — click any heading, text, image, or button to customize.', 'center', $text_color, [ 'maxWidth' => '620px', 'marginBottom' => '28px' ]),
		];
		// Build feature cards in a full-width follow-up section for cleaner layout.
		$cols = [];
		$count = max(1, count($boxes));
		$width = (int) floor(100 / $count);
		foreach ($boxes as $i => $box) {
			$w = $i === $count - 1 ? 100 - ($width * ($count - 1)) : $width;
			$cols[] = self::column($w, [ self::icon_box($box[0], $box[1], $box[2], 'center', $dark_cards) ]);
		}
		return [
			self::section(
				[ self::column(100, $children) ],
				[ 'padding' => '80px 24px 12px', 'background' => $bg, 'gap' => 0, 'textColor' => $title_color ]
			),
			self::section($cols, [ 'padding' => '12px 24px 88px', 'background' => $bg, 'gap' => 22, 'textColor' => $title_color ]),
		];
	}

	private static function stats_section($stats, $accent, $bg = '#0f172a') {
		$cols = [];
		foreach ($stats as $stat) {
			$cols[] = self::column((int) floor(100 / max(1, count($stats))), [
				self::counter($stat[0], $stat[1], $stat[2] ?? '', $accent),
			]);
		}
		return self::section($cols, [
			'padding'    => '64px 24px',
			'background' => $bg,
			'textColor'  => '#ffffff',
			'gap'        => 16,
		]);
	}

	private static function testimonials_section($items, $bg = '#f8fafc') {
		$cols = [];
		foreach ($items as $item) {
			$cols[] = self::column((int) floor(100 / max(1, count($items))), [
				self::testimonial($item[0], $item[1], $item[2], $item[3] ?? ''),
			]);
		}
		return [
			self::section(
				[ self::column(100, [
					self::heading('What people say', 'h2', 'center', '#0f172a', [ 'fontFamily' => self::font('Outfit') ]),
				]) ],
				[ 'padding' => '72px 24px 16px', 'background' => $bg ]
			),
			self::section($cols, [ 'padding' => '16px 24px 80px', 'background' => $bg, 'gap' => 20 ]),
		];
	}

	private static function pricing_section($plans, $bg = '#ffffff') {
		$cols = [];
		foreach ($plans as $plan) {
			$cols[] = self::column((int) floor(100 / max(1, count($plans))), [
				self::price_table($plan[0], $plan[1], $plan[2], $plan[3], $plan[4], $plan[5], !empty($plan[6])),
			]);
		}
		return [
			self::section(
				[ self::column(100, [
					self::heading('Simple pricing', 'h2', 'center', '#0f172a', [ 'fontFamily' => self::font('Outfit') ]),
					self::text('Pick a starting plan — every card is editable in the visual builder.', 'center', '#64748b'),
				]) ],
				[ 'padding' => '72px 24px 16px', 'background' => $bg ]
			),
			self::section($cols, [ 'padding' => '16px 24px 80px', 'background' => $bg, 'gap' => 20 ]),
		];
	}

	private static function cta_section($title, $text, $label, $bg) {
		return self::section(
			[ self::column(100, [ self::cta($title, $text, $label, $bg) ]) ],
			[ 'padding' => '40px 24px 80px', 'background' => '#ffffff' ]
		);
	}

	private static function flatten($parts) {
		$out = [];
		foreach ($parts as $part) {
			if (isset($part['type']) && $part['type'] === 'section') {
				$out[] = $part;
			} elseif (is_array($part)) {
				foreach ($part as $inner) {
					if (isset($inner['type']) && $inner['type'] === 'section') {
						$out[] = $inner;
					}
				}
			}
		}
		return $out;
	}

	/* ---------- page builders ---------- */

	private static function page_generic($brand, $accent, $hero, $title) {
		$f = self::font('DM Sans');
		return self::doc(self::flatten([
			self::site_header($brand, $accent, ['Home', 'Features', 'About', 'Contact'], 'Get Started', '#ffffff', '#0f172a', 'DM Sans'),
			self::section(
				[ self::column(100, [
					self::heading($brand, 'h1', 'center', $accent, [
						'fontFamily' => $f, 'fontSize' => '13px', 'fontWeight' => '700',
						'letterSpacing' => '0.18em', 'textTransform' => 'uppercase', 'marginBottom' => '18px',
					]),
					self::heading($title . "\nBuilt to edit.", 'h1', 'center', '#0f172a', [
						'fontFamily' => $f, 'fontSize' => '56px', 'fontWeight' => '800', 'marginBottom' => '18px', 'lineHeight' => '1.08',
					]),
					self::text('A polished starter layout — swap copy, images, and colors, then publish.', 'center', '#64748b', [
						'fontFamily' => $f, 'maxWidth' => '520px', 'marginBottom' => '28px', 'fontSize' => '18px',
					]),
					self::button('Get started', $accent, 'center', [ 'borderRadius' => '10px' ]),
				], [ 'verticalAlign' => 'middle' ]) ],
				[
					'padding' => '120px 24px 88px', 'background' => 'linear-gradient(160deg, #f8fafc 0%, #e2e8f0 100%)',
					'minHeight' => '560px',
				]
			),
			self::section(
				[
					self::column(25, [ self::counter(12, 'Sections', '+', $accent) ]),
					self::column(25, [ self::counter(40, 'Widgets', '+', $accent) ]),
					self::column(25, [ self::counter(100, 'Editable', '%', $accent) ]),
					self::column(25, [ self::counter(1, 'Click to customize', '', $accent) ]),
				],
				[ 'padding' => '48px 24px', 'background' => '#0f172a', 'gap' => 12, 'textColor' => '#ffffff' ]
			),
			self::section(
				[
					self::column(52, [
						self::heading('What you can edit', 'h2', 'left', '#0f172a', [ 'fontFamily' => $f, 'fontSize' => '34px' ]),
						self::widget('icon-list', [
							'items' => [
								[ 'icon' => '✓', 'text' => 'Headings, text, and buttons' ],
								[ 'icon' => '✓', 'text' => 'Images and section backgrounds' ],
								[ 'icon' => '✓', 'text' => 'Colors, spacing, and fonts' ],
								[ 'icon' => '✓', 'text' => 'Header, footer, and CTAs' ],
							],
							'color' => '#0f172a',
							'style' => self::style([ 'marginBottom' => '0px' ]),
						]),
					], [ 'verticalAlign' => 'middle' ]),
					self::column(48, [
						self::image($hero, $brand, [ 'borderRadius' => '16px' ]),
					], [ 'verticalAlign' => 'middle' ]),
				],
				[ 'padding' => '72px 24px', 'background' => '#ffffff', 'gap' => 36 ]
			),
			self::section(
				[
					self::column(33, [ self::icon_box('1', 'Sections', 'Stack full-width bands with unique backgrounds and rhythm.') ]),
					self::column(33, [ self::icon_box('2', 'Widgets', 'Headings, galleries, pricing, FAQs, and more.') ]),
					self::column(34, [ self::icon_box('3', 'Publish', 'Export clean HTML, CSS, and JS when ready.') ]),
				],
				[ 'padding' => '64px 24px', 'background' => '#f1f5f9', 'gap' => 20 ]
			),
			self::section(
				[
					self::column(50, [ self::testimonial('Swapped images and copy in minutes — the layout held together.', 'Jordan P.', 'Studio owner') ]),
					self::column(50, [ self::testimonial('Perfect starter for client sites that need a full page, not a stub.', 'Riley K.', 'Freelancer') ]),
				],
				[ 'padding' => '64px 24px', 'background' => '#eef2ff', 'gap' => 20 ]
			),
			self::section(
				[ self::column(100, [
					self::cta('Ready when you are', 'Open the visual builder and make it yours.', 'Publish page', $accent),
				]) ],
				[ 'padding' => '48px 24px 80px', 'background' => '#ffffff' ]
			),
			self::site_footer($brand, $accent, 'A flexible starter page you can reshape for any brand.', [
				'Explore' => ['Features', 'Templates', 'Docs'],
				'Company' => ['About', 'Contact', 'Support'],
				'Follow' => ['Twitter', 'LinkedIn', 'Newsletter'],
			], '#0f172a', '#ffffff', 'rgba(255,255,255,.72)', 'DM Sans'),
		]));
	}

	private static function page_coffee($brand, $accent, $hero) {
		$f = self::font('Fraunces');
		return self::doc(self::flatten([
			self::site_header($brand, $accent, ['Home', 'Menu', 'Story', 'Visit'], 'Reserve', '#F7F1E8', '#3F2A1D', 'Fraunces'),
			self::section(
				[ self::column(100, [
					self::heading($brand, 'h1', 'center', '#F5E6D3', [
						'fontFamily' => $f, 'fontSize' => '14px', 'fontWeight' => '600',
						'letterSpacing' => '0.2em', 'textTransform' => 'uppercase', 'marginBottom' => '22px',
					]),
					self::heading("Slow mornings.\nBetter coffee.", 'h1', 'center', '#ffffff', [
						'fontFamily' => $f, 'fontSize' => '60px', 'fontWeight' => '700', 'lineHeight' => '1.08', 'marginBottom' => '20px',
					]),
					self::text('Single-origin beans, handcrafted drinks, and a warm corner for your next conversation.', 'center', 'rgba(255,255,255,.9)', [
						'fontFamily' => $f, 'fontSize' => '19px', 'maxWidth' => '540px', 'marginBottom' => '28px',
					]),
					self::button('Reserve a table', $accent, 'center', [ 'borderRadius' => '999px', 'fontFamily' => $f ]),
				], [ 'verticalAlign' => 'middle' ]) ],
				[
					'padding' => '140px 24px 120px', 'minHeight' => '680px', 'textColor' => '#ffffff',
					'background' => 'linear-gradient(180deg, rgba(63,42,29,.45) 0%, rgba(63,42,29,.78) 100%)',
					'backgroundImage' => $hero,
				]
			),
			self::section(
				[ self::column(100, [
					self::heading('Cafe menu', 'h2', 'center', '#3F2A1D', [ 'fontFamily' => $f, 'fontSize' => '38px', 'marginBottom' => '28px' ]),
					self::widget('price-list', [
						'items' => [
							[ 'title' => 'House espresso', 'price' => '$3.50', 'description' => 'Balanced shots pulled fresh all day' ],
							[ 'title' => 'Oat flat white', 'price' => '$5.00', 'description' => 'Silky microfoam, single origin' ],
							[ 'title' => 'Pour-over', 'price' => '$5.50', 'description' => 'Rotating filter roast, 12oz' ],
							[ 'title' => 'Butter croissant', 'price' => '$4.25', 'description' => 'Baked every morning' ],
							[ 'title' => 'Seasonal tart', 'price' => '$6.00', 'description' => 'Ask about today\'s fruit' ],
						],
						'style' => self::style([ 'marginBottom' => '0px', 'fontFamily' => $f ]),
					]),
				]) ],
				[ 'padding' => '80px 24px', 'background' => '#FFF8F0', 'contentWidth' => 720 ]
			),
			self::section(
				[
					self::column(48, [
						self::image(self::media(), 'Latte art', [ 'borderRadius' => '20px' ]),
					], [ 'verticalAlign' => 'middle' ]),
					self::column(52, [
						self::heading('Our story', 'h2', 'left', '#3F2A1D', [ 'fontFamily' => $f, 'fontSize' => '36px' ]),
						self::text('We roast in small batches and brew with patience. Every cup starts with growers we know by name and ends with a seat by the window.', 'left', '#5B4636', [
							'fontFamily' => $f, 'fontSize' => '17px',
						]),
						self::text('Dog-friendly patio · Free Wi-Fi · Beans to take home.', 'left', '#8B5E3C', [
							'fontFamily' => $f, 'fontSize' => '15px', 'fontWeight' => '600',
						]),
					], [ 'verticalAlign' => 'middle', 'padding' => '24px 16px' ]),
				],
				[ 'padding' => '80px 24px', 'background' => '#EDE4D8', 'gap' => 36 ]
			),
			self::section(
				[
					self::column(33, [ self::icon_box('☕', 'House roast', 'Small-batch espresso blend, roasted weekly.') ]),
					self::column(33, [ self::icon_box('🌱', 'Single origin', 'Rotating filter lots from trusted farms.') ]),
					self::column(34, [ self::icon_box('🥐', 'Pastry case', 'Buttery laminates baked before open.') ]),
				],
				[ 'padding' => '64px 24px', 'background' => '#FFFDF9', 'gap' => 20 ]
			),
			self::section(
				[
					self::column(25, [ self::counter(12, 'Origins sourced', '', '#F5E6D3') ]),
					self::column(25, [ self::counter(6, 'Roasts / week', '', '#F5E6D3') ]),
					self::column(25, [ self::counter(40, 'Seats inside', '', '#F5E6D3') ]),
					self::column(25, [ self::counter(2014, 'Established', '', '#F5E6D3') ]),
				],
				[ 'padding' => '56px 24px', 'background' => '#3F2A1D', 'gap' => 12, 'textColor' => '#ffffff' ]
			),
			self::section(
				[ self::column(100, [
					self::testimonial('The flat white is perfect and the patio feels like a secret garden.', 'Elena M.', 'Regular', self::media()),
				]) ],
				[ 'padding' => '72px 24px', 'background' => '#F7F1E8', 'contentWidth' => 760 ]
			),
			self::section(
				[
					self::column(50, [
						self::heading('Hours', 'h2', 'left', '#3F2A1D', [ 'fontFamily' => $f, 'fontSize' => '28px' ]),
						self::hours([
							[ 'day' => 'Mon - Fri', 'hours' => '7:00 AM - 6:00 PM' ],
							[ 'day' => 'Saturday', 'hours' => '8:00 AM - 5:00 PM' ],
							[ 'day' => 'Sunday', 'hours' => '8:00 AM - 2:00 PM' ],
						]),
					]),
					self::column(50, [
						self::heading('Find us', 'h2', 'left', '#3F2A1D', [ 'fontFamily' => $f, 'fontSize' => '28px' ]),
						self::text("142 Market Street · Downtown\nTwo blocks from the transit plaza. Street parking and bike racks out front.", 'left', '#5B4636', [ 'fontFamily' => $f ]),
						self::button('Get directions', $accent, 'left', [ 'borderRadius' => '999px' ]),
					]),
				],
				[ 'padding' => '72px 24px', 'background' => '#FFF8F0', 'gap' => 28 ]
			),
			self::site_footer($brand, $accent, 'Neighborhood coffee roasted with care — come for the cup, stay for the calm.', [
				'Explore' => ['Menu', 'Beans', 'Catering'],
				'Visit' => ['142 Market St', 'Open daily', 'hello@roastbloom.com'],
				'Follow' => ['Instagram', 'Facebook', 'Newsletter'],
			], '#3F2A1D', '#ffffff', 'rgba(255,255,255,.72)', 'Fraunces'),
		]));
	}

	private static function page_fitness($brand, $accent, $hero) {
		$f = self::font('Space Grotesk');
		return self::doc(self::flatten([
			self::site_header($brand, $accent, ['Home', 'Classes', 'Coaches', 'Membership'], 'Join', '#0a0a0a', '#ffffff', 'Space Grotesk'),
			self::section(
				[ self::column(100, [
					self::heading($brand, 'h1', 'left', '#E11D48', [
						'fontFamily' => $f, 'fontSize' => '13px', 'fontWeight' => '700',
						'letterSpacing' => '0.2em', 'textTransform' => 'uppercase', 'marginBottom' => '18px',
					]),
					self::heading("Train harder.\nFeel stronger.", 'h1', 'left', '#ffffff', [
						'fontFamily' => $f, 'fontSize' => '64px', 'fontWeight' => '800', 'lineHeight' => '1.0', 'marginBottom' => '20px',
					]),
					self::text('Classes, coaching, and a community that keeps you accountable — no fluff, no excuses.', 'left', 'rgba(255,255,255,.88)', [
						'fontFamily' => $f, 'fontSize' => '18px', 'maxWidth' => '480px',
					]),
					self::button('Start free week', $accent, 'left', [ 'borderRadius' => '6px', 'fontFamily' => $f ]),
				], [ 'verticalAlign' => 'middle', 'padding' => '32px 20px' ]) ],
				[
					'padding' => '130px 24px', 'minHeight' => '700px',
					'background' => 'linear-gradient(105deg, rgba(10,10,10,.94) 0%, rgba(10,10,10,.55) 55%, rgba(225,29,72,.35) 100%)',
					'backgroundImage' => $hero, 'textColor' => '#ffffff',
				]
			),
			self::section(
				[
					self::column(33, [
						self::heading('Strength', 'h3', 'left', '#ffffff', [ 'fontFamily' => $f, 'fontSize' => '24px', 'marginBottom' => '10px' ]),
						self::text("Mon / Wed / Fri · 6:00 AM & 6:30 PM\nBarbell blocks + accessory work.", 'left', '#94a3b8', [ 'fontFamily' => $f, 'fontSize' => '15px' ]),
						self::heading('45 min', 'h3', 'left', '#E11D48', [ 'fontFamily' => $f, 'fontSize' => '13px', 'letterSpacing' => '0.1em', 'textTransform' => 'uppercase' ]),
					], [ 'padding' => '28px', 'background' => '#1a1a1a' ]),
					self::column(33, [
						self::heading('HIIT Burn', 'h3', 'left', '#ffffff', [ 'fontFamily' => $f, 'fontSize' => '24px', 'marginBottom' => '10px' ]),
						self::text("Tue / Thu · 12:15 PM & 7:00 PM\nIntervals that leave nothing on the floor.", 'left', '#94a3b8', [ 'fontFamily' => $f, 'fontSize' => '15px' ]),
						self::heading('30 min', 'h3', 'left', '#E11D48', [ 'fontFamily' => $f, 'fontSize' => '13px', 'letterSpacing' => '0.1em', 'textTransform' => 'uppercase' ]),
					], [ 'padding' => '28px', 'background' => '#1a1a1a' ]),
					self::column(34, [
						self::heading('Mobility', 'h3', 'left', '#ffffff', [ 'fontFamily' => $f, 'fontSize' => '24px', 'marginBottom' => '10px' ]),
						self::text("Sat · 9:00 AM\nRecover smarter between hard days.", 'left', '#94a3b8', [ 'fontFamily' => $f, 'fontSize' => '15px' ]),
						self::heading('40 min', 'h3', 'left', '#E11D48', [ 'fontFamily' => $f, 'fontSize' => '13px', 'letterSpacing' => '0.1em', 'textTransform' => 'uppercase' ]),
					], [ 'padding' => '28px', 'background' => '#1a1a1a' ]),
				],
				[ 'padding' => '64px 24px', 'background' => '#111111', 'gap' => 18, 'textColor' => '#ffffff' ]
			),
			self::section(
				[
					self::column(33, [ self::team('Maya Cruz', 'Strength Lead', 'Olympic lifting & programming.', self::media()) ]),
					self::column(33, [ self::team('Jordan Lee', 'HIIT Coach', 'Conditioning that scales to you.', self::media()) ]),
					self::column(34, [ self::team('Sam Park', 'Mobility', 'Recovery that unlocks PRs.', self::media()) ]),
				],
				[ 'padding' => '72px 24px', 'background' => '#fafafa', 'gap' => 20 ]
			),
			self::section(
				[
					self::column(33, [ self::price_table('Drop-in', '$25', '/class', [ 'Any group class', 'Locker access', 'No contract' ], 'Book class', '#1a1a1a') ]),
					self::column(33, [ self::price_table('Unlimited', '$89', '/mo', [ 'All classes', '2 coaching sessions', 'App tracking' ], 'Join now', '#2a0a12', true) ]),
					self::column(34, [ self::price_table('Personal', '$199', '/mo', [ '4 PT sessions', 'Custom plan', 'Nutrition guide' ], 'Talk to coach', '#1a1a1a') ]),
				],
				[ 'padding' => '72px 24px', 'background' => '#111111', 'gap' => 18, 'textColor' => '#ffffff' ]
			),
			self::section(
				[
					self::column(25, [ self::counter(1200, 'Members', '+', '#E11D48') ]),
					self::column(25, [ self::counter(48, 'Classes / week', '', '#E11D48') ]),
					self::column(25, [ self::counter(12, 'Coaches', '', '#E11D48') ]),
					self::column(25, [ self::counter(92, 'Member PR rate', '%', '#E11D48') ]),
				],
				[ 'padding' => '56px 24px', 'background' => '#1c1917', 'gap' => 12, 'textColor' => '#ffffff' ]
			),
			self::section(
				[
					self::column(40, [
						self::heading('FAQ', 'h2', 'left', '#0f172a', [ 'fontFamily' => $f, 'fontSize' => '34px' ]),
						self::text('Straight answers before you drop in.', 'left', '#64748b', [ 'fontFamily' => $f ]),
					]),
					self::column(60, [
						self::accordion([
							[ 'title' => 'Do I need experience?', 'content' => 'No. Coaches scale every class to your level on day one.' ],
							[ 'title' => 'What should I bring?', 'content' => 'Shoes, water, and a towel. We have mats and racks ready.' ],
							[ 'title' => 'Is the free week really free?', 'content' => 'Yes — full class access for 7 days, no card required.' ],
						]),
					]),
				],
				[ 'padding' => '72px 24px', 'background' => '#ffffff', 'gap' => 28 ]
			),
			self::section(
				[ self::column(100, [
					self::heading('Your next PR starts here.', 'h2', 'center', '#ffffff', [ 'fontFamily' => $f, 'fontSize' => '44px' ]),
					self::text('Claim a free week. Show up ready.', 'center', '#fda4af', [ 'fontFamily' => $f ]),
					self::button('Claim free week', '#0a0a0a', 'center', [ 'borderRadius' => '6px', 'fontSize' => '16px' ]),
				]) ],
				[ 'padding' => '88px 24px', 'background' => '#E11D48', 'textColor' => '#ffffff' ]
			),
			self::site_footer($brand, $accent, 'Train with intention. Recover with purpose. Belong to a crew that shows up.', [
				'Train' => ['Classes', 'Personal training', 'App'],
				'Studio' => ['12 Pulse Ave', 'Open 5AM-10PM', 'hello@pulse.fit'],
				'Follow' => ['Instagram', 'YouTube', 'Newsletter'],
			], '#0a0a0a', '#ffffff', 'rgba(255,255,255,.72)', 'Space Grotesk'),
		]));
	}

	private static function page_saas($brand, $accent, $hero) {
		$f = self::font('DM Sans');
		return self::doc(self::flatten([
			self::site_header($brand, $accent, ['Product', 'Pricing', 'Customers', 'Docs'], 'Start free', '#ffffff', '#0f172a', 'DM Sans'),
			self::section(
				[
					self::column(52, [
						self::heading($brand . ' · Product', 'h1', 'left', '#4F46E5', [
							'fontFamily' => $f, 'fontSize' => '13px', 'fontWeight' => '700',
							'letterSpacing' => '0.12em', 'textTransform' => 'uppercase', 'marginBottom' => '16px',
						]),
						self::heading("Ship campaigns\nfaster", 'h1', 'left', '#0f172a', [
							'fontFamily' => $f, 'fontSize' => '56px', 'fontWeight' => '800', 'lineHeight' => '1.06', 'marginBottom' => '18px',
						]),
						self::text('Plan, launch, and measure growth workflows in one calm workspace built for modern teams.', 'left', '#475569', [
							'fontFamily' => $f, 'fontSize' => '18px', 'maxWidth' => '480px',
						]),
						self::dual('Start free trial', 'Watch demo', $accent, '#e2e8f0'),
					], [ 'verticalAlign' => 'middle', 'padding' => '20px 12px' ]),
					self::column(48, [
						self::image($hero, 'Product screenshot', [
							'borderRadius' => '14px',
							'boxShadow' => '0 28px 70px rgba(79,70,229,.22)',
						]),
					], [ 'verticalAlign' => 'middle' ]),
				],
				[ 'padding' => '96px 24px 72px', 'background' => '#ffffff', 'gap' => 40, 'minHeight' => '580px' ]
			),
			self::section(
				[
					self::column(25, [ self::counter(120, 'Teams onboarded', '+', $accent) ]),
					self::column(25, [ self::counter(4, 'Hours saved / week', 'x', $accent) ]),
					self::column(25, [ self::counter(99, 'Uptime', '%', $accent) ]),
					self::column(25, [ self::counter(14, 'Day free trial', '', $accent) ]),
				],
				[ 'padding' => '52px 24px', 'background' => '#EEF2FF', 'gap' => 12 ]
			),
			self::section(
				[
					self::column(50, [ self::icon_box('⚡', 'Automations', 'Trigger the right follow-up without busywork.') ]),
					self::column(50, [ self::icon_box('📊', 'Live insights', 'See what converts before the week ends.') ]),
				],
				[ 'padding' => '72px 24px 16px', 'background' => '#f8fafc', 'gap' => 20 ]
			),
			self::section(
				[
					self::column(50, [ self::icon_box('🔒', 'Secure by default', 'SSO, roles, and audit-ready logs.') ]),
					self::column(50, [ self::icon_box('🧩', 'Integrations', 'Connect Slack, HubSpot, and your CRM in minutes.') ]),
				],
				[ 'padding' => '16px 24px 72px', 'background' => '#f8fafc', 'gap' => 20 ]
			),
			self::section(
				[
					self::column(55, [
						self::heading('Built for focus', 'h2', 'left', '#ffffff', [ 'fontFamily' => $f, 'fontSize' => '36px' ]),
						self::text('A calm product surface so your team ships campaigns without tab chaos.', 'left', 'rgba(255,255,255,.8)', [ 'fontFamily' => $f ]),
					], [ 'verticalAlign' => 'middle' ]),
					self::column(45, [
						self::image(self::media(), 'Dashboard', [ 'borderRadius' => '12px' ]),
					], [ 'verticalAlign' => 'middle' ]),
				],
				[ 'padding' => '72px 24px', 'background' => '#312e81', 'gap' => 32, 'textColor' => '#ffffff' ]
			),
			self::section(
				[
					self::column(33, [ self::price_table('Starter', '$0', '/mo', [ '3 projects', 'Basic analytics', 'Email support' ], 'Start free', '#ffffff') ]),
					self::column(33, [ self::price_table('Growth', '$49', '/mo', [ 'Unlimited projects', 'Automations', 'Priority support' ], 'Try Growth', '#eef2ff', true) ]),
					self::column(34, [ self::price_table('Scale', '$149', '/mo', [ 'SSO & roles', 'Custom workflows', 'Success manager' ], 'Talk to sales', '#ffffff') ]),
				],
				[ 'padding' => '72px 24px', 'background' => '#ffffff', 'gap' => 20 ]
			),
			self::section(
				[
					self::column(40, [
						self::heading('Questions', 'h2', 'left', '#0f172a', [ 'fontFamily' => $f, 'fontSize' => '32px' ]),
						self::text('Everything you need before starting a trial.', 'left', '#64748b', [ 'fontFamily' => $f ]),
					]),
					self::column(60, [
						self::accordion([
							[ 'title' => 'Do I need a credit card?', 'content' => 'No — the 14-day trial is free with no card required.' ],
							[ 'title' => 'Can I invite my team?', 'content' => 'Yes. Add teammates anytime; seats are included on Growth and Scale.' ],
							[ 'title' => 'How do exports work?', 'content' => 'CSV and API exports are available on all paid plans.' ],
						]),
					]),
				],
				[ 'padding' => '64px 24px', 'background' => '#f1f5f9', 'gap' => 32 ]
			),
			self::section(
				[ self::column(100, [
					self::cta('See LaunchPad in action', 'No credit card required for the 14-day trial.', 'Book a demo', $accent),
				]) ],
				[ 'padding' => '48px 24px 80px', 'background' => '#EEF2FF' ]
			),
			self::site_footer($brand, $accent, 'The growth workspace for teams that ship campaigns without the chaos.', [
				'Product' => ['Features', 'Integrations', 'Security'],
				'Company' => ['About', 'Careers', 'Contact'],
				'Resources' => ['Docs', 'Blog', 'Status'],
			], '#1e293b', '#ffffff', 'rgba(255,255,255,.72)', 'DM Sans'),
		]));
	}

	private static function page_agency($brand, $accent, $hero) {
		$f = self::font('Space Grotesk');
		return self::doc(self::flatten([
			self::site_header($brand, '#ffffff', ['Work', 'Services', 'Studio', 'Contact'], 'Start a project', '#141414', '#ffffff', 'Space Grotesk'),
			self::section(
				[
					self::column(62, [
						self::heading($brand, 'h1', 'left', 'rgba(255,255,255,.5)', [
							'fontFamily' => $f, 'fontSize' => '12px', 'fontWeight' => '700',
							'letterSpacing' => '0.22em', 'textTransform' => 'uppercase', 'marginBottom' => '28px',
						]),
						self::heading("Brand systems\nthat travel.", 'h1', 'left', '#ffffff', [
							'fontFamily' => $f, 'fontSize' => '64px', 'fontWeight' => '800', 'lineHeight' => '0.96', 'marginBottom' => '24px',
						]),
						self::text('Strategy, design, and digital experiences for ambitious companies.', 'left', 'rgba(255,255,255,.72)', [
							'fontFamily' => $f, 'fontSize' => '18px', 'maxWidth' => '420px',
						]),
						self::button('View work', '#ffffff', 'left', [ 'borderRadius' => '0px', 'fontFamily' => $f ]),
					], [ 'verticalAlign' => 'middle', 'padding' => '24px 12px' ]),
					self::column(38, [
						self::heading('01 / Studio', 'h3', 'left', '#a1a1aa', [ 'fontFamily' => $f, 'fontSize' => '14px', 'letterSpacing' => '0.12em', 'textTransform' => 'uppercase' ]),
						self::text('Brooklyn · Remote worldwide\nSelected for brands that need systems, not one-offs.', 'left', 'rgba(255,255,255,.65)', [ 'fontFamily' => $f, 'fontSize' => '15px' ]),
					], [ 'verticalAlign' => 'bottom', 'padding' => '24px 8px' ]),
				],
				[ 'padding' => '120px 24px 100px', 'background' => '#1a1a1a', 'textColor' => '#ffffff', 'gap' => 40, 'minHeight' => '640px' ]
			),
			self::section(
				[
					self::column(38, [
						self::heading('Services', 'h2', 'left', '#0f172a', [ 'fontFamily' => $f, 'fontSize' => '42px' ]),
					]),
					self::column(62, [
						self::widget('icon-list', [
							'items' => [
								[ 'icon' => '→', 'text' => 'Brand strategy — positioning that clarifies what you stand for' ],
								[ 'icon' => '→', 'text' => 'Visual identity — logos, type, and systems that scale' ],
								[ 'icon' => '→', 'text' => 'Web experiences — fast sites that feel intentional' ],
								[ 'icon' => '→', 'text' => 'Campaign creative — launches with a point of view' ],
							],
							'color' => '#0f172a',
							'style' => self::style([ 'marginBottom' => '0px', 'fontFamily' => $f ]),
						]),
					]),
				],
				[ 'padding' => '88px 24px', 'background' => '#fafafa', 'gap' => 24 ]
			),
			self::section(
				[ self::column(100, [
					self::heading('Selected cases', 'h2', 'left', '#ffffff', [ 'fontFamily' => $f, 'fontSize' => '36px', 'marginBottom' => '28px' ]),
					self::gallery([
						[ 'src' => $hero, 'alt' => 'Case 1' ],
						[ 'src' => self::media(), 'alt' => 'Case 2' ],
						[ 'src' => self::media(), 'alt' => 'Case 3' ],
						[ 'src' => self::media(), 'alt' => 'Case 4' ],
						[ 'src' => self::media(), 'alt' => 'Case 5' ],
						[ 'src' => self::media(), 'alt' => 'Case 6' ],
					], 3),
				]) ],
				[ 'padding' => '80px 24px', 'background' => '#0f172a', 'textColor' => '#ffffff' ]
			),
			self::section(
				[
					self::column(25, [ self::icon_box('01', 'Discover', 'Workshops that surface the real brief.', 'center', true) ]),
					self::column(25, [ self::icon_box('02', 'Define', 'Positioning, audience, and narrative.', 'center', true) ]),
					self::column(25, [ self::icon_box('03', 'Design', 'Identity systems and digital craft.', 'center', true) ]),
					self::column(25, [ self::icon_box('04', 'Deliver', 'Launch kits your team can run.', 'center', true) ]),
				],
				[ 'padding' => '64px 24px', 'background' => '#27272a', 'gap' => 16, 'textColor' => '#ffffff' ]
			),
			self::section(
				[
					self::column(33, [ self::team('Ava Chen', 'Creative Director', 'Leads brand systems and art direction.', self::media()) ]),
					self::column(33, [ self::team('Noah Blake', 'Strategy Lead', 'Turns messy briefs into sharp narratives.', self::media()) ]),
					self::column(34, [ self::team('Sam Ortiz', 'Web Lead', 'Builds fast, accessible front-end systems.', self::media()) ]),
				],
				[ 'padding' => '72px 24px', 'background' => '#ffffff', 'gap' => 20 ]
			),
			self::section(
				[ self::column(100, [
					self::testimonial('They gave our brand a spine — suddenly every touchpoint felt related.', 'Mina Park', 'CMO, Orbit'),
				]) ],
				[ 'padding' => '72px 24px', 'background' => '#18181b', 'textColor' => '#ffffff', 'contentWidth' => 800 ]
			),
			self::site_footer($brand, '#ffffff', 'A creative studio building brands that travel across markets and media.', [
				'Studio' => ['Work', 'Services', 'Careers'],
				'Visit' => ['88 Studio Row', 'By appointment', 'hello@northstar.agency'],
				'Follow' => ['Instagram', 'Behance', 'LinkedIn'],
			], '#000000', '#ffffff', 'rgba(255,255,255,.72)', 'Space Grotesk'),
		]));
	}

	private static function page_restaurant($brand, $accent, $hero) {
		$f = self::font('Playfair Display');
		$b = self::font('IBM Plex Sans');
		return self::doc(self::flatten([
			self::site_header($brand, $accent, ['Menu', 'Wine', 'About', 'Reserve'], 'Reserve', '#F4F7F2', '#14532d', 'Playfair Display'),
			self::section(
				[ self::column(100, [
					self::heading($brand, 'h1', 'center', 'rgba(255,255,255,.85)', [
						'fontFamily' => $f, 'fontSize' => '14px', 'letterSpacing' => '0.22em',
						'textTransform' => 'uppercase', 'marginBottom' => '20px',
					]),
					self::heading("Seasonal plates.\nQuiet luxury.", 'h1', 'center', '#ffffff', [
						'fontFamily' => $f, 'fontSize' => '58px', 'fontWeight' => '700', 'lineHeight' => '1.1', 'marginBottom' => '18px',
					]),
					self::text('A neighborhood dining room for long conversations and unforgettable courses.', 'center', 'rgba(255,255,255,.92)', [
						'fontFamily' => $b, 'fontSize' => '18px', 'maxWidth' => '480px',
					]),
					self::button('Reserve a table', '#14532d', 'center', [ 'borderRadius' => '0px', 'fontFamily' => $f ]),
				], [ 'verticalAlign' => 'middle' ]) ],
				[
					'padding' => '130px 24px', 'minHeight' => '660px', 'textColor' => '#ffffff',
					'background' => 'linear-gradient(180deg, rgba(20,83,45,.35) 0%, rgba(20,83,45,.82) 100%)',
					'backgroundImage' => $hero,
				]
			),
			self::section(
				[
					self::column(50, [
						self::heading('Our kitchen', 'h2', 'left', '#14532d', [ 'fontFamily' => $f, 'fontSize' => '36px' ]),
						self::text('We cook with the market — herbs from nearby farms, seafood from the morning boats, and a wine list that rewards curiosity.', 'left', '#3f6212', [
							'fontFamily' => $b, 'fontSize' => '17px',
						]),
					], [ 'verticalAlign' => 'middle' ]),
					self::column(50, [
						self::image(self::media(), 'Plated dish', [ 'borderRadius' => '8px' ]),
					], [ 'verticalAlign' => 'middle' ]),
				],
				[ 'padding' => '80px 24px', 'background' => '#F4F7F2', 'gap' => 36 ]
			),
			self::section(
				[ self::column(100, [
					self::heading('Tasting menu', 'h2', 'center', '#14532d', [ 'fontFamily' => $f, 'fontSize' => '36px', 'marginBottom' => '28px' ]),
					self::widget('price-list', [
						'items' => [
							[ 'title' => 'Garden crudo', 'price' => '$18', 'description' => 'Citrus, fennel pollen, olive oil' ],
							[ 'title' => 'Handmade ravioli', 'price' => '$26', 'description' => 'Ricotta, brown butter, sage' ],
							[ 'title' => 'Wood-fired duck', 'price' => '$38', 'description' => 'Cherry glaze, roasted roots' ],
							[ 'title' => 'Dark chocolate souffle', 'price' => '$14', 'description' => 'Sea salt, creme fraiche' ],
						],
						'style' => self::style([ 'marginBottom' => '0px', 'fontFamily' => $f ]),
					]),
				]) ],
				[ 'padding' => '80px 24px', 'background' => '#ffffff', 'contentWidth' => 700 ]
			),
			self::section(
				[
					self::column(33, [ self::icon_box('🍷', 'Natural wines', 'Small producers, low intervention, thoughtful pairings.') ]),
					self::column(33, [ self::icon_box('🥂', 'By the glass', 'Twelve rotating pours refreshed weekly.') ]),
					self::column(34, [ self::icon_box('🍾', 'Cellar list', 'Verticals and special bottles on request.') ]),
				],
				[ 'padding' => '64px 24px', 'background' => '#ECFDF5', 'gap' => 18 ]
			),
			self::section(
				[ self::column(100, [
					self::heading('The room', 'h2', 'center', '#ffffff', [ 'fontFamily' => $f, 'fontSize' => '34px', 'marginBottom' => '24px' ]),
					self::gallery([
						[ 'src' => self::media(), 'alt' => 'Dining room' ],
						[ 'src' => self::media(), 'alt' => 'Table setting' ],
						[ 'src' => self::media(), 'alt' => 'Wine' ],
						[ 'src' => self::media(), 'alt' => 'Dessert' ],
					], 2),
				]) ],
				[ 'padding' => '72px 24px', 'background' => '#166534', 'textColor' => '#ffffff' ]
			),
			self::section(
				[
					self::column(40, [
						self::image(self::media(), 'Chef', [ 'borderRadius' => '8px' ]),
					]),
					self::column(60, [
						self::heading('Meet the chef', 'h2', 'left', '#14532d', [ 'fontFamily' => $f, 'fontSize' => '34px' ]),
						self::text('Chef Isla Moreno trained in Lyon and cooks with a gardener\'s patience — bright flavors, quiet technique, and plates that feel like home.', 'left', '#3f6212', [ 'fontFamily' => $b ]),
					], [ 'verticalAlign' => 'middle' ]),
				],
				[ 'padding' => '72px 24px', 'background' => '#F4F7F2', 'gap' => 32 ]
			),
			self::section(
				[
					self::column(50, [
						self::heading('Hours', 'h2', 'left', '#ffffff', [ 'fontFamily' => $f, 'fontSize' => '28px' ]),
						self::hours([
							[ 'day' => 'Tue - Thu', 'hours' => '5:00 PM - 10:00 PM' ],
							[ 'day' => 'Fri - Sat', 'hours' => '5:00 PM - 11:00 PM' ],
							[ 'day' => 'Sunday', 'hours' => '4:00 PM - 9:00 PM' ],
						]),
					]),
					self::column(50, [
						self::heading('Reserve', 'h2', 'left', '#ffffff', [ 'fontFamily' => $f, 'fontSize' => '28px' ]),
						self::text('Tables for two to eight. Private dining by request.', 'left', 'rgba(255,255,255,.85)', [ 'fontFamily' => $b ]),
						self::dual('Book a table', 'Private dining', '#F4F7F2', 'rgba(255,255,255,.15)'),
					]),
				],
				[ 'padding' => '72px 24px', 'background' => '#14532d', 'gap' => 28, 'textColor' => '#ffffff' ]
			),
			self::site_footer($brand, '#F4F7F2', 'Seasonal cooking in a calm dining room — reserve your table for the next long evening.', [
				'Visit' => ['18 Orchard Lane', 'Dinner Tue-Sun', 'hello@maisonverde.com'],
				'Menu' => ['Tasting', 'Wine', 'Private events'],
				'Follow' => ['Instagram', 'Resy', 'Newsletter'],
			], '#14532d', '#ffffff', 'rgba(255,255,255,.72)', 'Playfair Display'),
		]));
	}

	private static function page_realestate($brand, $accent, $hero) {
		$f = self::font('Manrope');
		return self::doc(self::flatten([
			self::site_header($brand, $accent, ['Buy', 'Sell', 'Neighborhoods', 'Agents'], 'Talk to an agent', '#E0F2FE', '#0c4a6e', 'Manrope'),
			self::section(
				[ self::column(100, [
					self::heading('Find your next address', 'h1', 'center', '#ffffff', [
						'fontFamily' => $f, 'fontSize' => '52px', 'fontWeight' => '800', 'lineHeight' => '1.1', 'marginBottom' => '16px',
					]),
					self::text('Search waterfront homes, quiet cul-de-sacs, and downtown lofts with advisors who know every block.', 'center', 'rgba(255,255,255,.92)', [
						'fontFamily' => $f, 'fontSize' => '18px', 'maxWidth' => '560px', 'marginBottom' => '28px',
					]),
					self::dual('Browse listings', 'Get a valuation', '#ffffff', 'rgba(255,255,255,.2)'),
				], [ 'verticalAlign' => 'middle' ]) ],
				[
					'padding' => '120px 24px', 'minHeight' => '600px', 'textColor' => '#ffffff',
					'background' => 'linear-gradient(120deg, rgba(3,105,161,.88) 0%, rgba(12,74,110,.75) 100%)',
					'backgroundImage' => $hero,
				]
			),
			self::section(
				[ self::column(100, [
					self::heading('Featured listings', 'h2', 'left', '#0c4a6e', [ 'fontFamily' => $f, 'fontSize' => '32px', 'marginBottom' => '8px' ]),
				]) ],
				[ 'padding' => '72px 24px 8px', 'background' => '#f0f9ff' ]
			),
			self::section(
				[
					self::column(33, [
						self::image(self::media(), 'Harbor view home', [ 'borderRadius' => '12px' ]),
						self::heading('Harbor View · 4 bed', 'h3', 'left', '#0c4a6e', [ 'fontFamily' => $f, 'fontSize' => '20px', 'marginBottom' => '4px' ]),
						self::text('$1,245,000', 'left', $accent, [ 'fontFamily' => $f, 'fontSize' => '18px', 'fontWeight' => '800' ]),
					]),
					self::column(33, [
						self::image(self::media(), 'Garden cottage', [ 'borderRadius' => '12px' ]),
						self::heading('Garden Cottage · 3 bed', 'h3', 'left', '#0c4a6e', [ 'fontFamily' => $f, 'fontSize' => '20px', 'marginBottom' => '4px' ]),
						self::text('$875,000', 'left', $accent, [ 'fontFamily' => $f, 'fontSize' => '18px', 'fontWeight' => '800' ]),
					]),
					self::column(34, [
						self::image(self::media(), 'Skyline loft', [ 'borderRadius' => '12px' ]),
						self::heading('Skyline Loft · 2 bed', 'h3', 'left', '#0c4a6e', [ 'fontFamily' => $f, 'fontSize' => '20px', 'marginBottom' => '4px' ]),
						self::text('$695,000', 'left', $accent, [ 'fontFamily' => $f, 'fontSize' => '18px', 'fontWeight' => '800' ]),
					]),
				],
				[ 'padding' => '16px 24px 72px', 'background' => '#f0f9ff', 'gap' => 20 ]
			),
			self::section(
				[
					self::column(33, [ self::icon_box('🌊', 'Seaside', 'Walkable waterfront with ferry access.') ]),
					self::column(33, [ self::icon_box('🌳', 'Parkside', 'Tree-lined streets and top schools.') ]),
					self::column(34, [ self::icon_box('🏙', 'Downtown', 'Lofts near cafes, transit, and offices.') ]),
				],
				[ 'padding' => '64px 24px', 'background' => '#ffffff', 'gap' => 18 ]
			),
			self::section(
				[
					self::column(50, [
						self::heading('Why Harbor Homes', 'h2', 'left', '#0c4a6e', [ 'fontFamily' => $f, 'fontSize' => '34px' ]),
						self::widget('icon-list', [
							'items' => [
								[ 'icon' => '✓', 'text' => 'Local pricing intel updated weekly' ],
								[ 'icon' => '✓', 'text' => 'Photographers and staging included' ],
								[ 'icon' => '✓', 'text' => 'Offer strategy that protects your timeline' ],
							],
							'color' => '#0c4a6e',
							'style' => self::style([ 'marginBottom' => '0px' ]),
						]),
					], [ 'verticalAlign' => 'middle' ]),
					self::column(50, [
						self::image(self::media(), 'Living room', [ 'borderRadius' => '12px' ]),
					], [ 'verticalAlign' => 'middle' ]),
				],
				[ 'padding' => '72px 24px', 'background' => '#E0F2FE', 'gap' => 32 ]
			),
			self::section(
				[
					self::column(33, [ self::team('Priya Shah', 'Buyer Agent', 'Specialty: first homes & condos.', self::media()) ]),
					self::column(33, [ self::team('Marcus Cole', 'Listing Lead', 'Specialty: waterfront sales.', self::media()) ]),
					self::column(34, [ self::team('Nina Ortiz', 'Relocation', 'Specialty: out-of-town buyers.', self::media()) ]),
				],
				[ 'padding' => '72px 24px', 'background' => '#f8fafc', 'gap' => 20 ]
			),
			self::section(
				[ self::column(100, [
					self::cta('Ready to tour?', 'Tell us your budget, timeline, and neighborhood wish list.', 'Start inquiry', $accent),
				]) ],
				[ 'padding' => '48px 24px 80px', 'background' => '#bae6fd' ]
			),
			self::site_footer($brand, $accent, 'Local advisors for buyers and sellers who care about the right address — not just the next one.', [
				'Buy' => ['Listings', 'Open houses', 'Mortgage partners'],
				'Sell' => ['Home valuation', 'Staging', 'Marketing'],
				'Office' => ['220 Pier Ave', 'Mon-Sat 9-6', 'hello@harborhomes.com'],
			], '#0c4a6e', '#ffffff', 'rgba(255,255,255,.72)', 'Manrope'),
		]));
	}

	private static function page_beauty($brand, $accent, $hero) {
		$f = self::font('Fraunces');
		$b = self::font('Outfit');
		return self::doc(self::flatten([
			self::site_header($brand, $accent, ['Services', 'Gallery', 'Team', 'Book'], 'Book now', '#FDF2F8', '#831843', 'Fraunces'),
			self::section(
				[ self::column(100, [
					self::heading($brand, 'h1', 'center', '#FBCFE8', [
						'fontFamily' => $f, 'fontSize' => '13px', 'letterSpacing' => '0.2em',
						'textTransform' => 'uppercase', 'marginBottom' => '18px',
					]),
					self::heading("Soft glam.\nLasting glow.", 'h1', 'center', '#ffffff', [
						'fontFamily' => $f, 'fontSize' => '56px', 'fontWeight' => '700', 'lineHeight' => '1.08', 'marginBottom' => '18px',
					]),
					self::text('Hair, skin, and nail rituals designed for luminous everyday beauty.', 'center', 'rgba(255,255,255,.92)', [
						'fontFamily' => $b, 'fontSize' => '18px', 'maxWidth' => '480px', 'marginBottom' => '28px',
					]),
					self::button('Book appointment', $accent, 'center', [ 'borderRadius' => '999px' ]),
				], [ 'verticalAlign' => 'middle' ]) ],
				[
					'padding' => '120px 24px', 'minHeight' => '620px', 'textColor' => '#ffffff',
					'background' => 'linear-gradient(160deg, rgba(190,24,93,.55) 0%, rgba(131,24,67,.75) 100%)',
					'backgroundImage' => $hero,
				]
			),
			self::section(
				[ self::column(100, [
					self::heading('Services menu', 'h2', 'center', '#831843', [ 'fontFamily' => $f, 'fontSize' => '36px', 'marginBottom' => '24px' ]),
					self::widget('price-list', [
						'items' => [
							[ 'title' => 'Signature cut & style', 'price' => '$85', 'description' => 'Consultation, cut, blowout' ],
							[ 'title' => 'Balayage', 'price' => '$220', 'description' => 'Custom color, tone, treatment' ],
							[ 'title' => 'Glow facial', 'price' => '$120', 'description' => 'Cleanse, peel, LED finish' ],
							[ 'title' => 'Gel manicure', 'price' => '$55', 'description' => 'Shape, polish, cuticle care' ],
						],
						'style' => self::style([ 'marginBottom' => '0px', 'fontFamily' => $b ]),
					]),
				]) ],
				[ 'padding' => '72px 24px', 'background' => '#ffffff', 'contentWidth' => 680 ]
			),
			self::section(
				[ self::column(100, [
					self::heading('Before & after', 'h2', 'center', '#831843', [ 'fontFamily' => $f, 'fontSize' => '34px', 'marginBottom' => '24px' ]),
					self::gallery([
						[ 'src' => self::media(), 'alt' => 'Look 1' ],
						[ 'src' => self::media(), 'alt' => 'Look 2' ],
						[ 'src' => self::media(), 'alt' => 'Look 3' ],
						[ 'src' => self::media(), 'alt' => 'Look 4' ],
					], 2),
				]) ],
				[ 'padding' => '72px 24px', 'background' => '#FCE7F3' ]
			),
			self::section(
				[
					self::column(40, [
						self::heading('★★★★★', 'h2', 'left', '#BE185D', [ 'fontFamily' => $f, 'fontSize' => '28px', 'marginBottom' => '8px' ]),
						self::heading('Loved by the neighborhood', 'h3', 'left', '#831843', [ 'fontFamily' => $f, 'fontSize' => '26px' ]),
					], [ 'verticalAlign' => 'middle' ]),
					self::column(60, [
						self::testimonial('The balayage looks natural and my facial left my skin calm for weeks.', 'Sophie L.', 'Client'),
					]),
				],
				[ 'padding' => '64px 24px', 'background' => '#FFF1F2', 'gap' => 24 ]
			),
			self::section(
				[
					self::column(33, [ self::team('Aria Kim', 'Color Lead', 'Soft balayage & glosses.', self::media()) ]),
					self::column(33, [ self::team('Jules Hart', 'Stylist', 'Cuts that grow out gracefully.', self::media()) ]),
					self::column(34, [ self::team('Mina Cho', 'Esthetician', 'Glow facials & skin rituals.', self::media()) ]),
				],
				[ 'padding' => '72px 24px', 'background' => '#ffffff', 'gap' => 20 ]
			),
			self::section(
				[
					self::column(50, [
						self::heading('Salon hours', 'h2', 'left', '#831843', [ 'fontFamily' => $f, 'fontSize' => '28px' ]),
						self::hours([
							[ 'day' => 'Tue - Fri', 'hours' => '10:00 AM - 7:00 PM' ],
							[ 'day' => 'Saturday', 'hours' => '9:00 AM - 5:00 PM' ],
							[ 'day' => 'Sun - Mon', 'hours' => 'Closed' ],
						]),
					]),
					self::column(50, [
						self::heading('Visit', 'h2', 'left', '#831843', [ 'fontFamily' => $f, 'fontSize' => '28px' ]),
						self::text('44 Blossom Street · Suite 2\nStreet parking validated for appointments.', 'left', '#9d174d', [ 'fontFamily' => $b ]),
					]),
				],
				[ 'padding' => '64px 24px', 'background' => '#FDF2F8', 'gap' => 28 ]
			),
			self::section(
				[ self::column(100, [
					self::heading('Book your glow-up', 'h2', 'center', '#ffffff', [ 'fontFamily' => $f, 'fontSize' => '36px' ]),
					self::text('Online booking opens 8 weeks out. Walk-ins welcome for express services.', 'center', '#FBCFE8', [ 'fontFamily' => $b ]),
					self::button('Book now', '#ffffff', 'center', [ 'borderRadius' => '999px' ]),
				]) ],
				[ 'padding' => '80px 24px', 'background' => '#BE185D', 'textColor' => '#ffffff' ]
			),
			self::site_footer($brand, $accent, 'A blush-toned salon for soft glam, healthy hair, and skin that glows.', [
				'Book' => ['Hair', 'Skin', 'Nails'],
				'Visit' => ['44 Blossom St', 'Tue-Sat', 'hello@luxeglow.com'],
				'Follow' => ['Instagram', 'TikTok', 'Gift cards'],
			], '#9d174d', '#ffffff', 'rgba(255,255,255,.72)', 'Fraunces'),
		]));
	}

	private static function page_yoga($brand, $accent, $hero) {
		$f = self::font('Fraunces');
		$b = self::font('Manrope');
		return self::doc(self::flatten([
			self::site_header($brand, $accent, ['Classes', 'Teachers', 'Pricing', 'Join'], 'Start free', '#ECFDF5', '#115e59', 'Fraunces'),
			self::section(
				[ self::column(100, [
					self::heading($brand, 'h1', 'center', '#99f6e4', [
						'fontFamily' => $f, 'fontSize' => '13px', 'letterSpacing' => '0.18em',
						'textTransform' => 'uppercase', 'marginBottom' => '18px',
					]),
					self::heading("Breathe deeper.\nMove softer.", 'h1', 'center', '#ffffff', [
						'fontFamily' => $f, 'fontSize' => '56px', 'fontWeight' => '700', 'lineHeight' => '1.1', 'marginBottom' => '18px',
					]),
					self::text('A calm studio for vinyasa, restorative, and mindful strength — all levels welcome.', 'center', 'rgba(255,255,255,.92)', [
						'fontFamily' => $b, 'fontSize' => '18px', 'maxWidth' => '520px', 'marginBottom' => '28px',
					]),
					self::button('Try a class', $accent, 'center', [ 'borderRadius' => '999px' ]),
				], [ 'verticalAlign' => 'middle' ]) ],
				[
					'padding' => '120px 24px', 'minHeight' => '620px', 'textColor' => '#ffffff',
					'background' => 'linear-gradient(180deg, rgba(13,148,136,.4) 0%, rgba(19,78,74,.85) 100%)',
					'backgroundImage' => $hero,
				]
			),
			self::section(
				[
					self::column(48, [
						self::image(self::media(), 'Vinyasa', [ 'borderRadius' => '20px' ]),
					], [ 'verticalAlign' => 'middle' ]),
					self::column(52, [
						self::heading('Morning vinyasa', 'h2', 'left', '#115e59', [ 'fontFamily' => $f, 'fontSize' => '34px' ]),
						self::text('Flow with breath for 60 minutes. Build heat gently, open the hips, and leave clearer than you arrived.', 'left', '#0f766e', [ 'fontFamily' => $b ]),
						self::text('Mon · Wed · Fri · 7:00 AM', 'left', '#0d9488', [ 'fontFamily' => $b, 'fontWeight' => '700' ]),
					], [ 'verticalAlign' => 'middle' ]),
				],
				[ 'padding' => '80px 24px', 'background' => '#F0FDFA', 'gap' => 36 ]
			),
			self::section(
				[
					self::column(52, [
						self::heading('Restorative evenings', 'h2', 'left', '#ffffff', [ 'fontFamily' => $f, 'fontSize' => '34px' ]),
						self::text('Long holds, soft lighting, and props that support every body. Perfect after a heavy week.', 'left', 'rgba(255,255,255,.88)', [ 'fontFamily' => $b ]),
						self::text('Tue · Thu · 7:30 PM', 'left', '#99f6e4', [ 'fontFamily' => $b, 'fontWeight' => '700' ]),
					], [ 'verticalAlign' => 'middle' ]),
					self::column(48, [
						self::image(self::media(), 'Restorative', [ 'borderRadius' => '20px' ]),
					], [ 'verticalAlign' => 'middle' ]),
				],
				[ 'padding' => '80px 24px', 'background' => '#0f766e', 'gap' => 36, 'textColor' => '#ffffff' ]
			),
			self::section(
				[
					self::column(33, [ self::team('Lila Stone', 'Lead Teacher', 'Vinyasa & meditation.', self::media()) ]),
					self::column(33, [ self::team('Theo Mars', 'Yin Guide', 'Restorative & breathwork.', self::media()) ]),
					self::column(34, [ self::team('Asha Reed', 'Strength', 'Mindful mobility.', self::media()) ]),
				],
				[ 'padding' => '72px 24px', 'background' => '#ffffff', 'gap' => 20 ]
			),
			self::section(
				[
					self::column(40, [
						self::heading('Common questions', 'h2', 'left', '#115e59', [ 'fontFamily' => $f, 'fontSize' => '32px' ]),
					]),
					self::column(60, [
						self::accordion([
							[ 'title' => 'Do I need to be flexible?', 'content' => 'Not at all. We teach options for every body and every day.' ],
							[ 'title' => 'Are mats provided?', 'content' => 'Yes — mats, blocks, and blankets are included. Bring water.' ],
							[ 'title' => 'Can beginners join?', 'content' => 'Absolutely. Look for Foundations and Restorative on the schedule.' ],
						]),
					]),
				],
				[ 'padding' => '72px 24px', 'background' => '#ECFDF5', 'gap' => 28 ]
			),
			self::section(
				[
					self::column(50, [ self::price_table('Drop-in', '$22', '/class', [ 'Any open class', 'Mat included', 'No commitment' ], 'Book class', '#ffffff') ]),
					self::column(50, [ self::price_table('Unlimited', '$110', '/mo', [ 'All classes', 'Workshops 20% off', 'Guest pass monthly' ], 'Join membership', '#F0FDFA', true) ]),
				],
				[ 'padding' => '72px 24px', 'background' => '#ccfbf1', 'gap' => 20 ]
			),
			self::section(
				[ self::column(100, [
					self::heading('Begin where you are', 'h2', 'center', '#ffffff', [ 'fontFamily' => $f, 'fontSize' => '36px' ]),
					self::text('Your first week is complimentary — come breathe with us.', 'center', '#99f6e4', [ 'fontFamily' => $b ]),
					self::button('Claim free week', '#ffffff', 'center', [ 'borderRadius' => '999px' ]),
				]) ],
				[ 'padding' => '80px 24px', 'background' => '#0d9488', 'textColor' => '#ffffff' ]
			),
			self::site_footer($brand, $accent, 'A sage-toned studio for mindful movement, quiet strength, and community.', [
				'Practice' => ['Schedule', 'Workshops', 'Retreats'],
				'Visit' => ['9 Stillpoint Way', 'Daily classes', 'hello@stillpoint.yoga'],
				'Follow' => ['Instagram', 'Spotify', 'Newsletter'],
			], '#115e59', '#ffffff', 'rgba(255,255,255,.72)', 'Fraunces'),
		]));
	}

	private static function page_manufacturing($brand, $accent, $hero) {
		$f = self::font('IBM Plex Sans');
		return self::doc(self::flatten([
			self::site_header($brand, $accent, ['Capabilities', 'Process', 'Quality', 'Contact'], 'Request quote', '#1c1917', '#ffffff', 'IBM Plex Sans'),
			self::section(
				[
					self::column(58, [
						self::heading('Precision manufacturing', 'h1', 'left', '#FBBF24', [
							'fontFamily' => $f, 'fontSize' => '13px', 'letterSpacing' => '0.16em',
							'textTransform' => 'uppercase', 'marginBottom' => '16px',
						]),
						self::heading("CNC parts.\nOn schedule.", 'h1', 'left', '#ffffff', [
							'fontFamily' => $f, 'fontSize' => '54px', 'fontWeight' => '700', 'lineHeight' => '1.05', 'marginBottom' => '18px',
						]),
						self::text('From prototype to production runs — tight tolerances, documented quality, and plant tours by appointment.', 'left', 'rgba(255,255,255,.85)', [
							'fontFamily' => $f, 'fontSize' => '17px', 'maxWidth' => '480px',
						]),
						self::button('Talk to engineering', $accent, 'left', [ 'borderRadius' => '4px' ]),
					], [ 'verticalAlign' => 'middle', 'padding' => '24px 12px' ]),
					self::column(42, [
						self::image($hero, 'Factory floor', [ 'borderRadius' => '8px' ]),
					], [ 'verticalAlign' => 'middle' ]),
				],
				[
					'padding' => '100px 24px', 'minHeight' => '600px', 'textColor' => '#ffffff', 'gap' => 32,
					'background' => 'linear-gradient(135deg, #1c1917 0%, #292524 55%, #44403c 100%)',
				]
			),
			self::section(
				[
					self::column(25, [ self::counter(48, 'Hour prototype turnaround', '', '#FBBF24') ]),
					self::column(25, [ self::counter(12, 'CNC cells', '', '#FBBF24') ]),
					self::column(25, [ self::counter(99, 'On-time delivery', '%', '#FBBF24') ]),
					self::column(25, [ self::counter(30, 'Years machining', '+', '#FBBF24') ]),
				],
				[ 'padding' => '56px 24px', 'background' => '#0c0a09', 'gap' => 12, 'textColor' => '#ffffff' ]
			),
			self::section(
				[
					self::column(33, [ self::icon_box('⚙', '5-axis CNC', 'Complex geometries in aluminum, steel, and titanium.') ]),
					self::column(33, [ self::icon_box('📐', 'Tight tolerance', 'Inspection to print with CMM reports.') ]),
					self::column(34, [ self::icon_box('📦', 'Production runs', 'Repeatable lots with lot traceability.') ]),
				],
				[ 'padding' => '72px 24px', 'background' => '#fafaf9', 'gap' => 18 ]
			),
			self::section(
				[ self::column(100, [
					self::heading('Certifications & compliance', 'h2', 'center', '#ffffff', [ 'fontFamily' => $f, 'fontSize' => '30px', 'marginBottom' => '20px' ]),
					self::text('ISO 9001 · AS9100 ready processes · ITAR registered · Material certs on every lot', 'center', '#FDE68A', [
						'fontFamily' => $f, 'fontSize' => '16px', 'marginBottom' => '0px',
					]),
				]) ],
				[ 'padding' => '48px 24px', 'background' => '#B45309', 'textColor' => '#ffffff' ]
			),
			self::section(
				[
					self::column(50, [
						self::heading('A partner on the floor', 'h2', 'left', '#1c1917', [ 'fontFamily' => $f, 'fontSize' => '32px' ]),
						self::text('When a medical device client needed a mid-run geometry change, we re-fixtured overnight and kept the line moving — with full documentation.', 'left', '#57534e', [ 'fontFamily' => $f ]),
					], [ 'verticalAlign' => 'middle' ]),
					self::column(50, [
						self::testimonial('Apex treats our prints like their own. Communication is crisp and parts arrive ready.', 'Dana Reeves', 'VP Ops, Helix Devices'),
					]),
				],
				[ 'padding' => '72px 24px', 'background' => '#ffffff', 'gap' => 28 ]
			),
			self::section(
				[
					self::column(50, [
						self::image(self::media(), 'Machining', [ 'borderRadius' => '8px' ]),
					]),
					self::column(50, [
						self::heading('Plant capabilities', 'h2', 'left', '#1c1917', [ 'fontFamily' => $f, 'fontSize' => '30px' ]),
						self::widget('icon-list', [
							'items' => [
								[ 'icon' => '→', 'text' => 'Turning, milling, and wire EDM' ],
								[ 'icon' => '→', 'text' => 'Anodize and passivate partners' ],
								[ 'icon' => '→', 'text' => 'Assembly and kitting available' ],
							],
							'color' => '#1c1917',
							'style' => self::style([ 'marginBottom' => '0px' ]),
						]),
					], [ 'verticalAlign' => 'middle' ]),
				],
				[ 'padding' => '72px 24px', 'background' => '#f5f5f4', 'gap' => 32 ]
			),
			self::section(
				[ self::column(100, [
					self::cta('Request a plant quote', 'Share drawings or STEP files — engineering responds within one business day.', 'Contact plant', $accent),
				]) ],
				[ 'padding' => '48px 24px 80px', 'background' => '#fffbeb' ]
			),
			self::site_footer($brand, $accent, 'Precision CNC manufacturing for industries that cannot miss a tolerance.', [
				'Plant' => ['Capabilities', 'Quality', 'Careers'],
				'Contact' => ['Quote desk', 'Shipping', 'hello@apexp.com'],
				'Follow' => ['LinkedIn', 'YouTube', 'News'],
			], '#1c1917', '#ffffff', 'rgba(255,255,255,.72)', 'IBM Plex Sans'),
		]));
	}

	private static function page_steel($brand, $accent, $hero) {
		$f = self::font('Space Grotesk');
		return self::doc(self::flatten([
			self::site_header($brand, $accent, ['Capabilities', 'Projects', 'Process', 'Quote'], 'Get a quote', '#0a0a0a', '#ffffff', 'Space Grotesk'),
			self::section(
				[ self::column(100, [
					self::heading('Forge & Beam', 'h1', 'left', '#FCA5A5', [
						'fontFamily' => $f, 'fontSize' => '13px', 'letterSpacing' => '0.2em',
						'textTransform' => 'uppercase', 'marginBottom' => '18px',
					]),
					self::heading("Structural steel.\nBuilt to load.", 'h1', 'left', '#ffffff', [
						'fontFamily' => $f, 'fontSize' => '58px', 'fontWeight' => '800', 'lineHeight' => '1.02', 'marginBottom' => '18px',
					]),
					self::text('Fabrication, erection support, and mill-quality detailing for commercial and industrial builds.', 'left', 'rgba(255,255,255,.85)', [
						'fontFamily' => $f, 'fontSize' => '18px', 'maxWidth' => '520px',
					]),
					self::button('Request project quote', $accent, 'left', [ 'borderRadius' => '2px' ]),
				], [ 'verticalAlign' => 'middle', 'padding' => '28px 16px' ]) ],
				[
					'padding' => '120px 24px', 'minHeight' => '640px', 'textColor' => '#ffffff',
					'background' => 'linear-gradient(100deg, rgba(0,0,0,.92) 0%, rgba(127,29,29,.7) 100%)',
					'backgroundImage' => $hero,
				]
			),
			self::section(
				[
					self::column(50, [
						self::heading('Capabilities', 'h2', 'left', '#0a0a0a', [ 'fontFamily' => $f, 'fontSize' => '34px' ]),
						self::text('Plate cutting, beam fabrication, welding certified to AWS, and shop drawings that keep field crews moving.', 'left', '#44403c', [ 'fontFamily' => $f ]),
						self::widget('icon-list', [
							'items' => [
								[ 'icon' => '■', 'text' => 'Structural beams & columns' ],
								[ 'icon' => '■', 'text' => 'Custom plate assemblies' ],
								[ 'icon' => '■', 'text' => 'Shop & field weld procedures' ],
							],
							'color' => '#0a0a0a',
							'style' => self::style([ 'marginBottom' => '0px' ]),
						]),
					], [ 'verticalAlign' => 'middle' ]),
					self::column(50, [
						self::image(self::media(), 'Steel fabrication', [ 'borderRadius' => '4px' ]),
					], [ 'verticalAlign' => 'middle' ]),
				],
				[ 'padding' => '80px 24px', 'background' => '#fafafa', 'gap' => 36 ]
			),
			self::section(
				[
					self::column(25, [ self::icon_box('1', 'Bid', 'Takeoff and clarifying questions within 72 hours.', 'center', true) ]),
					self::column(25, [ self::icon_box('2', 'Detail', 'Shop drawings for approval.', 'center', true) ]),
					self::column(25, [ self::icon_box('3', 'Fabricate', 'Cut, weld, finish in our bay.', 'center', true) ]),
					self::column(25, [ self::icon_box('4', 'Deliver', 'Staged loads to your site.', 'center', true) ]),
				],
				[ 'padding' => '64px 24px', 'background' => '#171717', 'gap' => 14, 'textColor' => '#ffffff' ]
			),
			self::section(
				[ self::column(100, [
					self::heading('Recent projects', 'h2', 'left', '#ffffff', [ 'fontFamily' => $f, 'fontSize' => '34px', 'marginBottom' => '24px' ]),
					self::gallery([
						[ 'src' => self::media(), 'alt' => 'Project 1' ],
						[ 'src' => self::media(), 'alt' => 'Project 2' ],
						[ 'src' => self::media(), 'alt' => 'Project 3' ],
						[ 'src' => self::media(), 'alt' => 'Project 4' ],
					], 2),
				]) ],
				[ 'padding' => '72px 24px', 'background' => '#450a0a', 'textColor' => '#ffffff' ]
			),
			self::section(
				[
					self::column(33, [ self::counter(850, 'Tons / year', '+', '#FCA5A5') ]),
					self::column(33, [ self::counter(120, 'Projects', '+', '#FCA5A5') ]),
					self::column(34, [ self::counter(24, 'Hour bid turnaround', '', '#FCA5A5') ]),
				],
				[ 'padding' => '56px 24px', 'background' => '#1c1917', 'gap' => 12, 'textColor' => '#ffffff' ]
			),
			self::section(
				[ self::column(100, [
					self::heading('Need a structural quote?', 'h2', 'center', '#ffffff', [ 'fontFamily' => $f, 'fontSize' => '40px' ]),
					self::text('Send drawings or a bid package — estimating responds within two business days.', 'center', '#FCA5A5', [ 'fontFamily' => $f ]),
					self::button('Request quote', $accent, 'center', [ 'borderRadius' => '2px' ]),
				]) ],
				[ 'padding' => '88px 24px', 'background' => '#7f1d1d', 'textColor' => '#ffffff' ]
			),
			self::site_footer($brand, $accent, 'Structural steel fabrication for commercial and industrial builds that cannot miss a load path.', [
				'Shop' => ['Capabilities', 'Certifications', 'Safety'],
				'Projects' => ['Commercial', 'Industrial', 'Infrastructure'],
				'Contact' => ['Estimating', 'Shipping', 'hello@forgebeam.com'],
			], '#0a0a0a', '#ffffff', 'rgba(255,255,255,.72)', 'Space Grotesk'),
		]));
	}
	private static function page_photographer($brand, $accent, $hero) {
		$f = self::font('Outfit');
		return self::doc(self::flatten([
			self::site_header($brand, $accent, ['Work', 'About', 'Packages', 'Book'], 'Book session', '#ffffff', '#0f172a', 'Outfit'),
			self::section(
				[ self::column(100, [
					self::heading('Selected work', 'h2', 'left', '#0f172a', [ 'fontFamily' => $f, 'fontSize' => '14px', 'letterSpacing' => '0.16em', 'textTransform' => 'uppercase', 'marginBottom' => '20px' ]),
					self::gallery([
						[ 'src' => $hero, 'alt' => 'Portrait 1' ],
						[ 'src' => self::media(), 'alt' => 'Portrait 2' ],
						[ 'src' => self::media(), 'alt' => 'Portrait 3' ],
						[ 'src' => self::media(), 'alt' => 'Portrait 4' ],
						[ 'src' => self::media(), 'alt' => 'Portrait 5' ],
						[ 'src' => self::media(), 'alt' => 'Portrait 6' ],
					], 3),
				]) ],
				[ 'padding' => '56px 24px 72px', 'background' => '#fafafa' ]
			),
			self::section(
				[
					self::column(55, [
						self::heading("Lens & Light", 'h1', 'left', '#0f172a', [
							'fontFamily' => $f, 'fontSize' => '48px', 'fontWeight' => '800', 'lineHeight' => '1.05', 'marginBottom' => '16px',
						]),
						self::text('Portrait, editorial, and brand photography with a quiet, natural style. Based in Portland — available worldwide.', 'left', '#475569', [
							'fontFamily' => $f, 'fontSize' => '18px',
						]),
					], [ 'verticalAlign' => 'middle' ]),
					self::column(45, [
						self::image(self::media(), 'Photographer', [ 'borderRadius' => '4px' ]),
					], [ 'verticalAlign' => 'middle' ]),
				],
				[ 'padding' => '80px 24px', 'background' => '#ffffff', 'gap' => 36, 'minHeight' => '480px' ]
			),
			self::section(
				[
					self::column(50, [ self::price_table('Portrait', '$450', '/session', [ '90 minutes', '40 edited images', 'Online gallery' ], 'Book portrait', '#ffffff') ]),
					self::column(50, [ self::price_table('Brand day', '$1400', '/day', [ 'Full day on location', 'Usage rights included', 'Rush available' ], 'Book brand day', '#f1f5f9', true) ]),
				],
				[ 'padding' => '72px 24px', 'background' => '#e2e8f0', 'gap' => 24 ]
			),
			self::section(
				[
					self::column(25, [ self::icon_box('01', 'Inquire', 'Share the date, mood, and deliverables.') ]),
					self::column(25, [ self::icon_box('02', 'Plan', 'Locations, wardrobe, and shot list.') ]),
					self::column(25, [ self::icon_box('03', 'Shoot', 'Calm direction, natural light first.') ]),
					self::column(25, [ self::icon_box('04', 'Deliver', 'Gallery within 10 business days.') ]),
				],
				[ 'padding' => '64px 24px', 'background' => '#f8fafc', 'gap' => 14 ]
			),
			self::section(
				[ self::column(100, [
					self::testimonial('Quiet direction, beautiful light — the brand images still feel fresh a year later.', 'Camila Ortiz', 'Founder, North Clay'),
				]) ],
				[ 'padding' => '64px 24px', 'background' => '#ffffff', 'contentWidth' => 720 ]
			),
			self::section(
				[ self::column(100, [
					self::heading('Book a session', 'h2', 'center', '#ffffff', [ 'fontFamily' => $f, 'fontSize' => '36px' ]),
					self::text('Limited dates each month. Tell me about your project.', 'center', '#94a3b8', [ 'fontFamily' => $f ]),
					self::button('Inquire now', '#ffffff', 'center', [ 'borderRadius' => '0px' ]),
				]) ],
				[ 'padding' => '80px 24px', 'background' => '#1e293b', 'textColor' => '#ffffff' ]
			),
			self::site_footer($brand, '#ffffff', 'Minimal portrait and brand photography — natural light, quiet direction, lasting images.', [
				'Work' => ['Portraits', 'Editorial', 'Brands'],
				'Book' => ['Packages', 'Availability', 'hello@lenslight.com'],
				'Follow' => ['Instagram', 'Behance', 'Pinterest'],
			], '#0f172a', '#ffffff', 'rgba(255,255,255,.72)', 'Outfit'),
		]));
	}

	private static function page_bakery($brand, $accent, $hero) {
		$f = self::font('Fraunces');
		$b = self::font('Outfit');
		return self::doc(self::flatten([
			self::site_header($brand, $accent, ['Menu', 'Story', 'Catering', 'Visit'], 'Order ahead', '#FFF7ED', '#7c2d12', 'Fraunces'),
			self::section(
				[ self::column(100, [
					self::heading($brand, 'h1', 'center', '#FED7AA', [
						'fontFamily' => $f, 'fontSize' => '14px', 'letterSpacing' => '0.18em',
						'textTransform' => 'uppercase', 'marginBottom' => '18px',
					]),
					self::heading("Warm ovens.\nButtery layers.", 'h1', 'center', '#ffffff', [
						'fontFamily' => $f, 'fontSize' => '56px', 'fontWeight' => '700', 'lineHeight' => '1.08', 'marginBottom' => '18px',
					]),
					self::text('Pastries, sourdough, and seasonal sweets baked before sunrise.', 'center', 'rgba(255,255,255,.92)', [
						'fontFamily' => $b, 'fontSize' => '18px', 'maxWidth' => '480px', 'marginBottom' => '28px',
					]),
					self::button('See today\'s bake', $accent, 'center', [ 'borderRadius' => '999px' ]),
				], [ 'verticalAlign' => 'middle' ]) ],
				[
					'padding' => '120px 24px', 'minHeight' => '640px', 'textColor' => '#ffffff',
					'background' => 'linear-gradient(180deg, rgba(194,65,12,.45) 0%, rgba(124,45,18,.82) 100%)',
					'backgroundImage' => $hero,
				]
			),
			self::section(
				[
					self::column(33, [
						self::image(self::media(), 'Croissant', [ 'borderRadius' => '16px' ]),
						self::heading('Butter croissant', 'h3', 'left', '#7c2d12', [ 'fontFamily' => $f, 'fontSize' => '22px', 'marginBottom' => '4px' ]),
						self::text('$4.25 · laminated daily', 'left', '#c2410c', [ 'fontFamily' => $b ]),
					]),
					self::column(33, [
						self::image(self::media(), 'Sourdough', [ 'borderRadius' => '16px' ]),
						self::heading('Country loaf', 'h3', 'left', '#7c2d12', [ 'fontFamily' => $f, 'fontSize' => '22px', 'marginBottom' => '4px' ]),
						self::text('$8.00 · naturally leavened', 'left', '#c2410c', [ 'fontFamily' => $b ]),
					]),
					self::column(34, [
						self::image(self::media(), 'Cake', [ 'borderRadius' => '16px' ]),
						self::heading('Seasonal tart', 'h3', 'left', '#7c2d12', [ 'fontFamily' => $f, 'fontSize' => '22px', 'marginBottom' => '4px' ]),
						self::text('$6.50 · fruit of the week', 'left', '#c2410c', [ 'fontFamily' => $b ]),
					]),
				],
				[ 'padding' => '72px 24px', 'background' => '#ffffff', 'gap' => 22 ]
			),
			self::section(
				[
					self::column(48, [
						self::image(self::media(), 'Bakery interior', [ 'borderRadius' => '16px' ]),
					], [ 'verticalAlign' => 'middle' ]),
					self::column(52, [
						self::heading('Baked with patience', 'h2', 'left', '#7c2d12', [ 'fontFamily' => $f, 'fontSize' => '34px' ]),
						self::text('We mill some flour in-house, prefer long ferments, and never rush a laminate. Come early for the best selection — favorites sell out by noon.', 'left', '#9a3412', [ 'fontFamily' => $b ]),
					], [ 'verticalAlign' => 'middle' ]),
				],
				[ 'padding' => '72px 24px', 'background' => '#FFEDD5', 'gap' => 36 ]
			),
			self::section(
				[
					self::column(50, [
						self::heading('Hours', 'h2', 'left', '#7c2d12', [ 'fontFamily' => $f, 'fontSize' => '28px' ]),
						self::hours([
							[ 'day' => 'Wed - Fri', 'hours' => '7:00 AM - 3:00 PM' ],
							[ 'day' => 'Saturday', 'hours' => '7:00 AM - 4:00 PM' ],
							[ 'day' => 'Sunday', 'hours' => '8:00 AM - 2:00 PM' ],
						]),
					]),
					self::column(50, [
						self::heading('Find us', 'h2', 'left', '#7c2d12', [ 'fontFamily' => $f, 'fontSize' => '28px' ]),
						self::text("21 Oven Lane · Eastside\nBike racks out front. Wholesale inquiries welcome.", 'left', '#9a3412', [ 'fontFamily' => $b ]),
						self::button('Get directions', $accent, 'left', [ 'borderRadius' => '999px' ]),
					]),
				],
				[ 'padding' => '64px 24px', 'background' => '#FFF7ED', 'gap' => 28 ]
			),
			self::section(
				[ self::column(100, [
					self::widget('price-list', [
						'items' => [
							[ 'title' => 'Morning bun', 'price' => '$4.00', 'description' => 'Cinnamon sugar, orange zest' ],
							[ 'title' => 'Almond croissant', 'price' => '$5.50', 'description' => 'Frangipane, toasted flakes' ],
							[ 'title' => 'Focaccia slab', 'price' => '$7.00', 'description' => 'Rosemary, sea salt' ],
						],
						'style' => self::style([ 'marginBottom' => '0px', 'fontFamily' => $b ]),
					]),
				]) ],
				[ 'padding' => '56px 24px', 'background' => '#ffffff', 'contentWidth' => 640 ]
			),
			self::section(
				[ self::column(100, [
					self::heading('Catering & cake orders', 'h2', 'center', '#ffffff', [ 'fontFamily' => $f, 'fontSize' => '34px' ]),
					self::text('Office boxes, wedding cakes, and holiday trays — order 72 hours ahead.', 'center', '#FED7AA', [ 'fontFamily' => $b ]),
					self::button('Plan catering', '#ffffff', 'center', [ 'borderRadius' => '999px' ]),
				]) ],
				[ 'padding' => '80px 24px', 'background' => '#C2410C', 'textColor' => '#ffffff' ]
			),
			self::site_footer($brand, $accent, 'Neighborhood bakery for laminated pastries, country loaves, and seasonal sweets.', [
				'Order' => ['Pastries', 'Bread', 'Catering'],
				'Visit' => ['21 Oven Lane', 'Wed-Sun', 'hello@crumbcrust.com'],
				'Follow' => ['Instagram', 'TikTok', 'Newsletter'],
			], '#7c2d12', '#ffffff', 'rgba(255,255,255,.72)', 'Fraunces'),
		]));
	}

	private static function page_law($brand, $accent, $hero) {
		$f = self::font('Playfair Display');
		$b = self::font('IBM Plex Sans');
		return self::doc(self::flatten([
			self::site_header($brand, $accent, ['Practice', 'Attorneys', 'Insights', 'Contact'], 'Free consult', '#0f172a', '#ffffff', 'Playfair Display'),
			self::section(
				[
					self::column(56, [
						self::heading('Sterling & Hale LLP', 'h1', 'left', '#94a3b8', [
							'fontFamily' => $b, 'fontSize' => '13px', 'letterSpacing' => '0.16em',
							'textTransform' => 'uppercase', 'marginBottom' => '18px',
						]),
						self::heading("Counsel you can\ntrust.", 'h1', 'left', '#ffffff', [
							'fontFamily' => $f, 'fontSize' => '54px', 'fontWeight' => '700', 'lineHeight' => '1.08', 'marginBottom' => '18px',
						]),
						self::text('Business, employment, and dispute resolution for companies and individuals who value clarity.', 'left', 'rgba(255,255,255,.85)', [
							'fontFamily' => $b, 'fontSize' => '17px', 'maxWidth' => '460px',
						]),
						self::button('Request consultation', '#C9A227', 'left', [ 'borderRadius' => '2px' ]),
					], [ 'verticalAlign' => 'middle', 'padding' => '24px 12px' ]),
					self::column(44, [
						self::image($hero, 'Law library', [ 'borderRadius' => '4px' ]),
					], [ 'verticalAlign' => 'middle' ]),
				],
				[
					'padding' => '100px 24px', 'minHeight' => '600px', 'textColor' => '#ffffff', 'gap' => 36,
					'background' => 'linear-gradient(135deg, #0f172a 0%, #1e3a5f 100%)',
				]
			),
			self::section(
				[
					self::column(40, [
						self::heading('Practice areas', 'h2', 'left', '#0f172a', [ 'fontFamily' => $f, 'fontSize' => '34px' ]),
						self::text('Focused counsel across the matters that shape your business and family.', 'left', '#475569', [ 'fontFamily' => $b ]),
					]),
					self::column(60, [
						self::accordion([
							[ 'title' => 'Corporate & commercial', 'content' => 'Entity formation, contracts, and governance for growing companies.' ],
							[ 'title' => 'Employment counseling', 'content' => 'Handbooks, investigations, and executive agreements handled discreetly.' ],
							[ 'title' => 'Litigation & disputes', 'content' => 'Strategic advocacy with a preference for efficient resolution.' ],
							[ 'title' => 'Real estate transactions', 'content' => 'Purchase, lease, and development counsel for complex deals.' ],
						]),
					]),
				],
				[ 'padding' => '80px 24px', 'background' => '#f8fafc', 'gap' => 32 ]
			),
			self::section(
				[
					self::column(33, [ self::team('Margaret Sterling', 'Managing Partner', 'Corporate & M&A.', self::media()) ]),
					self::column(33, [ self::team('James Hale', 'Senior Partner', 'Litigation lead.', self::media()) ]),
					self::column(34, [ self::team('Priya Nair', 'Counsel', 'Employment & compliance.', self::media()) ]),
				],
				[ 'padding' => '72px 24px', 'background' => '#ffffff', 'gap' => 20 ]
			),
			self::section(
				[
					self::column(25, [ self::counter(40, 'Years combined', '+', '#C9A227') ]),
					self::column(25, [ self::counter(500, 'Matters closed', '+', '#C9A227') ]),
					self::column(25, [ self::counter(98, 'Client retention', '%', '#C9A227') ]),
					self::column(25, [ self::counter(24, 'Hour response', '', '#C9A227') ]),
				],
				[ 'padding' => '56px 24px', 'background' => '#1e3a5f', 'gap' => 12, 'textColor' => '#ffffff' ]
			),
			self::section(
				[
					self::column(40, [
						self::heading('FAQ', 'h2', 'left', '#0f172a', [ 'fontFamily' => $f, 'fontSize' => '32px' ]),
					]),
					self::column(60, [
						self::accordion([
							[ 'title' => 'Is the consultation really free?', 'content' => 'Yes — a 30-minute introductory call to assess fit and next steps.' ],
							[ 'title' => 'Do you work remotely?', 'content' => 'We meet in person or by video; filings and negotiations are handled either way.' ],
							[ 'title' => 'How are fees structured?', 'content' => 'Hourly or flat-fee engagements depending on the matter. Estimates provided upfront.' ],
						]),
					]),
				],
				[ 'padding' => '72px 24px', 'background' => '#eef2ff', 'gap' => 28 ]
			),
			self::section(
				[ self::column(100, [
					self::cta('Schedule a confidential consultation', 'Tell us briefly about your matter — we respond within one business day.', 'Book consult', '#1e3a5f'),
				]) ],
				[ 'padding' => '48px 24px 80px', 'background' => '#f1f5f9' ]
			),
			self::site_footer($brand, '#C9A227', 'A boutique firm providing clear counsel for business, employment, and dispute matters.', [
				'Firm' => ['Attorneys', 'Insights', 'Careers'],
				'Contact' => ['100 Court Square', 'Mon-Fri 9-6', 'intake@sterlinghale.com'],
				'Practice' => ['Corporate', 'Employment', 'Litigation'],
			], '#0f172a', '#ffffff', 'rgba(255,255,255,.72)', 'Playfair Display'),
		]));
	}

	private static function page_dentist($brand, $accent, $hero) {
		$f = self::font('Manrope');
		return self::doc(self::flatten([
			self::site_header($brand, $accent, ['Services', 'Reviews', 'Hours', 'Contact'], 'Book visit', '#F0F9FF', '#0c4a6e', 'Manrope'),
			self::section(
				[
					self::column(52, [
						self::heading($brand, 'h1', 'left', '#0284C7', [
							'fontFamily' => $f, 'fontSize' => '13px', 'letterSpacing' => '0.14em',
							'textTransform' => 'uppercase', 'marginBottom' => '14px',
						]),
						self::heading("Smiles that feel\nat home.", 'h1', 'left', '#0c4a6e', [
							'fontFamily' => $f, 'fontSize' => '52px', 'fontWeight' => '800', 'lineHeight' => '1.08', 'marginBottom' => '16px',
						]),
						self::text('Gentle dentistry for families — cleanings, cosmetics, and same-day emergency care.', 'left', '#0369a1', [
							'fontFamily' => $f, 'fontSize' => '18px', 'maxWidth' => '460px',
						]),
						self::dual('Book appointment', 'New patient forms', $accent, '#bae6fd'),
					], [ 'verticalAlign' => 'middle', 'padding' => '20px 12px' ]),
					self::column(48, [
						self::image($hero, 'Dental care', [ 'borderRadius' => '20px' ]),
					], [ 'verticalAlign' => 'middle' ]),
				],
				[ 'padding' => '88px 24px', 'background' => '#F0F9FF', 'gap' => 36, 'minHeight' => '560px' ]
			),
			self::section(
				[
					self::column(25, [ self::icon_box('🦷', 'Cleanings', 'Preventive care every six months.') ]),
					self::column(25, [ self::icon_box('✨', 'Whitening', 'In-office brightening treatments.') ]),
					self::column(25, [ self::icon_box('😁', 'Invisalign', 'Clear aligners with digital scans.') ]),
					self::column(25, [ self::icon_box('⏱', 'Same-day', 'Emergency slots held daily.') ]),
				],
				[ 'padding' => '64px 24px', 'background' => '#ffffff', 'gap' => 16 ]
			),
			self::section(
				[
					self::column(33, [
						self::heading('★★★★★', 'h3', 'center', '#0284C7', [ 'fontFamily' => $f, 'fontSize' => '22px', 'marginBottom' => '12px' ]),
						self::testimonial('The team made my kids feel comfortable — first cavity-free visit in years.', 'Hannah G.', 'Parent'),
					]),
					self::column(33, [
						self::heading('★★★★★', 'h3', 'center', '#0284C7', [ 'fontFamily' => $f, 'fontSize' => '22px', 'marginBottom' => '12px' ]),
						self::testimonial('Clear explanations and zero pressure. My whitening results look natural.', 'Chris M.', 'Patient'),
					]),
					self::column(34, [
						self::heading('★★★★★', 'h3', 'center', '#0284C7', [ 'fontFamily' => $f, 'fontSize' => '22px', 'marginBottom' => '12px' ]),
						self::testimonial('Got me in same-day for a cracked tooth. Truly grateful.', 'Elena R.', 'Patient'),
					]),
				],
				[ 'padding' => '72px 24px', 'background' => '#e0f2fe', 'gap' => 18 ]
			),
			self::section(
				[
					self::column(50, [
						self::heading('Meet Dr. Patel', 'h2', 'left', '#0c4a6e', [ 'fontFamily' => $f, 'fontSize' => '32px' ]),
						self::text('Dr. Anika Patel brings 12 years of family dentistry with a calm chairside manner and modern digital tools.', 'left', '#0369a1', [ 'fontFamily' => $f ]),
					], [ 'verticalAlign' => 'middle' ]),
					self::column(50, [
						self::team('Dr. Anika Patel', 'Lead Dentist', 'Family & cosmetic care.', self::media()),
					]),
				],
				[ 'padding' => '64px 24px', 'background' => '#f8fafc', 'gap' => 28 ]
			),
			self::section(
				[
					self::column(50, [
						self::heading('Office hours', 'h2', 'left', '#ffffff', [ 'fontFamily' => $f, 'fontSize' => '28px' ]),
						self::hours([
							[ 'day' => 'Mon - Thu', 'hours' => '8:00 AM - 5:00 PM' ],
							[ 'day' => 'Friday', 'hours' => '8:00 AM - 2:00 PM' ],
							[ 'day' => 'Sat - Sun', 'hours' => 'Emergency only' ],
						]),
					]),
					self::column(50, [
						self::heading('Insurance', 'h2', 'left', '#ffffff', [ 'fontFamily' => $f, 'fontSize' => '28px' ]),
						self::text('We accept most PPO plans and offer transparent in-house memberships for uninsured patients.', 'left', '#bae6fd', [ 'fontFamily' => $f ]),
					]),
				],
				[ 'padding' => '64px 24px', 'background' => '#0369a1', 'gap' => 28, 'textColor' => '#ffffff' ]
			),
			self::section(
				[ self::column(100, [
					self::heading('Book your next visit', 'h2', 'center', '#0c4a6e', [ 'fontFamily' => $f, 'fontSize' => '34px' ]),
					self::text('New patients welcome — request a time that works for your family.', 'center', '#0369a1', [ 'fontFamily' => $f ]),
					self::button('Request appointment', $accent, 'center', [ 'borderRadius' => '999px' ]),
				]) ],
				[ 'padding' => '72px 24px', 'background' => '#F0F9FF' ]
			),
			self::site_footer($brand, $accent, 'Friendly family dentistry with modern tools and a calm chairside manner.', [
				'Care' => ['Cleanings', 'Whitening', 'Invisalign'],
				'Visit' => ['55 Smile Ave', 'Mon-Fri', 'hello@brightbite.com'],
				'Patients' => ['Forms', 'Insurance', 'FAQs'],
			], '#0c4a6e', '#ffffff', 'rgba(255,255,255,.72)', 'Manrope'),
		]));
	}

	private static function page_hotel($brand, $accent, $hero) {
		$f = self::font('Playfair Display');
		$b = self::font('Outfit');
		return self::doc(self::flatten([
			self::site_header($brand, '#FDE68A', ['Rooms', 'Amenities', 'Location', 'Reserve'], 'Reserve', '#1c1917', '#ffffff', 'Playfair Display'),
			self::section(
				[ self::column(100, [
					self::heading($brand, 'h1', 'center', '#FDE68A', [
						'fontFamily' => $f, 'fontSize' => '14px', 'letterSpacing' => '0.22em',
						'textTransform' => 'uppercase', 'marginBottom' => '20px',
					]),
					self::heading("Rest well.\nStay longer.", 'h1', 'center', '#ffffff', [
						'fontFamily' => $f, 'fontSize' => '58px', 'fontWeight' => '700', 'lineHeight' => '1.08', 'marginBottom' => '18px',
					]),
					self::text('A boutique harbor hotel with quiet rooms, thoughtful amenities, and golden-hour views.', 'center', 'rgba(255,255,255,.9)', [
						'fontFamily' => $b, 'fontSize' => '18px', 'maxWidth' => '520px', 'marginBottom' => '28px',
					]),
					self::button('Check availability', '#854D0E', 'center', [ 'borderRadius' => '0px' ]),
				], [ 'verticalAlign' => 'middle' ]) ],
				[
					'padding' => '130px 24px', 'minHeight' => '680px', 'textColor' => '#ffffff',
					'background' => 'linear-gradient(180deg, rgba(28,25,23,.35) 0%, rgba(28,25,23,.8) 100%)',
					'backgroundImage' => $hero,
				]
			),
			self::section(
				[
					self::column(25, [ self::icon_box('🛏', 'King suites', 'Floor-to-ceiling windows and rain showers.') ]),
					self::column(25, [ self::icon_box('🍽', 'Harbor kitchen', 'Seasonal breakfast and evening small plates.') ]),
					self::column(25, [ self::icon_box('🌊', 'Spa bath', 'Steam, sauna, and coastal treatments.') ]),
					self::column(25, [ self::icon_box('🚲', 'Concierge', 'Bikes, boats, and local reservations.') ]),
				],
				[ 'padding' => '72px 24px', 'background' => '#FFFBEB', 'gap' => 16 ]
			),
			self::section(
				[
					self::column(33, [ self::price_table('Harbor Twin', '$189', '/night', [ 'Two queen beds', 'Harbor view', 'Breakfast included' ], 'Reserve', '#ffffff') ]),
					self::column(33, [ self::price_table('Oak Suite', '$289', '/night', [ 'King bed', 'Sitting room', 'Late checkout' ], 'Reserve', '#FEF3C7', true) ]),
					self::column(34, [ self::price_table('Anchor Penthouse', '$449', '/night', [ 'Private terrace', 'Butler pantry', 'Spa credits' ], 'Reserve', '#ffffff') ]),
				],
				[ 'padding' => '72px 24px', 'background' => '#ffffff', 'gap' => 18 ]
			),
			self::section(
				[ self::column(100, [
					self::heading('A look inside', 'h2', 'center', '#ffffff', [ 'fontFamily' => $f, 'fontSize' => '34px', 'marginBottom' => '24px' ]),
					self::gallery([
						[ 'src' => self::media(), 'alt' => 'Room' ],
						[ 'src' => self::media(), 'alt' => 'Lobby' ],
						[ 'src' => self::media(), 'alt' => 'Pool' ],
						[ 'src' => self::media(), 'alt' => 'View' ],
					], 2),
				]) ],
				[ 'padding' => '72px 24px', 'background' => '#44403c', 'textColor' => '#ffffff' ]
			),
			self::section(
				[
					self::column(50, [
						self::heading('Location', 'h2', 'left', '#78350f', [ 'fontFamily' => $f, 'fontSize' => '34px' ]),
						self::text("12 Pier Walk · Harbor District\nFive minutes to the ferry, ten to downtown. Valet and secure parking available.", 'left', '#92400e', [ 'fontFamily' => $b ]),
					], [ 'verticalAlign' => 'middle' ]),
					self::column(50, [
						self::image(self::media(), 'Hotel exterior', [ 'borderRadius' => '8px' ]),
					], [ 'verticalAlign' => 'middle' ]),
				],
				[ 'padding' => '72px 24px', 'background' => '#FEF3C7', 'gap' => 32 ]
			),
			self::section(
				[ self::column(100, [
					self::heading('Reserve your stay', 'h2', 'center', '#ffffff', [ 'fontFamily' => $f, 'fontSize' => '36px' ]),
					self::text('Best rate guaranteed when you book direct.', 'center', '#FDE68A', [ 'fontFamily' => $b ]),
					self::dual('Book direct', 'Call front desk', '#FDE68A', 'rgba(255,255,255,.15)'),
				]) ],
				[ 'padding' => '80px 24px', 'background' => '#854D0E', 'textColor' => '#ffffff' ]
			),
			self::site_footer($brand, '#FDE68A', 'A boutique harbor hotel for unhurried stays and golden-hour views.', [
				'Stay' => ['Rooms', 'Suites', 'Offers'],
				'Visit' => ['12 Pier Walk', 'Front desk 24/7', 'stay@oakanchor.com'],
				'Explore' => ['Dining', 'Spa', 'Events'],
			], '#292524', '#ffffff', 'rgba(255,255,255,.72)', 'Playfair Display'),
		]));
	}

	private static function page_nonprofit($brand, $accent, $hero) {
		$f = self::font('Manrope');
		return self::doc(self::flatten([
			self::site_header($brand, $accent, ['Mission', 'Programs', 'Stories', 'Donate'], 'Donate', '#F0FDF4', '#14532d', 'Manrope'),
			self::section(
				[
					self::column(25, [ self::counter(12000, 'Meals served', '+', '#15803D') ]),
					self::column(25, [ self::counter(48, 'Partner schools', '', '#15803D') ]),
					self::column(25, [ self::counter(860, 'Volunteers', '+', '#15803D') ]),
					self::column(25, [ self::counter(14, 'Neighborhoods', '', '#15803D') ]),
				],
				[ 'padding' => '64px 24px', 'background' => '#14532d', 'gap' => 12, 'textColor' => '#ffffff' ]
			),
			self::section(
				[
					self::column(52, [
						self::heading('Our mission', 'h2', 'left', '#14532d', [ 'fontFamily' => $f, 'fontSize' => '40px' ]),
						self::text('Horizon Collective expands access to food, tutoring, and green spaces so every neighborhood can thrive — with neighbors leading the work.', 'left', '#166534', [
							'fontFamily' => $f, 'fontSize' => '18px',
						]),
						self::button('Read our story', $accent, 'left', [ 'borderRadius' => '8px' ]),
					], [ 'verticalAlign' => 'middle' ]),
					self::column(48, [
						self::image($hero, 'Community', [ 'borderRadius' => '16px' ]),
					], [ 'verticalAlign' => 'middle' ]),
				],
				[ 'padding' => '80px 24px', 'background' => '#ffffff', 'gap' => 36, 'minHeight' => '520px' ]
			),
			self::section(
				[
					self::column(33, [ self::icon_box('🥗', 'Food access', 'Weekly markets and pantry partnerships.') ]),
					self::column(33, [ self::icon_box('📚', 'Youth tutoring', 'After-school literacy and STEM labs.') ]),
					self::column(34, [ self::icon_box('🌿', 'Green blocks', 'Pocket parks and tree plantings.') ]),
				],
				[ 'padding' => '64px 24px', 'background' => '#ECFDF5', 'gap' => 18 ]
			),
			self::section(
				[
					self::column(50, [ self::testimonial('The tutoring lab changed how my daughter sees school — she asks to go early now.', 'Rosa M.', 'Parent volunteer') ]),
					self::column(50, [ self::testimonial('Horizon treats neighbors as partners, not recipients. That dignity matters.', 'Keith L.', 'Block captain') ]),
				],
				[ 'padding' => '64px 24px', 'background' => '#F0FDF4', 'gap' => 20 ]
			),
			self::section(
				[ self::column(100, [
					self::heading('Programs in action', 'h2', 'center', '#ffffff', [ 'fontFamily' => $f, 'fontSize' => '32px', 'marginBottom' => '24px' ]),
					self::gallery([
						[ 'src' => self::media(), 'alt' => 'Program 1' ],
						[ 'src' => self::media(), 'alt' => 'Program 2' ],
						[ 'src' => self::media(), 'alt' => 'Program 3' ],
					], 3),
				]) ],
				[ 'padding' => '72px 24px', 'background' => '#166534', 'textColor' => '#ffffff' ]
			),
			self::section(
				[
					self::column(40, [
						self::heading('How gifts are used', 'h2', 'left', '#14532d', [ 'fontFamily' => $f, 'fontSize' => '30px' ]),
					]),
					self::column(60, [
						self::accordion([
							[ 'title' => 'Where does $50 go?', 'content' => 'Funds a week of pantry staples for a family of four.' ],
							[ 'title' => 'Can I volunteer?', 'content' => 'Yes — tutoring, markets, and park days open monthly.' ],
							[ 'title' => 'Is my donation tax-deductible?', 'content' => 'Horizon Collective is a 501(c)(3). Receipts emailed instantly.' ],
						]),
					]),
				],
				[ 'padding' => '72px 24px', 'background' => '#ffffff', 'gap' => 28 ]
			),
			self::section(
				[ self::column(100, [
					self::heading('Fuel the next neighborhood win', 'h2', 'center', '#ffffff', [ 'fontFamily' => $f, 'fontSize' => '36px' ]),
					self::text('Monthly gifts keep tutoring labs and markets running year-round.', 'center', '#BBF7D0', [ 'fontFamily' => $f ]),
					self::button('Donate now', '#ffffff', 'center', [ 'borderRadius' => '8px' ]),
				]) ],
				[ 'padding' => '80px 24px', 'background' => '#15803D', 'textColor' => '#ffffff' ]
			),
			self::site_footer($brand, $accent, 'Neighbors building food access, tutoring, and green spaces — together.', [
				'Act' => ['Donate', 'Volunteer', 'Partner'],
				'Learn' => ['Impact report', 'Programs', 'News'],
				'Contact' => ['hello@horizon.org', 'Press', 'Careers'],
			], '#14532d', '#ffffff', 'rgba(255,255,255,.72)', 'Manrope'),
		]));
	}

	private static function page_portfolio($brand, $accent, $hero) {
		$f = self::font('Sora');
		return self::doc(self::flatten([
			self::site_header($brand, $accent, ['Work', 'Skills', 'About', 'Contact'], 'Hire me', '#ffffff', '#0f172a', 'Sora'),
			self::section(
				[ self::column(100, [
					self::heading('Product designer', 'h1', 'left', $accent, [
						'fontFamily' => $f, 'fontSize' => '14px', 'letterSpacing' => '0.16em',
						'textTransform' => 'uppercase', 'marginBottom' => '16px',
					]),
					self::heading("Alex\nRivera", 'h1', 'left', '#0f172a', [
						'fontFamily' => $f, 'fontSize' => '72px', 'fontWeight' => '800', 'lineHeight' => '0.95', 'marginBottom' => '20px',
					]),
					self::text('I design digital products that feel inevitable — research-led, visually sharp, and built with engineers in the room.', 'left', '#475569', [
						'fontFamily' => $f, 'fontSize' => '18px', 'maxWidth' => '520px',
					]),
					self::dual('View work', 'Download CV', $accent, '#e2e8f0'),
				], [ 'verticalAlign' => 'middle', 'padding' => '20px 8px' ]) ],
				[ 'padding' => '110px 24px 90px', 'background' => '#faf5ff', 'minHeight' => '580px' ]
			),
			self::section(
				[
					self::column(40, [
						self::heading('Skills', 'h2', 'left', '#ffffff', [ 'fontFamily' => $f, 'fontSize' => '32px' ]),
					]),
					self::column(60, [
						self::widget('icon-list', [
							'items' => [
								[ 'icon' => '→', 'text' => 'Product strategy & UX research' ],
								[ 'icon' => '→', 'text' => 'Interface systems & design ops' ],
								[ 'icon' => '→', 'text' => 'Prototyping in Figma & code' ],
								[ 'icon' => '→', 'text' => 'Design leadership for startups' ],
							],
							'color' => '#ffffff',
							'style' => self::style([ 'marginBottom' => '0px', 'fontFamily' => $f ]),
						]),
					]),
				],
				[ 'padding' => '64px 24px', 'background' => '#5b21b6', 'gap' => 24, 'textColor' => '#ffffff' ]
			),
			self::section(
				[ self::column(100, [
					self::heading('Selected projects', 'h2', 'left', '#0f172a', [ 'fontFamily' => $f, 'fontSize' => '32px', 'marginBottom' => '24px' ]),
					self::gallery([
						[ 'src' => $hero, 'alt' => 'Project 1' ],
						[ 'src' => self::media(), 'alt' => 'Project 2' ],
						[ 'src' => self::media(), 'alt' => 'Project 3' ],
						[ 'src' => self::media(), 'alt' => 'Project 4' ],
						[ 'src' => self::media(), 'alt' => 'Project 5' ],
						[ 'src' => self::media(), 'alt' => 'Project 6' ],
					], 3),
				]) ],
				[ 'padding' => '72px 24px', 'background' => '#ffffff' ]
			),
			self::section(
				[
					self::column(50, [
						self::image(self::media(), 'Alex Rivera', [ 'borderRadius' => '12px' ]),
					]),
					self::column(50, [
						self::heading('About', 'h2', 'left', '#0f172a', [ 'fontFamily' => $f, 'fontSize' => '32px' ]),
						self::text('Previously at two Series B startups and a design studio. I care about clarity, craft, and shipping with kindness.', 'left', '#475569', [ 'fontFamily' => $f ]),
						self::text('Available for select product design engagements in 2026.', 'left', $accent, [ 'fontFamily' => $f, 'fontWeight' => '700' ]),
					], [ 'verticalAlign' => 'middle' ]),
				],
				[ 'padding' => '72px 24px', 'background' => '#f1f5f9', 'gap' => 36 ]
			),
			self::section(
				[
					self::column(33, [ self::counter(40, 'Products shipped', '+', $accent) ]),
					self::column(33, [ self::counter(12, 'Years designing', '', $accent) ]),
					self::column(34, [ self::counter(8, 'Industries', '', $accent) ]),
				],
				[ 'padding' => '56px 24px', 'background' => '#ede9fe', 'gap' => 12 ]
			),
			self::section(
				[ self::column(100, [
					self::heading('Let\'s build something sharp', 'h2', 'center', '#ffffff', [ 'fontFamily' => $f, 'fontSize' => '34px' ]),
					self::text('Open to freelance and fractional design leadership.', 'center', '#ddd6fe', [ 'fontFamily' => $f ]),
					self::dual('Email me', 'Book a call', '#ffffff', 'rgba(255,255,255,.15)'),
				]) ],
				[ 'padding' => '80px 24px', 'background' => '#7C3AED', 'textColor' => '#ffffff' ]
			),
			self::site_footer($brand, $accent, 'Product designer focused on clear systems, sharp interfaces, and kind collaboration.', [
				'Work' => ['Case studies', 'Process', 'Speaking'],
				'Contact' => ['hello@alexrivera.design', 'LinkedIn', 'Dribbble'],
				'Elsewhere' => ['Newsletter', 'Reading list', 'CV'],
			], '#1e1b4b', '#ffffff', 'rgba(255,255,255,.72)', 'Sora'),
		]));
	}

	private static function page_education($brand, $accent, $hero) {
		$f = self::font('DM Sans');
		return self::doc(self::flatten([
			self::site_header($brand, $accent, ['Courses', 'Curriculum', 'Mentors', 'Enroll'], 'Enroll', '#EFF6FF', '#1e3a8a', 'DM Sans'),
			self::section(
				[
					self::column(55, [
						self::heading('Online learning', 'h1', 'left', '#2563EB', [
							'fontFamily' => $f, 'fontSize' => '13px', 'letterSpacing' => '0.14em',
							'textTransform' => 'uppercase', 'marginBottom' => '14px',
						]),
						self::heading("Learn skills that\nget used Monday.", 'h1', 'left', '#1e3a8a', [
							'fontFamily' => $f, 'fontSize' => '48px', 'fontWeight' => '800', 'lineHeight' => '1.08', 'marginBottom' => '16px',
						]),
						self::text('Cohort-based courses with mentors, projects, and accountability — not endless video libraries.', 'left', '#1d4ed8', [
							'fontFamily' => $f, 'fontSize' => '18px', 'maxWidth' => '480px',
						]),
						self::button('Browse courses', $accent, 'left', [ 'borderRadius' => '10px' ]),
					], [ 'verticalAlign' => 'middle' ]),
					self::column(45, [
						self::image($hero, 'Students learning', [ 'borderRadius' => '16px' ]),
					], [ 'verticalAlign' => 'middle' ]),
				],
				[ 'padding' => '88px 24px', 'background' => '#EFF6FF', 'gap' => 36, 'minHeight' => '560px' ]
			),
			self::section(
				[
					self::column(50, [ self::price_table('Product Design', '$490', '/cohort', [ '6 weeks live', 'Portfolio project', 'Mentor reviews' ], 'Enroll', '#ffffff', true) ]),
					self::column(50, [ self::price_table('Data Analytics', '$540', '/cohort', [ '8 weeks live', 'SQL + dashboards', 'Career clinic' ], 'Enroll', '#dbeafe') ]),
				],
				[ 'padding' => '72px 24px', 'background' => '#ffffff', 'gap' => 22 ]
			),
			self::section(
				[
					self::column(40, [
						self::heading('Curriculum', 'h2', 'left', '#ffffff', [ 'fontFamily' => $f, 'fontSize' => '32px' ]),
						self::text('Each week ships a tangible artifact you can show employers.', 'left', '#bfdbfe', [ 'fontFamily' => $f ]),
					]),
					self::column(60, [
						self::accordion([
							[ 'title' => 'Week 1-2 · Foundations', 'content' => 'Mindsets, tool setup, and your first critique-ready draft.' ],
							[ 'title' => 'Week 3-4 · Build', 'content' => 'Deep project work with mentor office hours twice weekly.' ],
							[ 'title' => 'Week 5-6 · Ship & present', 'content' => 'Polish, portfolio framing, and a live demo day.' ],
						]),
					]),
				],
				[ 'padding' => '72px 24px', 'background' => '#1e40af', 'gap' => 28, 'textColor' => '#ffffff' ]
			),
			self::section(
				[
					self::column(33, [ self::team('Dr. Leah Kim', 'Design Mentor', 'Ex-Figma · 10 yrs teaching.', self::media()) ]),
					self::column(33, [ self::team('Omar Hassan', 'Data Mentor', 'Analytics lead at Scale.', self::media()) ]),
					self::column(34, [ self::team('Sofia Grant', 'Career Coach', 'Hiring manager turned coach.', self::media()) ]),
				],
				[ 'padding' => '72px 24px', 'background' => '#f8fafc', 'gap' => 20 ]
			),
			self::section(
				[
					self::column(25, [ self::counter(2400, 'Alumni', '+', $accent) ]),
					self::column(25, [ self::counter(92, 'Completion rate', '%', $accent) ]),
					self::column(25, [ self::counter(4, 'Avg mentor rating', '.8', $accent) ]),
					self::column(25, [ self::counter(18, 'Cohorts / year', '', $accent) ]),
				],
				[ 'padding' => '56px 24px', 'background' => '#dbeafe', 'gap' => 12 ]
			),
			self::section(
				[ self::column(100, [
					self::testimonial('I shipped a portfolio case study in six weeks and landed interviews the month after.', 'Jamie Cole', 'Alumni, Product Design'),
				]) ],
				[ 'padding' => '64px 24px', 'background' => '#ffffff', 'contentWidth' => 760 ]
			),
			self::section(
				[ self::column(100, [
					self::cta('Enroll in the next cohort', 'Seats are limited — applications close two weeks before kickoff.', 'Apply now', $accent),
				]) ],
				[ 'padding' => '48px 24px 80px', 'background' => '#EFF6FF' ]
			),
			self::site_footer($brand, $accent, 'Cohort-based courses with mentors, projects, and accountability that sticks.', [
				'Learn' => ['Courses', 'Curriculum', 'FAQ'],
				'Community' => ['Alumni', 'Events', 'Blog'],
				'Contact' => ['hello@brightpath.edu', 'Help center', 'Press'],
			], '#1e3a8a', '#ffffff', 'rgba(255,255,255,.72)', 'DM Sans'),
		]));
	}

	private static function page_consulting($brand, $accent, $hero) {
		$f = self::font('Manrope');
		return self::doc(self::flatten([
			self::site_header($brand, $accent, ['Approach', 'Services', 'Cases', 'Contact'], 'Book discovery', '#F0FDFA', '#134e4a', 'Manrope'),
			self::section(
				[
					self::column(25, [ self::icon_box('01', 'Discover', 'Stakeholder interviews and data diagnostics.', 'center', true) ]),
					self::column(25, [ self::icon_box('02', 'Design', 'Prioritized roadmap with owners.', 'center', true) ]),
					self::column(25, [ self::icon_box('03', 'Deploy', 'Working sessions with your team.', 'center', true) ]),
					self::column(25, [ self::icon_box('04', 'Deliver', 'Metrics, playbooks, and handoff.', 'center', true) ]),
				],
				[ 'padding' => '56px 24px', 'background' => '#0f766e', 'gap' => 14, 'textColor' => '#ffffff' ]
			),
			self::section(
				[
					self::column(55, [
						self::heading($brand, 'h1', 'left', '#0d9488', [
							'fontFamily' => $f, 'fontSize' => '13px', 'letterSpacing' => '0.14em',
							'textTransform' => 'uppercase', 'marginBottom' => '14px',
						]),
						self::heading("Strategy that\noperates.", 'h1', 'left', '#134e4a', [
							'fontFamily' => $f, 'fontSize' => '52px', 'fontWeight' => '800', 'lineHeight' => '1.06', 'marginBottom' => '16px',
						]),
						self::text('We help leadership teams clarify growth bets, align org design, and install operating rhythms that last.', 'left', '#0f766e', [
							'fontFamily' => $f, 'fontSize' => '18px', 'maxWidth' => '480px',
						]),
						self::button('Book discovery call', $accent, 'left', [ 'borderRadius' => '8px' ]),
					], [ 'verticalAlign' => 'middle' ]),
					self::column(45, [
						self::image($hero, 'Consulting workshop', [ 'borderRadius' => '12px' ]),
					], [ 'verticalAlign' => 'middle' ]),
				],
				[ 'padding' => '80px 24px', 'background' => '#ffffff', 'gap' => 36, 'minHeight' => '540px' ]
			),
			self::section(
				[
					self::column(33, [ self::icon_box('◎', 'Growth strategy', 'Market focus, pricing, and GTM clarity.') ]),
					self::column(33, [ self::icon_box('◎', 'Operating model', 'Roles, rituals, and decision rights.') ]),
					self::column(34, [ self::icon_box('◎', 'Change enablement', 'Comms plans and manager toolkits.') ]),
				],
				[ 'padding' => '64px 24px', 'background' => '#F0FDFA', 'gap' => 18 ]
			),
			self::section(
				[
					self::column(50, [
						self::heading('Case snapshot', 'h2', 'left', '#ffffff', [ 'fontFamily' => $f, 'fontSize' => '32px' ]),
						self::text('A Series C SaaS client realigned three product lines and cut decision latency by half in one quarter.', 'left', '#99f6e4', [ 'fontFamily' => $f ]),
					], [ 'verticalAlign' => 'middle' ]),
					self::column(50, [
						self::testimonial('Meridian installed clarity without the theater. Our exec team finally rows the same direction.', 'Helen Cho', 'COO, Northwind'),
					]),
				],
				[ 'padding' => '72px 24px', 'background' => '#134e4a', 'gap' => 28, 'textColor' => '#ffffff' ]
			),
			self::section(
				[
					self::column(25, [ self::counter(60, 'Engagements', '+', $accent) ]),
					self::column(25, [ self::counter(18, 'Industries', '', $accent) ]),
					self::column(25, [ self::counter(9, 'Avg NPS', '/10', $accent) ]),
					self::column(25, [ self::counter(3, 'Week discovery', '', $accent) ]),
				],
				[ 'padding' => '56px 24px', 'background' => '#ccfbf1', 'gap' => 12 ]
			),
			self::section(
				[
					self::column(40, [
						self::heading('Engagement FAQ', 'h2', 'left', '#134e4a', [ 'fontFamily' => $f, 'fontSize' => '30px' ]),
					]),
					self::column(60, [
						self::accordion([
							[ 'title' => 'How long is a typical project?', 'content' => 'Discovery is 3 weeks. Full engagements run 8-16 weeks depending on scope.' ],
							[ 'title' => 'Do you work with our team or replace them?', 'content' => 'We embed with your leaders — the goal is capability, not dependency.' ],
							[ 'title' => 'What does discovery include?', 'content' => 'Interviews, data review, and a prioritized recommendation deck.' ],
						]),
					]),
				],
				[ 'padding' => '72px 24px', 'background' => '#f8fafc', 'gap' => 28 ]
			),
			self::section(
				[ self::column(100, [
					self::cta('Start with a discovery call', 'Thirty minutes to pressure-test fit and urgency.', 'Schedule now', $accent),
				]) ],
				[ 'padding' => '48px 24px 80px', 'background' => '#ECFDF5' ]
			),
			self::site_footer($brand, $accent, 'Strategy consulting that installs operating clarity — not slide decks that gather dust.', [
				'Firm' => ['Approach', 'Services', 'Insights'],
				'Contact' => ['Discovery call', 'hello@meridian.com', 'Press'],
				'Follow' => ['LinkedIn', 'Substack', 'Newsletter'],
			], '#134e4a', '#ffffff', 'rgba(255,255,255,.72)', 'Manrope'),
		]));
	}

	private static function page_ecommerce($brand, $accent, $hero) {
		$f = self::font('Outfit');
		return self::doc(self::flatten([
			self::site_header($brand, $accent, ['Shop', 'New', 'About', 'Support'], 'Shop', '#FFFBEB', '#1c1917', 'Outfit'),
			self::section(
				[ self::column(100, [
					self::heading('Free shipping this week · Use code FIELD10', 'h2', 'center', '#78350f', [
						'fontFamily' => $f, 'fontSize' => '15px', 'fontWeight' => '700',
						'letterSpacing' => '0.04em', 'marginBottom' => '0px',
					]),
				]) ],
				[ 'padding' => '14px 24px', 'background' => '#FDE68A', 'textColor' => '#78350f' ]
			),
			self::section(
				[
					self::column(50, [
						self::heading($brand, 'h1', 'left', $accent, [
							'fontFamily' => $f, 'fontSize' => '13px', 'letterSpacing' => '0.14em',
							'textTransform' => 'uppercase', 'marginBottom' => '14px',
						]),
						self::heading("Goods made for\neveryday rituals", 'h1', 'left', '#1c1917', [
							'fontFamily' => $f, 'fontSize' => '48px', 'fontWeight' => '800', 'lineHeight' => '1.08', 'marginBottom' => '16px',
						]),
						self::text('Thoughtful essentials with durable materials and quiet design.', 'left', '#57534e', [
							'fontFamily' => $f, 'fontSize' => '17px',
						]),
						self::button('Shop collection', $accent, 'left', [ 'borderRadius' => '8px' ]),
					], [ 'verticalAlign' => 'middle', 'padding' => '20px' ]),
					self::column(50, [
						self::image($hero, 'Featured product', [ 'borderRadius' => '12px' ]),
					], [ 'verticalAlign' => 'middle' ]),
				],
				[ 'padding' => '72px 24px', 'background' => '#FFFBEB', 'gap' => 32, 'minHeight' => '520px' ]
			),
			self::section(
				[
					self::column(33, [
						self::image(self::media(), 'Canvas tote', [ 'borderRadius' => '10px' ]),
						self::heading('Canvas tote', 'h3', 'left', '#1c1917', [ 'fontFamily' => $f, 'fontSize' => '20px', 'marginBottom' => '4px' ]),
						self::text('$48', 'left', $accent, [ 'fontFamily' => $f, 'fontSize' => '16px', 'fontWeight' => '700' ]),
					]),
					self::column(33, [
						self::image(self::media(), 'Daylight mug', [ 'borderRadius' => '10px' ]),
						self::heading('Daylight mug', 'h3', 'left', '#1c1917', [ 'fontFamily' => $f, 'fontSize' => '20px', 'marginBottom' => '4px' ]),
						self::text('$28', 'left', $accent, [ 'fontFamily' => $f, 'fontSize' => '16px', 'fontWeight' => '700' ]),
					]),
					self::column(34, [
						self::image(self::media(), 'Desk lamp', [ 'borderRadius' => '10px' ]),
						self::heading('Desk lamp', 'h3', 'left', '#1c1917', [ 'fontFamily' => $f, 'fontSize' => '20px', 'marginBottom' => '4px' ]),
						self::text('$120', 'left', $accent, [ 'fontFamily' => $f, 'fontSize' => '16px', 'fontWeight' => '700' ]),
					]),
				],
				[ 'padding' => '64px 24px', 'background' => '#ffffff', 'gap' => 22 ]
			),
			self::section(
				[
					self::column(33, [ self::icon_box('🧵', 'Better materials', 'Natural fibers and lasting hardware.') ]),
					self::column(33, [ self::icon_box('📦', 'Ships fast', 'Most orders leave within 48 hours.') ]),
					self::column(34, [ self::icon_box('♻️', 'Easy returns', '30 days, no fuss.') ]),
				],
				[ 'padding' => '56px 24px', 'background' => '#FFF7ED', 'gap' => 18 ]
			),
			self::section(
				[
					self::column(50, [
						self::heading('Shipping', 'h3', 'left', '#1c1917', [ 'fontFamily' => $f, 'fontSize' => '22px' ]),
						self::text('Free shipping on orders over $75. Standard delivery 3-5 business days.', 'left', '#57534e', [ 'fontFamily' => $f ]),
					]),
					self::column(50, [
						self::heading('Returns', 'h3', 'left', '#1c1917', [ 'fontFamily' => $f, 'fontSize' => '22px' ]),
						self::text('30-day returns on unused items. Prepaid label included in every box.', 'left', '#57534e', [ 'fontFamily' => $f ]),
					]),
				],
				[ 'padding' => '56px 24px', 'background' => '#fafaf9', 'gap' => 28 ]
			),
			self::section(
				[
					self::column(50, [ self::testimonial('Quality you can feel — the tote has become my daily bag.', 'Morgan S.', 'Customer') ]),
					self::column(50, [ self::testimonial('Packaging was lovely and shipping was faster than expected.', 'Jules R.', 'Customer') ]),
				],
				[ 'padding' => '56px 24px', 'background' => '#FEF3C7', 'gap' => 20 ]
			),
			self::section(
				[ self::column(100, [
					self::heading('Shop the collection', 'h2', 'center', '#ffffff', [ 'fontFamily' => $f, 'fontSize' => '34px' ]),
					self::text('Warm essentials for home and desk — built to last.', 'center', '#FDE68A', [ 'fontFamily' => $f ]),
					self::button('Shop now', '#FDE68A', 'center', [ 'borderRadius' => '8px' ]),
				]) ],
				[ 'padding' => '80px 24px', 'background' => $accent, 'textColor' => '#ffffff' ]
			),
			self::site_footer($brand, $accent, 'Everyday essentials with durable materials and quiet, lasting design.', [
				'Shop' => ['New arrivals', 'Bestsellers', 'Gift cards'],
				'Help' => ['Shipping', 'Returns', 'support@fieldco.com'],
				'Follow' => ['Instagram', 'Pinterest', 'Newsletter'],
			], '#1c1917', '#ffffff', 'rgba(255,255,255,.72)', 'Outfit'),
		]));
	}
}
