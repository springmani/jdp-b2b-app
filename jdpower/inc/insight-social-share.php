<?php
/**
 * Social share links for singular insight posts.
 *
 * @package jdpower
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether the current request is a singular insight post type.
 *
 * @param int|null $post_id Post ID; defaults to current post.
 * @return bool
 */
function jdpower_is_insight_singular( $post_id = null ) {
	if ( ! is_singular() ) {
		return false;
	}

	$post_id   = null === $post_id ? get_the_ID() : (int) $post_id;
	$post_type = $post_id > 0 ? get_post_type( $post_id ) : '';

	$insight_types = function_exists( 'jdpower_post_filters_insight_post_type_slugs' )
		? jdpower_post_filters_insight_post_type_slugs()
		: array();

	return is_string( $post_type ) && in_array( $post_type, $insight_types, true );
}

/**
 * Share destinations for a post.
 *
 * @param int|null $post_id Post ID; defaults to current post.
 * @return array<int, array{id: string, label: string, url: string, target: string, rel: string}>
 */
function jdpower_insight_social_share_items( $post_id = null ) {
	$post_id = null === $post_id ? get_the_ID() : (int) $post_id;
	if ( $post_id <= 0 ) {
		return array();
	}

	$permalink = get_permalink( $post_id );
	if ( ! $permalink ) {
		return array();
	}

	$share_url   = rawurlencode( $permalink );
	$share_title = rawurlencode( wp_strip_all_tags( get_the_title( $post_id ) ) );

	return array(
		array(
			'id'     => 'facebook',
			'label'  => __( 'Share on Facebook', 'jdpower' ),
			'url'    => 'https://www.facebook.com/sharer/sharer.php?u=' . $share_url,
			'target' => '_blank',
			'rel'    => 'noopener noreferrer',
		),
		array(
			'id'     => 'x',
			'label'  => __( 'Share on X', 'jdpower' ),
			'url'    => 'https://twitter.com/intent/tweet?url=' . $share_url . '&text=' . $share_title,
			'target' => '_blank',
			'rel'    => 'noopener noreferrer',
		),
		array(
			'id'     => 'linkedin',
			'label'  => __( 'Share on LinkedIn', 'jdpower' ),
			'url'    => 'https://www.linkedin.com/sharing/share-offsite/?url=' . $share_url,
			'target' => '_blank',
			'rel'    => 'noopener noreferrer',
		),
		array(
			'id'     => 'email',
			'label'  => __( 'Share by email', 'jdpower' ),
			'url'    => 'mailto:?subject=' . $share_title . '&body=' . $share_url,
			'target' => '',
			'rel'    => '',
		),
	);
}

/**
 * Inline SVG icon for a share network.
 *
 * @param string $network facebook|x|linkedin|email.
 * @return string Safe SVG markup or empty string.
 */
function jdpower_insight_social_share_icon_svg( $network ) {
	$icons = array(
		'facebook' => '<svg class="insight-social-share__icon" focusable="false" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32"><path fill="currentColor" d="M18.667 12h4V8h-4c-2.933 0-5.333 2.4-5.333 5.333V16H9.333v4h4v9.333h4V20h4v-4h-4v-2.667c0-0.8 0.533-1.333 1.333-1.333z"/></svg>',
		'x'        => '<svg class="insight-social-share__icon" focusable="false" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32"><g transform="translate(16 16) scale(1.092) translate(-12 -12)"><path fill="currentColor" d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></g></svg>',
		'linkedin' => '<svg class="insight-social-share__icon" focusable="false" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32"><path fill="currentColor" d="M6.227 12.61h4.19v13.48h-4.19zm2.095-6.7a2.43 2.43 0 0 1 0 4.86c-1.344 0-2.428-1.09-2.428-2.43s1.084-2.43 2.428-2.43m4.72 6.7h4.02v1.84h.058c.56-1.058 1.927-2.176 3.965-2.176 4.238 0 5.02 2.792 5.02 6.42v7.395h-4.183v-6.56c0-1.564-.03-3.574-2.178-3.574-2.18 0-2.514 1.7-2.514 3.46v6.668h-4.187z"/></svg>',
		'email'    => '<svg class="insight-social-share__icon" focusable="false" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="5" y="9" width="22" height="14" rx="1"/><path d="M5 10l11 8.5L27 10"/></svg>',
	);

	$key = sanitize_key( (string) $network );

	return isset( $icons[ $key ] ) ? $icons[ $key ] : '';
}

/**
 * Social share nav for insight singles.
 *
 * @param string $placement `inline` (mobile meta) or `sticky` (desktop column).
 */
function jdpower_the_insight_social_share( $placement = 'inline' ) {
	if ( ! jdpower_is_insight_singular() ) {
		return;
	}

	get_template_part(
		'template-parts/partials/insight-social',
		'share',
		array(
			'placement' => $placement,
		)
	);
}

/**
 * Opens the insight single layout wrapper (sticky share column + main column).
 */
function jdpower_the_insight_single_layout_open() {
	if ( ! jdpower_is_insight_singular() ) {
		return;
	}

	echo '<div class="insight-single-layout">';
	jdpower_the_insight_social_share( 'sticky' );
	echo '<div class="insight-single-layout__main">';
}

/**
 * Closes the insight single layout wrapper.
 */
function jdpower_the_insight_single_layout_close() {
	if ( ! jdpower_is_insight_singular() ) {
		return;
	}

	echo '</div></div>';
}

/**
 * Entry meta row: mobile share + published date (insight singles).
 */
function jdpower_the_insight_entry_meta() {
	if ( ! jdpower_is_insight_singular() ) {
		jdpower_posted_on();
		return;
	}

	get_template_part( 'template-parts/partials/insight', 'entry-meta' );
}
