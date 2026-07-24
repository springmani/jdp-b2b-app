<?php
/**
 * Polylang language switcher — slug labels (EN / ES) and header icon.
 *
 * @package jdpower
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Nav-menu language switcher options from Polylang (_pll_menu_item meta).
 *
 * @param array<string, mixed> $args Switcher args.
 * @return bool
 */
function jdpower_pll_is_nav_menu_switcher_args( $args ) {
	return is_array( $args )
		&& isset( $args['dropdown'] )
		&& array_key_exists( 'hide_if_no_translation', $args );
}

/**
 * Use language slug (en → EN) instead of full name in the nav menu switcher.
 *
 * @param array<string, mixed> $args Polylang switcher args.
 * @return array<string, mixed>
 */
function jdpower_pll_nav_menu_switcher_args( $args ) {
	if ( ! jdpower_pll_is_nav_menu_switcher_args( $args ) ) {
		return $args;
	}

	$args['display_names_as'] = 'slug';
	$args['show_flags']       = 0;

	return $args;
}
add_filter( 'pll_the_languages_args', 'jdpower_pll_nav_menu_switcher_args' );

/**
 * Uppercase language slug for display (en → EN).
 *
 * @param string $label Language label or slug.
 * @return string
 */
function jdpower_pll_format_language_switcher_label( $label ) {
	$label = wp_strip_all_tags( (string) $label );

	return strtoupper( $label );
}

/**
 * Inline language icon SVG for the switcher trigger.
 *
 * @return string
 */
function jdpower_pll_get_language_switcher_icon() {
	static $icon = null;

	if ( null !== $icon ) {
		return $icon;
	}

	$path = get_template_directory() . '/images/language-icon.svg';
	if ( ! is_readable( $path ) ) {
		$icon = '';
		return $icon;
	}

	$svg = file_get_contents( $path );
	if ( false !== $svg && '' !== $svg ) {
		$svg  = str_replace( 'fill="white"', 'fill="currentColor"', $svg );
		$icon = '<span class="jdpower-lang-switcher__icon" aria-hidden="true">' . $svg . '</span>';
	} else {
		$icon = '';
	}

	return $icon;
}

/**
 * Parent language switcher trigger: icon + slug label.
 *
 * @param object $item       Menu item.
 * @param string $attributes Anchor attribute string.
 * @param object $args       wp_nav_menu() args.
 * @return string
 */
function jdpower_pll_render_language_switcher_trigger( $item, $attributes, $args ) {
	$label = jdpower_pll_format_language_switcher_label( $item->title );
	$icon  = jdpower_pll_get_language_switcher_icon();

	$output  = isset( $args->before ) ? $args->before : '';
	$output .= '<a class="jdpower-lang-switcher-trigger"' . $attributes . '>';
	$output .= $icon;
	$output .= '<span class="jdpower-lang-switcher-label">' . esc_html( $label ) . '</span>';
	$output .= '</a>';
	$output .= isset( $args->after ) ? $args->after : '';

	return $output;
}

/**
 * Uppercase slug labels for switcher parent and dropdown items.
 *
 * @param array $items Menu items.
 * @return array
 */
function jdpower_pll_format_nav_menu_switcher_titles( $items ) {
	if ( is_admin() || ! is_array( $items ) ) {
		return $items;
	}

	foreach ( $items as $item ) {
		if ( empty( $item->classes ) || ! is_array( $item->classes ) ) {
			continue;
		}

		$is_switcher = in_array( 'pll-parent-menu-item', $item->classes, true )
			|| in_array( 'lang-item', $item->classes, true );

		if ( ! $is_switcher ) {
			continue;
		}

		$item->title = esc_html( jdpower_pll_format_language_switcher_label( $item->title ) );
	}

	return $items;
}
add_filter( 'wp_nav_menu_objects', 'jdpower_pll_format_nav_menu_switcher_titles', 25 );
