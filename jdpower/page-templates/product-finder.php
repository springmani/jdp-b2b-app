<?php
/**
 * Template Name: Product Finder
 *
 * Product grid with shared post-filters behavior (AJAX, URL, load more).
 *
 * @package jdpower
 */

get_header();
?>

	<main id="primary" class="site-main site-main--product-finder">

		<?php
		$config                   = jdpower_post_filters_get_config( JDPOWER_POST_FILTERS_CONTEXT_PRODUCT );
		$config['finder_page_id'] = get_queried_object_id();
		$request                  = jdpower_post_filters_parse_request( $config, wp_unslash( $_GET ) );
		$query = new WP_Query( jdpower_post_filters_build_query_args( $config, $request ) );
		jdpower_post_filters_normalize_grid_query_pagination( $query, $config, $request );

		set_query_var( 'jdpower_pf_config', $config );
		set_query_var( 'jdpower_pf_request', $request );
		set_query_var( 'jdpower_pf_query', $query );
		get_template_part( 'template-parts/post-filters/layout' );
		?>

	</main>

<?php
get_footer();
