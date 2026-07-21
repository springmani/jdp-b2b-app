<?php
/**
 * Disable default WordPress features we do not use.
 * Keeps functions.php focused on theme setup and enqueue.
 *
 * @package jdpower
 */

// -----------------------------------------------------------------------------
// Emojis
// -----------------------------------------------------------------------------

/**
 * Remove WordPress emoji scripts and styles (reduces bloat; modern browsers handle emoji natively).
 */
function jdpower_disable_emojis() {
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'admin_print_styles', 'print_emoji_styles' );
	remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
	remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
	remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
}
add_action( 'init', 'jdpower_disable_emojis' );

/**
 * Remove emoji from TinyMCE (block/classic editor).
 */
function jdpower_disable_emojis_tinymce( $plugins ) {
	return is_array( $plugins ) ? array_diff( $plugins, array( 'wpemoji' ) ) : array();
}
add_filter( 'tiny_mce_plugins', 'jdpower_disable_emojis_tinymce' );

// -----------------------------------------------------------------------------
// Comments (disable comments on the front end; keep admin UI/endpoints; remove comments-feed <link> tags from <head>)
// -----------------------------------------------------------------------------
add_filter( 'comments_open', '__return_false', 20, 2 );
add_filter( 'pings_open', '__return_false', 20, 2 );
add_filter( 'comments_array', '__return_empty_array', 10, 2 );
add_filter( 'feed_links_show_comments_feed', '__return_false' );
