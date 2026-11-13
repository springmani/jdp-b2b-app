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
 * Timezone the event start time was entered in (ACF "webinar_event_timezone").
 *
 * Falls back to the site timezone when no timezone is set (events saved before the field existed).
 *
 * @param int|null $post_id Post ID or null for current post in loop.
 * @return DateTimeZone
 */
function jdpower_event_webinar_timezone( $post_id = null ) {
	$post_id = $post_id ? (int) $post_id : get_the_ID();
	$tz_name = function_exists( 'get_field' ) ? (string) get_field( 'webinar_event_timezone', $post_id ) : '';
	if ( '' !== $tz_name ) {
		try {
			return new DateTimeZone( $tz_name );
		} catch ( Exception $e ) {
			// Unknown identifier: fall back to the site timezone below.
		}
	}
	return wp_timezone();
}

/**
 * Uppercase line for the event time row (e.g. NOVEMBER 19, 2026 - 1:00 pm EST).
 *
 * The time is read in the event's own timezone and the abbreviation (EST, CET, ...) follows it, including DST.
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
	$tz = jdpower_event_webinar_timezone( $post_id );
	try {
		$start = new DateTimeImmutable( $raw, $tz );
	} catch ( Exception $e ) {
		return '';
	}
	return wp_date( 'F j, Y - g:i a T', $start->getTimestamp(), $tz );
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
