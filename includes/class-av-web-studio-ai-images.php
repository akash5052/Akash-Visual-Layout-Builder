<?php

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Image slots for AI-generated pages. Always uses files shipped in this plugin.
 */
class Av_Web_Studio_AI_Images {

	/**
	 * Map industry keys to bundled template image folders (assets/images/templates).
	 *
	 * @var array<string,string>
	 */
	private static $industry_files = [
		'coffee'        => 'coffee',
		'restaurant'    => 'restaurant',
		'bakery'        => 'bakery',
		'fitness'       => 'fitness',
		'yoga'          => 'yoga',
		'health'        => 'dentist',
		'beauty'        => 'beauty',
		'realestate'    => 'realestate',
		'saas'          => 'saas',
		'agency'        => 'agency',
		'education'     => 'education',
		'ecommerce'     => 'ecommerce',
		'travel'        => 'hotel',
		'law'           => 'law',
		'photography'   => 'photographer',
		'cleaning'      => 'consulting',
		'plumbing'      => 'manufacturing',
		'manufacturing' => 'manufacturing',
		'steel'         => 'steel',
		'business'      => 'consulting',
		'portrait'      => 'photographer',
		'creative'      => 'agency',
		'food'          => 'restaurant',
		'technology'    => 'saas',
	];

	/**
	 * Scene recipes per industry + section role for highly relevant AI prompts.
	 *
	 * @var array<string,array<string,string>>
	 */
	private static $scene_recipes = [
		'coffee' => [
			'hero'     => 'wide cinematic photo of a specialty coffee shop interior with espresso bar, warm morning window light, inviting atmosphere',
			'features' => 'close-up of a barista pouring latte art into a ceramic cup on a wooden counter',
			'gallery'  => 'artisan roasted coffee beans and pour-over dripper on marble, shallow depth of field',
			'about'    => 'friendly barista smiling behind a cafe counter with fresh pastries',
			'menu'     => 'overhead flat lay of espresso, cappuccino and croissant on a cafe table',
			'default'  => 'professional coffee shop photography, espresso machine and cozy seating',
		],
		'restaurant' => [
			'hero'     => 'elegant restaurant dining room with set tables, soft evening lighting, fine dining atmosphere',
			'features' => 'chef plating a gourmet dish in a professional kitchen',
			'gallery'  => 'beautifully plated seasonal entree on white ceramic, restaurant photography',
			'menu'     => 'overhead of signature dishes and wine glasses on a restaurant table',
			'default'  => 'upscale restaurant food and dining photography',
		],
		'bakery' => [
			'hero'     => 'warm bakery storefront with fresh bread and pastries in display cases',
			'features' => 'baker shaping dough on a floured wooden table',
			'gallery'  => 'assorted artisan breads and croissants on a cooling rack',
			'default'  => 'artisan bakery photography with fresh pastries',
		],
		'fitness' => [
			'hero'     => 'modern gym with free weights and motivated athletes training, dramatic lighting',
			'features' => 'personal trainer coaching a client through a strength exercise',
			'gallery'  => 'close-up of kettlebells and resistance bands in a clean fitness studio',
			'default'  => 'professional fitness gym photography',
		],
		'yoga' => [
			'hero'     => 'bright yoga studio with people in peaceful warrior pose, natural light',
			'features' => 'close-up of a yoga instructor demonstrating a calm stretch on a mat',
			'gallery'  => 'meditation cushions and yoga mats in a serene wellness studio',
			'default'  => 'peaceful yoga and wellness studio photography',
		],
		'health' => [
			'hero'     => 'modern medical clinic reception with clean design and soft natural light',
			'features' => 'doctor consulting with a patient in a bright examination room',
			'gallery'  => 'medical professionals collaborating in a contemporary healthcare facility',
			'default'  => 'professional healthcare clinic photography',
		],
		'beauty' => [
			'hero'     => 'luxurious beauty salon interior with styling chairs and soft glamorous lighting',
			'features' => 'stylist carefully cutting hair in a modern salon',
			'gallery'  => 'spa treatment room with towels, oils and candles',
			'default'  => 'beauty salon and spa photography',
		],
		'realestate' => [
			'hero'     => 'stunning modern home exterior with landscaped yard at golden hour',
			'features' => 'bright open-plan living room with large windows and stylish furniture',
			'gallery'  => 'architectural detail of a luxury kitchen and dining area',
			'default'  => 'professional real estate property photography',
		],
		'saas' => [
			'hero'     => 'modern tech workspace with dual monitors showing clean software dashboards',
			'features' => 'close-up of hands typing on a laptop with analytics charts on screen',
			'gallery'  => 'startup team collaborating around a whiteboard in a bright office',
			'default'  => 'modern SaaS product and technology workspace photography',
		],
		'technology' => [
			'hero'     => 'futuristic technology workspace with screens, code and soft blue lighting',
			'features' => 'designer arranging website sections on a laptop',
			'default'  => 'professional technology and software photography',
		],
		'agency' => [
			'hero'     => 'creative agency studio with mood boards, design screens and natural light',
			'features' => 'designers collaborating on a branding project at a large table',
			'gallery'  => 'close-up of creative tools, sketches and color swatches',
			'default'  => 'creative marketing agency studio photography',
		],
		'education' => [
			'hero'     => 'bright classroom or learning space with students engaged in a lesson',
			'features' => 'teacher helping a student with a laptop in a modern academy',
			'gallery'  => 'books, notebooks and learning materials on a study desk',
			'default'  => 'education and learning environment photography',
		],
		'ecommerce' => [
			'hero'     => 'stylish retail boutique with curated product displays and soft lighting',
			'features' => 'hands unboxing a premium product on a clean surface',
			'gallery'  => 'fashion or lifestyle product flat lay for an online store',
			'default'  => 'retail shopping and ecommerce product photography',
		],
		'travel' => [
			'hero'     => 'breathtaking travel destination landscape at sunrise, wanderlust atmosphere',
			'features' => 'traveler with backpack overlooking a scenic viewpoint',
			'gallery'  => 'luxury hotel pool or resort terrace with ocean view',
			'default'  => 'inspiring travel and destination photography',
		],
		'law' => [
			'hero'     => 'prestigious law firm office with law books and professional wood interiors',
			'features' => 'attorney reviewing documents at a polished desk',
			'default'  => 'professional law firm office photography',
		],
		'photography' => [
			'hero'     => 'photographer shooting a creative portrait session in a studio',
			'features' => 'professional camera and lens on a studio backdrop',
			'gallery'  => 'artistic portrait lighting setup in a photo studio',
			'default'  => 'professional photography studio imagery',
		],
		'cleaning' => [
			'hero'     => 'sparkling clean modern home interior after professional cleaning',
			'features' => 'cleaning professional wiping a kitchen counter with eco supplies',
			'default'  => 'professional home cleaning service photography',
		],
		'plumbing' => [
			'hero'     => 'skilled plumber repairing a modern bathroom fixture with tools',
			'features' => 'close-up of professional plumbing tools and pipes',
			'default'  => 'professional home services and plumbing photography',
		],
		'manufacturing' => [
			'hero'     => 'wide cinematic photo of a modern CNC manufacturing plant floor with machines and cool industrial lighting',
			'features' => 'close-up of precision CNC milling aluminum part with coolant spray',
			'gallery'  => 'industrial assembly line with workers in safety gear inspecting components',
			'about'    => 'quality engineer measuring a precision machined part with calipers in a factory',
			'team'     => 'professional manufacturing plant manager portrait in hard hat, soft industrial background',
			'default'  => 'modern precision manufacturing factory photography',
		],
		'steel' => [
			'hero'     => 'dramatic steel mill interior with molten metal glow, sparks, and heavy industrial atmosphere',
			'features' => 'structural steel beams being welded in a fabrication shop with sparks flying',
			'gallery'  => 'stacked steel plates and I-beams in an industrial yard at dusk',
			'about'    => 'steel fabricator inspecting welded structural joints in a heavy manufacturing bay',
			'team'     => 'confident steel plant supervisor in hard hat and flame-resistant jacket',
			'default'  => 'steel manufacturing mill and fabrication photography',
		],
		'food' => [
			'hero'     => 'appetizing food photography of a signature dish with natural light',
			'menu'     => 'overhead of delicious plated meals ready to serve',
			'default'  => 'professional food photography',
		],
		'business' => [
			'hero'     => 'confident professionals collaborating in a bright modern office',
			'features' => 'business handshake in a contemporary meeting room',
			'gallery'  => 'modern office workspace with laptops and natural light',
			'default'  => 'professional business office photography',
		],
		'portrait' => [
			'team'     => 'professional headshot portrait of a confident business person, soft studio lighting, neutral background',
			'default'  => 'professional team member headshot portrait, natural smile, soft lighting',
		],
		'creative' => [
			'hero'     => 'creative studio workspace with art supplies and inspiration boards',
			'default'  => 'creative studio photography',
		],
	];

	/** @var array<string,string> */
	private static $industry_labels = [
		'coffee'      => 'coffee shop',
		'restaurant'  => 'restaurant dining',
		'bakery'      => 'bakery and pastries',
		'fitness'     => 'fitness training',
		'yoga'        => 'yoga and wellness',
		'health'      => 'healthcare clinic',
		'beauty'      => 'beauty salon',
		'realestate'  => 'real estate property',
		'saas'        => 'software and technology',
		'agency'      => 'creative agency',
		'education'   => 'education and learning',
		'ecommerce'   => 'retail shopping',
		'travel'      => 'travel and destinations',
		'law'         => 'law firm',
		'photography' => 'professional photography',
		'cleaning'    => 'cleaning service',
		'plumbing'    => 'home services',
		'manufacturing' => 'precision manufacturing',
		'steel'       => 'steel manufacturing',
		'business'    => 'professional business',
		'portrait'    => 'team portrait',
		'creative'    => 'creative studio',
		'food'        => 'food and dining',
		'technology'  => 'technology workspace',
	];

	public static function industry_for_topic($topic, $page_title = '') {
		return Av_Web_Studio_AI_Profiles::detect($topic, $page_title);
	}

	/** @deprecated Use industry_for_topic */
	public static function category_for_topic($topic) {
		return self::industry_for_topic($topic);
	}

	public static function is_generic_topic($topic) {
		$t = strtolower(trim($topic));
		if ($t === '') {
			return true;
		}
		$generic = [
			'professional business', 'modern professional website', 'professional', 'business',
			'website', 'modern website', 'section', 'hero', 'hero section', 'banner', 'header',
			'footer', 'pricing', 'gallery', 'image', 'images', 'photo', 'photos',
		];
		return in_array($t, $generic, true);
	}

	/**
	 * Build the best image topic from prompt, section HTML, and page title.
	 *
	 * @param string      $prompt_topic Topic from user prompt.
	 * @param string      $page_title   Page title.
	 * @param string|null $section_html Target section HTML.
	 * @param string      $page_html    Full page HTML.
	 * @return string
	 */
	public static function resolve_topic($prompt_topic, $page_title = '', $section_html = null, $page_html = '') {
		$candidates = [];

		$prompt_topic = trim((string) $prompt_topic);
		if ($prompt_topic !== '' && ! self::is_generic_topic($prompt_topic)) {
			$candidates[] = $prompt_topic;
		}

		if ($section_html) {
			$heading = Av_Web_Studio_AI_Editor::extract_heading($section_html);
			if ($heading && ! Av_Web_Studio_AI_Intent::is_bad_title($heading) && ! self::is_generic_topic($heading)) {
				$candidates[] = $heading;
			}
			$body = self::extract_section_text($section_html);
			if ($body !== '') {
				$candidates[] = $body;
			}
		}

		if ($page_title && ! Av_Web_Studio_AI_Intent::is_bad_title($page_title) && ! self::is_generic_topic($page_title)) {
			$candidates[] = $page_title;
		}

		if ($page_html) {
			$h1 = Av_Web_Studio_AI_Editor::extract_page_title($page_html);
			if ($h1 && ! self::is_generic_topic($h1)) {
				$candidates[] = $h1;
			}
		}

		if (empty($candidates)) {
			return 'professional business';
		}

		foreach ($candidates as $c) {
			if (self::industry_for_topic($c, $page_title) !== 'business') {
				return $c;
			}
		}

		return $candidates[0];
	}

	public static function extract_section_text($section_html) {
		$text = '';
		if (preg_match_all('/<(?:h[1-6]|p|figcaption|li|span)[^>]*>(.*?)<\/(?:h[1-6]|p|figcaption|li|span)>/is', $section_html, $matches)) {
			$parts = [];
			foreach ($matches[1] as $chunk) {
				$clean = trim(wp_strip_all_tags($chunk));
				if ($clean !== '' && ! Av_Web_Studio_AI_Intent::is_bad_title($clean)) {
					$parts[] = $clean;
				}
			}
			$text = implode(' ', $parts);
		}
		$text = preg_replace('/\s+/', ' ', $text);
		return trim(mb_substr($text, 0, 280));
	}

	public static function industry_label($industry) {
		return self::$industry_labels[ $industry ] ?? self::$industry_labels['business'];
	}

	public static function pool_for_section($industry, $section_type = '') {
		if ($section_type === 'team') {
			return 'portrait';
		}
		if (in_array($section_type, [ 'menu' ], true) && ! isset(self::$industry_labels[ $industry ])) {
			return 'food';
		}
		return isset(self::$industry_labels[ $industry ]) ? $industry : 'business';
	}

	/**
	 * Build a photorealistic visual prompt for AI image generation.
	 *
	 * @param string $topic         Business / topic.
	 * @param string $page_title    Page title.
	 * @param string $section_type  Section type slug.
	 * @param string $heading       Section heading.
	 * @param string $body          Section body snippet.
	 * @param int    $variation     Variation index.
	 * @return string
	 */
	public static function build_visual_prompt($topic, $page_title = '', $section_type = '', $heading = '', $body = '', $variation = 0) {
		$industry = self::industry_for_topic($topic . ' ' . $heading . ' ' . $body, $page_title);
		$pool     = self::pool_for_section($industry, $section_type);
		$role     = $section_type ?: 'default';
		if ($role === 'team') {
			$pool = 'portrait';
			$role = 'team';
		}

		$recipes = self::$scene_recipes[ $pool ] ?? self::$scene_recipes['business'];
		$scene   = $recipes[ $role ] ?? ( $recipes['default'] ?? self::$scene_recipes['business']['default'] );

		$brand = trim($page_title);
		if ($brand === '' || Av_Web_Studio_AI_Intent::is_bad_title($brand)) {
			$brand = self::is_generic_topic($topic) ? self::industry_label($industry) : $topic;
		}

		$parts = [ $scene ];

		if ($heading && ! self::is_generic_topic($heading) && ! Av_Web_Studio_AI_Intent::is_bad_title($heading)) {
			$parts[] = 'related to "' . self::clip($heading, 60) . '"';
		}

		$parts[] = 'for ' . self::clip($brand, 50) . ' website';

		if ($body !== '') {
			$keyword = self::clip($body, 80);
			if ($keyword !== '') {
				$parts[] = 'context: ' . $keyword;
			}
		}

		$variations = [
			'wide angle composition',
			'detail-focused composition',
			'lifestyle candid moment',
			'editorial magazine style',
			'bright airy lighting',
			'warm golden hour mood',
		];
		$parts[] = $variations[ absint($variation) % count($variations) ];
		$parts[] = 'photorealistic, high resolution, no text, no watermark, no logo, no UI mockup';

		return self::clip(implode(', ', $parts), 320);
	}

	/**
	 * Local plugin image URL matched to topic/section.
	 *
	 * @param string $topic        Topic.
	 * @param int    $width        Width (unused; kept for callers).
	 * @param int    $height       Height (unused; kept for callers).
	 * @param int    $index        Variation index.
	 * @param string $page_title   Page title.
	 * @param string $section_type Section type.
	 * @param string $heading      Heading.
	 * @param string $body         Body text.
	 * @return string
	 */
	public static function relevant_url($topic, $width = 800, $height = 600, $index = 0, $page_title = '', $section_type = '', $heading = '', $body = '') {
		unset($width, $height);
		$blob     = trim($topic . ' ' . $page_title . ' ' . $heading . ' ' . $body . ' ' . $section_type);
		$industry = self::industry_for_topic($blob, $page_title);
		$pool     = self::pool_for_section($industry, $section_type);
		return self::stock_url($pool, $index);
	}

	/**
	 * Map a Claude/AI visual prompt to a working stock image URL.
	 *
	 * @param string $prompt       Visual prompt / scene description.
	 * @param int    $width        Width.
	 * @param int    $height       Height.
	 * @param int    $index        Index.
	 * @param string $section_type Section type.
	 * @param string $page_title   Page title.
	 * @return string
	 */
	public static function url_from_suggestion($prompt, $width = 800, $height = 600, $index = 0, $section_type = '', $page_title = '') {
		$prompt = trim(wp_strip_all_tags((string) $prompt));
		if ($section_type === 'team' || preg_match('/\b(headshot|portrait|team member)\b/i', $prompt)) {
			return self::stock_url('portrait', $index);
		}
		$industry = self::industry_for_topic($prompt, $page_title);
		$pool     = self::pool_for_section($industry, $section_type);
		return self::stock_url($pool, $index);
	}

	/**
	 * @deprecated Kept for compatibility — now returns reliable stock matched to the prompt.
	 *
	 * @param string $prompt Visual prompt.
	 * @param int    $width  Width.
	 * @param int    $height Height.
	 * @param int    $index  Seed variation.
	 * @return string
	 */
	public static function ai_image_url($prompt, $width = 800, $height = 600, $index = 0) {
		return self::url_from_suggestion($prompt, $width, $height, $index);
	}

	public static function url($topic, $width = 800, $height = 600, $index = 0) {
		$topic = self::sanitize_topic($topic);
		if ($topic === '') {
			$topic = 'professional business';
		}
		return self::relevant_url($topic, $width, $height, $index);
	}

	public static function stock_url($industry, $index = 0, $width = 1200, $height = 700) {
		unset($width, $height);
		$key  = isset(self::$industry_files[ $industry ]) ? $industry : 'business';
		$id   = self::$industry_files[ $key ];
		$slot = absint($index) % 4;
		$rel  = sprintf('assets/images/templates/%s-%d.jpg', $id, $slot);
		if (file_exists(AV_WEB_STUDIO_PLUGIN_DIR . $rel)) {
			return AV_WEB_STUDIO_PLUGIN_URL . $rel;
		}
		return self::placeholder_url();
	}

	public static function placeholder_url() {
		return AV_WEB_STUDIO_PLUGIN_URL . 'assets/images/placeholder.svg';
	}

	public static function media() {
		return self::placeholder_url();
	}

	/**
	 * Replace third-party stock-photo URLs in HTML/CSS with a local URL.
	 *
	 * @param string          $markup      Markup.
	 * @param callable|string $replacement Replacement URL or callback.
	 * @return string
	 */
	public static function replace_remote_stock_urls($markup, $replacement) {
		return preg_replace_callback(
			'~https?://[^\s"\')]+~i',
			function ($m) use ($replacement) {
				if (!Av_Web_Studio_Output::is_remote_stock_src($m[0])) {
					return $m[0];
				}
				if (is_callable($replacement)) {
					return (string) call_user_func($replacement, $m[0]);
				}
				return (string) $replacement;
			},
			(string) $markup
		);
	}

	public static function sanitize_topic($topic) {
		$t = strtolower(trim($topic));

		if ($t === '' || preg_match('/\b(not proper|not good|not right|is wrong|looks bad|is not|doesn\'t look|broken|fix this|fix the)\b/i', $t)) {
			return 'professional business';
		}

		if (preg_match('/^(hero|hero section|section|banner|header|footer|pricing|gallery|image|images|photo|photos)$/', $t)) {
			return '';
		}

		if (preg_match('/\bhero\b/', $t) && strlen($t) < 40 && self::is_generic_topic($t)) {
			return '';
		}

		return $topic;
	}

	public static function img_tag($alt, $topic, $class = 'av-web-studio-ai-img', $width = 800, $height = 600, $index = 0, $page_title = '', $section_type = '', $heading = '') {
		$src     = esc_url(self::relevant_url($topic, $width, $height, $index, $page_title, $section_type, $heading));
		$alt_txt = esc_attr($alt);

		return '<img class="' . esc_attr($class) . '" src="' . $src . '" alt="' . $alt_txt . '" width="' . absint($width) . '" height="' . absint($height) . '" loading="lazy" decoding="async" />';
	}

	public static function descriptive_alt($topic, $industry = '', $index = 0, $context = '') {
		$industry = $industry ?: self::industry_for_topic($topic);
		$label    = self::industry_label($industry);
		$base     = $context ?: ( self::is_generic_topic($topic) ? $label : $topic );
		$base     = trim(preg_replace('/\s+/', ' ', wp_strip_all_tags($base)));
		if ($index > 0) {
			return ucfirst($base) . ' — photo ' . ( $index + 1 );
		}
		return ucfirst($base);
	}

	public static function sample_urls_for_industry($industry, $count = 6) {
		$urls = [];
		$pool = isset(self::$industry_files[ $industry ]) ? $industry : 'business';
		for ($i = 0; $i < max(1, absint($count)); $i++) {
			$urls[] = self::stock_url($pool, $i);
		}
		return implode("\n", $urls);
	}

	public static function ai_image_instructions() {
		return 'Do not insert remote image URLs. Leave image src empty or omit images so PHP can fill bundled local files, or so the site owner can choose files from the WordPress Media Library.';
	}

	public static function image_brief_for_ai($topic, $page_title = '', $section_type = '', $section_heading = '') {
		$industry = self::industry_for_topic($topic, $page_title);
		$label    = self::industry_label($industry);

		$lines = [
			'IMAGE REQUIREMENTS (mandatory):',
			'- Business / topic: ' . ( $topic ?: $page_title ?: $label ),
			'- Detected industry: ' . $industry . ' (' . $label . ')',
			'- Do not invent or paste remote photo URLs.',
			'- Alt text must describe the ' . $label . ' scene.',
		];

		if ($section_type) {
			$lines[] = '- Target section type: ' . $section_type;
		}
		if ($section_heading) {
			$lines[] = '- Section heading: ' . $section_heading;
		}
		if ($section_type === 'team') {
			$lines[] = '- Team section: describe portrait headshots in alt text only.';
		}

		$lines[] = self::ai_image_instructions();

		return implode("\n", $lines);
	}

	/**
	 * Replace image URLs in HTML/CSS with section-relevant AI images.
	 *
	 * @param array  $code       Code array.
	 * @param string $topic      Topic.
	 * @param string $page_title Page title.
	 * @return array
	 */
	public static function align_code_images($code, $topic, $page_title = '') {
		if (!is_array($code)) {
			return $code;
		}

		$topic = self::resolve_topic($topic, $page_title, null, $code['html'] ?? '');

		if (!empty($code['html'])) {
			$html = $code['html'];
			if (preg_match_all('/<section\b[\s\S]*?<\/section>/i', $html, $matches)) {
				foreach ($matches[0] as $section) {
					$type    = self::detect_section_type_from_html($section);
					$updated = self::align_section_images($section, $topic, $page_title, $type);
					$html    = str_replace($section, $updated, $html);
				}
				$code['html'] = $html;
			} else {
				$code['html'] = self::align_images_in_markup($html, $topic, $page_title, '', '');
			}
		}
		if (!empty($code['css'])) {
			$code['css'] = self::align_images_in_markup($code['css'], $topic, $page_title, '', '', true);
		}

		return $code;
	}

	public static function detect_section_type_from_html($section_html) {
		$map = [
			'team'     => [ '-team', 'team-' ],
			'gallery'  => [ '-gallery', 'gallery-', '-portfolio' ],
			'hero'     => [ '-hero', 'hero-' ],
			'menu'     => [ '-menu', 'menu-' ],
			'features' => [ '-features', 'features-', '-about', 'about-' ],
			'pricing'  => [ '-pricing', 'pricing-' ],
			'contact'  => [ '-contact', 'contact-' ],
			'faq'      => [ '-faq', 'faq-' ],
		];
		foreach ($map as $type => $needles) {
			foreach ($needles as $n) {
				if (stripos($section_html, $n) !== false) {
					return $type;
				}
			}
		}
		return '';
	}

	/**
	 * Swap remote stock URLs in markup for bundled plugin images.
	 *
	 * @param string $markup       HTML or CSS.
	 * @param string $topic        Topic.
	 * @param string $page_title   Page title.
	 * @param string $section_type Section type.
	 * @param string $heading      Heading.
	 * @param bool   $css_only     Skip alt rewrite.
	 * @return string
	 */
	public static function align_images_in_markup($markup, $topic, $page_title = '', $section_type = '', $heading = '', $css_only = false) {
		$body  = $css_only ? '' : self::extract_section_text($markup);
		$index = 0;

		$markup = self::replace_remote_stock_urls(
			$markup,
			function () use ($topic, $page_title, $section_type, $heading, $body, &$index) {
				$url = self::relevant_url($topic, 1200, 700, $index, $page_title, $section_type, $heading, $body);
				$index++;
				return $url;
			}
		);

		if ($css_only) {
			return $markup;
		}

		$industry  = self::pool_for_section(self::industry_for_topic($topic, $page_title), $section_type);
		$alt_index = 0;
		$markup    = preg_replace_callback(
			'/<img\b([^>]*)>/i',
			function ($m) use ($industry, $topic, $heading, &$alt_index) {
				$attrs = $m[1];
				$alt_index++;
				if ($industry === 'portrait') {
					$new_alt = 'Team member portrait ' . $alt_index;
				} else {
					$new_alt = self::descriptive_alt($topic, $industry, $alt_index - 1, $heading);
				}

				if (preg_match('/\balt\s*=\s*(["\'])(.*?)\1/i', $attrs, $am)) {
					$old = strtolower(trim($am[2]));
					if ($old === '' || preg_match('/^(image|photo|picture|img|banner|hero|section|professional image)(\s+\d+)?$/i', $old)) {
						$attrs = preg_replace('/\balt\s*=\s*(["\']).*?\1/i', 'alt="' . esc_attr($new_alt) . '"', $attrs, 1);
					}
				} else {
					$attrs .= ' alt="' . esc_attr($new_alt) . '"';
				}

				return '<img' . $attrs . '>';
			},
			$markup
		);

		return $markup;
	}

	public static function align_section_images($section_html, $topic, $page_title = '', $section_type = '') {
		$resolved = self::resolve_topic($topic, $page_title, $section_html);
		$heading  = Av_Web_Studio_AI_Editor::extract_heading($section_html);
		return self::align_images_in_markup($section_html, $resolved, $page_title, $section_type, $heading);
	}

	/**
	 * @param string $text Text.
	 * @param int    $max  Max length.
	 * @return string
	 */
	private static function clip($text, $max) {
		$text = trim(preg_replace('/\s+/', ' ', wp_strip_all_tags((string) $text)));
		if (strlen($text) <= $max) {
			return $text;
		}
		return rtrim(substr($text, 0, $max - 1)) . '…';
	}
}

