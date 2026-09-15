<?php

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Animated industrial landing pages (manufacturing & steel).
 *
 * Loaded as methods on Akash_Visual_Layout_Builder_AI_Content via require of this file's functions
 * being merged — actually we keep a dedicated class and call from Akash_Visual_Layout_Builder_AI_Content.
 */
class Akash_Visual_Layout_Builder_AI_Industrial {

	/**
	 * Generate a highly animated industrial landing page.
	 *
	 * @param string $topic      Topic.
	 * @param string $page_title Brand / title.
	 * @param string $variant    manufacturing|steel.
	 * @return array{html:string,css:string,js:string}
	 */
	public static function generate($topic, $page_title = '', $variant = 'manufacturing') {
		$p     = Akash_Visual_Layout_Builder_AI_Profiles::get($topic, $page_title);
		$slug  = sanitize_title(substr($p['brand'], 0, 24)) ?: 'industrial';
		$brand = $p['brand'];
		$is_steel = $variant === 'steel';

		$hero_img = esc_url(Akash_Visual_Layout_Builder_AI_Images::relevant_url($topic, 1800, 1000, 0, $page_title, 'hero', $p['hero_headline']));
		$plant_img = esc_url(Akash_Visual_Layout_Builder_AI_Images::relevant_url($topic, 1200, 800, 1, $page_title, 'about', $p['about_title']));
		$cta_img  = esc_url(Akash_Visual_Layout_Builder_AI_Images::relevant_url($topic, 1600, 800, 5, $page_title, 'hero', $p['cta_title']));

		$gal = '';
		for ($i = 0; $i < 6; $i++) {
			$src = esc_url(Akash_Visual_Layout_Builder_AI_Images::relevant_url($topic, 800, 600, $i + 2, $page_title, 'gallery', $brand));
			$gal .= '<figure class="' . $slug . '-shot" data-akash-visual-layout-builder-reveal data-akash-visual-layout-builder-delay="' . ( $i * 70 ) . '">'
				. '<img src="' . $src . '" alt="' . esc_attr($brand . ' facility ' . ( $i + 1 )) . '" loading="lazy" width="800" height="600" />'
				. '<figcaption>' . esc_html($is_steel ? 'Bay ' . ( $i + 1 ) : 'Cell ' . ( $i + 1 )) . '</figcaption></figure>';
		}

		$features = '';
		$fi = 0;
		foreach ($p['features'] as $f) {
			$features .= '<article class="' . $slug . '-cap" data-akash-visual-layout-builder-reveal data-akash-visual-layout-builder-delay="' . ( $fi * 90 ) . '">'
				. '<span class="' . $slug . '-cap__idx">' . esc_html($f['icon']) . '</span>'
				. '<h3>' . esc_html($f['title']) . '</h3>'
				. '<p>' . esc_html($f['text']) . '</p>'
				. '<div class="' . $slug . '-cap__bar" aria-hidden="true"><span data-akash-visual-layout-builder-bar="' . ( 78 + ( $fi * 5 ) ) . '"></span></div>'
				. '</article>';
			++$fi;
		}

		$steps = $is_steel
			? [
				[ 'Spec', 'Review drawings, grades, and weld procedures' ],
				[ 'Cut', 'Nest, plasma/laser cut, and edge prep' ],
				[ 'Fit', 'Fit-up, weld, and dimensional check' ],
				[ 'Finish', 'Blast, coat, stage, and ship' ],
			]
			: [
				[ 'Engineer', 'DFM review and process planning' ],
				[ 'Machine', 'CNC, turning, and multi-op cells' ],
				[ 'Inspect', 'In-process and CMM verification' ],
				[ 'Deliver', 'Package, serialize, and ship on time' ],
			];

		$process = '';
		foreach ($steps as $i => $step) {
			$process .= '<li class="' . $slug . '-step" data-akash-visual-layout-builder-reveal data-akash-visual-layout-builder-delay="' . ( $i * 100 ) . '">'
				. '<span class="' . $slug . '-step__num">' . str_pad((string) ( $i + 1 ), 2, '0', STR_PAD_LEFT) . '</span>'
				. '<div><strong>' . esc_html($step[0]) . '</strong><p>' . esc_html($step[1]) . '</p></div></li>';
		}

		$bullets = '';
		foreach ($p['about_bullets'] as $b) {
			$bullets .= '<li data-akash-visual-layout-builder-reveal><span></span>' . esc_html($b) . '</li>';
		}

		$testimonials = '';
		$ti = 0;
		foreach ($p['testimonials'] as $t) {
			$avatar = esc_url(Akash_Visual_Layout_Builder_AI_Images::relevant_url($topic, 96, 96, $ti + 12, $page_title, 'team', $t['name']));
			$testimonials .= '<blockquote class="' . $slug . '-quote" data-akash-visual-layout-builder-reveal data-akash-visual-layout-builder-delay="' . ( $ti * 90 ) . '">'
				. '<p>"' . esc_html($t['quote']) . '"</p>'
				. '<footer><img src="' . $avatar . '" alt="" width="48" height="48" loading="lazy" />'
				. '<div><strong>' . esc_html($t['name']) . '</strong><span>' . esc_html($t['role']) . '</span></div></footer></blockquote>';
			++$ti;
		}

		$faq = '';
		$qi = 0;
		foreach ($p['faq'] as $item) {
			$open = $qi === 0 ? ' open' : '';
			$faq .= '<details class="' . $slug . '-faq__item" data-akash-visual-layout-builder-reveal' . $open . '>'
				. '<summary>' . esc_html($item['q']) . '</summary>'
				. '<p>' . esc_html($item['a']) . '</p></details>';
			++$qi;
		}

		$certs = $is_steel
			? [ 'AWS D1.1', 'ISO 9001', 'Mill Certs', 'NDT Ready', 'AISC Partner' ]
			: [ 'ISO 9001', 'PPAP', 'CMM', 'ITAR Ready', 'AS9100 Path' ];

		$cert_html = '';
		foreach ($certs as $c) {
			$cert_html .= '<span>' . esc_html($c) . '</span>';
		}

		$stats = $is_steel
			? [
				[ '42', 'Years forging steel' ],
				[ '180', 'K tons fabricated / yr' ],
				[ '99', '% on-time shipments' ],
				[ '24', 'Hour quote response' ],
			]
			: [
				[ '28', 'Years in production' ],
				[ '120', 'CNC spindles online' ],
				[ '99', '% first-pass yield' ],
				[ '14', 'Day typical NPI' ],
			];

		$stats_html = '';
		foreach ($stats as $i => $s) {
			$stats_html .= '<div class="' . $slug . '-stat" data-akash-visual-layout-builder-reveal data-akash-visual-layout-builder-delay="' . ( $i * 80 ) . '">'
				. '<strong data-count="' . esc_attr($s[0]) . '">0</strong>'
				. '<span>' . esc_html($s[1]) . '</span></div>';
		}

		$headline = esc_html($p['hero_headline']);
		$badge    = esc_html($p['hero_badge']);
		$sub      = esc_html($p['hero_sub']);
		$cta1     = esc_html($p['cta_primary']);
		$cta2     = esc_html($p['cta_secondary']);
		$feat_t   = esc_html(str_replace('{brand}', $brand, $p['features_title']));
		$feat_l   = esc_html($p['features_lead']);
		$about_t  = esc_html($p['about_title']);
		$about_p1 = esc_html($p['about_p1']);
		$about_p2 = esc_html($p['about_p2']);
		$cta_t    = esc_html($p['cta_title']);
		$cta_x    = esc_html($p['cta_text']);
		$meta     = esc_html($p['meta']);
		$brand_e  = esc_html($brand);
		$a        = esc_attr($p['accent']);
		$a2       = esc_attr($p['accent2']);
		$theme    = $is_steel ? 'steel' : 'mfg';
		$gallery_h = $is_steel ? 'Inside the mill & fab bays' : 'Inside the production floor';
		$process_h = $is_steel ? 'From heat to shipment' : 'From print to pallet';

		$html = "<!--
  SEO: Title: {$brand_e}
  Meta Description: {$meta}
-->
<main class=\"{$slug}-page {$slug}-page--{$theme}\" data-akash-visual-layout-builder-industrial=\"{$theme}\">
  <section class=\"{$slug}-hero\" aria-labelledby=\"{$slug}-h1\">
    <div class=\"{$slug}-hero__bg\" style=\"background-image:url('{$hero_img}')\" role=\"img\" aria-label=\"{$brand_e}\"></div>
    <div class=\"{$slug}-hero__veil\"></div>
    <div class=\"{$slug}-hero__grid\" aria-hidden=\"true\"></div>
    <div class=\"{$slug}-hero__sparks\" aria-hidden=\"true\">
      <i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i>
    </div>
    <div class=\"{$slug}-hero__scan\" aria-hidden=\"true\"></div>
    <div class=\"{$slug}-hero__inner\">
      <span class=\"{$slug}-pill\" data-akash-visual-layout-builder-reveal>{$badge}</span>
      <h1 id=\"{$slug}-h1\" data-akash-visual-layout-builder-reveal data-akash-visual-layout-builder-delay=\"70\">{$headline}</h1>
      <p class=\"{$slug}-hero__lead\" data-akash-visual-layout-builder-reveal data-akash-visual-layout-builder-delay=\"130\">{$sub}</p>
      <div class=\"{$slug}-hero__actions\" data-akash-visual-layout-builder-reveal data-akash-visual-layout-builder-delay=\"190\">
        <a href=\"#contact\" class=\"{$slug}-btn {$slug}-btn--primary\">{$cta1}</a>
        <a href=\"#process\" class=\"{$slug}-btn {$slug}-btn--ghost\">{$cta2}</a>
      </div>
    </div>
    <div class=\"{$slug}-hero__meter\" aria-hidden=\"true\">
      <span></span><span></span><span></span>
    </div>
  </section>

  <section class=\"{$slug}-certs\" aria-label=\"Certifications\">
    <div class=\"{$slug}-certs__track\">
      <div class=\"{$slug}-certs__row\">{$cert_html}{$cert_html}</div>
    </div>
  </section>

  <section class=\"{$slug}-stats\">
    <div class=\"{$slug}-wrap {$slug}-stats__grid\">{$stats_html}</div>
  </section>

  <section id=\"capabilities\" class=\"{$slug}-caps\">
    <div class=\"{$slug}-wrap\">
      <header class=\"{$slug}-head\" data-akash-visual-layout-builder-reveal>
        <p class=\"{$slug}-eyebrow\">Capabilities</p>
        <h2>{$feat_t}</h2>
        <p>{$feat_l}</p>
      </header>
      <div class=\"{$slug}-caps__grid\">{$features}</div>
    </div>
  </section>

  <section id=\"process\" class=\"{$slug}-process\">
    <div class=\"{$slug}-wrap\">
      <header class=\"{$slug}-head {$slug}-head--light\" data-akash-visual-layout-builder-reveal>
        <p class=\"{$slug}-eyebrow\">Process</p>
        <h2>{$process_h}</h2>
      </header>
      <ol class=\"{$slug}-process__list\">{$process}</ol>
    </div>
  </section>

  <section class=\"{$slug}-about\">
    <div class=\"{$slug}-wrap {$slug}-about__grid\">
      <div class=\"{$slug}-about__media\" data-akash-visual-layout-builder-reveal=\"left\">
        <img src=\"{$plant_img}\" alt=\"{$brand_e} facility\" width=\"1200\" height=\"800\" loading=\"lazy\" />
        <div class=\"{$slug}-about__pulse\" aria-hidden=\"true\"></div>
      </div>
      <div class=\"{$slug}-about__copy\" data-akash-visual-layout-builder-reveal=\"right\">
        <p class=\"{$slug}-eyebrow\">About</p>
        <h2>{$about_t}</h2>
        <p>{$about_p1}</p>
        <p>{$about_p2}</p>
        <ul class=\"{$slug}-checks\">{$bullets}</ul>
      </div>
    </div>
  </section>

  <section id=\"gallery\" class=\"{$slug}-gallery\">
    <div class=\"{$slug}-wrap\">
      <header class=\"{$slug}-head {$slug}-head--light\" data-akash-visual-layout-builder-reveal>
        <p class=\"{$slug}-eyebrow\">Facility</p>
        <h2>{$gallery_h}</h2>
      </header>
      <div class=\"{$slug}-gallery__grid\">{$gal}</div>
    </div>
  </section>

  <section class=\"{$slug}-social\">
    <div class=\"{$slug}-wrap\">
      <header class=\"{$slug}-head\" data-akash-visual-layout-builder-reveal>
        <p class=\"{$slug}-eyebrow\">Partners</p>
        <h2>Trusted on critical programs</h2>
      </header>
      <div class=\"{$slug}-social__grid\">{$testimonials}</div>
    </div>
  </section>

  <section class=\"{$slug}-faq\">
    <div class=\"{$slug}-wrap {$slug}-faq__inner\">
      <header class=\"{$slug}-head\" data-akash-visual-layout-builder-reveal>
        <p class=\"{$slug}-eyebrow\">FAQ</p>
        <h2>Straight answers</h2>
      </header>
      {$faq}
    </div>
  </section>

  <section id=\"contact\" class=\"{$slug}-cta\">
    <div class=\"{$slug}-cta__bg\" style=\"background-image:url('{$cta_img}')\" aria-hidden=\"true\"></div>
    <div class=\"{$slug}-cta__veil\"></div>
    <div class=\"{$slug}-wrap {$slug}-cta__inner\" data-akash-visual-layout-builder-reveal>
      <h2>{$cta_t}</h2>
      <p>{$cta_x}</p>
      <a href=\"#\" class=\"{$slug}-btn {$slug}-btn--primary {$slug}-btn--lg\">{$cta1}</a>
    </div>
  </section>
</main>";

		$css = self::styles($slug, $a, $a2, $theme);
		$js  = self::scripts($brand, $p['meta'], $slug);

		return compact('html', 'css', 'js');
	}

	/**
	 * @param string $slug  CSS slug.
	 * @param string $a     Accent.
	 * @param string $a2    Accent 2.
	 * @param string $theme mfg|steel.
	 * @return string
	 */
	private static function styles($slug, $a, $a2, $theme) {
		$ink   = $theme === 'steel' ? '#140f0c' : '#0b1220';
		$panel = $theme === 'steel' ? '#1a1410' : '#111827';
		return ".{$slug}-page { --akash-visual-layout-builder-a:{$a}; --akash-visual-layout-builder-a2:{$a2}; --akash-visual-layout-builder-ink:{$ink}; --akash-visual-layout-builder-panel:{$panel}; font-family:'IBM Plex Sans',system-ui,sans-serif; color:#e5e7eb; background:var(--akash-visual-layout-builder-ink); line-height:1.6; width:100%; overflow-x:hidden; }
.{$slug}-wrap { max-width:1180px; margin:0 auto; padding:0 24px; }
.{$slug}-eyebrow { margin:0 0 10px; color:var(--akash-visual-layout-builder-a2); font-family:'IBM Plex Mono',monospace; font-size:.72rem; font-weight:600; letter-spacing:.16em; text-transform:uppercase; }
.{$slug}-head { text-align:center; margin-bottom:48px; max-width:680px; margin-left:auto; margin-right:auto; }
.{$slug}-head h2 { margin:0 0 12px; font-size:clamp(1.8rem,3.2vw,2.7rem); font-weight:700; letter-spacing:-.03em; color:#f8fafc; }
.{$slug}-head p { margin:0; color:#94a3b8; }
.{$slug}-head--light h2 { color:#fff; }
.{$slug}-pill { display:inline-flex; align-items:center; gap:8px; padding:8px 16px; border-radius:4px; border:1px solid color-mix(in srgb, var(--akash-visual-layout-builder-a2) 45%, transparent); background:rgba(255,255,255,.06); backdrop-filter:blur(10px); font-family:'IBM Plex Mono',monospace; font-size:12px; font-weight:600; letter-spacing:.04em; }
.{$slug}-btn { display:inline-flex; align-items:center; justify-content:center; padding:14px 28px; border-radius:4px; font-weight:700; font-size:14px; text-decoration:none; border:none; cursor:pointer; font-family:inherit; transition:transform .25s, box-shadow .25s, background .25s; }
.{$slug}-btn--primary { background:linear-gradient(135deg,var(--akash-visual-layout-builder-a),var(--akash-visual-layout-builder-a2)); color:#0b1020; box-shadow:0 0 0 1px rgba(255,255,255,.1), 0 12px 40px color-mix(in srgb, var(--akash-visual-layout-builder-a) 35%, transparent); }
.{$slug}-btn--primary:hover { transform:translateY(-2px); box-shadow:0 18px 50px color-mix(in srgb, var(--akash-visual-layout-builder-a) 45%, transparent); }
.{$slug}-btn--ghost { background:transparent; color:#fff; border:1px solid rgba(255,255,255,.35); }
.{$slug}-btn--ghost:hover { border-color:#fff; background:rgba(255,255,255,.08); }
.{$slug}-btn--lg { padding:16px 36px; font-size:15px; }
.{$slug}-hero { position:relative; min-height:min(96vh,900px); display:flex; align-items:center; overflow:hidden; color:#fff; }
.{$slug}-hero__bg { position:absolute; inset:0; background-size:cover; background-position:center; transform:scale(1.12); animation:{$slug}-ken 22s ease-in-out infinite alternate; }
.{$slug}-hero__veil { position:absolute; inset:0; background:
  linear-gradient(120deg, color-mix(in srgb, var(--akash-visual-layout-builder-ink) 92%, transparent) 0%, color-mix(in srgb, var(--akash-visual-layout-builder-ink) 55%, transparent) 48%, color-mix(in srgb, var(--akash-visual-layout-builder-a) 35%, transparent) 100%),
  radial-gradient(circle at 80% 20%, color-mix(in srgb, var(--akash-visual-layout-builder-a2) 40%, transparent), transparent 45%); }
.{$slug}-hero__grid { position:absolute; inset:0; background-image:
  linear-gradient(rgba(148,163,184,.08) 1px, transparent 1px),
  linear-gradient(90deg, rgba(148,163,184,.08) 1px, transparent 1px);
  background-size:48px 48px; mask-image:radial-gradient(ellipse at center, #000 20%, transparent 75%); animation:{$slug}-grid 18s linear infinite; opacity:.7; }
.{$slug}-hero__sparks { position:absolute; inset:0; pointer-events:none; overflow:hidden; }
.{$slug}-hero__sparks i { position:absolute; width:3px; height:3px; border-radius:50%; background:var(--akash-visual-layout-builder-a2); box-shadow:0 0 12px var(--akash-visual-layout-builder-a2); opacity:0; animation:{$slug}-spark 3.6s linear infinite; }
.{$slug}-hero__sparks i:nth-child(1){ left:12%; top:70%; animation-delay:0s; }
.{$slug}-hero__sparks i:nth-child(2){ left:28%; top:55%; animation-delay:.4s; }
.{$slug}-hero__sparks i:nth-child(3){ left:46%; top:78%; animation-delay:.9s; }
.{$slug}-hero__sparks i:nth-child(4){ left:62%; top:48%; animation-delay:1.2s; }
.{$slug}-hero__sparks i:nth-child(5){ left:74%; top:66%; animation-delay:1.7s; }
.{$slug}-hero__sparks i:nth-child(6){ left:84%; top:40%; animation-delay:2.1s; }
.{$slug}-hero__sparks i:nth-child(7){ left:18%; top:42%; animation-delay:2.5s; }
.{$slug}-hero__sparks i:nth-child(8){ left:55%; top:30%; animation-delay:2.9s; }
.{$slug}-hero__scan { position:absolute; left:0; right:0; height:2px; background:linear-gradient(90deg, transparent, var(--akash-visual-layout-builder-a2), transparent); top:0; opacity:.55; animation:{$slug}-scan 5.5s ease-in-out infinite; }
.{$slug}-hero__inner { position:relative; z-index:2; max-width:820px; padding:110px 24px 130px; margin:0 auto; text-align:left; width:100%; }
.{$slug}-hero h1 { margin:18px 0 18px; font-size:clamp(2.4rem,6vw,4.4rem); line-height:1.02; letter-spacing:-.04em; font-weight:700; max-width:16ch; }
.{$slug}-hero__lead { margin:0 0 32px; max-width:560px; color:#cbd5e1; font-size:clamp(1.02rem,1.8vw,1.2rem); }
.{$slug}-hero__actions { display:flex; flex-wrap:wrap; gap:12px; }
.{$slug}-hero__meter { position:absolute; right:28px; bottom:36px; display:flex; gap:8px; z-index:2; }
.{$slug}-hero__meter span { width:8px; height:42px; border-radius:2px; background:rgba(255,255,255,.15); overflow:hidden; position:relative; }
.{$slug}-hero__meter span::after { content:\"\"; position:absolute; inset:auto 0 0 0; background:linear-gradient(var(--akash-visual-layout-builder-a2), var(--akash-visual-layout-builder-a)); animation:{$slug}-meter 2.4s ease-in-out infinite; }
.{$slug}-hero__meter span:nth-child(2)::after { animation-delay:.35s; }
.{$slug}-hero__meter span:nth-child(3)::after { animation-delay:.7s; }
.{$slug}-certs { border-block:1px solid rgba(148,163,184,.15); background:rgba(255,255,255,.02); overflow:hidden; }
.{$slug}-certs__track { overflow:hidden; }
.{$slug}-certs__row { display:flex; gap:48px; width:max-content; padding:18px 0; animation:{$slug}-marquee 28s linear infinite; font-family:'IBM Plex Mono',monospace; font-size:12px; letter-spacing:.14em; text-transform:uppercase; color:#94a3b8; }
.{$slug}-certs__row span { white-space:nowrap; }
.{$slug}-certs__row span::before { content:\"◆\"; color:var(--akash-visual-layout-builder-a2); margin-right:48px; }
.{$slug}-stats { padding:56px 0 20px; }
.{$slug}-stats__grid { display:grid; grid-template-columns:repeat(4,1fr); gap:16px; }
.{$slug}-stat { padding:24px 18px; border:1px solid rgba(148,163,184,.16); border-radius:8px; background:linear-gradient(180deg, rgba(255,255,255,.04), transparent); text-align:center; }
.{$slug}-stat strong { display:block; font-size:clamp(1.8rem,3vw,2.5rem); font-weight:700; color:var(--akash-visual-layout-builder-a2); letter-spacing:-.03em; }
.{$slug}-stat span { color:#94a3b8; font-size:.88rem; }
.{$slug}-caps { padding:90px 0; }
.{$slug}-caps__grid { display:grid; grid-template-columns:repeat(2,1fr); gap:18px; }
.{$slug}-cap { position:relative; padding:28px 26px 24px; border:1px solid rgba(148,163,184,.16); border-radius:10px; background:color-mix(in srgb, var(--akash-visual-layout-builder-panel) 88%, transparent); overflow:hidden; transition:transform .35s, border-color .35s, box-shadow .35s; }
.{$slug}-cap:hover { transform:translateY(-6px); border-color:color-mix(in srgb, var(--akash-visual-layout-builder-a) 55%, transparent); box-shadow:0 24px 60px rgba(0,0,0,.35); }
.{$slug}-cap__idx { display:inline-block; margin-bottom:14px; font-family:'IBM Plex Mono',monospace; font-size:12px; font-weight:600; color:var(--akash-visual-layout-builder-a2); letter-spacing:.12em; }
.{$slug}-cap h3 { margin:0 0 10px; font-size:1.2rem; color:#f8fafc; }
.{$slug}-cap p { margin:0 0 18px; color:#94a3b8; font-size:.95rem; }
.{$slug}-cap__bar { height:3px; background:rgba(255,255,255,.08); border-radius:99px; overflow:hidden; }
.{$slug}-cap__bar span { display:block; height:100%; width:0; background:linear-gradient(90deg, var(--akash-visual-layout-builder-a), var(--akash-visual-layout-builder-a2)); transition:width 1.1s cubic-bezier(.22,1,.36,1); }
.{$slug}-cap.is-visible .{$slug}-cap__bar span, .{$slug}-cap .{$slug}-cap__bar span.is-on { width:var(--akash-visual-layout-builder-bar-w, 85%); }
.{$slug}-process { padding:100px 0; background:linear-gradient(180deg, color-mix(in srgb, var(--akash-visual-layout-builder-a) 12%, var(--akash-visual-layout-builder-ink)), var(--akash-visual-layout-builder-ink)); border-block:1px solid rgba(148,163,184,.12); }
.{$slug}-process__list { list-style:none; margin:0; padding:0; display:grid; grid-template-columns:repeat(4,1fr); gap:18px; counter-reset:none; }
.{$slug}-step { display:flex; gap:14px; padding:22px 18px; border-radius:10px; border:1px solid rgba(148,163,184,.16); background:rgba(0,0,0,.22); position:relative; }
.{$slug}-step:not(:last-child)::after { content:\"\"; position:absolute; right:-12px; top:50%; width:12px; height:2px; background:linear-gradient(90deg, var(--akash-visual-layout-builder-a2), transparent); }
.{$slug}-step__num { font-family:'IBM Plex Mono',monospace; color:var(--akash-visual-layout-builder-a2); font-weight:600; font-size:1.1rem; }
.{$slug}-step strong { display:block; color:#fff; margin-bottom:6px; }
.{$slug}-step p { margin:0; color:#94a3b8; font-size:.9rem; }
.{$slug}-about { padding:110px 0; }
.{$slug}-about__grid { display:grid; grid-template-columns:1.05fr .95fr; gap:48px; align-items:center; }
.{$slug}-about__media { position:relative; }
.{$slug}-about__media img { width:100%; border-radius:12px; aspect-ratio:5/4; object-fit:cover; box-shadow:0 30px 80px rgba(0,0,0,.45); }
.{$slug}-about__pulse { position:absolute; inset:auto 18px 18px auto; width:72px; height:72px; border-radius:50%; border:2px solid var(--akash-visual-layout-builder-a2); animation:{$slug}-pulse 2.4s ease-out infinite; }
.{$slug}-about__copy h2 { margin:0 0 16px; font-size:clamp(1.7rem,3vw,2.4rem); color:#f8fafc; letter-spacing:-.02em; }
.{$slug}-about__copy p { color:#94a3b8; margin:0 0 14px; }
.{$slug}-checks { list-style:none; margin:22px 0 0; padding:0; }
.{$slug}-checks li { display:flex; gap:12px; align-items:center; padding:12px 0; border-bottom:1px solid rgba(148,163,184,.12); color:#e2e8f0; font-weight:600; }
.{$slug}-checks span { width:10px; height:10px; border-radius:2px; background:var(--akash-visual-layout-builder-a2); box-shadow:0 0 12px var(--akash-visual-layout-builder-a2); flex-shrink:0; }
.{$slug}-gallery { padding:100px 0; background:#05070d; }
.{$slug}-gallery__grid { display:grid; grid-template-columns:repeat(3,1fr); gap:14px; }
.{$slug}-shot { margin:0; position:relative; overflow:hidden; border-radius:10px; aspect-ratio:4/3; border:1px solid rgba(148,163,184,.12); }
.{$slug}-shot img { width:100%; height:100%; object-fit:cover; display:block; transition:transform .7s ease; }
.{$slug}-shot:hover img { transform:scale(1.08); }
.{$slug}-shot figcaption { position:absolute; left:12px; bottom:12px; padding:6px 10px; background:rgba(0,0,0,.65); backdrop-filter:blur(8px); font-family:'IBM Plex Mono',monospace; font-size:11px; letter-spacing:.08em; text-transform:uppercase; }
.{$slug}-social { padding:100px 0; }
.{$slug}-social__grid { display:grid; grid-template-columns:repeat(3,1fr); gap:18px; }
.{$slug}-quote { margin:0; padding:26px; border-radius:10px; border:1px solid rgba(148,163,184,.14); background:rgba(255,255,255,.03); }
.{$slug}-quote p { margin:0 0 18px; color:#cbd5e1; font-size:1.02rem; font-style:italic; }
.{$slug}-quote footer { display:flex; gap:12px; align-items:center; }
.{$slug}-quote footer img { width:48px; height:48px; border-radius:50%; object-fit:cover; }
.{$slug}-quote footer strong { display:block; color:#f8fafc; font-size:.92rem; }
.{$slug}-quote footer span { color:var(--akash-visual-layout-builder-a2); font-size:.8rem; }
.{$slug}-faq { padding:90px 0 110px; }
.{$slug}-faq__inner { max-width:760px; }
.{$slug}-faq__item { border:1px solid rgba(148,163,184,.16); border-radius:8px; padding:4px 20px; margin-bottom:12px; background:rgba(255,255,255,.02); }
.{$slug}-faq__item[open] { border-color:color-mix(in srgb, var(--akash-visual-layout-builder-a) 55%, transparent); box-shadow:0 12px 36px rgba(0,0,0,.25); }
.{$slug}-faq__item summary { padding:16px 0; cursor:pointer; font-weight:700; color:#f8fafc; list-style:none; }
.{$slug}-faq__item summary::-webkit-details-marker { display:none; }
.{$slug}-faq__item p { margin:0 0 16px; color:#94a3b8; }
.{$slug}-cta { position:relative; padding:120px 0; overflow:hidden; text-align:center; }
.{$slug}-cta__bg { position:absolute; inset:0; background-size:cover; background-position:center; transform:scale(1.06); }
.{$slug}-cta__veil { position:absolute; inset:0; background:linear-gradient(135deg, color-mix(in srgb, var(--akash-visual-layout-builder-a) 88%, #000), color-mix(in srgb, var(--akash-visual-layout-builder-ink) 80%, transparent)); }
.{$slug}-cta__inner { position:relative; z-index:1; max-width:640px; }
.{$slug}-cta h2 { margin:0 0 14px; font-size:clamp(1.9rem,3vw,2.7rem); color:#fff; }
.{$slug}-cta p { margin:0 0 28px; color:#e2e8f0; font-size:1.08rem; }
[data-akash-visual-layout-builder-reveal]{opacity:0;transform:translateY(28px);transition:opacity .85s cubic-bezier(.22,1,.36,1),transform .85s cubic-bezier(.22,1,.36,1);}
[data-akash-visual-layout-builder-reveal=\"left\"]{transform:translateX(-40px);}
[data-akash-visual-layout-builder-reveal=\"right\"]{transform:translateX(40px);}
[data-akash-visual-layout-builder-reveal].is-visible{opacity:1;transform:none;}
@keyframes {$slug}-ken { from { transform:scale(1.12) translate3d(0,0,0); } to { transform:scale(1) translate3d(0,-1.5%,0); } }
@keyframes {$slug}-grid { from { background-position:0 0; } to { background-position:48px 48px; } }
@keyframes {$slug}-spark { 0% { opacity:0; transform:translate(0,0) scale(.6); } 15% { opacity:1; } 100% { opacity:0; transform:translate(42px,-88px) scale(0); } }
@keyframes {$slug}-scan { 0%,100% { top:8%; opacity:.15; } 50% { top:78%; opacity:.65; } }
@keyframes {$slug}-meter { 0%,100% { height:28%; } 50% { height:92%; } }
@keyframes {$slug}-marquee { from { transform:translateX(0); } to { transform:translateX(-50%); } }
@keyframes {$slug}-pulse { 0% { transform:scale(.7); opacity:.9; } 100% { transform:scale(1.6); opacity:0; } }
@media(max-width:960px){
  .{$slug}-stats__grid,.{$slug}-process__list,.{$slug}-caps__grid,.{$slug}-gallery__grid,.{$slug}-social__grid,.{$slug}-about__grid { grid-template-columns:1fr 1fr; }
  .{$slug}-step:not(:last-child)::after { display:none; }
  .{$slug}-hero__inner { text-align:center; }
  .{$slug}-hero h1 { margin-left:auto; margin-right:auto; }
  .{$slug}-hero__lead { margin-left:auto; margin-right:auto; }
  .{$slug}-hero__actions { justify-content:center; }
}
@media(max-width:640px){
  .{$slug}-stats__grid,.{$slug}-process__list,.{$slug}-caps__grid,.{$slug}-gallery__grid,.{$slug}-social__grid,.{$slug}-about__grid { grid-template-columns:1fr; }
  .{$slug}-hero { min-height:82vh; }
  .{$slug}-hero__meter { display:none; }
}
@media(prefers-reduced-motion:reduce){
  .{$slug}-hero__bg,.{$slug}-hero__grid,.{$slug}-hero__sparks i,.{$slug}-hero__scan,.{$slug}-hero__meter span::after,.{$slug}-certs__row,.{$slug}-about__pulse { animation:none !important; }
  [data-akash-visual-layout-builder-reveal]{opacity:1;transform:none;transition:none;}
}";
	}

	/**
	 * @param string $brand Brand.
	 * @param string $desc  Meta description.
	 * @param string $slug  CSS slug.
	 * @return string
	 */
	private static function scripts($brand, $desc, $slug) {
		$schema = [
			'@context'    => 'https://schema.org',
			'@type'       => 'Organization',
			'name'        => wp_strip_all_tags($brand),
			'description' => wp_strip_all_tags($desc),
		];
		$js  = Akash_Visual_Layout_Builder_AI_Content::motion_js();
		$js .= "(function(){
  var root=document.querySelector('.{$slug}-page');
  if(!root) return;
  root.querySelectorAll('[data-akash-visual-layout-builder-bar]').forEach(function(el){
    var w=parseInt(el.getAttribute('data-akash-visual-layout-builder-bar'),10)||80;
    el.style.setProperty('--akash-visual-layout-builder-bar-w', w+'%');
    var card=el.closest('[data-akash-visual-layout-builder-reveal]');
    var apply=function(){ el.classList.add('is-on'); el.style.width=w+'%'; };
    if(!card){ apply(); return; }
    var obs=new MutationObserver(function(){
      if(card.classList.contains('is-visible')){ apply(); obs.disconnect(); }
    });
    obs.observe(card,{attributes:true,attributeFilter:['class']});
    if(card.classList.contains('is-visible')) apply();
  });
})();";
		$js .= 'var akashVisualLayoutBuilderSchema=' . wp_json_encode($schema) . ';(function(){var s=document.createElement("script");s.type="application/ld+json";s.textContent=JSON.stringify(akashVisualLayoutBuilderSchema);document.head.appendChild(s);})();';
		return $js;
	}
}



