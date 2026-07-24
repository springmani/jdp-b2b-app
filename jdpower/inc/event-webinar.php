<?php
/**
 * Event & Webinar (event_webinar CPT) — single template helpers.
 *
 * @package jdpower
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether the event details sidebar (ACF) should show on the single.
 *
 * Shows if any meaningful field is set: date/time, registration URL, or in-person / hybrid format.
 * Default "online" alone with no date and no CTA does not open the box.
 *
 * @param int|null $post_id Post ID or null for current post in loop.
 * @return bool
 */
function jdpower_event_webinar_has_event_details( $post_id = null ) {
	if ( ! function_exists( 'get_field' ) ) {
		return false;
	}
	$post_id = $post_id ? (int) $post_id : get_the_ID();
	if ( $post_id <= 0 || 'event_webinar' !== get_post_type( $post_id ) ) {
		return false;
	}

	$datetime = get_field( 'webinar_event_datetime', $post_id );
	if ( is_string( $datetime ) && '' !== trim( $datetime ) ) {
		return true;
	}

	$cta = get_field( 'webinar_registration_cta', $post_id );
	if ( is_array( $cta ) && ! empty( $cta['url'] ) ) {
		return true;
	}

	$format = (string) get_field( 'webinar_event_format', $post_id );
	if ( in_array( $format, array( 'in_person', 'hybrid' ), true ) ) {
		return true;
	}

	return false;
}

/**
 * Uppercase line for the event time row (e.g. APRIL 1, 2026 - 9:30 AM PST).
 *
 * @param int $post_id Post ID.
 * @return string Empty if no datetime stored.
 */
function jdpower_event_webinar_datetime_display_line( $post_id = null ) {
	$post_id = $post_id ? (int) $post_id : get_the_ID();
	if ( $post_id <= 0 || ! function_exists( 'get_field' ) ) {
		return '';
	}
	$raw = get_field( 'webinar_event_datetime', $post_id );
	if ( ! is_string( $raw ) || '' === trim( $raw ) ) {
		return '';
	}
	$ts = strtotime( $raw );
	if ( ! $ts ) {
		return '';
	}
	// Match marketing style: "APRIL 1, 2026 - 9:30 AM EST" (site timezone; T = abbreviation).
	return wp_date( 'F j, Y - g:i a T', (int) $ts );
}

/**
 * Uppercase event format for the second row (ONLINE, IN PERSON, etc.).
 *
 * @param int|null $post_id Post ID.
 * @return string
 */
function jdpower_event_webinar_format_display_line( $post_id = null ) {
	$post_id = $post_id ? (int) $post_id : get_the_ID();
	if ( $post_id <= 0 || ! function_exists( 'get_field' ) ) {
		return '';
	}
	$value = (string) get_field( 'webinar_event_format', $post_id );
	$labels  = array(
		'online'    => __( 'ONLINE', 'jdpower' ),
		'in_person' => __( 'IN PERSON', 'jdpower' ),
		'hybrid'    => __( 'HYBRID (ONLINE & IN PERSON)', 'jdpower' ),
	);
	if ( '' === $value ) {
		$value = 'online';
	}
	if ( ! isset( $labels[ $value ] ) ) {
		return '';
	}
	return $labels[ $value ];
}
