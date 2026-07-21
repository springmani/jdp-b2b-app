<?php
/**
 * WP Engine GeoTarget helpers — continent → product_region slug mapping.
 *
 * @package jdpower
 */

/**
 * Read a GeoTarget environment variable from getenv() or $_SERVER.
 *
 * @param string $key Variable name without HTTP_ prefix (e.g. GEOIP_CITY_CONTINENT_CODE).
 * @return string Sanitized value, or empty string when unset.
 */
function jdpower_geotarget_env( $key ) {
	$key = preg_replace( '/[^A-Z0-9_]/', '', strtoupper( (string) $key ) );
	if ( '' === $key ) {
		return '';
	}

	$http_key = 'HTTP_' . $key;
	$raw      = getenv( $http_key );
	if ( false === $raw && isset( $_SERVER[ $http_key ] ) ) {
		$raw = $_SERVER[ $http_key ];
	}

	return is_string( $raw ) ? sanitize_text_field( wp_unslash( $raw ) ) : '';
}

/**
 * GeoTarget visitor signals (continent/country available for PHP logic; only continent is cache-safe for JS).
 *
 * @return array{continent: string, country: string}
 */
function jdpower_geotarget_get_visitor() {
	static $cached = null;

	if ( null !== $cached ) {
		return $cached;
	}

	$cached = array(
		'continent' => jdpower_geotarget_get_continent_code(),
		'country'   => jdpower_geotarget_env( 'GEOIP_COUNTRY_CODE' ),
	);

	return $cached;
}

/**
 * Whether ?jdp_continent= is allowed (local/dev only; ignored with ?geoip).
 *
 * @return bool
 */
function jdpower_geotarget_dev_continent_override_allowed() {
	if ( ! empty( $_GET['geoip'] ) ) {
		return false;
	}

	if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
		return true;
	}

	if ( function_exists( 'wp_get_environment_type' ) ) {
		return in_array( wp_get_environment_type(), array( 'local', 'development' ), true );
	}

	return false;
}

/**
 * Dev-only continent override from ?jdp_continent= (NA, EU, OC, etc.).
 *
 * @return string Two-letter code or empty.
 */
function jdpower_geotarget_dev_continent_override_code() {
	if ( ! jdpower_geotarget_dev_continent_override_allowed() || ! isset( $_GET['jdp_continent'] ) ) {
		return '';
	}

	$override = sanitize_text_field( wp_unslash( (string) $_GET['jdp_continent'] ) );
	// Uppercase before stripping non-letters — lowercase "eu"/"oc" must not be removed by [^A-Z].
	return strtoupper( preg_replace( '/[^A-Za-z]/', '', $override ) );
}

/**
 * MaxMind continent code (NA, EU, AS, AF, SA, OC) or empty when unknown.
 *
 * @return string
 */
function jdpower_geotarget_get_continent_code() {
	static $resolved = null;

	if ( null !== $resolved ) {
		return $resolved;
	}

	$code = strtoupper( jdpower_geotarget_env( 'GEOIP_CITY_CONTINENT_CODE' ) );

	if ( '' === $code ) {
		$override = jdpower_geotarget_dev_continent_override_code();
		if ( '' !== $override ) {
			$code = $override;
		}
	}

	$resolved = $code;

	return $resolved;
}

/**
 * Resolve product_region term from a MaxMind continent code.
 *
 * Term slugs are expected to match the lowercase continent code (e.g. EU → eu, OC → oc).
 * If no matching term exists, returns null and callers fall back to the default region.
 *
 * @param string $continent_code Two-letter continent code (NA, EU, OC, etc.).
 * @return WP_Term|null
 */
function jdpower_product_region_term_for_continent( $continent_code ) {
	$slug = strtolower( preg_replace( '/[^A-Za-z]/', '', (string) $continent_code ) );
	if ( '' === $slug ) {
		return null;
	}

	$term = get_term_by( 'slug', $slug, 'product_region' );
	if ( $term instanceof WP_Term && ! is_wp_error( $term ) ) {
		return $term;
	}

	return null;
}

/**
 * Geo-detected product_region slug for the current visitor, or na fallback.
 *
 * @return string
 */
function jdpower_product_region_visitor_slug() {
	static $cached = null;

	if ( null !== $cached ) {
		return $cached;
	}

	$continent = jdpower_geotarget_get_continent_code();
	$term      = jdpower_product_region_term_for_continent( $continent );

	if ( $term instanceof WP_Term ) {
		$cached = $term->slug;
		return $cached;
	}

	// Dev override: use continent code as slug even when the term is missing (local testing).
	if ( '' !== $continent && '' !== jdpower_geotarget_dev_continent_override_code() ) {
		$slug = strtolower( preg_replace( '/[^A-Za-z]/', '', $continent ) );
		if ( '' !== $slug ) {
			$cached = $slug;
			return $cached;
		}
	}

	$cached = jdpower_product_region_default_slug();

	return $cached;
}

/**
 * Cache-bucket-safe geo data for wp_localize_script (continent + slugs only).
 *
 * @return array{continent: string, detectedRegionSlug: string, fallbackRegionSlug: string, detectedRegionLabel: string}
 */
function jdpower_geotarget_script_data() {
	$visitor_slug  = jdpower_product_region_visitor_slug();
	$fallback_slug = jdpower_product_region_default_slug();
	$visitor_term  = '' !== $visitor_slug ? get_term_by( 'slug', $visitor_slug, 'product_region' ) : false;
	$label         = ( $visitor_term instanceof WP_Term && ! is_wp_error( $visitor_term ) ) ? $visitor_term->name : '';

	return array(
		'continent'          => jdpower_geotarget_get_continent_code(),
		'detectedRegionSlug' => $visitor_slug,
		'fallbackRegionSlug' => $fallback_slug,
		'detectedRegionLabel' => $label,
	);
}
