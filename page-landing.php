<?php
/**
 * Template Name: JDP Landing page without navigation
 * Template Post Type: page
 */

get_header('landing');
?>

	<main id="primary" class="site-main">

		<?php
		while ( have_posts() ) :
			the_post();

			get_template_part( 'template-parts/content', 'page' );
		endwhile;
		?>

	</main>

<?php
get_footer('landing');
