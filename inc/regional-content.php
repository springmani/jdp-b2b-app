<?php
/**
 * Regional mismatch popup — Theme Options copy + geo-detected region on singular hierarchy CPTs.
 *
 * @package jdpower
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Post types that can show the regional mismatch popup.
 *
 * @return string[]
 */
function jdpower_regional_popup_post_types() {
	return array( 'industries', 'segments', 'solution', 'product' );
}

/**
 * Theme option field suffix for a post type (industries, segments, solutions, products).
 *
 * @param string $post_type Post type slug.
 * @return string Empty when unsupported.
 */
function jdpower_regional_popup_option_suffix( $post_type ) {
	$map = array(
		'industries' => 'industries',
		'segments'   => 'segments',
		'solution'   => 'solutions',
		'product'    => 'products',
	);
	$post_type = (string) $post_type;
	return isset( $map[ $post_type ] ) ? $map[ $post_type ] : '';
}

/**
 * Config for reading assigned product_region slugs per post type.
 *
 * @param string $post_type Post type slug.
 * @return array{source: string, field?: string}|null
 */
function jdpower_regional_post_type_region_config( $post_type ) {
	$map = array(
		'industries' => array(
			'source' => 'acf',
			'field'  => 'industry_available_regions',
		),
		'segments'   => array(
			'source' => 'acf',
			'field'  => 'segment_available_regions',
		),
		'solution'   => array(
			'source' => 'acf',
			'field'  => 'solution_available_regions',
		),
		'product'    => array(
			'source' => 'taxonomy',
		),
	);
	$post_type = (string) $post_type;
	return isset( $map[ $post_type ] ) ? $map[ $post_type ] : null;
}

/**
 * Map product_region term IDs to slugs.
 *
 * @param int[] $term_ids Term IDs.
 * @return string[]
 */
function jdpower_regional_term_ids_to_slugs( $term_ids ) {
	$slugs = array();
	if ( ! is_array( $term_ids ) ) {
		return $slugs;
	}
	foreach ( $term_ids as $term_id ) {
		$term_id = (int) $term_id;
		if ( $term_id <= 0 ) {
			continue;
		}
		$term = get_term( $term_id, 'product_region' );
		if ( $term instanceof WP_Term && ! is_wp_error( $term ) && '' !== $term->slug ) {
			$slugs[] = $term->slug;
		}
	}
	return array_values( array_unique( $slugs ) );
}

/**
 * Product regions assigned to a post (slugs). Empty = available in all regions.
 *
 * @param int         $post_id   Post ID.
 * @param string|null $post_type Optional post type; defaults to post's type.
 * @return string[]
 */
function jdpower_post_region_slugs( $post_id, $post_type = null ) {
	$post_id = (int) $post_id;
	if ( $post_id <= 0 ) {
		return array();
	}

	if ( null === $post_type ) {
		$post_type = get_post_type( $post_id );
	}
	$post_type = (string) $post_type;

	$config = jdpower_regional_post_type_region_config( $post_type );
	if ( null === $config ) {
		return array();
	}

	if ( 'taxonomy' === $config['source'] ) {
		$terms = wp_get_post_terms( $post_id, 'product_region', array( 'fields' => 'slugs' ) );
		if ( is_wp_error( $terms ) || ! is_array( $terms ) ) {
			return array();
		}
		return array_values( array_unique( array_filter( array_map( 'strval', $terms ) ) ) );
	}

	if ( 'acf' === $config['source'] && function_exists( 'get_field' ) && ! empty( $config['field'] ) ) {
		$regions = get_field( $config['field'], $post_id );
		if ( ! is_array( $regions ) ) {
			return array();
		}
		return jdpower_regional_term_ids_to_slugs( $regions );
	}

	return array();
}

/**
 * Whether the post is unavailable in the visitor's geo-detected region.
 *
 * @param int         $post_id   Post ID.
 * @param string|null $post_type Optional post type.
 * @return bool
 */
function jdpower_post_unavailable_in_visitor_region( $post_id, $post_type = null ) {
	$slugs = jdpower_post_region_slugs( $post_id, $post_type );
	if ( empty( $slugs ) ) {
		return false;
	}

	$visitor = jdpower_product_region_visitor_slug();
	if ( '' === $visitor ) {
		return false;
	}

	return ! in_array( $visitor, $slugs, true );
}

/**
 * Display name for the visitor's detected product_region.
 *
 * @return string
 */
function jdpower_regional_visitor_region_label() {
	$slug = jdpower_product_region_visitor_slug();
	if ( '' === $slug ) {
		return '';
	}

	$term = get_term_by( 'slug', $slug, 'product_region' );
	if ( $term instanceof WP_Term && ! is_wp_error( $term ) ) {
		return (string) $term->name;
	}

	return '';
}

/**
 * Product Finder page ID from Theme Options.
 *
 * @return int
 */
function jdpower_regional_product_finder_page_id() {
	if ( function_exists( 'get_field' ) ) {
		$page_id = (int) get_field( 'regional_content_product_finder_page', 'option' );
		if ( $page_id > 0 ) {
			return $page_id;
		}
	}

	$pages = get_posts(
		array(
			'post_type'              => 'page',
			'post_status'            => 'publish',
			'posts_per_page'         => 1,
			'fields'                 => 'ids',
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'meta_query'             => array(
				array(
					'key'   => '_wp_page_template',
					'value' => 'page-templates/product-finder.php',
				),
			),
		)
	);

	return ! empty( $pages ) ? (int) $pages[0] : 0;
}

/**
 * Product Finder URL with optional region query arg.
 *
 * @param string $region_slug product_region slug.
 * @return string Empty when page not configured.
 */
function jdpower_regional_product_finder_url( $region_slug = '' ) {
	$page_id = jdpower_regional_product_finder_page_id();
	if ( $page_id <= 0 ) {
		return '';
	}

	$url = get_permalink( $page_id );
	if ( ! is_string( $url ) || '' === $url ) {
		return '';
	}

	$region_slug = sanitize_title( (string) $region_slug );
	if ( '' !== $region_slug ) {
		$url = add_query_arg( 'region', $region_slug, $url );
	}

	return $url;
}

/**
 * Normalize an ACF image field value for the regional popup.
 *
 * @param mixed $image ACF image array or attachment ID.
 * @return array{id: int, url: string, alt: string}|null
 */
function jdpower_regional_popup_image_data( $image ) {
	if ( is_numeric( $image ) ) {
		$image = (int) $image;
		if ( $image <= 0 ) {
			return null;
		}
		$url = wp_get_attachment_image_url( $image, 'full' );
		if ( ! is_string( $url ) || '' === $url ) {
			return null;
		}
		return array(
			'id'  => $image,
			'url' => $url,
			'alt' => (string) get_post_meta( $image, '_wp_attachment_image_alt', true ),
		);
	}

	if ( ! is_array( $image ) ) {
		return null;
	}

	$id = isset( $image['ID'] ) ? (int) $image['ID'] : ( isset( $image['id'] ) ? (int) $image['id'] : 0 );
	$url = isset( $image['url'] ) ? trim( (string) $image['url'] ) : '';
	if ( '' === $url && $id > 0 ) {
		$resolved = wp_get_attachment_image_url( $id, 'full' );
		$url      = is_string( $resolved ) ? $resolved : '';
	}
	if ( '' === $url ) {
		return null;
	}

	$alt = isset( $image['alt'] ) ? trim( (string) $image['alt'] ) : '';
	if ( '' === $alt && $id > 0 ) {
		$alt = (string) get_post_meta( $id, '_wp_attachment_image_alt', true );
	}

	return array(
		'id'  => $id,
		'url' => $url,
		'alt' => $alt,
	);
}

/**
 * Global modal map image (left card — continue viewing this page).
 *
 * @return array{id: int, url: string, alt: string}|null
 */
function jdpower_regional_popup_global_modal_image() {
	if ( ! function_exists( 'get_field' ) ) {
		return null;
	}

	return jdpower_regional_popup_image_data( get_field( 'regional_content_modal_image', 'option' ) );
}

/**
 * Detected region modal map image (right card — view regional products).
 *
 * @return array{id: int, url: string, alt: string}|null
 */
function jdpower_regional_popup_region_modal_image() {
	if ( ! function_exists( 'get_field' ) ) {
		return null;
	}

	$slug = jdpower_product_region_visitor_slug();
	if ( '' === $slug ) {
		return null;
	}

	$term = get_term_by( 'slug', $slug, 'product_region' );
	if ( ! $term instanceof WP_Term || is_wp_error( $term ) ) {
		return null;
	}

	return jdpower_regional_popup_image_data( get_field( 'region_modal_image', $term ) );
}

/**
 * Theme Options heading, choices line, sub heading (per post type), and card images.
 *
 * @param string $post_type Post type slug.
 * @return array{heading: string, sub_heading: string, choices_heading: string, global_image: array{id: int, url: string, alt: string}|null, region_image: array{id: int, url: string, alt: string}|null}|null Null when unsupported post type.
 */
function jdpower_regional_popup_option_content( $post_type ) {
	if ( ! function_exists( 'get_field' ) ) {
		return null;
	}

	$suffix = jdpower_regional_popup_option_suffix( $post_type );
	if ( '' === $suffix ) {
		return null;
	}

	$heading         = get_field( 'regional_content_heading', 'option' );
	$choices_heading = get_field( 'regional_content_choices_heading', 'option' );
	$sub_heading     = get_field( 'regional_content_' . $suffix . '_sub_heading', 'option' );

	$heading         = is_string( $heading ) ? trim( $heading ) : '';
	$choices_heading = is_string( $choices_heading ) ? trim( $choices_heading ) : '';
	$sub_heading     = is_string( $sub_heading ) ? trim( $sub_heading ) : '';

	if ( '' === $heading ) {
		$heading = __( "We noticed you're visiting from", 'jdpower' );
	}
	if ( '' === $choices_heading ) {
		$choices_heading = __( 'What would you like to do?', 'jdpower' );
	}

	if ( '' === $heading && '' === $sub_heading && '' === $choices_heading ) {
		return null;
	}

	return array(
		'heading'          => $heading,
		'sub_heading'      => $sub_heading,
		'choices_heading'  => $choices_heading,
		'global_image'     => jdpower_regional_popup_global_modal_image(),
		'region_image'     => jdpower_regional_popup_region_modal_image(),
	);
}

/**
 * Button labels from Theme Options with defaults.
 *
 * @return array{remain: string, continue: string}
 */
function jdpower_regional_popup_button_labels() {
	$remain   = function_exists( 'get_field' ) ? get_field( 'regional_content_remain_button_label', 'option' ) : '';
	$continue = function_exists( 'get_field' ) ? get_field( 'regional_content_continue_button_label', 'option' ) : '';

	$remain   = is_string( $remain ) ? trim( $remain ) : '';
	$continue = is_string( $continue ) ? trim( $continue ) : '';

	if ( '' === $remain ) {
		$remain = __( 'Continue Viewing This Page', 'jdpower' );
	}
	if ( '' === $continue ) {
		$continue = __( 'View Products Available In My Region', 'jdpower' );
	}

	return array(
		'remain'   => $remain,
		'continue' => $continue,
	);
}

/**
 * Use Theme Settings Product Finder page for post-filters when set.
 *
 * @param int $page_id Existing page ID from filter.
 * @return int
 */
function jdpower_regional_product_finder_page_id_from_options( $page_id ) {
	$opt = jdpower_regional_product_finder_page_id();
	return $opt > 0 ? $opt : (int) $page_id;
}
add_filter( 'jdpower_post_filters_product_finder_page_id', 'jdpower_regional_product_finder_page_id_from_options' );

/**
 * Render regional mismatch dialog in the footer when applicable.
 */
function jdpower_maybe_render_regional_popup() {
	if ( ! is_singular( jdpower_regional_popup_post_types() ) ) {
		return;
	}

	$post_id   = (int) get_queried_object_id();
	$post_type = get_post_type( $post_id );

	if ( ! $post_id || ! $post_type || ! jdpower_post_unavailable_in_visitor_region( $post_id, $post_type ) ) {
		return;
	}

	$content = jdpower_regional_popup_option_content( $post_type );
	if ( null === $content ) {
		return;
	}

	$region_slug = jdpower_product_region_visitor_slug();
	$continue_url = jdpower_regional_product_finder_url( $region_slug );
	if ( '' === $continue_url ) {
		return;
	}

	$labels = jdpower_regional_popup_button_labels();

	get_template_part(
		'template-parts/partials/regional',
		'popup',
		array(
			'heading'          => $content['heading'],
			'sub_heading'      => $content['sub_heading'],
			'choices_heading'  => $content['choices_heading'],
			'global_image'     => $content['global_image'],
			'region_image'     => $content['region_image'],
			'region_label'     => jdpower_regional_visitor_region_label(),
			'continue_url'     => $continue_url,
			'remain_label'     => $labels['remain'],
			'continue_label'   => $labels['continue'],
		)
	);
}
// Before wp_print_footer_scripts (priority 20) so script.js can find the dialog on DOM ready.
add_action( 'wp_footer', 'jdpower_maybe_render_regional_popup', 5 );
