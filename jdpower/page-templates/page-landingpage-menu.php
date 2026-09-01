<?php
/**
 * Template Name: Landingpage with Menu Footer
 *
 * Landing Page mit gleichem Header wie Landingpage 1,
 * aber Footer mit einem Menü + Copyright.
 *
 * @package jdpower
 */

// Header aus Theme-Root laden (header-landingpage.php)
get_header( 'landingpage' );
?>

<main id="primary" class="site-main landingpage-content">
	<?php
	while ( have_posts() ) :
		the_post();
		the_content();
	endwhile;
	?>
</main>

<?php
// Footer aus Theme-Root laden (footer-landingpage-menu.php)
get_footer( 'landingpage-menu' );
?>
