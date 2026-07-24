<?php
/**
 * Core block tweaks and front-end render helpers (Group wrapper, etc.).
 *
 * @package jdpower
 */

/**
 * Hide core Buttons in favor of the theme CTA block.
 */
function jdpower_disallowed_blocks_remove_core_buttons( $allowed_block_types, $editor_context ) {
	$remove = array( 'core/buttons', 'core/button' );

	if ( true === $allowed_block_types ) {
		$registry            = WP_Block_Type_Registry::get_instance();
		$allowed_block_types = array_keys( $registry->get_all_registered() );
	}

	if ( ! is_array( $allowed_block_types ) ) {
		return $allowed_block_types;
	}

	return array_values( array_diff( $allowed_block_types, $remove ) );
}
add_filter( 'allowed_block_types_all', 'jdpower_disallowed_blocks_remove_core_buttons', 10, 2 );

/**
 * Core Columns: optional “reverse stack on mobile” style.
 */
function jdpower_register_block_styles() {
	register_block_style(
		'core/columns',
		array(
			'name'  => 'reverse-stack-mobile',
			'label' => __( 'Reverse stack on mobile', 'jdpower' ),
		)
	);
}
add_action( 'init', 'jdpower_register_block_styles' );

/**
 * Store parent block name on $parsed_block (see `jdpower_block_wrapper`).
 */
function jdpower_block_parent_name( $parsed_block, $source_block, $parent_block ) {
	if ( $parent_block instanceof WP_Block ) {
		$parsed_block['_jdpower_parent_name'] = $parent_block->name;
	}
	return $parsed_block;
}
add_filter( 'render_block_data', 'jdpower_block_parent_name', 10, 3 );

/**
 * Wrap direct children of `core/group` in `.core-block > .container`. Skips
 * `core/columns` nested in `core/column`. Does not wrap root-level blocks.
 */
function jdpower_block_wrapper( $block_content, $block ) {
	$name = $block['blockName'] ?? '';
	if ( '' === $name ) {
		return $block_content;
	}

	$parent_name = $block['_jdpower_parent_name'] ?? '';

	if ( 'core/columns' === $name && 'core/column' === $parent_name ) {
		return $block_content;
	}

	if ( 'core/group' === $parent_name ) {
		return '<div class="core-block"><div class="container">' . $block_content . '</div></div>';
	}

	return $block_content;
}
add_filter( 'render_block', 'jdpower_block_wrapper', 10, 2 );

/**
 * Limit core Group padding controls to top and bottom.
 */
function jdpower_group_padding_top_bottom_only( $args, $block_name ) {
	if ( 'core/group' !== $block_name || empty( $args['supports']['spacing'] ) ) {
		return $args;
	}
	$args['supports']['spacing']['padding'] = array( 'top', 'bottom' );
	return $args;
}
add_filter( 'register_block_type_args', 'jdpower_group_padding_top_bottom_only', 10, 2 );
