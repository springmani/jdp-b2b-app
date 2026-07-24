<?php
/**
 * Theme-option fallback images for insight cards without a featured image.
 *
 * @package jdpower
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * ACF option field name for an insight post type fallback image.
 *
 * @param string $post_type Post type slug.
 * @return string Option field name or '' when unsupported.
 */
function jdpower_insight_card_fallback_image_option_field( $post_type ) {
	$map = array(
		'post'          => 'insight_fallback_image_news',
		'press_release' => 'insight_fallback_image_press_release',
		'resource'      => 'insight_fallback_image_resource',
	);

	$post_type = sanitize_key( (string) $post_type );

	return isset( $map[ $post_type ] ) ? $map[ $post_type ] : '';
}

/**
 * Normalize an ACF image field value to an attachment ID.
 *
 * @param mixed $image ACF image field value.
 * @return int
 */
function jdpower_acf_image_value_to_attachment_id( $image ) {
	if ( is_numeric( $image ) ) {
		return (int) $image;
	}
	if ( is_array( $image ) && ! empty( $image['ID'] ) ) {
		return (int) $image['ID'];
	}
	if ( $image instanceof WP_Post ) {
		return (int) $image->ID;
	}

	return 0;
}

/**
 * Theme-option fallback attachment ID for an insight post type.
 *
 * @param string $post_type Post type slug.
 * @return int
 */
function jdpower_insight_card_fallback_image_id_for_post_type( $post_type ) {
	$field = jdpower_insight_card_fallback_image_option_field( $post_type );
	if ( '' === $field || ! function_exists( 'get_field' ) ) {
		return 0;
	}

	$image_id = jdpower_acf_image_value_to_attachment_id( get_field( $field, 'option' ) );
	if ( $image_id <= 0 || 'attachment' !== get_post_type( $image_id ) ) {
		return 0;
	}

	return $image_id;
}

/**
 * Featured image for insight cards: post thumbnail, then theme-option fallback.
 *
 * @param int $post_id Post ID.
 * @return int Attachment ID, or 0 for the text placeholder overlay.
 */
function jdpower_insight_card_image_id( $post_id ) {
	$post_id = (int) $post_id;
	if ( $post_id <= 0 ) {
		return 0;
	}

	$image_id = (int) get_post_thumbnail_id( $post_id );
	if ( $image_id > 0 ) {
		return $image_id;
	}

	$post_type = get_post_type( $post_id );
	if ( ! is_string( $post_type ) || '' === $post_type ) {
		return 0;
	}

	return jdpower_insight_card_fallback_image_id_for_post_type( $post_type );
}
