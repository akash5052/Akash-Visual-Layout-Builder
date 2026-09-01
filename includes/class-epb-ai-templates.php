<?php

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Smart template engine — generates context-aware sections from natural language prompts.
 */
class EPB_AI_Templates {

	/**
	 * Generate code from a user prompt using intent detection.
	 *
	 * @param string $prompt User prompt.
	 * @return array{html:string,css:string,js:string}
	 */
	public static function generate($prompt, $context = null) {
		$p = strtolower(trim($prompt));

		if (self::is_vague_generate($p)) {
			return self::enhance_output(self::generic_content_section('content'), $prompt);
		}

		$title = self::extract_title($prompt);
		$slug  = self::slug($title ?: 'section');
		$topic = EPB_AI_Intent::extract_content_topic($prompt) ?: EPB_AI_Intent::extract_topic($prompt);

		if (self::matches($p, ['pricing', 'plan card', 'plans', 'subscription', 'packages'])) {
			$code = self::pricing_section($title, $slug, $topic);
		} elseif (self::matches($p, ['contact form', 'contact section', 'get in touch', 'reach us', 'send message', 'validation'])) {
			$code = self::contact_section($title, $slug);
		} elseif (self::matches($p, ['gallery', 'portfolio', 'showcase', 'hover effect', 'grid of images'])) {
			$code = self::gallery_section($title, $slug, $prompt, $topic);
		} elseif (self::matches($p, ['testimonial', 'review', 'what customers say', 'feedback'])) {
			$code = self::testimonials_section($title, $slug, $topic);
		} elseif (self::matches($p, ['faq', 'frequently asked', 'questions and answers'])) {
			$code = self::faq_section($title, $slug, $topic);
		} elseif (self::matches($p, ['menu', 'coffee shop', 'restaurant', 'cafe', 'food', 'dishes'])) {
			$code = self::menu_section($title, $slug, $topic);
		} elseif (self::matches($p, ['team', 'about us', 'our team', 'who we are', 'staff'])) {
			$code = self::team_section($title, $slug, $prompt, $topic);
		} elseif (self::matches($p, ['newsletter', 'subscribe', 'email signup', 'mailing list'])) {
			$code = self::newsletter_section($title, $slug);
		} elseif (self::matches($p, ['feature', 'services', 'what we offer', 'benefits'])) {
			$code = $topic
				? EPB_AI_Content::features_section($topic, $title, $slug)
				: self::features_section($title, $slug, $prompt);
		} elseif (self::matches($p, ['saas', 'software', 'startup', 'platform', 'app landing'])) {
			$code = $topic
				? EPB_AI_Content::hero_section($topic, $title, $slug)
				: self::saas_hero($title, $slug);
		} elseif (self::matches($p, ['hero', 'banner', 'landing', 'header section'])) {
			$code = EPB_AI_Content::hero_section($topic ?: 'professional business', $title, $slug);
		} elseif ($topic && strlen($topic) > 3) {
			$code = EPB_AI_Content::hero_section($topic, $title, $slug);
		} elseif ($title) {
			$code = EPB_AI_Content::hero_section($topic ?: $title, $title, $slug);
		} else {
			$code = self::generic_content_section($slug);
		}

		return self::enhance_output($code, $prompt);
	}

	/**
	 * Build a section by semantic type with a safe title.
	 *
	 * @param string $type   Section type.
	 * @param string $title  Display title.
	 * @param string $slug   CSS slug.
	 * @param string $prompt Original prompt.
	 * @return array
	 */
	public static function section_by_type($type, $title, $slug, $prompt = '') {
		if (EPB_AI_Intent::is_bad_title($title)) {
			$title = 'Welcome';
		}

		switch ($type) {
			case 'pricing':
				return self::pricing_section($title, $slug, $prompt);
			case 'gallery':
				return self::gallery_section($title, $slug, $prompt);
			case 'contact':
				return self::contact_section($title, $slug);
			case 'team':
				return self::team_section($title, $slug, $prompt);
			case 'menu':
				return self::menu_section($title, $slug);
			case 'faq':
				return self::faq_section($title, $slug);
			case 'features':
				return self::features_section($title, $slug, $prompt);
			case 'hero':
			default:
				$topic = EPB_AI_Intent::extract_content_topic($prompt) ?: EPB_AI_Intent::extract_topic($prompt) ?: $title;
				return EPB_AI_Content::hero_section($topic, $title, $slug);
		}
	}

	/**
	 * Check if page already has a section type.
	 *
	 * @param string $html Page HTML.
	 * @param string $type Section type.
	 * @return bool
	 */
	public static function page_has_section_type($html, $type) {
		return EPB_AI_Editor::get_section_by_type($html, $type) !== null;
	}

	/**
	 * Add images/animations when prompt requests them.
	 *
	 * @param array  $code   Section code.
	 * @param string $prompt User prompt.
	 * @return array
	 */
	private static function enhance_output($code, $prompt) {
		$p = strtolower($prompt);

		if (self::wants_images($p) && stripos($code['html'], '<img') === false) {
			$topic = EPB_AI_Intent::extract_image_topic($prompt) ?: EPB_AI_Intent::extract_topic($prompt) ?: 'modern professional website';
			$img   = EPB_AI_Images::img_tag('Professional section image', $topic, 'epb-section-img', 1000, 520);
			$code['html'] = preg_replace('/(<section[^>]*>)/i', '$1' . "\n  " . $img, $code['html'], 1);
			$code['css'] .= "\n.epb-section-img { width:100%; max-height:420px; object-fit:cover; border-radius:14px; margin-bottom:24px; display:block; }";
		}

		// Always include scroll-reveal motion; extra animation pass when requested.
		if (strpos($code['js'], '__epbMotionInit') === false) {
			$code['js'] = EPB_AI_Content::motion_js() . "\n" . $code['js'];
		}
		if (strpos($code['css'], '[data-epb-reveal]') === false) {
			$code['css'] .= "\n[data-epb-reveal]{opacity:0;transform:translateY(24px);transition:opacity .7s ease,transform .7s ease;}\n[data-epb-reveal].is-visible{opacity:1;transform:none;}\n@media(prefers-reduced-motion:reduce){[data-epb-reveal]{opacity:1;transform:none;transition:none;}}";
		}

		if (self::wants_animation($p)) {
			return EPB_AI_Editor::add_animations($code, 'all');
		}

		return $code;
	}

	private static function wants_animation($prompt) {
		return (bool) preg_match('/\b(?:animation|animations|animate|animated|fade|slide|transition|motion|scroll)\b/i', $prompt);
	}

	private static function wants_images($prompt) {
		return (bool) preg_match('/\b(?:image|images|photo|photos|picture|pictures|banner|thumbnail|with\s+images?)\b/i', $prompt);
	}

	/**
	 * Vague prompts like "generate a new section" with no real topic.
	 *
	 * @param string $prompt Lowercased prompt.
	 * @return bool
	 */
	private static function is_vague_generate($prompt) {
		$patterns = [
			'/^(?:please\s+)?(?:generate|create|add|make|build)\s+(?:a\s+)?(?:new\s+)?section\s*\.?$/',
			'/^(?:please\s+)?(?:generate|create|add|make|build)\s+(?:me\s+)?(?:a\s+)?(?:new\s+)?section\s*\.?$/',
			'/^(?:new|another)\s+section\s*\.?$/',
			'/^(?:add|generate)\s+(?:a\s+)?section\s*\.?$/',
		];
		foreach ($patterns as $pattern) {
			if (preg_match($pattern, $prompt)) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Edit/remove commands should not become visible page content.
	 *
	 * @param string $prompt User prompt.
	 * @return bool
	 */
	private static function is_command_prompt($prompt) {
		return (bool) preg_match(
			'/\b(?:remove|delete|clear|replace|update|edit|change|fix|modify)\b/i',
			$prompt
		);
	}

	private static function is_generic_title($title) {
		$generic = ['new', 'section', 'a section', 'new section', 'page', 'a page', 'another section'];
		return in_array(strtolower(trim($title)), $generic, true);
	}

	private static function clean_title($title) {
		$t = trim(preg_replace('/\s+(section|page|block|component)$/i', '', trim($title)));
		$t = trim(preg_replace('/^(?:a|an|the|new)\s+/i', '', $t));
		return ucfirst($t);
	}

	private static function matches($prompt, array $keywords) {
		foreach ($keywords as $kw) {
			if (strpos($prompt, $kw) !== false) {
				return true;
			}
		}
		return false;
	}

	private static function slug($text) {
		$s = sanitize_title(substr($text, 0, 30));
		return $s ?: 'section';
	}

	private static function extract_title($prompt) {
		if (self::is_command_prompt($prompt) || self::is_vague_generate(strtolower(trim($prompt)))) {
			return '';
		}

		if (EPB_AI_Intent::is_complaint(strtolower($prompt))) {
			return '';
		}

		$patterns = [
			'/(?:create|build|design|make|generate|add)\s+(?:a\s+)?(?:new\s+)?(.+?)(?:\s+(?:section|page|with|for|that|including)|$)/i',
			'/(?:landing page for|hero for|section for)\s+(?:a\s+)?(.+?)$/i',
		];
		foreach ($patterns as $pattern) {
			if (preg_match($pattern, $prompt, $m)) {
				$t = self::clean_title($m[1]);
				if (strlen($t) > 2 && strlen($t) < 80 && !self::is_generic_title($t) && !EPB_AI_Intent::is_bad_title($t)) {
					return $t;
				}
			}
		}

		if (strlen($prompt) < 70 && !self::is_command_prompt($prompt)) {
			$t = self::clean_title($prompt);
			if (!self::is_generic_title($t) && !EPB_AI_Intent::is_bad_title($t)) {
				return $t;
			}
		}

		return '';
	}

	private static function summarize($prompt) {
		$words = preg_split('/\s+/', trim($prompt));
		return ucfirst(implode(' ', array_slice($words, 0, 6)));
	}

	private static function pricing_section($title, $slug, $topic = '') {
		$p       = $topic ? EPB_AI_Profiles::get($topic, $title) : null;
		$accent  = $p ? esc_attr($p['accent']) : '#0f766e';
		$heading = $title ?: ( $p ? 'Plans & Pricing' : 'Simple, Transparent Pricing' );
		$img     = esc_url(EPB_AI_Images::relevant_url($topic ?: 'saas software', 1400, 520, 0, $title, 'hero', $heading));

		$html = "<section class=\"{$slug}-pricing\">
  <div class=\"{$slug}-pricing__banner\" style=\"background-image:url('{$img}')\" data-epb-reveal>
    <div class=\"{$slug}-pricing__banner-overlay\"></div>
    <div class=\"{$slug}-pricing__banner-inner\">
      <h2>{$heading}</h2>
      <p>Choose the plan that fits your needs. All plans include a 14-day free trial.</p>
    </div>
  </div>
  <div class=\"{$slug}-pricing__inner\">
    <div class=\"{$slug}-pricing__grid\">
      <article class=\"{$slug}-plan\" data-epb-reveal>
        <h3>Starter</h3>
        <p class=\"{$slug}-plan__price\"><span>\\$9</span>/mo</p>
        <ul><li>5 Projects</li><li>Basic Analytics</li><li>Email Support</li></ul>
        <button class=\"{$slug}-plan__btn\">Get Started</button>
      </article>
      <article class=\"{$slug}-plan {$slug}-plan--featured\" data-epb-reveal data-epb-delay=\"80\">
        <span class=\"{$slug}-plan__badge\">Popular</span>
        <h3>Pro</h3>
        <p class=\"{$slug}-plan__price\"><span>\\$29</span>/mo</p>
        <ul><li>Unlimited Projects</li><li>Advanced Analytics</li><li>Priority Support</li><li>Custom Domain</li></ul>
        <button class=\"{$slug}-plan__btn {$slug}-plan__btn--primary\">Get Started</button>
      </article>
      <article class=\"{$slug}-plan\" data-epb-reveal data-epb-delay=\"160\">
        <h3>Enterprise</h3>
        <p class=\"{$slug}-plan__price\"><span>\\$99</span>/mo</p>
        <ul><li>Everything in Pro</li><li>SSO &amp; SAML</li><li>Dedicated Manager</li><li>SLA Guarantee</li></ul>
        <button class=\"{$slug}-plan__btn\">Contact Sales</button>
      </article>
    </div>
  </div>
</section>";

		$css = ".{$slug}-pricing { width:100%; font-family:'Outfit',system-ui,sans-serif; background:#f7f8fb; }
.{$slug}-pricing__banner { position:relative; min-height:280px; background-size:cover; background-position:center; color:#fff; display:flex; align-items:center; justify-content:center; text-align:center; overflow:hidden; }
.{$slug}-pricing__banner-overlay { position:absolute; inset:0; background:linear-gradient(135deg,rgba(15,23,42,.82),{$accent}cc); }
.{$slug}-pricing__banner-inner { position:relative; z-index:1; padding:64px 24px; max-width:720px; }
.{$slug}-pricing__banner h2 { margin:0 0 12px; font-size:clamp(2rem,4vw,3rem); font-weight:800; letter-spacing:-.03em; }
.{$slug}-pricing__banner p { margin:0; opacity:.92; font-size:1.08rem; }
.{$slug}-pricing__inner { max-width:1100px; margin:-56px auto 0; padding:0 20px 90px; position:relative; z-index:2; }
.{$slug}-pricing__grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(260px,1fr)); gap:22px; }
.{$slug}-plan { background:#fff; border-radius:22px; padding:36px 28px; box-shadow:0 16px 40px rgba(15,23,42,.08); position:relative; transition:transform .3s,box-shadow .3s; border:1px solid #eef1f5; }
.{$slug}-plan:hover { transform:translateY(-8px); box-shadow:0 24px 50px rgba(15,23,42,.12); }
.{$slug}-plan--featured { border:2px solid {$accent}; transform:translateY(-8px); }
.{$slug}-plan__badge { position:absolute; top:-12px; left:50%; transform:translateX(-50%); background:{$accent}; color:#fff; padding:5px 16px; border-radius:999px; font-size:12px; font-weight:700; }
.{$slug}-plan h3 { margin:0 0 8px; font-size:1.25rem; }
.{$slug}-plan__price { font-size:2.6rem; font-weight:800; color:#0f172a; margin:0 0 20px; letter-spacing:-.03em; }
.{$slug}-plan ul { list-style:none; padding:0; margin:0 0 28px; text-align:left; }
.{$slug}-plan li { padding:10px 0; color:#475569; border-bottom:1px solid #f1f5f9; }
.{$slug}-plan__btn { width:100%; padding:13px; border:2px solid #e2e8f0; background:transparent; border-radius:999px; font-weight:700; cursor:pointer; transition:all .25s; font-family:inherit; }
.{$slug}-plan__btn--primary, .{$slug}-plan__btn:hover { background:{$accent}; color:#fff; border-color:{$accent}; }";

		$js = "document.querySelectorAll('.{$slug}-plan__btn').forEach(function(btn){btn.addEventListener('click',function(){btn.textContent='Selected!';});});";

		return compact('html', 'css', 'js');
	}

	private static function contact_section($title, $slug) {
		$heading = $title ?: 'Get In Touch';
		$img     = esc_url(EPB_AI_Images::relevant_url('professional office meeting', 900, 1100, 0, $heading, 'contact', $heading));

		$html = "<section class=\"{$slug}-contact\">
  <div class=\"{$slug}-contact__grid\">
    <div class=\"{$slug}-contact__visual\" data-epb-reveal=\"left\">
      <img src=\"{$img}\" alt=\"Contact our team\" loading=\"lazy\" width=\"900\" height=\"1100\" />
      <div class=\"{$slug}-contact__card\">
        <strong>We reply within 24 hours</strong>
        <p>hello@example.com · Mon–Fri 9–6</p>
      </div>
    </div>
    <div class=\"{$slug}-contact__inner\" data-epb-reveal=\"right\">
      <p class=\"{$slug}-contact__eyebrow\">Contact</p>
      <h2>{$heading}</h2>
      <p>We'd love to hear from you. Fill out the form and we'll respond within 24 hours.</p>
      <form class=\"{$slug}-form\" id=\"{$slug}-form\">
        <div class=\"{$slug}-form__row\">
          <input type=\"text\" name=\"name\" placeholder=\"Your Name\" required />
          <input type=\"email\" name=\"email\" placeholder=\"Email Address\" required />
        </div>
        <input type=\"text\" name=\"subject\" placeholder=\"Subject\" required />
        <textarea name=\"message\" rows=\"5\" placeholder=\"Your Message\" required></textarea>
        <p class=\"{$slug}-form__error\" id=\"{$slug}-error\"></p>
        <button type=\"submit\">Send Message</button>
      </form>
    </div>
  </div>
</section>";

		$css = ".{$slug}-contact { padding:90px 24px; background:linear-gradient(180deg,#fff,#f6f7fb); width:100%; font-family:'Outfit',system-ui,sans-serif; }
.{$slug}-contact__grid { max-width:1100px; margin:0 auto; display:grid; grid-template-columns:0.95fr 1.05fr; gap:40px; align-items:center; }
.{$slug}-contact__visual { position:relative; }
.{$slug}-contact__visual img { width:100%; height:560px; object-fit:cover; border-radius:28px; box-shadow:0 24px 60px rgba(15,23,42,.14); }
.{$slug}-contact__card { position:absolute; left:20px; right:20px; bottom:20px; background:rgba(255,255,255,.92); backdrop-filter:blur(10px); border-radius:18px; padding:18px 20px; box-shadow:0 10px 30px rgba(0,0,0,.12); }
.{$slug}-contact__card strong { display:block; margin-bottom:4px; color:#0f172a; }
.{$slug}-contact__card p { margin:0; color:#64748b; font-size:.92rem; }
.{$slug}-contact__eyebrow { color:#0f766e; font-size:.78rem; font-weight:700; letter-spacing:.12em; text-transform:uppercase; margin:0 0 8px; }
.{$slug}-contact h2 { font-size:clamp(1.8rem,3vw,2.5rem); margin:0 0 10px; color:#0f172a; letter-spacing:-.03em; }
.{$slug}-contact__inner > p { color:#64748b; margin:0 0 28px; }
.{$slug}-form { display:flex; flex-direction:column; gap:14px; }
.{$slug}-form__row { display:grid; grid-template-columns:1fr 1fr; gap:14px; }
.{$slug}-form input, .{$slug}-form textarea { padding:14px 16px; border:1px solid #e2e8f0; border-radius:14px; font-size:15px; font-family:inherit; background:#fff; transition:border-color .2s,box-shadow .2s; }
.{$slug}-form input:focus, .{$slug}-form textarea:focus { outline:none; border-color:#0f766e; box-shadow:0 0 0 4px rgba(15,118,110,.12); }
.{$slug}-form button { padding:14px; background:#0f766e; color:#fff; border:none; border-radius:999px; font-size:16px; font-weight:700; cursor:pointer; transition:transform .2s,box-shadow .2s; }
.{$slug}-form button:hover { transform:translateY(-2px); box-shadow:0 10px 24px rgba(15,118,110,.28); }
.{$slug}-form__error { color:#ef4444; font-size:14px; min-height:20px; margin:0; }
@media(max-width:800px){ .{$slug}-contact__grid{ grid-template-columns:1fr; } .{$slug}-contact__visual img{ height:360px; } .{$slug}-form__row{ grid-template-columns:1fr; } }";

		$js = "document.getElementById('{$slug}-form').addEventListener('submit',function(e){
  e.preventDefault();
  var err=document.getElementById('{$slug}-error');
  var email=this.email.value;
  if(!/^[^\\\\s@]+@[^\\\\s@]+\\\\.[^\\\\s@]+\\$/.test(email)){err.textContent='Please enter a valid email.';return;}
  if(this.message.value.length<10){err.textContent='Message must be at least 10 characters.';return;}
  err.textContent='';
  this.querySelector('button').textContent='Message Sent!';
  this.reset();
});";

		return compact('html', 'css', 'js');
	}

	private static function gallery_section($title, $slug, $prompt = '', $topic = '') {
		$heading  = $title ?: 'Our Portfolio';
		$topic    = $topic ?: EPB_AI_Intent::extract_image_topic($prompt) ?: EPB_AI_Intent::extract_topic($prompt) ?: 'creative portfolio';
		$industry = EPB_AI_Images::industry_for_topic($topic);
		$label    = EPB_AI_Images::industry_label($industry);
		$items    = '';
		for ($i = 0; $i < 8; $i++) {
			$src   = EPB_AI_Images::relevant_url($topic, 720, 540, $i, '', 'gallery', $heading);
			$alt   = EPB_AI_Images::descriptive_alt($topic, $industry, $i, $heading);
			$cap   = ucfirst($label) . ' ' . ( $i + 1 );
			$span  = in_array($i, [0, 5], true) ? ' ' . $slug . '-gallery__item--wide' : '';
			$items .= '<figure class="' . $slug . '-gallery__item' . $span . '" data-epb-reveal data-epb-delay="' . ( $i * 50 ) . '"><img src="' . esc_url($src) . '" alt="' . esc_attr($alt) . '" loading="lazy" /><figcaption>' . esc_html($cap) . '</figcaption></figure>';
		}

		$html = "<section class=\"{$slug}-gallery\"><div class=\"{$slug}-gallery__inner\"><header data-epb-reveal><p class=\"{$slug}-gallery__eyebrow\">Showcase</p><h2>{$heading}</h2></header><div class=\"{$slug}-gallery__grid\">{$items}</div></div></section>";

		$css = ".{$slug}-gallery { padding:100px 20px; background:#090d18; width:100%; font-family:'Outfit',system-ui,sans-serif; }
.{$slug}-gallery__inner { max-width:1200px; margin:0 auto; }
.{$slug}-gallery header { text-align:center; margin-bottom:42px; }
.{$slug}-gallery__eyebrow { color:#93c5fd; letter-spacing:.14em; text-transform:uppercase; font-size:.78rem; font-weight:700; margin:0 0 8px; }
.{$slug}-gallery h2 { color:#fff; font-size:clamp(2rem,4vw,2.8rem); margin:0; letter-spacing:-.03em; }
.{$slug}-gallery__grid { display:grid; grid-template-columns:repeat(4,1fr); gap:14px; }
.{$slug}-gallery__item { position:relative; overflow:hidden; border-radius:18px; cursor:pointer; margin:0; aspect-ratio:1; }
.{$slug}-gallery__item--wide { grid-column:span 2; aspect-ratio:2/1; }
.{$slug}-gallery__item img { width:100%; height:100%; object-fit:cover; display:block; transition:transform .65s ease; }
.{$slug}-gallery__item:hover img { transform:scale(1.1); }
.{$slug}-gallery__item figcaption { position:absolute; inset:auto 0 0 0; padding:16px; background:linear-gradient(transparent,rgba(0,0,0,.8)); color:#fff; font-weight:600; opacity:0; transform:translateY(10px); transition:opacity .3s,transform .3s; }
.{$slug}-gallery__item:hover figcaption { opacity:1; transform:none; }
@media(max-width:900px){ .{$slug}-gallery__grid{ grid-template-columns:1fr 1fr; } .{$slug}-gallery__item--wide{ grid-column:span 1; aspect-ratio:1; } }";

		$js = '';

		return compact('html', 'css', 'js');
	}

	private static function menu_section($title, $slug, $topic = '') {
		$heading = $title ?: 'Our Menu';
		$topic   = $topic ?: 'coffee shop cafe restaurant';
		$items   = [
			['Espresso', 'Rich and bold single shot', '$3.50'],
			['Cappuccino', 'Steamed milk with foam art', '$4.50'],
			['Latte', 'Smooth espresso with milk', '$4.75'],
			['Croissant', 'Buttery, flaky pastry', '$3.25'],
			['Avocado Toast', 'Sourdough with fresh avocado', '$8.50'],
			['Acai Bowl', 'Granola, berries, honey drizzle', '$9.00'],
		];
		$cards = '';
		foreach ($items as $i => $item) {
			$src = esc_url(EPB_AI_Images::relevant_url($topic, 480, 360, $i, $heading, 'menu', $item[0]));
			$cards .= '<article class="' . $slug . '-menu__item" data-epb-reveal data-epb-delay="' . ( $i * 60 ) . '">'
				. '<img src="' . $src . '" alt="' . esc_attr($item[0]) . '" loading="lazy" />'
				. '<div><h4>' . esc_html($item[0]) . '</h4><p>' . esc_html($item[1]) . '</p><span>' . esc_html($item[2]) . '</span></div></article>';
		}

		$html = "<section class=\"{$slug}-menu\">
  <div class=\"{$slug}-menu__inner\">
    <header data-epb-reveal>
      <h2>{$heading}</h2>
      <p class=\"{$slug}-menu__sub\">Freshly prepared with love every day</p>
    </header>
    <div class=\"{$slug}-menu__grid\">{$cards}</div>
  </div>
</section>";

		$css = ".{$slug}-menu { padding:100px 20px; background:linear-gradient(160deg,#2a1810,#3E2723 50%,#1c100c); color:#fff; width:100%; font-family:'Outfit',system-ui,sans-serif; }
.{$slug}-menu__inner { max-width:1080px; margin:0 auto; }
.{$slug}-menu header { text-align:center; margin-bottom:42px; }
.{$slug}-menu h2 { font-family:'Fraunces',Georgia,serif; font-size:clamp(2rem,4vw,2.8rem); margin:0 0 8px; }
.{$slug}-menu__sub { color:#D7A86E; margin:0; }
.{$slug}-menu__grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(280px,1fr)); gap:18px; }
.{$slug}-menu__item { display:grid; grid-template-columns:110px 1fr; gap:16px; align-items:center; background:rgba(255,255,255,.07); padding:14px; border-radius:18px; border:1px solid rgba(255,255,255,.1); transition:transform .3s,background .3s; }
.{$slug}-menu__item:hover { transform:translateY(-4px); background:rgba(255,255,255,.12); }
.{$slug}-menu__item img { width:110px; height:110px; object-fit:cover; border-radius:14px; }
.{$slug}-menu__item h4 { margin:0 0 6px; color:#D7A86E; font-size:1.1rem; }
.{$slug}-menu__item p { margin:0 0 10px; color:#d6d3d1; font-size:.9rem; }
.{$slug}-menu__item span { font-weight:700; font-size:1.15rem; color:#fff; }";

		$js = '';

		return compact('html', 'css', 'js');
	}

	private static function testimonials_section($title, $slug, $topic = '') {
		$heading = $title ?: 'What Our Customers Say';
		$p       = $topic ? EPB_AI_Profiles::get($topic, $title) : null;
		$accent  = $p ? esc_attr($p['accent']) : '#0f766e';
		$topic   = $topic ?: 'professional business';
		$blocks  = '';
		$source  = ($p && ! empty($p['testimonials'])) ? $p['testimonials'] : [
			['quote' => 'Absolutely transformed our workflow. The team is incredibly responsive.', 'name' => 'Sarah M.', 'role' => 'CEO'],
			['quote' => 'Best investment we made this year. Results exceeded all expectations.', 'name' => 'James L.', 'role' => 'Founder'],
			['quote' => 'Clean design, powerful features, and outstanding support. Highly recommend!', 'name' => 'Priya K.', 'role' => 'Designer'],
		];
		foreach ($source as $i => $t) {
			$avatar = esc_url(EPB_AI_Images::relevant_url($topic, 96, 96, $i + 10, $heading, 'team', $t['name']));
			$blocks .= '<blockquote data-epb-reveal data-epb-delay="' . ( $i * 80 ) . '"><div class="' . $slug . '-stars">★★★★★</div><p>"' . esc_html($t['quote']) . '"</p><footer><img src="' . $avatar . '" alt="" loading="lazy" /><cite>— ' . esc_html($t['name']) . ', ' . esc_html($t['role']) . '</cite></footer></blockquote>';
		}

		$html = "<section class=\"{$slug}-testimonials\">
  <div class=\"{$slug}-testimonials__inner\">
    <h2 data-epb-reveal>{$heading}</h2>
    <div class=\"{$slug}-testimonials__grid\">{$blocks}</div>
  </div>
</section>";

		$css = ".{$slug}-testimonials { padding:100px 20px; background:linear-gradient(180deg,#f8fafc,#eef6f4); width:100%; font-family:'Outfit',system-ui,sans-serif; }
.{$slug}-testimonials__inner { max-width:1100px; margin:0 auto; text-align:center; }
.{$slug}-testimonials h2 { font-size:clamp(1.9rem,3vw,2.6rem); margin:0 0 48px; color:#0f172a; letter-spacing:-.03em; }
.{$slug}-testimonials__grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(280px,1fr)); gap:22px; }
.{$slug}-testimonials blockquote { background:#fff; padding:30px; border-radius:22px; box-shadow:0 12px 32px rgba(15,23,42,.06); margin:0; text-align:left; border-top:4px solid {$accent}; transition:transform .3s; }
.{$slug}-testimonials blockquote:hover { transform:translateY(-6px); }
.{$slug}-stars { color:#f59e0b; letter-spacing:2px; margin-bottom:10px; }
.{$slug}-testimonials blockquote p { font-size:1.05rem; color:#334155; margin:0 0 18px; line-height:1.65; font-style:italic; }
.{$slug}-testimonials footer { display:flex; align-items:center; gap:12px; }
.{$slug}-testimonials footer img { width:44px; height:44px; border-radius:50%; object-fit:cover; }
.{$slug}-testimonials cite { color:{$accent}; font-weight:600; font-style:normal; font-size:.9rem; }";

		$js = '';

		return compact('html', 'css', 'js');
	}

	private static function faq_section($title, $slug, $topic = '') {
		$heading = $title ?: 'Frequently Asked Questions';
		$p       = $topic ? EPB_AI_Profiles::get($topic, $title) : null;
		$accent  = $p ? esc_attr($p['accent']) : '#0f766e';
		$items   = '';
		if ($p && ! empty($p['faq'])) {
			$i = 0;
			foreach ($p['faq'] as $item) {
				$open   = $i === 0 ? ' open' : '';
				$items .= '<details class="' . $slug . '-faq__item" data-epb-reveal' . $open . '><summary>' . esc_html($item['q']) . '</summary><p>' . esc_html($item['a']) . '</p></details>';
				++$i;
			}
		} else {
			$items = '<details class="' . $slug . '-faq__item" data-epb-reveal open><summary>How do I get started?</summary><p>Sign up for a free account and follow our quick setup guide.</p></details>'
				. '<details class="' . $slug . '-faq__item" data-epb-reveal><summary>Can I cancel anytime?</summary><p>Yes — no long-term contracts. Cancel anytime from settings.</p></details>'
				. '<details class="' . $slug . '-faq__item" data-epb-reveal><summary>Do you offer refunds?</summary><p>30-day money-back guarantee on all paid plans.</p></details>';
		}

		$html = "<section class=\"{$slug}-faq\">
  <div class=\"{$slug}-faq__inner\">
    <h2 data-epb-reveal>{$heading}</h2>
    <div class=\"{$slug}-faq__list\">{$items}</div>
  </div>
</section>";

		$css = ".{$slug}-faq { padding:100px 20px; background:#fff; width:100%; font-family:'Outfit',system-ui,sans-serif; }
.{$slug}-faq__inner { max-width:760px; margin:0 auto; }
.{$slug}-faq h2 { font-size:clamp(1.8rem,3vw,2.3rem); margin:0 0 32px; text-align:center; color:#0f172a; }
.{$slug}-faq__list { display:flex; flex-direction:column; gap:12px; }
.{$slug}-faq__item { border:1px solid #e2e8f0; border-radius:16px; padding:4px 22px; background:#fff; transition:border-color .25s,box-shadow .25s,transform .25s; }
.{$slug}-faq__item summary { padding:16px 0; font-weight:700; cursor:pointer; color:#0f172a; list-style:none; }
.{$slug}-faq__item summary::-webkit-details-marker { display:none; }
.{$slug}-faq__item p { margin:0 0 16px; color:#64748b; line-height:1.6; }
.{$slug}-faq__item[open] { border-color:{$accent}; box-shadow:0 12px 28px {$accent}18; transform:translateY(-2px); }";

		$js = '';

		return compact('html', 'css', 'js');
	}

	private static function team_section($title, $slug, $prompt = '', $topic = '') {
		$heading = $title ?: 'Meet Our Team';
		$ind     = EPB_AI_Images::industry_for_topic($topic ?: EPB_AI_Intent::extract_topic($prompt));
		$label   = EPB_AI_Images::industry_label($ind);
		$members = [
			['Alex Rivera', 'CEO & Founder'],
			['Maya Chen', 'Head of Design'],
			['Jordan Lee', 'Lead Developer'],
			['Sam Patel', 'Marketing Director'],
		];
		$cards = '';
		foreach ($members as $i => $m) {
			$img = EPB_AI_Images::relevant_url($topic ?: $label, 480, 560, $i, '', 'team', $m[0] . ' ' . $m[1]);
			$alt = $m[0] . ', ' . $m[1] . ' at ' . $label;
			$cards .= '<article data-epb-reveal data-epb-delay="' . ( $i * 70 ) . '"><div class="' . $slug . '-team__photo-wrap"><img class="' . $slug . '-team__photo" src="' . esc_url($img) . '" alt="' . esc_attr($alt) . '" loading="lazy" /></div><h4>' . esc_html($m[0]) . '</h4><p>' . esc_html($m[1]) . '</p></article>';
		}

		$html = "<section class=\"{$slug}-team\">
  <div class=\"{$slug}-team__inner\">
    <header data-epb-reveal>
      <h2>{$heading}</h2>
      <p class=\"{$slug}-team__sub\">Passionate people building great products</p>
    </header>
    <div class=\"{$slug}-team__grid\">{$cards}</div>
  </div>
</section>";

		$css = ".{$slug}-team { padding:100px 20px; background:#f7f8fb; width:100%; font-family:'Outfit',system-ui,sans-serif; }
.{$slug}-team__inner { max-width:1100px; margin:0 auto; text-align:center; }
.{$slug}-team h2 { font-size:clamp(1.9rem,3vw,2.6rem); margin:0 0 8px; letter-spacing:-.03em; }
.{$slug}-team__sub { color:#64748b; margin:0 0 48px; }
.{$slug}-team__grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:24px; }
.{$slug}-team__photo-wrap { overflow:hidden; border-radius:22px; margin-bottom:16px; aspect-ratio:4/5; }
.{$slug}-team__photo { width:100%; height:100%; object-fit:cover; display:block; transition:transform .55s ease; }
.{$slug}-team__grid article:hover .{$slug}-team__photo { transform:scale(1.07); }
.{$slug}-team__grid h4 { margin:0 0 4px; color:#0f172a; }
.{$slug}-team__grid p { margin:0; color:#0f766e; font-size:.9rem; font-weight:600; }";

		$js = '';

		return compact('html', 'css', 'js');
	}

	private static function newsletter_section($title, $slug) {
		$heading = $title ?: 'Stay in the Loop';
		$img     = esc_url(EPB_AI_Images::relevant_url('newsletter creative workspace', 1400, 700, 0, $heading, 'hero', $heading));

		$html = "<section class=\"{$slug}-newsletter\">
  <div class=\"{$slug}-newsletter__bg\" style=\"background-image:url('{$img}')\"></div>
  <div class=\"{$slug}-newsletter__overlay\"></div>
  <div class=\"{$slug}-newsletter__inner\" data-epb-reveal>
    <h2>{$heading}</h2>
    <p>Subscribe for updates, tips, and exclusive offers.</p>
    <form class=\"{$slug}-newsletter__form\" id=\"{$slug}-nl-form\">
      <input type=\"email\" placeholder=\"Enter your email\" required />
      <button type=\"submit\">Subscribe</button>
    </form>
  </div>
</section>";

		$css = ".{$slug}-newsletter { position:relative; padding:110px 20px; color:#fff; width:100%; text-align:center; overflow:hidden; font-family:'Outfit',system-ui,sans-serif; }
.{$slug}-newsletter__bg { position:absolute; inset:0; background-size:cover; background-position:center; transform:scale(1.05); animation:{$slug}-nl-ken 16s ease-in-out infinite alternate; }
.{$slug}-newsletter__overlay { position:absolute; inset:0; background:linear-gradient(135deg,rgba(15,23,42,.88),rgba(15,118,110,.78)); }
.{$slug}-newsletter__inner { position:relative; z-index:1; max-width:560px; margin:0 auto; }
.{$slug}-newsletter h2 { font-size:clamp(1.8rem,3vw,2.4rem); margin:0 0 10px; letter-spacing:-.03em; }
.{$slug}-newsletter p { opacity:.92; margin:0 0 28px; }
.{$slug}-newsletter__form { display:flex; gap:10px; background:rgba(255,255,255,.12); padding:8px; border-radius:999px; border:1px solid rgba(255,255,255,.2); backdrop-filter:blur(10px); }
.{$slug}-newsletter__form input { flex:1; padding:14px 18px; border:none; border-radius:999px; font-size:15px; background:transparent; color:#fff; font-family:inherit; }
.{$slug}-newsletter__form input::placeholder { color:rgba(255,255,255,.7); }
.{$slug}-newsletter__form button { padding:14px 26px; background:#fff; color:#0f766e; border:none; border-radius:999px; font-weight:700; cursor:pointer; white-space:nowrap; font-family:inherit; }
@keyframes {$slug}-nl-ken { from { transform:scale(1.05); } to { transform:scale(1); } }
@media(max-width:560px){ .{$slug}-newsletter__form{ flex-direction:column; border-radius:18px; } }";

		$js = "document.getElementById('{$slug}-nl-form').addEventListener('submit',function(e){e.preventDefault();this.querySelector('button').textContent='Subscribed!';this.reset();});";

		return compact('html', 'css', 'js');
	}

	private static function features_section($title, $slug, $prompt) {
		$heading = $title ?: 'Why Choose Us';
		$topic   = EPB_AI_Intent::extract_content_topic($prompt) ?: EPB_AI_Intent::extract_topic($prompt) ?: 'professional business';
		$items   = [
			['⚡', 'Lightning Fast', 'Optimized performance for the best user experience.'],
			['🔒', 'Secure', 'Enterprise-grade security to protect your data.'],
			['🎨', 'Beautiful Design', 'Modern, clean interfaces that users love.'],
			['📱', 'Responsive', 'Looks perfect on every device and screen size.'],
		];
		$cards = '';
		foreach ($items as $i => $item) {
			$img = esc_url(EPB_AI_Images::relevant_url($topic, 640, 420, $i, $heading, 'features', $item[1]));
			$cards .= '<article data-epb-reveal data-epb-delay="' . ( $i * 70 ) . '"><div class="' . $slug . '-features__media"><img src="' . $img . '" alt="" loading="lazy" /></div><div class="' . $slug . '-features__body"><div class="' . $slug . '-features__icon">' . $item[0] . '</div><h3>' . esc_html($item[1]) . '</h3><p>' . esc_html($item[2]) . '</p></div></article>';
		}

		$html = "<section class=\"{$slug}-features\">
  <div class=\"{$slug}-features__inner\">
    <h2 data-epb-reveal>{$heading}</h2>
    <div class=\"{$slug}-features__grid\">{$cards}</div>
  </div>
</section>";

		$css = ".{$slug}-features { padding:100px 20px; background:linear-gradient(180deg,#fff,#f5f7fa); width:100%; font-family:'Outfit',system-ui,sans-serif; }
.{$slug}-features__inner { max-width:1140px; margin:0 auto; text-align:center; }
.{$slug}-features h2 { font-size:clamp(1.9rem,3vw,2.6rem); margin:0 0 48px; color:#0f172a; letter-spacing:-.03em; }
.{$slug}-features__grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(240px,1fr)); gap:22px; text-align:left; }
.{$slug}-features__grid article { background:#fff; border-radius:22px; overflow:hidden; border:1px solid #e8ecf1; box-shadow:0 8px 24px rgba(15,23,42,.04); transition:transform .35s,box-shadow .35s; }
.{$slug}-features__grid article:hover { transform:translateY(-8px); box-shadow:0 20px 44px rgba(15,118,110,.14); }
.{$slug}-features__media { aspect-ratio:16/10; overflow:hidden; }
.{$slug}-features__media img { width:100%; height:100%; object-fit:cover; transition:transform .55s; }
.{$slug}-features__grid article:hover .{$slug}-features__media img { transform:scale(1.08); }
.{$slug}-features__body { padding:22px; }
.{$slug}-features__icon { font-size:1.6rem; margin-bottom:10px; }
.{$slug}-features__grid h3 { margin:0 0 8px; color:#0f172a; }
.{$slug}-features__grid p { margin:0; color:#64748b; line-height:1.6; }";

		$js = '';

		return compact('html', 'css', 'js');
	}

	private static function saas_hero($title, $slug) {
		$heading = $title ?: 'Build Faster, Ship Smarter';
		$img     = esc_url(EPB_AI_Images::relevant_url('saas software startup dashboard', 1600, 900, 0, $heading, 'hero', $heading));

		$html = "<section class=\"{$slug}-hero\">
  <div class=\"{$slug}-hero__bg\" style=\"background-image:url('{$img}')\"></div>
  <div class=\"{$slug}-hero__overlay\"></div>
  <div class=\"{$slug}-hero__inner\">
    <span class=\"{$slug}-hero__badge\" data-epb-reveal>New: AI-Powered Workflows</span>
    <h1 data-epb-reveal data-epb-delay=\"80\">{$heading}</h1>
    <p data-epb-reveal data-epb-delay=\"140\">The all-in-one platform that helps teams collaborate, automate, and scale without limits.</p>
    <div class=\"{$slug}-hero__actions\" data-epb-reveal data-epb-delay=\"200\">
      <button class=\"{$slug}-hero__btn {$slug}-hero__btn--primary\">Start Free Trial</button>
      <button class=\"{$slug}-hero__btn\">Watch Demo</button>
    </div>
  </div>
</section>";

		$css = ".{$slug}-hero { position:relative; min-height:min(90vh,780px); display:flex; align-items:center; justify-content:center; color:#fff; text-align:center; width:100%; overflow:hidden; font-family:'Outfit',system-ui,sans-serif; }
.{$slug}-hero__bg { position:absolute; inset:0; background-size:cover; background-position:center; transform:scale(1.08); animation:{$slug}-saas-ken 18s ease-in-out infinite alternate; }
.{$slug}-hero__overlay { position:absolute; inset:0; background:linear-gradient(145deg,rgba(8,12,24,.9),rgba(15,118,110,.72)); }
.{$slug}-hero__inner { position:relative; z-index:1; max-width:760px; padding:100px 24px; }
.{$slug}-hero__badge { display:inline-block; background:rgba(255,255,255,.12); color:#99f6e4; padding:7px 16px; border-radius:999px; font-size:13px; font-weight:600; margin-bottom:24px; border:1px solid rgba(255,255,255,.2); }
.{$slug}-hero h1 { font-family:'Fraunces',Georgia,serif; font-size:clamp(2.2rem,5vw,3.6rem); margin:0 0 20px; line-height:1.08; }
.{$slug}-hero p { font-size:1.15rem; color:rgba(255,255,255,.88); margin:0 0 36px; }
.{$slug}-hero__actions { display:flex; gap:14px; justify-content:center; flex-wrap:wrap; }
.{$slug}-hero__btn { padding:14px 30px; border-radius:999px; font-size:15px; font-weight:700; cursor:pointer; border:2px solid rgba(255,255,255,.35); background:transparent; color:#fff; font-family:inherit; transition:transform .25s,background .25s; }
.{$slug}-hero__btn--primary { background:#fff; border-color:#fff; color:#0f766e; }
.{$slug}-hero__btn:hover { transform:translateY(-3px); }
@keyframes {$slug}-saas-ken { from { transform:scale(1.08); } to { transform:scale(1); } }";

		$js = "document.querySelectorAll('.{$slug}-hero__btn').forEach(function(b){b.addEventListener('click',function(){b.textContent='Thanks!';});});";

		return compact('html', 'css', 'js');
	}

	private static function generic_content_section($slug) {
		$topic = 'modern professional website';
		$cards = '';
		$items = [
			['⚡', 'Fast Setup', 'Launch polished sections in minutes, not hours.'],
			['🎨', 'Modern Design', 'Clean layouts with responsive styling built in.'],
			['🔧', 'Full Control', 'Every section, column, and widget is fully editable.'],
		];
		foreach ($items as $i => $item) {
			$img = esc_url(EPB_AI_Images::relevant_url($topic, 640, 420, $i, 'Why Choose Us', 'features', $item[1]));
			$cards .= '<article data-epb-reveal data-epb-delay="' . ( $i * 80 ) . '"><img src="' . $img . '" alt="" loading="lazy" /><span class="' . $slug . '-block__icon">' . $item[0] . '</span><h3>' . esc_html($item[1]) . '</h3><p>' . esc_html($item[2]) . '</p></article>';
		}

		$html = "<section class=\"{$slug}-block\">
  <div class=\"{$slug}-block__inner\">
    <h2 data-epb-reveal>Why Choose Us</h2>
    <p class=\"{$slug}-block__sub\" data-epb-reveal>Everything you need to build a beautiful, high-converting page.</p>
    <div class=\"{$slug}-block__grid\">{$cards}</div>
  </div>
</section>";

		$css = ".{$slug}-block { padding:100px 20px; background:#f7f8fb; width:100%; font-family:'Outfit',system-ui,sans-serif; }
.{$slug}-block__inner { max-width:1100px; margin:0 auto; text-align:center; }
.{$slug}-block h2 { font-size:clamp(1.9rem,3vw,2.4rem); margin:0 0 10px; color:#0f172a; }
.{$slug}-block__sub { color:#64748b; margin:0 0 40px; font-size:1.05rem; }
.{$slug}-block__grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(240px,1fr)); gap:22px; text-align:left; }
.{$slug}-block__grid article { background:#fff; padding:18px 18px 26px; border-radius:20px; box-shadow:0 10px 28px rgba(15,23,42,.06); transition:transform .3s; overflow:hidden; }
.{$slug}-block__grid article:hover { transform:translateY(-6px); }
.{$slug}-block__grid img { width:100%; height:160px; object-fit:cover; border-radius:14px; margin-bottom:16px; }
.{$slug}-block__icon { font-size:1.5rem; display:block; margin-bottom:8px; }
.{$slug}-block__grid h3 { margin:0 0 8px; color:#0f172a; }
.{$slug}-block__grid p { margin:0; color:#64748b; line-height:1.6; }";

		$js = '';

		return compact('html', 'css', 'js');
	}
}



