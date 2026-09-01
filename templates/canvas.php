<?php
/**
 * EPB Canvas — minimal page shell without theme header or footer.
 *
 * @package WPVisualX
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
<body <?php body_class('epb-canvas-template'); ?>>
<?php wp_body_open(); ?>
<main id="epb-canvas-content" class="epb-canvas-content">
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
