<?php
/**
 * Single solution template — page-like layout.
 *
 * @package jdpower
 */

get_header();
?>

	<main id="primary" class="site-main site-main--solution">

		<?php
		while ( have_posts() ) :
			the_post();
			get_template_part( 'template-parts/content', 'solution' );
		endwhile;
		?>

	</main>

<?php
get_footer();
