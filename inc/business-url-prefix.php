<?php
/**
 * Site-wide /business/ URL prefix for all public content except the homepage.
 *
 * Public URL order: /business/{lang}/{path}/ (default language omits {lang} when Polylang hides it).
 * Incoming /business/… requests are rewritten to /{lang}/{path}/ before WordPress routing.
 *
 * After deploying, visit Settings → Permalinks → Save once per environment to flush rules.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'JDPOWER_URL_PREFIX' ) ) {
	define( 'JDPOWER_URL_PREFIX', 'business' );
}

/**
 * Public path prefix slug (no slashes).
 */
function jdpower_url_prefix() {
	return JDPOWER_URL_PREFIX;
}

/**
 * Polylang language slugs when available.
 *
 * @return string[]
 */
function jdpower_get_pll_language_slugs() {
	if ( ! function_exists( 'jdpower_pll_is_active' ) || ! jdpower_pll_is_active() ) {
		return array();
	}

	return pll_languages_list( array( 'fields' => 'slug' ) );
}

/**
 * Request path relative to the site home, without leading/trailing slashes.
 *
 * @return string
 */
function jdpower_get_request_path() {
	$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? (string) wp_unslash( $_SERVER['REQUEST_URI'] ) : '';
	$path        = (string) parse_url( $request_uri, PHP_URL_PATH );
	$path        = rawurldecode( $path );

	$home_path = trim( (string) parse_url( home_url( '/' ), PHP_URL_PATH ), '/' );
	$trimmed   = trim( $path, '/' );

	if ( $home_path && ( $trimmed === $home_path || str_starts_with( $trimmed, $home_path . '/' ) ) ) {
		$trimmed = ltrim( substr( $trimmed, strlen( $home_path ) ), '/' );
	}

	return untrailingslashit( $trimmed );
}

/**
 * Original request path captured before stripping (for canonical redirects).
 *
 * @return string
 */
function jdpower_get_original_request_path() {
	global $jdpower_request_path_original;

	if ( is_string( $jdpower_request_path_original ) ) {
		return $jdpower_request_path_original;
	}

	return jdpower_get_request_path();
}

/**
 * Strip a leading path segment when present.
 *
 * @param string $path   Relative path.
 * @param string $prefix Segment to remove from the start.
 * @return string
 */
function jdpower_strip_leading_path_segment( $path, $prefix ) {
	$path   = untrailingslashit( (string) $path );
	$prefix = (string) $prefix;

	if ( $path === $prefix ) {
		return '';
	}

	if ( str_starts_with( $path, $prefix . '/' ) ) {
		return substr( $path, strlen( $prefix ) + 1 );
	}

	return $path;
}

/**
 * Extract a leading Polylang language slug from a relative path.
 *
 * @param string $path Relative path.
 * @return array{lang: string, remainder: string}
 */
function jdpower_extract_leading_language_slug( $path ) {
	$path = untrailingslashit( (string) $path );

	foreach ( jdpower_get_pll_language_slugs() as $lang ) {
		$lang = (string) $lang;

		if ( $path === $lang ) {
			return array(
				'lang'      => $lang,
				'remainder' => '',
			);
		}

		if ( str_starts_with( $path, $lang . '/' ) ) {
			return array(
				'lang'      => $lang,
				'remainder' => substr( $path, strlen( $lang ) + 1 ),
			);
		}
	}

	return array(
		'lang'      => '',
		'remainder' => $path,
	);
}

/**
 * Parse a relative path into language + content segments (business prefix agnostic).
 *
 * Supports both /business/{lang}/{content} and legacy /{lang}/business/{content}.
 *
 * @param string $path Relative path.
 * @return array{lang: string, content: string}
 */
function jdpower_parse_business_path_components( $path ) {
	$path   = untrailingslashit( (string) $path );
	$prefix = jdpower_url_prefix();

	$leading = jdpower_extract_leading_language_slug( $path );

	// Legacy order: /{lang}/business/{content}
	if ( $leading['lang'] && ( $leading['remainder'] === $prefix || str_starts_with( $leading['remainder'], $prefix . '/' ) ) ) {
		return array(
			'lang'    => $leading['lang'],
			'content' => jdpower_strip_leading_path_segment( $leading['remainder'], $prefix ),
		);
	}

	// Canonical order: /business/{lang?}/{content}
	$after_business = jdpower_strip_leading_path_segment( $path, $prefix );
	if ( $after_business !== $path ) {
		$inner = jdpower_extract_leading_language_slug( $after_business );

		return array(
			'lang'    => $inner['lang'],
			'content' => $inner['remainder'],
		);
	}

	// Unprefixed path: /{lang?}/{content}
	return array(
		'lang'    => $leading['lang'],
		'content' => $leading['lang'] ? $leading['remainder'] : $path,
	);
}

/**
 * Build the public /business/… path from language + content segments.
 *
 * @param string $lang    Language slug or empty.
 * @param string $content Remaining path segments.
 * @return string
 */
function jdpower_build_business_prefixed_path( $lang, $content ) {
	$segments = array( jdpower_url_prefix() );
	$lang     = (string) $lang;
	$content  = (string) $content;

	$include_lang = ( '' !== $lang );
	if ( $include_lang && function_exists( 'jdpower_pll_hide_default' ) && jdpower_pll_hide_default() ) {
		$default = jdpower_pll_default_language();
		if ( $lang === $default ) {
			$include_lang = false;
		}
	}

	if ( $include_lang ) {
		$segments[] = $lang;
	}

	if ( '' !== $content ) {
		$segments[] = $content;
	}

	return implode( '/', $segments );
}

/**
 * Whether a relative path includes a /business/ segment (any supported order).
 *
 * @param string $path Path without leading/trailing slashes.
 */
function jdpower_path_has_business_prefix( $path ) {
	$path   = untrailingslashit( (string) $path );
	$prefix = jdpower_url_prefix();

	if ( $path === $prefix || str_starts_with( $path, $prefix . '/' ) ) {
		return true;
	}

	foreach ( jdpower_get_pll_language_slugs() as $lang ) {
		$lang = (string) $lang;
		if ( $path === $lang . '/' . $prefix || str_starts_with( $path, $lang . '/' . $prefix . '/' ) ) {
			return true;
		}
	}

	return false;
}

/**
 * Whether a relative path is the homepage (root or language home only).
 *
 * @param string $path Path without leading/trailing slashes.
 */
function jdpower_is_front_page_path( $path ) {
	$path = untrailingslashit( (string) $path );

	if ( '' === $path ) {
		return true;
	}

	return in_array( $path, jdpower_get_pll_language_slugs(), true );
}

/**
 * Convert public /business/… path components to Polylang's internal /{lang}/{content} route path.
 *
 * @param string $lang    Language slug or empty.
 * @param string $content Remaining path segments.
 * @return string
 */
function jdpower_business_path_to_internal_route( $lang, $content ) {
	$lang    = (string) $lang;
	$content = (string) $content;

	if ( '' !== $lang && '' !== $content ) {
		return $lang . '/' . $content;
	}

	if ( '' !== $lang ) {
		return $lang;
	}

	return $content;
}

/**
 * Strip /business/ from a public path for WordPress / Polylang routing.
 *
 * @param string $path Path without leading/trailing slashes.
 * @return string
 */
function jdpower_strip_business_from_path( $path ) {
	if ( ! jdpower_path_has_business_prefix( $path ) ) {
		return $path;
	}

	$components = jdpower_parse_business_path_components( $path );

	return jdpower_business_path_to_internal_route( $components['lang'], $components['content'] );
}

/**
 * Rewrite REQUEST_URI so WordPress routes against the stripped path.
 *
 * @param string $new_path Relative path without leading/trailing slashes.
 */
function jdpower_rewrite_request_uri_path( $new_path ) {
	$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? (string) wp_unslash( $_SERVER['REQUEST_URI'] ) : '';
	$query       = parse_url( $request_uri, PHP_URL_QUERY );

	$home_path = trim( (string) parse_url( home_url( '/' ), PHP_URL_PATH ), '/' );
	$base      = $home_path ? '/' . $home_path : '';

	$new_path = untrailingslashit( (string) $new_path );
	if ( '' === $new_path ) {
		$new_uri = trailingslashit( $base ? $base : '/' );
	} else {
		$new_uri = user_trailingslashit( $base . '/' . $new_path );
	}

	if ( is_string( $query ) && '' !== $query ) {
		$new_uri .= '?' . $query;
	}

	$_SERVER['REQUEST_URI'] = $new_uri;
}

/**
 * Strip /business/ from incoming front-end requests before rewrite matching.
 *
 * Must run on wp_loaded (not plugins_loaded): the theme loads after plugins_loaded,
 * so a plugins_loaded hook registered from functions.php would never fire.
 */
function jdpower_strip_business_url_prefix_from_request() {
	if ( is_admin() || wp_doing_cron() || wp_doing_ajax() ) {
		return;
	}

	if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
		return;
	}

	global $jdpower_request_path_original;

	$jdpower_request_path_original = jdpower_get_request_path();

	if ( ! jdpower_path_has_business_prefix( $jdpower_request_path_original ) ) {
		return;
	}

	jdpower_rewrite_request_uri_path(
		jdpower_strip_business_from_path( $jdpower_request_path_original )
	);
}
add_action( 'wp_loaded', 'jdpower_strip_business_url_prefix_from_request', 0 );

/**
 * Install path from the WordPress "Home" URL (not Polylang's filtered home_url()).
 *
 * @return string Path without leading/trailing slashes (may be empty).
 */
function jdpower_get_install_path() {
	$home = (string) get_option( 'home' );
	if ( '' === $home ) {
		$home = (string) home_url( '/' );
	}

	$path = (string) parse_url( $home, PHP_URL_PATH );

	return untrailingslashit( rawurldecode( $path ) );
}

/**
 * Path relative to site home extracted from a full URL.
 *
 * @param string $url Full URL.
 * @return string
 */
function jdpower_url_to_relative_path( $url ) {
	if ( ! is_string( $url ) || '' === $url ) {
		return '';
	}

	$url_path  = rawurldecode( (string) parse_url( $url, PHP_URL_PATH ) );
	$home_path = jdpower_get_install_path();

	if ( $home_path ) {
		$prefix = '/' . $home_path;
		if ( $url_path === $prefix || str_starts_with( $url_path, $prefix . '/' ) ) {
			$url_path = substr( $url_path, strlen( $prefix ) );
		}
	}

	return untrailingslashit( ltrim( $url_path, '/' ) );
}

/**
 * Append query string and fragment from one URL onto another.
 *
 * @param string $new_url Base URL.
 * @param string $source  Source URL to copy from.
 * @return string
 */
function jdpower_merge_url_query_and_fragment( $new_url, $source ) {
	$parsed = wp_parse_url( $source );

	if ( ! empty( $parsed['query'] ) ) {
		$new_url .= '?' . $parsed['query'];
	}
	if ( ! empty( $parsed['fragment'] ) ) {
		$new_url .= '#' . $parsed['fragment'];
	}

	return $new_url;
}

/**
 * Whether a URL should receive the business prefix.
 *
 * @param string $url Full URL.
 */
function jdpower_should_prefix_url( $url ) {
	if ( ! is_string( $url ) || '' === $url ) {
		return false;
	}

	$path = jdpower_url_to_relative_path( $url );
	if ( jdpower_is_front_page_path( $path ) ) {
		return false;
	}

	$components   = jdpower_parse_business_path_components( $path );
	$canonical    = jdpower_build_business_prefixed_path( $components['lang'], $components['content'] );
	if ( untrailingslashit( $path ) === untrailingslashit( $canonical ) ) {
		return false;
	}

	$excluded_prefixes = array(
		'wp-admin',
		'wp-json',
		'wp-content',
		'wp-includes',
		'wp-login.php',
		'pr-id',
	);

	foreach ( $excluded_prefixes as $excluded ) {
		if ( $path === $excluded || str_starts_with( $path, $excluded . '/' ) ) {
			return false;
		}
	}

	if ( preg_match( '/\.[a-z0-9]{1,8}$/i', $path ) ) {
		return false;
	}

	return true;
}

/**
 * Normalize a full URL to /business/{lang}/{path}/ order.
 *
 * @param string $url Full URL.
 * @return string
 */
function jdpower_add_business_prefix_to_url( $url ) {
	if ( ! jdpower_should_prefix_url( $url ) ) {
		return $url;
	}

	$path = jdpower_url_to_relative_path( $url );
	if ( '' === $path ) {
		return $url;
	}

	$components = jdpower_parse_business_path_components( $path );
	$new_path   = jdpower_build_business_prefixed_path( $components['lang'], $components['content'] );
	$new_url    = home_url( user_trailingslashit( $new_path ) );

	return jdpower_merge_url_query_and_fragment( $new_url, $url );
}

/**
 * Build a home URL with the business prefix applied to the given path.
 *
 * @param string $path Relative path (may include trailing slash semantics via user_trailingslashit).
 * @return string
 */
function jdpower_prefixed_home_url( $path ) {
	return jdpower_add_business_prefix_to_url( home_url( user_trailingslashit( ltrim( (string) $path, '/' ) ) ) );
}

/**
 * Current-language front page URL with the /business/ prefix when applicable.
 *
 * Uses get_permalink() for static front pages so page_link filters apply (same
 * as the language switcher). Falls back to pll_home_url() for blog-style homes.
 *
 * @return string
 */
function jdpower_get_home_url() {
	$page_on_front = (int) get_option( 'page_on_front' );

	if ( $page_on_front > 0 ) {
		$page_id = $page_on_front;

		if ( function_exists( 'pll_get_post' ) ) {
			$lang = function_exists( 'pll_current_language' ) ? pll_current_language( 'slug' ) : '';
			if ( $lang ) {
				$translated = (int) pll_get_post( $page_on_front, $lang );
				if ( $translated > 0 ) {
					$page_id = $translated;
				}
			}
		}

		$permalink = get_permalink( $page_id );
		if ( is_string( $permalink ) && '' !== $permalink ) {
			return $permalink;
		}
	}

	$url = function_exists( 'pll_home_url' ) ? pll_home_url() : home_url( '/' );

	return jdpower_add_business_prefix_to_url( $url );
}

/**
 * Append /business/ to generated permalinks (after Polylang adjusts language prefixes).
 *
 * @param string $url Generated URL.
 * @return string
 */
function jdpower_filter_permalink_with_business_prefix( $url ) {
	return jdpower_add_business_prefix_to_url( (string) $url );
}

add_filter( 'post_link', 'jdpower_filter_permalink_with_business_prefix', 999 );
add_filter( 'page_link', 'jdpower_filter_permalink_with_business_prefix', 999 );
add_filter( '_get_page_link', 'jdpower_filter_permalink_with_business_prefix', 999 );
add_filter( 'post_type_link', 'jdpower_filter_permalink_with_business_prefix', 999 );
add_filter( 'term_link', 'jdpower_filter_permalink_with_business_prefix', 999 );
add_filter( 'post_type_archive_link', 'jdpower_filter_permalink_with_business_prefix', 999 );
add_filter( 'pll_post_type_link', 'jdpower_filter_permalink_with_business_prefix', 999 );
add_filter( 'pll_term_link', 'jdpower_filter_permalink_with_business_prefix', 999 );
add_filter( 'pll_the_language_link', 'jdpower_filter_permalink_with_business_prefix', 999 );

/**
 * 301 redirect legacy /{lang}/business/… URLs to /business/{lang}/….
 */
function jdpower_redirect_legacy_lang_before_business_urls() {
	if ( is_admin() || wp_doing_ajax() || wp_doing_cron() ) {
		return;
	}

	if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
		return;
	}

	$path   = jdpower_get_original_request_path();
	$prefix = jdpower_url_prefix();

	foreach ( jdpower_get_pll_language_slugs() as $lang ) {
		$lang   = (string) $lang;
		$legacy = $lang . '/' . $prefix;

		if ( $path !== $legacy && ! str_starts_with( $path, $legacy . '/' ) ) {
			continue;
		}

		$components = jdpower_parse_business_path_components( $path );
		$target     = home_url(
			user_trailingslashit(
				jdpower_build_business_prefixed_path( $components['lang'], $components['content'] )
			)
		);

		wp_safe_redirect( $target, 301 );
		exit;
	}
}
add_action( 'template_redirect', 'jdpower_redirect_legacy_lang_before_business_urls', 0 );

/**
 * Redirect unprefixed content URLs to their /business/ canonical form.
 */
function jdpower_redirect_unprefixed_urls_to_business() {
	if ( is_admin() || wp_doing_ajax() || wp_doing_cron() ) {
		return;
	}

	if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
		return;
	}

	$path = jdpower_get_original_request_path();

	if ( jdpower_is_front_page_path( $path ) || jdpower_path_has_business_prefix( $path ) ) {
		return;
	}

	if ( '' === $path ) {
		return;
	}

	$excluded_prefixes = array(
		'wp-admin',
		'wp-json',
		'wp-content',
		'wp-includes',
		'wp-login.php',
		'pr-id',
	);

	foreach ( $excluded_prefixes as $excluded ) {
		if ( $path === $excluded || str_starts_with( $path, $excluded . '/' ) ) {
			return;
		}
	}

	if ( preg_match( '/\.[a-z0-9]{1,8}$/i', $path ) ) {
		return;
	}

	$target  = jdpower_add_business_prefix_to_url( home_url( user_trailingslashit( $path ) ) );
	$current = ( is_ssl() ? 'https://' : 'http://' ) . ( $_SERVER['HTTP_HOST'] ?? '' ) . ( $_SERVER['REQUEST_URI'] ?? '' );

	if ( untrailingslashit( $target ) === untrailingslashit( $current ) ) {
		return;
	}

	wp_safe_redirect( $target, 301 );
	exit;
}
add_action( 'template_redirect', 'jdpower_redirect_unprefixed_urls_to_business', 1 );

/**
 * Prevent WordPress canonical redirects from stripping the /business/ prefix.
 *
 * @param string|false $redirect_url  Canonical redirect target.
 * @param string       $requested_url Requested URL.
 * @return string|false
 */
function jdpower_preserve_business_prefix_in_canonical_redirect( $redirect_url, $requested_url ) {
	unset( $requested_url );

	if ( ! is_string( $redirect_url ) || '' === $redirect_url ) {
		return $redirect_url;
	}

	$requested_path = jdpower_get_original_request_path();
	if ( ! jdpower_path_has_business_prefix( $requested_path ) ) {
		return $redirect_url;
	}

	$redirect_path = jdpower_url_to_relative_path( $redirect_url );
	if ( jdpower_path_has_business_prefix( $redirect_path ) ) {
		return $redirect_url;
	}

	return false;
}
add_filter( 'redirect_canonical', 'jdpower_preserve_business_prefix_in_canonical_redirect', 10, 2 );

/**
 * Prefix Yoast sub-sitemap URLs in the root sitemap index (page-sitemap.xml, post-sitemap.xml, etc.).
 *
 * Content URLs inside those sitemaps already use /business/ via permalink filters; only the
 * index child loc values need updating. Prefixed requests are stripped before routing.
 *
 * @param array<int, array<string, mixed>> $links Sitemap index entries.
 * @return array<int, array<string, mixed>>
 */
function jdpower_route_yoast_sub_sitemaps_to_business_path( $links ) {
	if ( empty( $links ) || ! is_array( $links ) ) {
		return $links;
	}

	$home     = trailingslashit( home_url( '/' ) );
	$prefixed = trailingslashit( home_url( '/' . jdpower_url_prefix() . '/' ) );

	foreach ( $links as $key => $link ) {
		if ( ! is_array( $link ) || empty( $link['loc'] ) || ! is_string( $link['loc'] ) ) {
			continue;
		}

		$path = jdpower_url_to_relative_path( $link['loc'] );
		if ( jdpower_path_has_business_prefix( $path ) ) {
			continue;
		}

		$links[ $key ]['loc'] = str_replace( $home, $prefixed, $link['loc'] );
	}

	return $links;
}
add_filter( 'wpseo_sitemap_index_links', 'jdpower_route_yoast_sub_sitemaps_to_business_path' );
