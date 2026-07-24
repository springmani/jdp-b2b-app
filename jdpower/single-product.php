<?php
/**
 * Single product template — page-like full-width content (not the narrow post layout).
 *
 * @package jdpower
 */

get_header();
?>

	<main id="primary" class="site-main site-main--product">

		<?php
		while ( have_posts() ) :
			the_post();
			get_template_part( 'template-parts/content', 'product' );
		endwhile;
		?>

	</main>

<?php
get_footer();
