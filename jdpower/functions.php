<?php
/**
 * jdpower functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package jdpower
 */

if ( ! defined( 'THEME_VERSION' ) ) {
	// Replace the version number of the theme on each release.
	define( 'THEME_VERSION', '1.0.150' );
}

/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 * Note that this function is hooked into the after_setup_theme hook, which
 * runs before the init hook. The init hook is too late for some features, such
 * as indicating support for post thumbnails.
 */
function jdpower_setup() {
	// Add default post feed link in head (comment feed removed below).
	add_theme_support( 'automatic-feed-links' );

	/*
	* Let WordPress manage the document title.
	* By adding theme support, we declare that this theme does not use a
	* hard-coded <title> tag in the document head, and expect WordPress to
	* provide it for us.
	*/
	add_theme_support( 'title-tag' );

	/*
	* Enable support for Post Thumbnails on posts and pages.
	*
	* @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
	*/
	add_theme_support( 'post-thumbnails' );

	// Primary navigation and four footer column menus.
	register_nav_menus(
		array(
			'primary' => esc_html__( 'Primary', 'jdpower' ),
			'footer_column_1' => esc_html__( 'Footer column 1', 'jdpower' ),
			'footer_column_2' => esc_html__( 'Footer column 2', 'jdpower' ),
			'footer_column_3' => esc_html__( 'Footer column 3', 'jdpower' ),
			'footer_column_4' => esc_html__( 'Footer column 4', 'jdpower' ),
		)
	);

	// HTML5 markup for search form, gallery, etc. (comment form not used).
	add_theme_support(
		'html5',
		array(
			'search-form',
			'gallery',
			'caption',
			'style',
			'script',
		)
	);

	// Load theme styles in the block editor so front-end appearance matches.
	add_theme_support( 'editor-styles' );
	add_editor_style( array( 'style.css', 'editor-style.css' ) );
}
add_action( 'after_setup_theme', 'jdpower_setup' );

/**
 * Set global $content_width for WordPress core (not used by theme templates directly).
 */
function jdpower_content_width() {
	$GLOBALS['content_width'] = (int) apply_filters( 'jdpower_content_width', 1600 );
}
add_action( 'after_setup_theme', 'jdpower_content_width', 0 );

/**
 * Enqueue scripts and styles.
 */
function jdpower_scripts() {
	wp_enqueue_style( 'jdpower-style', get_stylesheet_uri(), array(), THEME_VERSION );
	wp_style_add_data( 'jdpower-style', 'rtl', 'replace' );

    wp_enqueue_script( 'jdpower-script', get_template_directory_uri() . '/js/script.js', array('jquery'), THEME_VERSION, true );
	wp_localize_script( 'jdpower-script', 'jdpowerGeo', jdpower_geotarget_script_data() );

    //Slick Slider
	#wp_enqueue_style( 'slick', get_stylesheet_directory_uri() .'/css/slick.css' );
	#wp_enqueue_style( 'slick-theme', get_stylesheet_directory_uri() .'/css/slick-theme.css' );
	#wp_enqueue_script( 'slick-scripts', get_stylesheet_directory_uri() . '/js/slick.min.js', array('jquery'), '1.0.0', TRUE );

	// Magnific Popup
	#wp_enqueue_script( 'magnific-popup-scripts', 'https://cdnjs.cloudflare.com/ajax/libs/magnific-popup.js/1.1.0/jquery.magnific-popup.min.js', array('jquery'), '1.1.0', TRUE );
}
add_action( 'wp_enqueue_scripts', 'jdpower_scripts' );

/**
 * Disable default WP features we do not use (emojis, comments, etc.).
 */
require get_template_directory() . '/inc/disable-features.php';

/**
 * Core Posts → "News" labels (admin menu, insight cards).
 */
require get_template_directory() . '/inc/post-news-labels.php';

/**
 * Insight card fallback images (Theme Settings → Insights).
 */
require get_template_directory() . '/inc/insight-card-fallbacks.php';

/**
 * Tiered insight queries (Related Insights + Featured Insights Dynamic By Type).
 */
require get_template_directory() . '/inc/insight-tiered-query.php';

/**
 * Custom template tags for this theme.
 */
require get_template_directory() . '/inc/template-tags.php';

/**
 * Social share for singular insight posts.
 */
require get_template_directory() . '/inc/insight-social-share.php';

/**
 * Taxonomy helpers (primary term) — used by Featured Insights cards and Insight Center.
 */
require_once get_template_directory() . '/inc/taxonomy-helpers.php';

/**
 * Event & Webinar single helpers
 */
require get_template_directory() . '/inc/event-webinar.php';

/**
 * Featured Insights card partial (defines card helpers for the block and singular “related insights”).
 */
require_once get_template_directory() . '/template-parts/partials/featured-insights-card.php';

/**
 * Polylang Pro — register theme strings (optional; no-op if Polylang inactive).
 */
require get_template_directory() . '/inc/polylang-strings.php';
require get_template_directory() . '/inc/polylang-language-switcher.php';

/**
 * Block editor overrides (categories, core blocks, Group wrapper, etc.).
 */
require_once get_template_directory() . '/inc/gutenberg-overrides.php';

/**
 * ACF Options, Blocks
 */
require_once get_template_directory() . '/inc/acf-config.php';

/**
 * WP Engine GeoTarget — continent → product_region mapping.
 */
require_once get_template_directory() . '/inc/geotarget.php';

/**
 * Regional mismatch popup — Theme Options + singular hierarchy CPTs.
 */
require_once get_template_directory() . '/inc/regional-content.php';

/**
 * Insight Center / Product Finder — shared filters + AJAX
 *
 * After adding or changing ACF-registered post types (acf-json/post_type_*.json), visit
 * Settings → Permalinks → Save once per environment to flush rewrite rules.
 */
require_once get_template_directory() . '/inc/post-filters.php';

/**
 * Polylang helpers for custom rewrite rules.
 */
require_once get_template_directory() . '/inc/polylang-rewrites.php';

/**
 * Site-wide /business/ URL prefix (all content except the homepage).
 */
require_once get_template_directory() . '/inc/business-url-prefix.php';

/**
 * Industries / Segments / Solutions — rewrites and permalinks
 */
require_once get_template_directory() . '/inc/industry-rewrites.php';

/**
 * Products — top-level permalinks and legacy /products/ redirects
 */
require_once get_template_directory() . '/inc/product-rewrites.php';

/**
 * Press release legacy URLs: /pr-id/{id}/ → single by meta `press_release_id`.
 */
require_once get_template_directory() . '/inc/press-release-id-redirect.php';

/**
 * Products admin list: column order
 */
require_once get_template_directory() . '/inc/admin-product-columns.php';

/**
 * Segments / Solutions admin list: relationship columns
 */
require_once get_template_directory() . '/inc/admin-segment-solution-columns.php';

/**
 * Search page: ?keyword= query param and page template Search (page-search.php).
 * (Before custom navigation so hub helpers exist for the primary menu walker.)
 */
require_once get_template_directory() . '/inc/search-page-routing.php';

/**
 * Custom Navigation Functionality
 */
require_once get_template_directory() . '/inc/custom-navigation-functionality.php';