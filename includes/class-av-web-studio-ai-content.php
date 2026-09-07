<?php

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Industry-aware full-page generation with images, motion, and polished layouts.
 */
class Av_Web_Studio_AI_Content {

	/**
	 * Starter template catalog for the Templates UI.
	 *
	 * @return array<int,array<string,mixed>>
	 */
	public static function starter_catalog() {
		$items = [
			[
				'id'          => 'manufacturing',
				'title'       => 'Precision Manufacturing',
				'description' => 'Animated industrial landing page for CNC, assembly, and ISO-ready production partners.',
				'topic'       => 'precision manufacturing CNC factory plant',
				'brand'       => 'Apex Precision',
			],
			[
				'id'          => 'steel',
				'title'       => 'Steel Manufacturing',
				'description' => 'Bold steel mill & fabrication template with sparks, process timeline, and heavy-industry motion.',
				'topic'       => 'steel manufacturing mill fabrication foundry',
				'brand'       => 'Forge & Beam',
			],
			[
				'id'          => 'coffee',
				'title'       => 'Artisan Coffee Shop',
				'description' => 'Warm café landing page with hero, menu highlights, gallery, and booking CTA.',
				'topic'       => 'artisan coffee shop cafe',
				'brand'       => 'Roast & Bloom',
			],
			[
				'id'          => 'fitness',
				'title'       => 'Fitness Studio',
				'description' => 'High-energy gym site with classes, trainers, pricing, and membership CTA.',
				'topic'       => 'fitness gym studio',
				'brand'       => 'Pulse Fitness',
			],
			[
				'id'          => 'saas',
				'title'       => 'SaaS Product',
				'description' => 'Modern software landing page with features, social proof, pricing, and demo CTA.',
				'topic'       => 'saas software startup platform',
				'brand'       => 'LaunchPad',
			],
			[
				'id'          => 'agency',
				'title'       => 'Creative Agency',
				'description' => 'Bold agency portfolio with case gallery, services, team, and contact.',
				'topic'       => 'creative design marketing agency',
				'brand'       => 'Northstar Agency',
			],
			[
				'id'          => 'restaurant',
				'title'       => 'Fine Dining Restaurant',
				'description' => 'Elegant restaurant page with ambiance gallery, signature dishes, and reservations.',
				'topic'       => 'fine dining restaurant',
				'brand'       => 'Maison Verde',
			],
			[
				'id'          => 'realestate',
				'title'       => 'Real Estate',
				'description' => 'Property showcase with listings gallery, agent team, testimonials, and inquiry form.',
				'topic'       => 'real estate luxury homes',
				'brand'       => 'Harbor Homes',
			],
			[
				'id'          => 'beauty',
				'title'       => 'Beauty Salon',
				'description' => 'Soft, premium salon page with services, before/after gallery, and booking CTA.',
				'topic'       => 'beauty salon spa',
				'brand'       => 'Luxe Glow',
			],
			[
				'id'          => 'yoga',
				'title'       => 'Yoga & Wellness',
				'description' => 'Calm wellness studio with class schedule feel, instructors, FAQ, and signup.',
				'topic'       => 'yoga wellness studio',
				'brand'       => 'Stillpoint Yoga',
			],
		];

		foreach ($items as &$item) {
			try {
				$item['preview'] = Av_Web_Studio_AI_Images::relevant_url($item['topic'], 960, 640, 0, $item['brand'], 'hero', $item['title']);
				$profile         = Av_Web_Studio_AI_Profiles::get($item['topic'], $item['brand']);
				$item['accent']  = $profile['accent'] ?? '#2563eb';
			} catch (Throwable $e) {
				$item['preview'] = '';
				$item['accent']  = '#2563eb';
			}
		}
		unset($item);

		return $items;
	}

	/**
	 * Build a starter page by catalog id.
	 *
	 * @param string $id Catalog id.
	 * @return array{html:string,css:string,js:string,title:string}|null
	 */
	public static function starter_by_id($id) {
		foreach (self::starter_catalog() as $item) {
			if ($item['id'] === $id) {
				if (in_array($id, [ 'manufacturing', 'steel' ], true)) {
					$code = self::generate_industrial_page($item['topic'], $item['brand'], $id);
				} else {
					$code = self::generate_full_page($item['topic'], $item['brand']);
				}
				$code['title'] = $item['brand'];
				return $code;
			}
		}
		return null;
	}

	/**
	 * Animated industrial landing page (manufacturing / steel).
	 *
	 * @param string $topic      Topic.
	 * @param string $page_title Brand.
	 * @param string $variant    manufacturing|steel.
	 * @return array{html:string,css:string,js:string}
	 */
	public static function generate_industrial_page($topic, $page_title = '', $variant = 'manufacturing') {
		return Av_Web_Studio_AI_Industrial::generate($topic, $page_title, $variant);
	}

	/**
	 * Generate a complete landing page from topic + profile.
	 *
	 * @param string $topic      Business/topic.
	 * @param string $page_title Page title.
	 * @return array{html:string,css:string,js:string}
	 */
	public static function generate_full_page($topic, $page_title = '') {
		$p     = Av_Web_Studio_AI_Profiles::get($topic, $page_title);
		$slug  = sanitize_title(substr($p['brand'], 0, 24)) ?: 'landing';
		$brand = $p['brand'];

		$hero_img = esc_url(Av_Web_Studio_AI_Images::relevant_url($topic, 1600, 900, 0, $page_title, 'hero', $p['hero_headline']));
		$feat_img = esc_url(Av_Web_Studio_AI_Images::relevant_url($topic, 1000, 720, 1, $page_title, 'features', $p['features_title']));
		$cta_img  = esc_url(Av_Web_Studio_AI_Images::relevant_url($topic, 1400, 700, 5, $page_title, 'hero', $p['cta_title']));

		$gal_items = '';
		for ($i = 0; $i < 6; $i++) {
			$src = esc_url(Av_Web_Studio_AI_Images::relevant_url($topic, 720, 540, $i + 2, $page_title, 'gallery', $brand));
			$alt = esc_attr($brand . ' gallery ' . ( $i + 1 ));
			$gal_items .= '<figure class="' . $slug . '-gallery__item" data-av-web-studio-reveal data-av-web-studio-delay="' . ( $i * 60 ) . '">'
				. '<img src="' . $src . '" alt="' . $alt . '" loading="lazy" width="720" height="540" />'
				. '<figcaption>' . esc_html($brand) . ' · ' . ( $i + 1 ) . '</figcaption></figure>';
		}

		$thumbs = '';
		for ($i = 0; $i < 3; $i++) {
			$src = esc_url(Av_Web_Studio_AI_Images::relevant_url($topic, 320, 240, $i + 8, $page_title, 'gallery', $brand));
			$thumbs .= '<img src="' . $src . '" alt="" loading="lazy" width="320" height="240" />';
		}

		$headline   = esc_html($p['hero_headline']);
		$badge      = esc_html($p['hero_badge']);
		$hero_sub   = esc_html($p['hero_sub']);
		$cta1       = esc_html($p['cta_primary']);
		$cta2       = esc_html($p['cta_secondary']);
		$feat_title = esc_html(str_replace('{brand}', $brand, $p['features_title']));
		$feat_lead  = esc_html($p['features_lead']);
		$meta       = esc_html($p['meta']);
		$about_title = esc_html($p['about_title']);
		$about_p1    = esc_html($p['about_p1']);
		$about_p2    = esc_html($p['about_p2']);
		$cta_title   = esc_html($p['cta_title']);
		$cta_text    = esc_html($p['cta_text']);
		$img_alt     = esc_attr($brand . ' — professional services');
		$brand_esc   = esc_html($brand);

		$features_html = '';
		$fi = 0;
		foreach ($p['features'] as $f) {
			$features_html .= '<article class="' . $slug . '-feat-card" data-av-web-studio-reveal data-av-web-studio-delay="' . ( $fi * 80 ) . '">'
				. '<div class="' . $slug . '-feat-icon">' . esc_html($f['icon']) . '</div>'
				. '<h3>' . esc_html($f['title']) . '</h3>'
				. '<p>' . esc_html($f['text']) . '</p></article>';
			++$fi;
		}

		$bullets = '';
		foreach ($p['about_bullets'] as $b) {
			$bullets .= '<li data-av-web-studio-reveal><span aria-hidden="true">✓</span> ' . esc_html($b) . '</li>';
		}

		$testimonials_html = '';
		$ti = 0;
		foreach ($p['testimonials'] as $t) {
			$avatar = esc_url(Av_Web_Studio_AI_Images::relevant_url($topic, 96, 96, $ti + 12, $page_title, 'team', $t['name']));
			$testimonials_html .= '<blockquote class="' . $slug . '-quote" data-av-web-studio-reveal data-av-web-studio-delay="' . ( $ti * 90 ) . '">'
				. '<div class="' . $slug . '-quote__stars" aria-hidden="true">★★★★★</div>'
				. '<p>"' . esc_html($t['quote']) . '"</p>'
				. '<footer><img src="' . $avatar . '" alt="" width="48" height="48" loading="lazy" />'
				. '<div><strong>' . esc_html($t['name']) . '</strong><span>' . esc_html($t['role']) . '</span></div></footer></blockquote>';
			++$ti;
		}

		$faq_html = '';
		$faq_i    = 0;
		foreach ($p['faq'] as $item) {
			$open     = $faq_i === 0 ? ' open' : '';
			$faq_html .= '<details class="' . $slug . '-faq__item" data-av-web-studio-reveal' . $open . '>'
				. '<summary>' . esc_html($item['q']) . '</summary>'
				. '<p>' . esc_html($item['a']) . '</p></details>';
			++$faq_i;
		}

		$html = "<!--
  SEO: Title: {$brand_esc}
  Meta Description: {$meta}
-->
<main class=\"{$slug}-page\">
  <section class=\"{$slug}-hero\" aria-labelledby=\"{$slug}-hero-h1\">
    <div class=\"{$slug}-hero__bg\" style=\"background-image:url('{$hero_img}')\" role=\"img\" aria-label=\"{$brand_esc}\"></div>
    <div class=\"{$slug}-hero__overlay\"></div>
    <div class=\"{$slug}-hero__glow\" aria-hidden=\"true\"></div>
    <div class=\"{$slug}-hero__inner\">
      <span class=\"{$slug}-hero__badge\" data-av-web-studio-reveal>{$badge}</span>
      <h1 id=\"{$slug}-hero-h1\" data-av-web-studio-reveal data-av-web-studio-delay=\"80\">{$headline}</h1>
      <p class=\"{$slug}-hero__lead\" data-av-web-studio-reveal data-av-web-studio-delay=\"140\">{$hero_sub}</p>
      <div class=\"{$slug}-hero__actions\" data-av-web-studio-reveal data-av-web-studio-delay=\"200\">
        <a href=\"#contact\" class=\"{$slug}-btn {$slug}-btn--primary\">{$cta1}</a>
        <a href=\"#gallery\" class=\"{$slug}-btn {$slug}-btn--ghost\">{$cta2}</a>
      </div>
    </div>
    <div class=\"{$slug}-hero__scroll\" aria-hidden=\"true\"><span></span></div>
  </section>

  <section class=\"{$slug}-stats\" aria-label=\"Highlights\">
    <div class=\"{$slug}-wrap {$slug}-stats__grid\">
      <div class=\"{$slug}-stat\" data-av-web-studio-reveal><strong data-count=\"12\">0</strong><span>Years experience</span></div>
      <div class=\"{$slug}-stat\" data-av-web-studio-reveal data-av-web-studio-delay=\"80\"><strong data-count=\"850\">0</strong><span>Happy clients</span></div>
      <div class=\"{$slug}-stat\" data-av-web-studio-reveal data-av-web-studio-delay=\"160\"><strong data-count=\"98\">0</strong><span>Satisfaction %</span></div>
      <div class=\"{$slug}-stat\" data-av-web-studio-reveal data-av-web-studio-delay=\"240\"><strong data-count=\"24\">0</strong><span>Awards won</span></div>
    </div>
  </section>

  <section id=\"features\" class=\"{$slug}-features\">
    <div class=\"{$slug}-wrap\">
      <header class=\"{$slug}-section-head\" data-av-web-studio-reveal>
        <p class=\"{$slug}-eyebrow\">What we offer</p>
        <h2>{$feat_title}</h2>
        <p class=\"{$slug}-lead\">{$feat_lead}</p>
      </header>
      <div class=\"{$slug}-feat-grid\">{$features_html}</div>
    </div>
  </section>

  <section class=\"{$slug}-about\">
    <div class=\"{$slug}-about__grid {$slug}-wrap\">
      <div class=\"{$slug}-about__visual\" data-av-web-studio-reveal=\"left\">
        <img class=\"{$slug}-about__img\" src=\"{$feat_img}\" alt=\"{$img_alt}\" width=\"1000\" height=\"720\" loading=\"lazy\" />
        <div class=\"{$slug}-about__thumbs\">{$thumbs}</div>
      </div>
      <div class=\"{$slug}-about__text\" data-av-web-studio-reveal=\"right\">
        <p class=\"{$slug}-eyebrow\">Our story</p>
        <h2>{$about_title}</h2>
        <p>{$about_p1}</p>
        <p>{$about_p2}</p>
        <ul class=\"{$slug}-about__list\">{$bullets}</ul>
      </div>
    </div>
  </section>

  <section id=\"gallery\" class=\"{$slug}-gallery\">
    <div class=\"{$slug}-wrap\">
      <header class=\"{$slug}-section-head\" data-av-web-studio-reveal>
        <p class=\"{$slug}-eyebrow\">Gallery</p>
        <h2>Moments that define us</h2>
        <p class=\"{$slug}-lead\">A visual look at the craft, people, and places behind {$brand_esc}.</p>
      </header>
      <div class=\"{$slug}-gallery__grid\">{$gal_items}</div>
    </div>
  </section>

  <section class=\"{$slug}-testimonials\">
    <div class=\"{$slug}-wrap\">
      <header class=\"{$slug}-section-head\" data-av-web-studio-reveal>
        <p class=\"{$slug}-eyebrow\">Testimonials</p>
        <h2>Loved by people like you</h2>
      </header>
      <div class=\"{$slug}-test-grid\">{$testimonials_html}</div>
    </div>
  </section>

  <section class=\"{$slug}-faq\">
    <div class=\"{$slug}-wrap {$slug}-faq__wrap\">
      <header class=\"{$slug}-section-head\" data-av-web-studio-reveal>
        <p class=\"{$slug}-eyebrow\">FAQ</p>
        <h2>Questions, answered</h2>
      </header>
      {$faq_html}
    </div>
  </section>

  <section id=\"contact\" class=\"{$slug}-cta\">
    <div class=\"{$slug}-cta__bg\" style=\"background-image:url('{$cta_img}')\" aria-hidden=\"true\"></div>
    <div class=\"{$slug}-cta__overlay\"></div>
    <div class=\"{$slug}-wrap {$slug}-cta__inner\" data-av-web-studio-reveal>
      <h2>{$cta_title}</h2>
      <p>{$cta_text}</p>
      <a href=\"#\" class=\"{$slug}-btn {$slug}-btn--primary {$slug}-btn--lg\">{$cta1}</a>
    </div>
  </section>
</main>";

		$css = self::styles($slug, $p['accent'], $p['accent2']);
		$js  = self::scripts($brand, $meta, $slug);

		return compact('html', 'css', 'js');
	}

	/**
	 * Build a single modern hero section from profile (for append).
	 *
	 * @param string $topic      Topic.
	 * @param string $page_title Page title.
	 * @param string $slug       CSS slug.
	 * @return array
	 */
	public static function hero_section($topic, $page_title, $slug) {
		$p        = Av_Web_Studio_AI_Profiles::get($topic, $page_title);
		$img      = esc_url(Av_Web_Studio_AI_Images::relevant_url($topic, 1600, 900, 0, $page_title, 'hero', $p['hero_headline']));
		$headline = esc_html($p['hero_headline']);
		$badge    = esc_html($p['hero_badge']);
		$sub      = esc_html($p['hero_sub']);
		$cta1     = esc_html($p['cta_primary']);
		$cta2     = esc_html($p['cta_secondary']);
		$a        = esc_attr($p['accent']);
		$a2       = esc_attr($p['accent2']);

		$html = "<section class=\"{$slug}-hero\">
  <div class=\"{$slug}-hero__bg\" style=\"background-image:url('{$img}')\"></div>
  <div class=\"{$slug}-hero__overlay\"></div>
  <div class=\"{$slug}-hero__inner\">
    <span class=\"{$slug}-hero__badge\" data-av-web-studio-reveal>{$badge}</span>
    <h1 data-av-web-studio-reveal data-av-web-studio-delay=\"80\">{$headline}</h1>
    <p data-av-web-studio-reveal data-av-web-studio-delay=\"140\">{$sub}</p>
    <div class=\"{$slug}-hero__btns\" data-av-web-studio-reveal data-av-web-studio-delay=\"200\">
      <button class=\"{$slug}-hero__btn {$slug}-hero__btn--fill\">{$cta1}</button>
      <button class=\"{$slug}-hero__btn {$slug}-hero__btn--outline\">{$cta2}</button>
    </div>
  </div>
</section>";

		$css = ".{$slug}-hero { position:relative; min-height:min(92vh,820px); display:flex; align-items:center; justify-content:center; overflow:hidden; color:#fff; text-align:center; width:100%; font-family:'Outfit',system-ui,sans-serif; }
.{$slug}-hero__bg { position:absolute; inset:0; background-size:cover; background-position:center; transform:scale(1.08); animation:{$slug}-ken 18s ease-in-out infinite alternate; }
.{$slug}-hero__overlay { position:absolute; inset:0; background:linear-gradient(135deg,rgba(10,12,20,.82) 0%,{$a}b8 55%,{$a2}99 100%); }
.{$slug}-hero__inner { position:relative; z-index:1; max-width:820px; padding:96px 24px; }
.{$slug}-hero__badge { display:inline-block; padding:8px 18px; background:rgba(255,255,255,.12); border:1px solid rgba(255,255,255,.22); border-radius:999px; font-size:13px; font-weight:600; margin-bottom:22px; backdrop-filter:blur(10px); }
.{$slug}-hero h1 { font-family:'Fraunces',Georgia,serif; font-size:clamp(2.2rem,5.5vw,3.8rem); font-weight:700; margin:0 0 18px; line-height:1.08; letter-spacing:-.03em; }
.{$slug}-hero p { font-size:clamp(1rem,2vw,1.2rem); opacity:.92; margin:0 0 32px; line-height:1.7; }
.{$slug}-hero__btns { display:flex; gap:14px; justify-content:center; flex-wrap:wrap; }
.{$slug}-hero__btn { padding:14px 30px; border-radius:999px; font-size:15px; font-weight:700; cursor:pointer; border:none; transition:transform .25s,box-shadow .25s,background .25s; font-family:inherit; }
.{$slug}-hero__btn--fill { background:#fff; color:{$a}; box-shadow:0 10px 30px rgba(0,0,0,.25); }
.{$slug}-hero__btn--outline { background:transparent; color:#fff; border:2px solid rgba(255,255,255,.55); }
.{$slug}-hero__btn:hover { transform:translateY(-3px); }
[data-av-web-studio-reveal]{opacity:0;transform:translateY(28px);transition:opacity .75s cubic-bezier(.22,1,.36,1),transform .75s cubic-bezier(.22,1,.36,1);}
[data-av-web-studio-reveal].is-visible{opacity:1;transform:none;}
@keyframes {$slug}-ken { from { transform:scale(1.08) translateY(0); } to { transform:scale(1) translateY(-1.5%); } }";

		$js = self::motion_js() . "document.querySelectorAll('.{$slug}-hero__btn').forEach(function(b){b.addEventListener('click',function(){b.textContent='Thank you!';});});";

		return compact('html', 'css', 'js');
	}

	/**
	 * Features section from profile.
	 *
	 * @param string $topic Topic.
	 * @param string $page_title Page title.
	 * @param string $slug CSS slug.
	 * @return array
	 */
	public static function features_section($topic, $page_title, $slug) {
		$p     = Av_Web_Studio_AI_Profiles::get($topic, $page_title);
		$a     = esc_attr($p['accent']);
		$title = esc_html(str_replace('{brand}', $p['brand'], $p['features_title']));
		$lead  = esc_html($p['features_lead']);
		$cards = '';
		$i     = 0;
		foreach ($p['features'] as $f) {
			$img = esc_url(Av_Web_Studio_AI_Images::relevant_url($topic, 640, 420, $i, $page_title, 'features', $f['title']));
			$cards .= '<article data-av-web-studio-reveal data-av-web-studio-delay="' . ( $i * 80 ) . '">'
				. '<div class="media"><img src="' . $img . '" alt="" loading="lazy" width="640" height="420" /></div>'
				. '<div class="body"><span class="icon">' . esc_html($f['icon']) . '</span><h3>' . esc_html($f['title']) . '</h3><p>' . esc_html($f['text']) . '</p></div></article>';
			++$i;
		}

		$html = "<section class=\"{$slug}-feat\"><div class=\"inner\"><h2 data-av-web-studio-reveal>{$title}</h2><p class=\"lead\" data-av-web-studio-reveal>{$lead}</p><div class=\"grid\">{$cards}</div></div></section>";

		$css = ".{$slug}-feat { padding:110px 24px; background:linear-gradient(180deg,#fff 0%,#f4f6f9 100%); width:100%; font-family:'Outfit',system-ui,sans-serif; }
.{$slug}-feat .inner { max-width:1140px; margin:0 auto; text-align:center; }
.{$slug}-feat h2 { font-size:clamp(1.9rem,3vw,2.7rem); font-weight:800; margin:0 0 12px; color:#0f172a; letter-spacing:-.03em; }
.{$slug}-feat .lead { color:#64748b; max-width:560px; margin:0 auto 52px; font-size:1.05rem; }
.{$slug}-feat .grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(250px,1fr)); gap:24px; text-align:left; }
.{$slug}-feat article { background:#fff; border-radius:22px; overflow:hidden; border:1px solid #e8ecf1; box-shadow:0 8px 30px rgba(15,23,42,.05); transition:transform .35s,box-shadow .35s; }
.{$slug}-feat article:hover { transform:translateY(-8px); box-shadow:0 22px 50px {$a}22; }
.{$slug}-feat .media { aspect-ratio:16/10; overflow:hidden; }
.{$slug}-feat .media img { width:100%; height:100%; object-fit:cover; transition:transform .6s ease; }
.{$slug}-feat article:hover .media img { transform:scale(1.08); }
.{$slug}-feat .body { padding:24px 22px 28px; }
.{$slug}-feat .icon { font-size:1.5rem; display:block; margin-bottom:10px; }
.{$slug}-feat h3 { margin:0 0 8px; font-size:1.12rem; color:#0f172a; }
.{$slug}-feat p { margin:0; color:#64748b; line-height:1.65; font-size:.95rem; }
[data-av-web-studio-reveal]{opacity:0;transform:translateY(24px);transition:opacity .7s ease,transform .7s ease;}
[data-av-web-studio-reveal].is-visible{opacity:1;transform:none;}";

		$js = self::motion_js();

		return compact('html', 'css', 'js');
	}

	/**
	 * Shared scroll-reveal + counter script.
	 *
	 * @return string
	 */
	public static function motion_js() {
		return "(function(){
  if(window.__avWebStudioMotionInit) return; window.__avWebStudioMotionInit=true;
  var reduce=window.matchMedia&&window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var els=document.querySelectorAll('[data-av-web-studio-reveal]');
  function show(el){
    var d=parseInt(el.getAttribute('data-av-web-studio-delay')||'0',10);
    setTimeout(function(){el.classList.add('is-visible');}, reduce?0:d);
  }
  if(reduce || !('IntersectionObserver' in window)){els.forEach(show); }
  else{
    var io=new IntersectionObserver(function(entries){
      entries.forEach(function(en){ if(en.isIntersecting){ show(en.target); io.unobserve(en.target);} });
    },{threshold:0.14,rootMargin:'0px 0px -8% 0px'});
    els.forEach(function(el){io.observe(el);});
  }
  document.querySelectorAll('[data-count]').forEach(function(el){
    var target=parseInt(el.getAttribute('data-count'),10)||0;
    var run=function(){
      if(reduce){el.textContent=String(target);return;}
      var start=null; var dur=1200;
      function step(ts){
        if(!start) start=ts; var p=Math.min(1,(ts-start)/dur);
        el.textContent=String(Math.floor(target*(0.5-Math.cos(Math.PI*p)/2)));
        if(p<1) requestAnimationFrame(step); else el.textContent=String(target);
      }
      requestAnimationFrame(step);
    };
    if(!('IntersectionObserver' in window)){run();}
    else{
      var cio=new IntersectionObserver(function(entries){
        entries.forEach(function(en){ if(en.isIntersecting){ run(); cio.unobserve(en.target);} });
      },{threshold:0.4});
      cio.observe(el);
    }
  });
})();";
	}

	private static function styles($slug, $accent, $accent2) {
		$a  = esc_attr($accent);
		$a2 = esc_attr($accent2);
		return ".{$slug}-page { font-family:'Outfit',system-ui,sans-serif; color:#0f172a; line-height:1.65; width:100%; overflow-x:hidden; background:#fff; }
.{$slug}-wrap { max-width:1160px; margin:0 auto; padding:0 24px; }
.{$slug}-eyebrow { display:inline-block; margin:0 0 10px; color:{$a}; font-size:.8rem; font-weight:700; letter-spacing:.12em; text-transform:uppercase; }
.{$slug}-section-head { text-align:center; margin-bottom:48px; }
.{$slug}-section-head h2 { font-family:'Fraunces',Georgia,serif; font-size:clamp(1.9rem,3.4vw,2.8rem); font-weight:700; margin:0 0 12px; letter-spacing:-.02em; }
.{$slug}-lead { color:#64748b; font-size:1.08rem; max-width:580px; margin:0 auto; }
.{$slug}-hero { position:relative; min-height:min(94vh,860px); display:flex; align-items:center; justify-content:center; text-align:center; color:#fff; overflow:hidden; width:100%; }
.{$slug}-hero__bg { position:absolute; inset:0; background-size:cover; background-position:center; transform:scale(1.1); animation:{$slug}-ken 20s ease-in-out infinite alternate; }
.{$slug}-hero__overlay { position:absolute; inset:0; background:linear-gradient(160deg,rgba(8,10,18,.88) 0%,{$a}aa 50%,{$a2}88 100%); }
.{$slug}-hero__glow { position:absolute; width:50vw; height:50vw; border-radius:50%; background:radial-gradient(circle,{$a2}55 0%,transparent 70%); top:10%; right:-10%; filter:blur(40px); pointer-events:none; animation:{$slug}-float 10s ease-in-out infinite; }
.{$slug}-hero__inner { position:relative; z-index:1; max-width:860px; padding:100px 24px 120px; }
.{$slug}-hero__badge { display:inline-block; background:rgba(255,255,255,.12); backdrop-filter:blur(12px); padding:8px 20px; border-radius:999px; font-size:13px; font-weight:600; margin-bottom:24px; border:1px solid rgba(255,255,255,.22); }
.{$slug}-hero h1 { font-family:'Fraunces',Georgia,serif; font-size:clamp(2.4rem,6vw,4.2rem); font-weight:700; margin:0 0 20px; line-height:1.05; letter-spacing:-.04em; }
.{$slug}-hero__lead { font-size:clamp(1.05rem,2vw,1.28rem); opacity:.93; margin:0 auto 36px; max-width:640px; }
.{$slug}-hero__actions { display:flex; gap:14px; justify-content:center; flex-wrap:wrap; }
.{$slug}-hero__scroll { position:absolute; bottom:28px; left:50%; transform:translateX(-50%); width:24px; height:38px; border:2px solid rgba(255,255,255,.45); border-radius:14px; }
.{$slug}-hero__scroll span { display:block; width:4px; height:8px; margin:8px auto 0; background:#fff; border-radius:4px; animation:{$slug}-scroll 1.6s ease infinite; }
.{$slug}-btn { display:inline-flex; align-items:center; justify-content:center; padding:15px 32px; border-radius:999px; font-weight:700; font-size:15px; text-decoration:none; transition:transform .25s,box-shadow .25s,background .25s; cursor:pointer; border:none; font-family:inherit; }
.{$slug}-btn--primary { background:#fff; color:{$a}; box-shadow:0 10px 30px rgba(0,0,0,.2); }
.{$slug}-btn--primary:hover { transform:translateY(-3px); box-shadow:0 16px 40px rgba(0,0,0,.28); }
.{$slug}-btn--ghost { background:rgba(255,255,255,.1); color:#fff; border:2px solid rgba(255,255,255,.45); backdrop-filter:blur(8px); }
.{$slug}-btn--ghost:hover { background:rgba(255,255,255,.2); border-color:#fff; }
.{$slug}-btn--lg { padding:18px 42px; font-size:17px; }
.{$slug}-stats { padding:0; margin-top:-42px; position:relative; z-index:2; width:100%; }
.{$slug}-stats__grid { display:grid; grid-template-columns:repeat(4,1fr); gap:16px; background:#fff; border:1px solid #e8ecf1; border-radius:24px; padding:28px 18px; box-shadow:0 20px 50px rgba(15,23,42,.08); }
.{$slug}-stat { text-align:center; }
.{$slug}-stat strong { display:block; font-size:clamp(1.6rem,3vw,2.2rem); font-weight:800; color:{$a}; letter-spacing:-.03em; }
.{$slug}-stat span { color:#64748b; font-size:.9rem; }
.{$slug}-features { padding:110px 0 90px; background:linear-gradient(180deg,#fff 0%,#f7f8fb 100%); width:100%; }
.{$slug}-feat-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(240px,1fr)); gap:22px; }
.{$slug}-feat-card { background:#fff; padding:34px 26px; border-radius:22px; border:1px solid #e8ecf1; box-shadow:0 4px 20px rgba(0,0,0,.03); transition:transform .35s,box-shadow .35s,border-color .35s; }
.{$slug}-feat-card:hover { transform:translateY(-8px); box-shadow:0 22px 50px {$a}18; border-color:{$a}55; }
.{$slug}-feat-icon { font-size:2rem; margin-bottom:14px; }
.{$slug}-feat-card h3 { margin:0 0 10px; font-size:1.12rem; font-weight:700; }
.{$slug}-feat-card p { margin:0; color:#64748b; font-size:.95rem; }
.{$slug}-about { padding:110px 0; background:#fff; width:100%; }
.{$slug}-about__grid { display:grid; grid-template-columns:1.05fr .95fr; gap:56px; align-items:center; }
.{$slug}-about__img { width:100%; border-radius:28px; box-shadow:0 28px 70px rgba(0,0,0,.14); object-fit:cover; aspect-ratio:5/4; }
.{$slug}-about__thumbs { display:grid; grid-template-columns:repeat(3,1fr); gap:12px; margin-top:14px; }
.{$slug}-about__thumbs img { width:100%; height:104px; object-fit:cover; border-radius:16px; transition:transform .35s; }
.{$slug}-about__thumbs img:hover { transform:translateY(-4px); }
.{$slug}-about__text h2 { font-family:'Fraunces',Georgia,serif; font-size:clamp(1.7rem,3vw,2.5rem); font-weight:700; margin:0 0 20px; text-align:left; }
.{$slug}-about__text p { color:#475569; margin:0 0 16px; }
.{$slug}-about__list { list-style:none; padding:0; margin:24px 0 0; }
.{$slug}-about__list li { display:flex; gap:10px; align-items:flex-start; padding:12px 0; color:#334155; font-weight:600; border-bottom:1px solid #f1f5f9; }
.{$slug}-about__list span { color:{$a}; }
.{$slug}-gallery { padding:100px 0; background:#0b1020; color:#fff; width:100%; }
.{$slug}-gallery .{$slug}-eyebrow { color:#a5b4fc; }
.{$slug}-gallery .{$slug}-section-head h2 { color:#fff; }
.{$slug}-gallery .{$slug}-lead { color:#94a3b8; }
.{$slug}-gallery__grid { display:grid; grid-template-columns:repeat(3,1fr); gap:16px; }
.{$slug}-gallery__item { position:relative; overflow:hidden; border-radius:20px; margin:0; aspect-ratio:4/3; }
.{$slug}-gallery__item:nth-child(1), .{$slug}-gallery__item:nth-child(6) { grid-row:span 1; }
.{$slug}-gallery__item img { width:100%; height:100%; object-fit:cover; display:block; transition:transform .7s ease; }
.{$slug}-gallery__item:hover img { transform:scale(1.1); }
.{$slug}-gallery__item figcaption { position:absolute; inset:auto 0 0 0; padding:18px; background:linear-gradient(transparent,rgba(0,0,0,.75)); color:#fff; font-weight:600; font-size:.92rem; transform:translateY(12px); opacity:0; transition:opacity .35s,transform .35s; }
.{$slug}-gallery__item:hover figcaption { opacity:1; transform:none; }
.{$slug}-testimonials { padding:110px 0; background:{$a}0c; width:100%; }
.{$slug}-test-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(280px,1fr)); gap:22px; }
.{$slug}-quote { background:#fff; padding:30px; border-radius:22px; margin:0; box-shadow:0 10px 30px rgba(15,23,42,.06); border:1px solid #eef1f5; }
.{$slug}-quote__stars { color:#f59e0b; letter-spacing:2px; margin-bottom:12px; font-size:.95rem; }
.{$slug}-quote p { font-style:italic; color:#334155; margin:0 0 20px; font-size:1.02rem; line-height:1.7; }
.{$slug}-quote footer { display:flex; align-items:center; gap:12px; }
.{$slug}-quote footer img { width:48px; height:48px; border-radius:50%; object-fit:cover; }
.{$slug}-quote footer strong { display:block; color:#0f172a; font-size:.95rem; }
.{$slug}-quote footer span { color:{$a}; font-size:.82rem; }
.{$slug}-faq { padding:100px 0; background:#fff; width:100%; }
.{$slug}-faq__wrap { max-width:760px; }
.{$slug}-faq__item { border:1px solid #e2e8f0; border-radius:16px; padding:4px 24px; margin-bottom:12px; transition:border-color .25s,box-shadow .25s,transform .25s; background:#fff; }
.{$slug}-faq__item[open] { border-color:{$a}; box-shadow:0 10px 28px {$a}18; transform:translateY(-2px); }
.{$slug}-faq__item summary { padding:18px 0; font-weight:700; cursor:pointer; list-style:none; }
.{$slug}-faq__item summary::-webkit-details-marker { display:none; }
.{$slug}-faq__item p { margin:0 0 18px; color:#64748b; }
.{$slug}-cta { position:relative; padding:120px 0; color:#fff; text-align:center; width:100%; overflow:hidden; }
.{$slug}-cta__bg { position:absolute; inset:0; background-size:cover; background-position:center; transform:scale(1.05); }
.{$slug}-cta__overlay { position:absolute; inset:0; background:linear-gradient(135deg,{$a}ee 0%,{$a2}dd 100%); }
.{$slug}-cta__inner { position:relative; z-index:1; max-width:640px; }
.{$slug}-cta h2 { font-family:'Fraunces',Georgia,serif; color:#fff; font-size:clamp(1.9rem,3vw,2.7rem); font-weight:700; margin:0 0 16px; }
.{$slug}-cta p { opacity:.94; font-size:1.1rem; margin:0 0 32px; }
[data-av-web-studio-reveal]{opacity:0;transform:translateY(28px);transition:opacity .8s cubic-bezier(.22,1,.36,1),transform .8s cubic-bezier(.22,1,.36,1); will-change:opacity,transform;}
[data-av-web-studio-reveal=\"left\"]{transform:translateX(-36px);}
[data-av-web-studio-reveal=\"right\"]{transform:translateX(36px);}
[data-av-web-studio-reveal].is-visible{opacity:1;transform:none;}
@keyframes {$slug}-ken { from { transform:scale(1.1) translate3d(0,0,0); } to { transform:scale(1) translate3d(0,-1.5%,0); } }
@keyframes {$slug}-float { 0%,100% { transform:translateY(0); } 50% { transform:translateY(-18px); } }
@keyframes {$slug}-scroll { 0% { opacity:1; transform:translateY(0); } 100% { opacity:0; transform:translateY(12px); } }
@media(max-width:900px){
  .{$slug}-about__grid,.{$slug}-gallery__grid { grid-template-columns:1fr 1fr; }
  .{$slug}-stats__grid { grid-template-columns:1fr 1fr; }
}
@media(max-width:640px){
  .{$slug}-about__grid,.{$slug}-gallery__grid,.{$slug}-stats__grid { grid-template-columns:1fr; }
  .{$slug}-hero { min-height:78vh; }
}
@media(prefers-reduced-motion:reduce){
  .{$slug}-hero__bg,.{$slug}-hero__glow,.{$slug}-hero__scroll span { animation:none !important; }
  [data-av-web-studio-reveal]{opacity:1;transform:none;transition:none;}
}";
	}

	private static function scripts($brand, $desc, $slug = '') {
		$schema = [
			'@context'    => 'https://schema.org',
			'@type'       => 'LocalBusiness',
			'name'        => wp_strip_all_tags($brand),
			'description' => wp_strip_all_tags($desc),
		];
		$js = self::motion_js();
		$js .= 'var avWebStudioSchema=' . wp_json_encode($schema) . ';(function(){var s=document.createElement("script");s.type="application/ld+json";s.textContent=JSON.stringify(avWebStudioSchema);document.head.appendChild(s);})();';
		return $js;
	}
}



