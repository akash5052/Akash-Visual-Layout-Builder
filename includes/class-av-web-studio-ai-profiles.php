<?php

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Industry-aware copy, features, and design tokens for relevant AI output.
 */
class Av_Web_Studio_AI_Profiles {

	/**
	 * Detect industry from prompt / page title.
	 *
	 * @param string $topic      Topic text.
	 * @param string $page_title Page title.
	 * @return string Industry key.
	 */
	public static function detect($topic, $page_title = '') {
		$text = strtolower(trim($topic . ' ' . $page_title));
		$map  = [
			'coffee'     => [ 'coffee', 'cafe', 'café', 'espresso', 'roastery', 'barista', 'latte', 'cappuccino', 'brew' ],
			'restaurant' => [ 'restaurant', 'bistro', 'dining', 'chef', 'cuisine', 'eatery', 'pizzeria', 'sushi', 'menu', 'food', 'kitchen', 'dinner', 'lunch' ],
			'bakery'     => [ 'bakery', 'pastry', 'cake', 'bread', 'patisserie', 'croissant', 'doughnut', 'donut' ],
			'fitness'    => [ 'gym', 'fitness', 'workout', 'crossfit', 'training', 'personal trainer', 'strength', 'cardio', 'weightlifting' ],
			'yoga'       => [ 'yoga', 'pilates', 'meditation', 'wellness studio', 'mindfulness', 'asana' ],
			'health'     => [ 'clinic', 'medical', 'doctor', 'dental', 'dentist', 'hospital', 'therapy', 'healthcare', 'physician', 'nurse' ],
			'beauty'     => [ 'salon', 'spa', 'beauty', 'hair', 'nails', 'skincare', 'barber', 'makeup', 'manicure', 'facial' ],
			'realestate' => [ 'real estate', 'realtor', 'property', 'homes for sale', 'apartment', 'condo', 'housing', 'listing', 'mortgage' ],
			'saas'       => [ 'saas', 'software', 'app', 'platform', 'startup', 'dashboard', 'api', 'cloud', 'subscription' ],
			'agency'     => [ 'agency', 'marketing', 'branding', 'creative', 'advertising', 'digital agency', 'seo agency', 'design studio' ],
			'education'  => [ 'school', 'academy', 'course', 'tutoring', 'university', 'learning', 'education', 'student', 'classroom', 'teacher' ],
			'ecommerce'  => [ 'shop', 'store', 'ecommerce', 'e-commerce', 'boutique', 'retail', 'fashion', 'online store', 'merchandise' ],
			'travel'     => [ 'travel', 'tour', 'hotel', 'resort', 'vacation', 'adventure', 'destination', 'trip', 'booking' ],
			'law'        => [ 'law', 'legal', 'attorney', 'lawyer', 'law firm', 'counsel', 'litigation', 'justice' ],
			'photography'=> [ 'photography', 'photographer', 'photo studio', 'wedding photo', 'portrait session', 'lens' ],
			'cleaning'   => [ 'cleaning', 'maid', 'janitorial', 'housekeeping', 'cleaner', 'deep clean' ],
			'plumbing'   => [ 'plumbing', 'plumber', 'hvac', 'electrician', 'contractor', 'handyman', 'repair' ],
			'technology' => [ 'technology', 'tech', 'developer', 'coding', 'it services', 'cyber', 'data' ],
			'steel'      => [ 'steel', 'steel mill', 'steel manufacturing', 'metal fabrication', 'structural steel', 'plate steel', 'foundry', 'rolling mill', 'ironworks', 'metalworks' ],
			'manufacturing' => [ 'manufacturing', 'manufacturer', 'factory', 'cnc', 'machining', 'industrial plant', 'production line', 'assembly plant', 'oem', 'fabrication shop' ],
		];

		foreach ($map as $key => $words) {
			foreach ($words as $w) {
				if (strpos($text, $w) !== false) {
					return $key;
				}
			}
		}

		return 'business';
	}

	/**
	 * Full profile for page generation.
	 *
	 * @param string $topic      Topic.
	 * @param string $page_title Page title.
	 * @return array
	 */
	public static function get($topic, $page_title = '') {
		$key = self::detect($topic, $page_title);
		$aliases = [
			'bakery'      => 'coffee',
			'ecommerce'   => 'business',
			'travel'      => 'business',
			'law'         => 'business',
			'photography' => 'agency',
			'cleaning'    => 'business',
			'plumbing'    => 'business',
			'education'   => 'business',
			'health'      => 'yoga',
		];
		$key   = $aliases[ $key ] ?? $key;
		$brand = self::brand($page_title, $topic, $key);
		$base  = self::profiles()[ $key ] ?? self::profiles()['business'];
		$base['industry'] = $key;
		$base['brand']    = $brand;
		$base['topic']    = $topic;

		// Personalize headlines with brand name.
		if ($brand && $brand !== 'Your Brand') {
			$base['hero_headline'] = str_replace('{brand}', $brand, $base['hero_headline_tpl']);
		} else {
			$base['hero_headline'] = $base['hero_headline_default'];
		}

		return $base;
	}

	/**
	 * @param string $page_title Page title.
	 * @param string $topic      Topic.
	 * @param string $industry   Industry key.
	 * @return string
	 */
	public static function brand($page_title, $topic, $industry = '') {
		if ($page_title && ! Av_Web_Studio_AI_Intent::is_bad_title($page_title)) {
			return esc_html(trim($page_title));
		}
		$t = trim($topic);
		if ($t && ! Av_Web_Studio_AI_Intent::is_bad_title($t) && strlen($t) < 50) {
			return esc_html(ucwords($t));
		}
		$labels = [
			'coffee' => 'The Daily Grind', 'restaurant' => 'Savory Kitchen', 'bakery' => 'Golden Crust',
			'fitness' => 'Peak Fitness', 'yoga' => 'Zen Studio', 'health' => 'Care Clinic',
			'beauty' => 'Glow Salon', 'realestate' => 'Prime Properties', 'saas' => 'Flowbase',
			'agency' => 'Bright Agency', 'education' => 'LearnHub', 'ecommerce' => 'Urban Shop',
			'travel' => 'Wanderlust Travel', 'law' => 'Sterling Law', 'photography' => 'Lens & Light',
			'cleaning' => 'Sparkle Clean', 'plumbing' => 'ProFix Plumbing', 'business' => 'Your Brand',
			'manufacturing' => 'Apex Precision', 'steel' => 'Forge & Beam',
		];
		$key = $industry ?: self::detect($topic, $page_title);
		return $labels[ $key ] ?? $labels['business'];
	}

	/**
	 * @return array<string,array>
	 */
	private static function profiles() {
		return [
			'coffee' => self::tpl([
				'accent' => '#6F4E37', 'accent2' => '#C4A77D',
				'hero_headline_tpl' => 'Welcome to {brand}',
				'hero_headline_default' => 'Artisan Coffee, Crafted Daily',
				'hero_badge' => '☕ Fresh roasted · Locally sourced beans',
				'hero_sub' => 'Specialty coffee, fresh pastries, and a warm atmosphere — the perfect spot to start your morning or catch up with friends.',
				'cta_primary' => 'View Menu', 'cta_secondary' => 'Find Us',
				'features_title' => 'The {brand} Experience',
				'features_lead' => 'Every cup tells a story — from bean selection to your first sip.',
				'features' => [
					[ 'icon' => '☕', 'title' => 'Single-Origin Beans', 'text' => 'Ethically sourced beans roasted in small batches for peak flavor.' ],
					[ 'icon' => '🥐', 'title' => 'Fresh Pastries Daily', 'text' => 'Buttery croissants, muffins, and seasonal treats baked every morning.' ],
					[ 'icon' => '🌿', 'title' => 'Plant-Based Options', 'text' => 'Oat, almond, and soy milk — plus vegan snacks on the menu.' ],
					[ 'icon' => '📶', 'title' => 'Cozy Workspace', 'text' => 'Fast WiFi, plenty of outlets, and a calm vibe for remote work.' ],
				],
				'about_title' => 'Our Story',
				'about_p1' => 'We opened our doors with one mission: serve exceptional coffee in a space where everyone feels at home. From pour-overs to cold brew, every drink is made with care.',
				'about_p2' => 'Our baristas are trained in latte art and flavor profiling, so whether you love a bold espresso or a smooth flat white, we have your perfect cup.',
				'about_bullets' => [ 'Locally roasted beans', 'Seasonal specialty drinks', 'Community events every month' ],
				'testimonials' => [
					[ 'quote' => 'Best latte in town. I come here every morning before work!', 'name' => 'Emma R.', 'role' => 'Regular customer' ],
					[ 'quote' => 'The atmosphere is perfect for studying. Great coffee and friendly staff.', 'name' => 'David K.', 'role' => 'Student' ],
					[ 'quote' => 'Their seasonal pumpkin spice is incredible. A true neighborhood gem.', 'name' => 'Lisa M.', 'role' => 'Food blogger' ],
				],
				'faq' => [
					[ 'q' => 'Do you offer dairy-free milk?', 'a' => 'Yes — oat, almond, soy, and coconut milk are available at no extra charge.' ],
					[ 'q' => 'Can I order ahead?', 'a' => 'Absolutely! Call ahead or visit us — we will have your order ready when you arrive.' ],
					[ 'q' => 'Do you host private events?', 'a' => 'We offer the space for small gatherings, book clubs, and coffee tastings.' ],
					[ 'q' => 'What are your hours?', 'a' => 'Open daily 7am–7pm. Extended hours on weekends.' ],
				],
				'cta_title' => 'Ready for Your Next Great Cup?',
				'cta_text' => 'Stop by today or explore our seasonal menu online.',
				'meta' => 'Specialty coffee shop serving artisan espresso, fresh pastries, and a welcoming atmosphere. Visit us for the best coffee experience.',
			]),
			'restaurant' => self::tpl([
				'accent' => '#B45309', 'accent2' => '#F59E0B',
				'hero_headline_tpl' => '{brand} — Fine Dining Redefined',
				'hero_headline_default' => 'Where Every Meal Is an Experience',
				'hero_badge' => '🍽️ Farm-to-table · Seasonal menu',
				'hero_sub' => 'Chef-crafted dishes using locally sourced ingredients, served in an elegant yet relaxed setting.',
				'cta_primary' => 'Reserve a Table', 'cta_secondary' => 'See Menu',
				'features_title' => 'Why Dine With Us',
				'features_lead' => 'Quality ingredients, expert chefs, and unforgettable flavors.',
				'features' => [
					[ 'icon' => '🥗', 'title' => 'Farm-to-Table', 'text' => 'Fresh ingredients sourced from local farms and producers.' ],
					[ 'icon' => '👨‍🍳', 'title' => 'Award-Winning Chef', 'text' => 'Creative menus that change with the seasons.' ],
					[ 'icon' => '🍷', 'title' => 'Curated Wine List', 'text' => 'Handpicked wines paired perfectly with every dish.' ],
					[ 'icon' => '🎉', 'title' => 'Private Events', 'text' => 'Host celebrations, dinners, and corporate events in style.' ],
				],
				'about_title' => 'Our Kitchen Philosophy',
				'about_p1' => 'We believe great food starts with great ingredients. Our chefs work closely with local farmers to bring you dishes that are fresh, flavorful, and thoughtfully prepared.',
				'about_p2' => 'Whether it is a romantic dinner or a family celebration, our team delivers warm hospitality and plates you will remember.',
				'about_bullets' => [ 'Seasonal tasting menus', 'Vegetarian & gluten-free options', 'Outdoor patio seating' ],
				'testimonials' => [
					[ 'quote' => 'An unforgettable dining experience. The tasting menu was perfection.', 'name' => 'Michael T.', 'role' => 'Food critic' ],
					[ 'quote' => 'We celebrated our anniversary here — impeccable service and food.', 'name' => 'Anna & James', 'role' => 'Guests' ],
					[ 'quote' => 'The best restaurant in the city. Book early — it fills up fast!', 'name' => 'Rachel S.', 'role' => 'Local guide' ],
				],
				'faq' => [
					[ 'q' => 'Do I need a reservation?', 'a' => 'Reservations are recommended, especially on weekends. Walk-ins welcome based on availability.' ],
					[ 'q' => 'Do you accommodate dietary restrictions?', 'a' => 'Yes — please inform us when booking and our chef will prepare suitable options.' ],
					[ 'q' => 'Is there parking available?', 'a' => 'Free valet parking is available Thursday through Sunday evenings.' ],
					[ 'q' => 'Do you offer catering?', 'a' => 'We provide full catering for events of 20+ guests. Contact us for a custom quote.' ],
				],
				'cta_title' => 'Reserve Your Table Tonight',
				'cta_text' => 'Join us for an evening of exceptional food and warm hospitality.',
				'meta' => 'Fine dining restaurant with farm-to-table cuisine, seasonal menus, and elegant atmosphere. Reserve your table today.',
			]),
			'fitness' => self::tpl([
				'accent' => '#DC2626', 'accent2' => '#F97316',
				'hero_headline_tpl' => 'Transform Your Body at {brand}',
				'hero_headline_default' => 'Train Hard. Live Strong.',
				'hero_badge' => '💪 Free 7-day trial · No commitment',
				'hero_sub' => 'State-of-the-art equipment, expert trainers, and classes for every fitness level — start your transformation today.',
				'cta_primary' => 'Start Free Trial', 'cta_secondary' => 'View Classes',
				'features_title' => 'Everything You Need to Succeed',
				'features_lead' => 'More than a gym — a community that pushes you to be your best.',
				'features' => [
					[ 'icon' => '🏋️', 'title' => 'Modern Equipment', 'text' => 'Free weights, cardio machines, and functional training zones.' ],
					[ 'icon' => '👤', 'title' => 'Personal Training', 'text' => 'Certified trainers create custom plans for your goals.' ],
					[ 'icon' => '📅', 'title' => '50+ Classes Weekly', 'text' => 'HIIT, spin, yoga, boxing, and more — included in membership.' ],
					[ 'icon' => '📱', 'title' => 'Member App', 'text' => 'Book classes, track workouts, and monitor progress on the go.' ],
				],
				'about_title' => 'Built for Results',
				'about_p1' => 'Our facility was designed for serious athletes and beginners alike. With over 15,000 sq ft of training space, you will never wait for equipment.',
				'about_p2' => 'Our coaches hold nationally recognized certifications and specialize in strength, weight loss, athletic performance, and injury recovery.',
				'about_bullets' => [ 'Open 24/7 for members', 'Locker rooms with showers', 'Nutrition coaching available' ],
				'testimonials' => [
					[ 'quote' => 'Lost 30 lbs in 4 months. The trainers here genuinely care about your progress.', 'name' => 'Marcus J.', 'role' => 'Member since 2024' ],
					[ 'quote' => 'Best gym I have ever joined. Clean, modern, and never overcrowded.', 'name' => 'Sofia L.', 'role' => 'CrossFit enthusiast' ],
					[ 'quote' => 'The group classes are addictive. I actually look forward to working out now!', 'name' => 'Tyler B.', 'role' => 'HIIT regular' ],
				],
				'faq' => [
					[ 'q' => 'Is there a joining fee?', 'a' => 'No joining fee on annual plans. Month-to-month available with a small setup fee.' ],
					[ 'q' => 'Can I bring a guest?', 'a' => 'Members get 2 guest passes per month. Additional passes available for purchase.' ],
					[ 'q' => 'Do you offer student discounts?', 'a' => 'Yes — valid student ID gets 20% off all membership tiers.' ],
					[ 'q' => 'What should I bring for my first visit?', 'a' => 'Comfortable workout clothes, athletic shoes, and a water bottle. Towels provided.' ],
				],
				'cta_title' => 'Start Your 7-Day Free Trial',
				'cta_text' => 'No credit card required. Experience everything we offer risk-free.',
				'meta' => 'Premium fitness gym with personal training, group classes, and modern equipment. Start your free trial today.',
			]),
			'yoga' => self::tpl([
				'accent' => '#059669', 'accent2' => '#34D399',
				'hero_headline_tpl' => 'Find Your Balance at {brand}',
				'hero_headline_default' => 'Breathe. Move. Transform.',
				'hero_badge' => '🧘 First class free · All levels welcome',
				'hero_sub' => 'Mindful yoga and meditation classes in a serene studio — restore your body, calm your mind, and reconnect with yourself.',
				'cta_primary' => 'Book Free Class', 'cta_secondary' => 'Class Schedule',
				'features_title' => 'Your Wellness Journey',
				'features_lead' => 'Holistic practices for body, mind, and spirit.',
				'features' => [
					[ 'icon' => '🧘', 'title' => 'All Levels', 'text' => 'From gentle restorative to power vinyasa — find your perfect flow.' ],
					[ 'icon' => '🌸', 'title' => 'Small Classes', 'text' => 'Intimate sessions with personalized attention from expert instructors.' ],
					[ 'icon' => '🕯️', 'title' => 'Meditation', 'text' => 'Guided meditation and breathwork to reduce stress and improve focus.' ],
					[ 'icon' => '🛍️', 'title' => 'Wellness Shop', 'text' => 'Mats, blocks, oils, and apparel curated for your practice.' ],
				],
				'about_title' => 'A Sanctuary for Self-Care',
				'about_p1' => 'Our studio is a peaceful escape from the everyday hustle. Natural light, calming scents, and experienced teachers create the ideal environment for your practice.',
				'about_p2' => 'We offer workshops, retreats, and teacher training programs for those who want to deepen their yoga journey.',
				'about_bullets' => [ 'Heated & non-heated rooms', 'Prenatal yoga classes', 'Monthly wellness workshops' ],
				'testimonials' => [
					[ 'quote' => 'This studio changed my life. I sleep better and feel more centered every day.', 'name' => 'Priya N.', 'role' => 'Yoga practitioner' ],
					[ 'quote' => 'The instructors are incredibly supportive. Perfect for beginners like me.', 'name' => 'Chris W.', 'role' => 'New member' ],
					[ 'quote' => 'Beautiful space, amazing energy. My weekly reset I cannot live without.', 'name' => 'Hannah F.', 'role' => 'Member 2 years' ],
				],
				'faq' => [
					[ 'q' => 'What should I wear?', 'a' => 'Comfortable, stretchy clothing. We practice barefoot. Mats are provided.' ],
					[ 'q' => 'I am not flexible — can I still join?', 'a' => 'Absolutely! Yoga meets you where you are. Our beginner classes are very welcoming.' ],
					[ 'q' => 'How do I book a class?', 'a' => 'Book online through our schedule or walk in 15 minutes before class starts.' ],
					[ 'q' => 'Do you offer memberships?', 'a' => 'Drop-in, class packs, and unlimited monthly memberships available.' ],
				],
				'cta_title' => 'Your First Class Is On Us',
				'cta_text' => 'Experience the difference mindful movement can make.',
				'meta' => 'Yoga studio offering classes for all levels, meditation, and wellness workshops. Book your free first class today.',
			]),
			'saas' => self::tpl([
				'accent' => '#4F46E5', 'accent2' => '#818CF8',
				'hero_headline_tpl' => '{brand} — Work Smarter, Not Harder',
				'hero_headline_default' => 'The Platform Teams Love',
				'hero_badge' => '⚡ Trusted by 5,000+ teams worldwide',
				'hero_sub' => 'Streamline workflows, automate repetitive tasks, and collaborate in real time — all in one beautiful dashboard.',
				'cta_primary' => 'Start Free Trial', 'cta_secondary' => 'Watch Demo',
				'features_title' => 'Built for Modern Teams',
				'features_lead' => 'Powerful features that scale from startup to enterprise.',
				'features' => [
					[ 'icon' => '⚡', 'title' => 'Lightning Fast', 'text' => 'Sub-second load times and real-time sync across all devices.' ],
					[ 'icon' => '🔗', 'title' => '100+ Integrations', 'text' => 'Connect Slack, Google, Salesforce, Zapier, and your favorite tools.' ],
					[ 'icon' => '🔒', 'title' => 'Enterprise Security', 'text' => 'SOC 2 compliant with SSO, 2FA, and end-to-end encryption.' ],
					[ 'icon' => '📊', 'title' => 'Smart Analytics', 'text' => 'Actionable insights and custom reports to drive better decisions.' ],
				],
				'about_title' => 'Why Teams Switch to Us',
				'about_p1' => 'We built this platform because we were tired of juggling five different tools. One unified workspace for projects, docs, and communication.',
				'about_p2' => 'From 5-person startups to Fortune 500 companies, our customers save an average of 12 hours per week per team member.',
				'about_bullets' => [ 'Free plan for up to 10 users', 'Migration assistance included', '24/7 priority support on Pro' ],
				'testimonials' => [
					[ 'quote' => 'Cut our project delivery time by 40%. The automation features are game-changing.', 'name' => 'Alex Chen', 'role' => 'CTO, TechFlow' ],
					[ 'quote' => 'Finally, a tool the whole team actually enjoys using. Onboarding took one afternoon.', 'name' => 'Sarah Mitchell', 'role' => 'Ops Director' ],
					[ 'quote' => 'Best ROI of any software we have purchased. Paid for itself in the first month.', 'name' => 'James Park', 'role' => 'Founder, ScaleUp' ],
				],
				'faq' => [
					[ 'q' => 'Is there a free plan?', 'a' => 'Yes — free forever for teams up to 10 users with core features included.' ],
					[ 'q' => 'Can I import data from other tools?', 'a' => 'We support imports from Trello, Asana, Notion, and CSV. Our team helps with migration.' ],
					[ 'q' => 'Is my data secure?', 'a' => 'We are SOC 2 Type II certified with data encrypted at rest and in transit.' ],
					[ 'q' => 'Do you offer annual billing discounts?', 'a' => 'Save 20% with annual billing on all paid plans.' ],
				],
				'cta_title' => 'Ready to Transform Your Workflow?',
				'cta_text' => 'Start your 14-day free trial. No credit card required.',
				'meta' => 'All-in-one SaaS platform for team collaboration, project management, and workflow automation. Start free today.',
			]),
			'agency' => self::tpl([
				'accent' => '#7C3AED', 'accent2' => '#A78BFA',
				'hero_headline_tpl' => '{brand} — Creative That Converts',
				'hero_headline_default' => 'We Build Brands That Stand Out',
				'hero_badge' => '🏆 Award-winning creative agency',
				'hero_sub' => 'Strategy, design, and digital marketing that drives real results for ambitious brands.',
				'cta_primary' => 'Start a Project', 'cta_secondary' => 'View Our Work',
				'features_title' => 'What We Do Best',
				'features_lead' => 'End-to-end creative services for brands ready to grow.',
				'features' => [
					[ 'icon' => '🎯', 'title' => 'Brand Strategy', 'text' => 'Positioning, messaging, and identity that resonates with your audience.' ],
					[ 'icon' => '🎨', 'title' => 'Design & UX', 'text' => 'Stunning visuals and intuitive experiences across web and mobile.' ],
					[ 'icon' => '📈', 'title' => 'Digital Marketing', 'text' => 'SEO, paid ads, and content that generates qualified leads.' ],
					[ 'icon' => '🚀', 'title' => 'Web Development', 'text' => 'Fast, responsive websites built for conversion and performance.' ],
				],
				'about_title' => 'Partners in Your Growth',
				'about_p1' => 'We are a team of strategists, designers, and developers obsessed with helping brands tell their story and grow their audience.',
				'about_p2' => 'From startups launching their first website to established companies refreshing their brand, we deliver work that makes an impact.',
				'about_bullets' => [ '50+ brands launched', 'Average 3x ROI on campaigns', 'Dedicated project manager' ],
				'testimonials' => [
					[ 'quote' => 'They completely transformed our brand. Website traffic tripled in 3 months.', 'name' => 'Nina Patel', 'role' => 'CEO, Bloom Co.' ],
					[ 'quote' => 'Creative, professional, and always on deadline. Our go-to agency.', 'name' => 'Tom Richards', 'role' => 'Marketing VP' ],
					[ 'quote' => 'The rebrand exceeded every expectation. Our customers love the new look.', 'name' => 'Kate Morrison', 'role' => 'Founder' ],
				],
				'faq' => [
					[ 'q' => 'What is your typical project timeline?', 'a' => 'Branding projects take 4-6 weeks. Websites 6-10 weeks. We provide a detailed timeline upfront.' ],
					[ 'q' => 'Do you work with small businesses?', 'a' => 'Yes — we have packages for startups and growing businesses at every budget.' ],
					[ 'q' => 'Can you manage our ongoing marketing?', 'a' => 'We offer monthly retainer packages for SEO, content, and paid advertising.' ],
					[ 'q' => 'How do we get started?', 'a' => 'Book a free 30-minute discovery call and we will outline a custom proposal.' ],
				],
				'cta_title' => 'Let Us Build Something Great Together',
				'cta_text' => 'Book a free strategy call — no obligation, just great ideas.',
				'meta' => 'Full-service creative agency specializing in branding, web design, and digital marketing. Start your project today.',
			]),
			'realestate' => self::tpl([
				'accent' => '#0D9488', 'accent2' => '#2DD4BF',
				'hero_headline_tpl' => 'Find Your Dream Home with {brand}',
				'hero_headline_default' => 'Your Next Chapter Starts Here',
				'hero_badge' => '🏡 500+ homes sold · Top-rated agents',
				'hero_sub' => 'Expert real estate agents helping you buy, sell, or invest with confidence and ease.',
				'cta_primary' => 'Browse Listings', 'cta_secondary' => 'Free Home Valuation',
				'features_title' => 'Why Choose Our Agency',
				'features_lead' => 'Local expertise, personalized service, proven results.',
				'features' => [
					[ 'icon' => '🏠', 'title' => 'Exclusive Listings', 'text' => 'Access to off-market properties before they hit the public market.' ],
					[ 'icon' => '📋', 'title' => 'Full-Service', 'text' => 'Buying, selling, staging, and closing — we handle every detail.' ],
					[ 'icon' => '💰', 'title' => 'Top Dollar Sales', 'text' => 'Our homes sell 15% faster and for 8% more than market average.' ],
					[ 'icon' => '🤝', 'title' => 'Dedicated Agent', 'text' => 'A personal agent available 7 days a week throughout your journey.' ],
				],
				'about_title' => 'Local Experts You Can Trust',
				'about_p1' => 'With over 15 years in the market, we know every neighborhood, school district, and investment opportunity in the area.',
				'about_p2' => 'Our agents combine deep local knowledge with cutting-edge marketing to get you the best deal — whether buying or selling.',
				'about_bullets' => [ 'Free home valuation', 'Professional photography included', 'Virtual tours for every listing' ],
				'testimonials' => [
					[ 'quote' => 'Sold our home in 5 days above asking price. Incredible marketing and negotiation.', 'name' => 'Robert & Linda', 'role' => 'Sellers' ],
					[ 'quote' => 'Found our perfect family home within our budget. Patient and knowledgeable.', 'name' => 'The Garcia Family', 'role' => 'Buyers' ],
					[ 'quote' => 'Best real estate experience we have ever had. Highly recommend!', 'name' => 'Steven H.', 'role' => 'Investor' ],
				],
				'faq' => [
					[ 'q' => 'How much does it cost to sell?', 'a' => 'Standard commission applies. We provide a free market analysis with no obligation.' ],
					[ 'q' => 'How long does buying take?', 'a' => 'Typically 30-45 days from offer to closing. We guide you through every step.' ],
					[ 'q' => 'Do you help first-time buyers?', 'a' => 'Yes — we specialize in first-time buyer programs and down payment assistance.' ],
					[ 'q' => 'What areas do you serve?', 'a' => 'We cover the entire metro area and surrounding suburbs.' ],
				],
				'cta_title' => 'Get Your Free Home Valuation',
				'cta_text' => 'Find out what your home is worth in today\'s market — no strings attached.',
				'meta' => 'Trusted real estate agency helping you buy and sell homes. Browse listings and get a free home valuation today.',
			]),
			'beauty' => self::tpl([
				'accent' => '#DB2777', 'accent2' => '#F472B6',
				'hero_headline_tpl' => 'Look & Feel Amazing at {brand}',
				'hero_headline_default' => 'Beauty Redefined',
				'hero_badge' => '✨ Luxury treatments · Expert stylists',
				'hero_sub' => 'Premium hair, skin, and nail services in a relaxing salon environment — because you deserve to feel your best.',
				'cta_primary' => 'Book Appointment', 'cta_secondary' => 'View Services',
				'features_title' => 'Our Services',
				'features_lead' => 'Expert care using premium products and the latest techniques.',
				'features' => [
					[ 'icon' => '💇', 'title' => 'Hair Styling', 'text' => 'Cuts, color, balayage, and treatments by master stylists.' ],
					[ 'icon' => '💅', 'title' => 'Nails & Lashes', 'text' => 'Manicures, pedicures, gel nails, and lash extensions.' ],
					[ 'icon' => '🧖', 'title' => 'Skin Care', 'text' => 'Facials, peels, and anti-aging treatments for radiant skin.' ],
					[ 'icon' => '💆', 'title' => 'Spa Packages', 'text' => 'Full-day pampering packages perfect for gifts or self-care.' ],
				],
				'about_title' => 'Your Beauty Destination',
				'about_p1' => 'Our salon combines luxury ambiance with skilled professionals who stay current on the latest trends and techniques.',
				'about_p2' => 'We use only premium, cruelty-free products and tailor every service to your unique style and preferences.',
				'about_bullets' => [ 'Walk-ins welcome', 'Bridal packages available', 'Loyalty rewards program' ],
				'testimonials' => [
					[ 'quote' => 'Best haircut I have ever had. They really listen to what you want.', 'name' => 'Jessica A.', 'role' => 'Regular client' ],
					[ 'quote' => 'The facial was heavenly. My skin has never looked better.', 'name' => 'Maria G.', 'role' => 'Spa guest' ],
					[ 'quote' => 'Got my bridal party done here — everyone looked stunning!', 'name' => 'Amanda T.', 'role' => 'Bride' ],
				],
				'faq' => [
					[ 'q' => 'How do I book?', 'a' => 'Book online 24/7 or call us. Same-day appointments often available.' ],
					[ 'q' => 'What is your cancellation policy?', 'a' => 'Please cancel 24 hours in advance to avoid a fee.' ],
					[ 'q' => 'Do you offer gift cards?', 'a' => 'Yes — digital and physical gift cards available in any amount.' ],
					[ 'q' => 'What brands do you use?', 'a' => 'We carry Oribe, Olaplex, Dermalogica, and other premium lines.' ],
				],
				'cta_title' => 'Treat Yourself Today',
				'cta_text' => 'Book your appointment and experience the difference.',
				'meta' => 'Luxury beauty salon offering hair, nails, skincare, and spa services. Book your appointment online today.',
			]),
			'manufacturing' => self::tpl([
				'accent' => '#1D4ED8', 'accent2' => '#38BDF8',
				'hero_headline_tpl' => '{brand} — Precision Manufacturing',
				'hero_headline_default' => 'Engineered for Production Excellence',
				'hero_badge' => 'ISO 9001 · CNC · On-time delivery',
				'hero_sub' => 'End-to-end manufacturing for OEMs and industrial partners — tight tolerances, scalable capacity, and quality systems you can audit.',
				'cta_primary' => 'Request a Quote', 'cta_secondary' => 'Tour Capabilities',
				'features_title' => 'Built for Demanding Production',
				'features_lead' => 'From prototype to full-rate manufacturing with measurable process control.',
				'features' => [
					[ 'icon' => '01', 'title' => 'CNC Machining', 'text' => '5-axis milling, turning, and multi-op cells for complex geometries.' ],
					[ 'icon' => '02', 'title' => 'Assembly & Test', 'text' => 'Kitting, sub-assembly, functional test, and serialized traceability.' ],
					[ 'icon' => '03', 'title' => 'Quality Systems', 'text' => 'In-process inspection, CMM verification, and documented PPAP support.' ],
					[ 'icon' => '04', 'title' => 'Supply Reliability', 'text' => 'Kanban, safety stock, and logistics programs that protect your line.' ],
				],
				'about_title' => 'Your Production Partner',
				'about_p1' => 'We run a modern manufacturing campus purpose-built for precision components and industrial assemblies. Our engineers collaborate early so parts are designed for manufacturability, cost, and throughput.',
				'about_p2' => 'Whether you need a bridge build or multi-year production, our cells, tooling, and quality gates are tuned for repeatability — shift after shift.',
				'about_bullets' => [ 'Prototype to production ramp', 'DFM & process engineering', 'Full lot traceability' ],
				'testimonials' => [
					[ 'quote' => 'They cut our scrap rate in half and hit every release date for 18 months straight.', 'name' => 'Daniel Orth', 'role' => 'VP Operations, AeroDrive' ],
					[ 'quote' => 'CMM reports, photos, and traveler docs arrive with every shipment. Zero surprises.', 'name' => 'Priya Nair', 'role' => 'Quality Manager' ],
					[ 'quote' => 'Best contract manufacturer we have worked with for complex aluminum housings.', 'name' => 'Marcus Hale', 'role' => 'Procurement Lead' ],
				],
				'faq' => [
					[ 'q' => 'What materials do you machine?', 'a' => 'Aluminum, stainless, carbon steel, titanium, plastics, and common engineering alloys.' ],
					[ 'q' => 'Can you support low-volume NPI?', 'a' => 'Yes — dedicated prototype cells with fast toolpaths and first-article inspection.' ],
					[ 'q' => 'Do you offer vendor-managed inventory?', 'a' => 'We run kanban, consignment, and scheduled releases aligned to your MRP.' ],
					[ 'q' => 'What certifications do you hold?', 'a' => 'ISO 9001 with industry-specific quality packs available on request.' ],
				],
				'cta_title' => 'Ready to Scale Production?',
				'cta_text' => 'Share drawings or RFQs — our estimating team responds within one business day.',
				'meta' => 'Precision manufacturing partner offering CNC machining, assembly, and ISO quality systems for industrial OEMs.',
			]),
			'steel' => self::tpl([
				'accent' => '#E11D48', 'accent2' => '#F59E0B',
				'hero_headline_tpl' => '{brand} — Steel Manufacturing',
				'hero_headline_default' => 'Structural Steel. Fabricated Right.',
				'hero_badge' => 'Mill-direct · Fabrication · Heavy plate',
				'hero_sub' => 'High-capacity steel manufacturing for structural, plate, and custom metal programs — from melt chemistry to finish coating.',
				'cta_primary' => 'Get Steel Quote', 'cta_secondary' => 'View Plant',
				'features_title' => 'Steel Capabilities That Ship',
				'features_lead' => 'Rolling, cutting, welding, and finishing under one roof with mill-grade discipline.',
				'features' => [
					[ 'icon' => '01', 'title' => 'Structural Fabrication', 'text' => 'Beams, columns, trusses, and bolted assemblies for industrial builds.' ],
					[ 'icon' => '02', 'title' => 'Plate & Processing', 'text' => 'Plasma, laser, and oxy cutting with forming and edge prep.' ],
					[ 'icon' => '03', 'title' => 'Welding Excellence', 'text' => 'Certified welders, WPS/PQR documentation, and NDT options.' ],
					[ 'icon' => '04', 'title' => 'Coatings & Logistics', 'text' => 'Blast, prime, paint, and staged delivery to your site schedule.' ],
				],
				'about_title' => 'Forged for Heavy Industry',
				'about_p1' => 'Our steel campus combines fabrication bays, overhead crane capacity, and finishing lines to move heavy work without bottlenecks. Specs are checked at every gate — chemistry, dimensions, weld quality, and coating.',
				'about_p2' => 'We partner with EPC firms, plant owners, and OEMs who need steel that arrives square, documented, and ready to erect.',
				'about_bullets' => [ 'Heavy lift crane capacity', 'Certified welding procedures', 'Project packaging & staging' ],
				'testimonials' => [
					[ 'quote' => 'They delivered 420 tons of structural steel sequenced perfectly for our shutdown window.', 'name' => 'Elena Vargas', 'role' => 'Project Director, Summit EPC' ],
					[ 'quote' => 'Weld quality and documentation were audit-ready. Our inspectors were impressed.', 'name' => 'Jonah Reed', 'role' => 'QA Superintendent' ],
					[ 'quote' => 'Plate work was flat, cut clean, and on trucks when promised. Rare in this industry.', 'name' => 'Chris Bao', 'role' => 'Plant Manager' ],
				],
				'faq' => [
					[ 'q' => 'What grades do you supply?', 'a' => 'Common carbon and HSLA grades, stainless options, and mill certs with every heat.' ],
					[ 'q' => 'Can you handle shop drawings?', 'a' => 'Yes — detailing, nesting, and BOM coordination with your engineering team.' ],
					[ 'q' => 'Do you offer field erection?', 'a' => 'We partner with erection crews and can package steel for lift sequence.' ],
					[ 'q' => 'What is your typical lead time?', 'a' => 'Depends on tonnage and finish — rush cells available for critical path items.' ],
				],
				'cta_title' => 'Need Steel on Your Critical Path?',
				'cta_text' => 'Send tonnage, specs, and schedule — we will return a clear fabrication plan.',
				'meta' => 'Steel manufacturing and fabrication for structural, plate, and industrial metal programs with certified welding and coatings.',
			]),
			'business' => self::tpl([
				'accent' => '#2563EB', 'accent2' => '#60A5FA',
				'hero_headline_tpl' => 'Welcome to {brand}',
				'hero_headline_default' => 'Professional Solutions You Can Trust',
				'hero_badge' => '⭐ Rated 4.9/5 by our clients',
				'hero_sub' => 'We deliver exceptional service, proven results, and a partnership approach that puts your success first.',
				'cta_primary' => 'Get Started', 'cta_secondary' => 'Learn More',
				'features_title' => 'Why Clients Choose Us',
				'features_lead' => 'Quality, reliability, and results — every single time.',
				'features' => [
					[ 'icon' => '⚡', 'title' => 'Fast Turnaround', 'text' => 'We respect your time with efficient processes and on-time delivery.' ],
					[ 'icon' => '🎯', 'title' => 'Results-Driven', 'text' => 'Every strategy is built around measurable outcomes for your business.' ],
					[ 'icon' => '🤝', 'title' => 'Dedicated Support', 'text' => 'A real person answers your call — not a chatbot or ticket queue.' ],
					[ 'icon' => '💎', 'title' => 'Premium Quality', 'text' => 'We never cut corners. Excellence is our standard, not our goal.' ],
				],
				'about_title' => 'About Us',
				'about_p1' => 'We have spent years perfecting our craft and building lasting relationships with clients who trust us to deliver.',
				'about_p2' => 'Our team brings deep expertise, fresh ideas, and genuine passion to every project we take on.',
				'about_bullets' => [ '10+ years of experience', 'Transparent pricing', 'Satisfaction guaranteed' ],
				'testimonials' => [
					[ 'quote' => 'Professional, responsive, and delivered exactly what they promised.', 'name' => 'Sarah K.', 'role' => 'Business owner' ],
					[ 'quote' => 'They went above and beyond. Could not recommend more highly.', 'name' => 'James M.', 'role' => 'Director' ],
					[ 'quote' => 'Our go-to partner for the last 3 years. Consistently excellent.', 'name' => 'Priya R.', 'role' => 'Manager' ],
				],
				'faq' => [
					[ 'q' => 'How do I get started?', 'a' => 'Contact us for a free consultation. We will discuss your needs and provide a clear proposal.' ],
					[ 'q' => 'What are your rates?', 'a' => 'Pricing depends on scope. We provide detailed quotes with no hidden fees.' ],
					[ 'q' => 'Do you offer guarantees?', 'a' => 'Yes — we stand behind our work with a satisfaction guarantee on all services.' ],
					[ 'q' => 'What areas do you serve?', 'a' => 'We serve clients locally and remotely across the region.' ],
				],
				'cta_title' => 'Ready to Get Started?',
				'cta_text' => 'Contact us today for a free consultation — no obligation.',
				'meta' => 'Professional business services with proven results and dedicated support. Contact us for a free consultation today.',
			]),
		];
	}

	/**
	 * @param array $data Profile fields.
	 * @return array
	 */
	private static function tpl($data) {
		$defaults = [
			'accent' => '#4F46E5', 'accent2' => '#818CF8',
			'hero_headline_tpl' => 'Welcome to {brand}',
			'hero_headline_default' => 'Welcome',
			'hero_badge' => '', 'hero_sub' => '', 'cta_primary' => 'Get Started', 'cta_secondary' => 'Learn More',
			'features_title' => 'Why Choose Us', 'features_lead' => '',
			'features' => [], 'about_title' => 'About Us', 'about_p1' => '', 'about_p2' => '',
			'about_bullets' => [], 'testimonials' => [], 'faq' => [],
			'cta_title' => 'Get Started Today', 'cta_text' => '', 'meta' => '',
		];
		return array_merge($defaults, $data);
	}
}

