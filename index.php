<?php
/**
 * The main template file
 *
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query.
 * E.g., it puts together the home page when no home.php file exists.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package jdpower
 */

get_header();
?>

	<main id="primary" class="site-main">

		<?php
		if ( jdpower_post_filters_is_insight_center_view() ) {
			$config  = jdpower_post_filters_get_config( JDPOWER_POST_FILTERS_CONTEXT_INSIGHT );
			$request = jdpower_post_filters_parse_request( $config, wp_unslash( $_GET ) );
			$query = new WP_Query( jdpower_post_filters_build_query_args( $config, $request ) );
			jdpower_post_filters_normalize_grid_query_pagination( $query, $config, $request );

			set_query_var( 'jdpower_pf_config', $config );
			set_query_var( 'jdpower_pf_request', $request );
			set_query_var( 'jdpower_pf_query', $query );
			get_template_part( 'template-parts/post-filters/layout' );
		} elseif ( have_posts() ) {

			while ( have_posts() ) {
				the_post();

				get_template_part( 'template-parts/content', get_post_type() );
			}

			?>
			<div class="container">
				<div class="pagination">
					<?php the_posts_navigation(); ?>
				</div>
			</div>
			<?php

		} else {

			get_template_part( 'template-parts/content', 'none' );

		}
		?>

	</main>

<?php
get_footer();
