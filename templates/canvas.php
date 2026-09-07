<?php
/**
 * AV Web Studio Canvas — minimal page shell without theme header or footer.
 *
 * @package AV Web Studio
 */

if (!defined('ABSPATH')) {
	exit;
}

?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo('charset'); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class('av-web-studio-canvas-template'); ?>>
<?php wp_body_open(); ?>
<main id="av-web-studio-canvas-content" class="av-web-studio-canvas-content">
<?php
while (have_posts()) {
	the_post();
	the_content();
}
?>
</main>
<?php wp_footer(); ?>
</body>
</html>
