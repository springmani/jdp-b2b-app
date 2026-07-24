<?php
/**
 * Polylang string registration for hardcoded copy in theme templates.
 *
 * Only register strings that literally appear in PHP (via jdpower_pll__( '…' ));
 * the registered text must match the argument to jdpower_pll__ exactly.
 *
 * @package jdpower
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Language slug for the page being viewed.
 *
 * On /business/{lang}/ URLs Polylang’s string MO may not match the rendered page;
 * prefer the queried post’s language, then the language in the original request path.
 *
 * @return string
 */
function jdpower_pll_viewing_language_slug() {
	if ( is_singular() && function_exists( 'pll_get_post_language' ) ) {
		$post_lang = (string) pll_get_post_language( get_queried_object_id(), 'slug' );
		if ( '' !== $post_lang ) {
			return $post_lang;
		}
	}

	global $jdpower_request_path_original;
	if ( ! empty( $jdpower_request_path_original ) && function_exists( 'jdpower_parse_business_path_components' ) ) {
		$components = jdpower_parse_business_path_components( $jdpower_request_path_original );
		$path_lang  = isset( $components['lang'] ) ? (string) $components['lang'] : '';
		if ( '' !== $path_lang && function_exists( 'pll_languages_list' ) && in_array( $path_lang, pll_languages_list(), true ) ) {
			return $path_lang;
		}
	}

	return function_exists( 'pll_current_language' ) ? (string) pll_current_language() : '';
}

/**
 * Return a string translated with Polylang when available, or the default text.
 *
 * @param string $string Default text (must match pll_register_string’s second argument).
 * @return string
 */
function jdpower_pll__( $string ) {
	if ( ! function_exists( 'pll__' ) ) {
		return $string;
	}

	$lang = jdpower_pll_viewing_language_slug();
	if ( '' !== $lang && function_exists( 'pll_translate_string' ) ) {
		static $loaded = array();

		if ( empty( $loaded[ $lang ] ) && function_exists( 'PLL' ) && PLL() instanceof PLL_Frontend ) {
			PLL()->load_strings_translations( $lang );
			$loaded[ $lang ] = true;
		}

		return pll_translate_string( $string, $lang );
	}

	return pll__( $string );
}

/**
 * Build “Explore …” card CTA copy; skip the prefix when the override already includes it.
 *
 * @param string $target Page title or CTA override text.
 * @return string Empty when target is blank.
 */
function jdpower_format_explore_cta( $target ) {
	$target = trim( (string) $target );
	if ( '' === $target ) {
		return '';
	}

	$explore_prefix = jdpower_pll__( 'Explore' );

	// Overrides may include the English source prefix; strip before applying translation.
	$english_pattern = '/^Explore(?:\s+|$|[:\-–—])/iu';
	if ( preg_match( $english_pattern, $target ) ) {
		$target = trim( preg_replace( $english_pattern, '', $target, 1 ) );
		if ( '' === $target ) {
			return '';
		}
	}

	if ( '' !== $explore_prefix ) {
		$pattern = '/^' . preg_quote( $explore_prefix, '/' ) . '(?:\s|$|[:\-–—])/iu';
		if ( preg_match( $pattern, $target ) ) {
			return $target;
		}
	}

	// Only “Explore” is translated in Polylang; the title/override is dynamic CMS content.
	return $explore_prefix . ' ' . $target;
}

/**
 * Register strings for Polylang (Languages → Translations → String translations).
 */
function jdpower_register_polylang_strings() {
	if ( ! function_exists( 'pll_register_string' ) ) {
		return;
	}

	$group = 'JDPower Theme';

	// Name = Polylang admin label; string must match jdpower_pll__() in theme PHP.
	$string_pairs = array(
		'explore_prefix'                 => 'Explore',
		'featured_solutions_all_regions' => 'All regions',
		'related_products_learn_more' => 'Learn More',
		'related_insights_heading' => 'Related Insights',
		'event_webinar_details_eyebrow' => 'Event details',
		'leaders_download_bio' => 'Download Bio',
		'leaders_open_bio' => 'Open biography for %s',
		'pf_loading'                   => 'Loading…',
	);

	foreach ( $string_pairs as $name => $string ) {
		pll_register_string( $name, $string, $group );
	}
}
add_action( 'init', 'jdpower_register_polylang_strings' );
