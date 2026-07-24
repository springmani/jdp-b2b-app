<?php
/**
 * Single industry template — page-like layout.
 *
 * @package jdpower
 */

get_header();
?>

	<main id="primary" class="site-main site-main--industries">

		<?php
		while ( have_posts() ) :
			the_post();
			get_template_part( 'template-parts/content', 'industries' );
		endwhile;
		?>

	</main>

<?php
get_footer();
