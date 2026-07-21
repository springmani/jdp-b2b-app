<?php
/**
 * PHP file to use when rendering the block type on the server to show on the front end.
 *
 * The following variables are exposed to the file:
 *     $attributes (array): The block attributes.
 *     $content (string): The block default content.
 *     $block (WP_Block): The block instance.
 *
 * @see https://github.com/WordPress/gutenberg/blob/trunk/docs/reference-guides/block-api/block-metadata.md#render
 *
 * @phpcs:disable Squiz.Commenting.FileComment.MissingPackageTag
 */

use function AtlasSearch\Blocks\WordPress\filters_from_query_string;
use function AtlasSearch\Blocks\WordPress\get_query_string;

use const AtlasSearch\Blocks\WordPress\RANGE_SLIDER_PREFIX;

// Default attributes.
$attributes = wp_parse_args(
	$attributes,
	[
		'hasLabel'  => true,
		'align'     => '',
		'className' => '',
		'label'     => __( 'Range Label', 'wpengine-smart-search' ),
	]
);

// Wrapper attributes.
$wrapper_attributes = get_block_wrapper_attributes(
	[
		'class' => implode(
			' ',
			array_filter(
				[
					'wpengine-smart-range-slider-container',
				]
			)
		),
	]
);

$query_params = filters_from_query_string( get_query_string() ) ?? [];

$filters              = \AtlasSearch\Blocks\WordPress\extract_filters( $query_params );
$selected_data_source = \AtlasSearch\Blocks\WordPress\prefix_filter(
	$attributes['selectedDataSource'],
	RANGE_SLIDER_PREFIX
);
$range_filter_value   = $filters[ $selected_data_source ] ?? [];
$min                  = (int) $attributes['range']['min'];
$max                  = (int) $attributes['range']['max'];
$default_from         = (int) $attributes['range']['from'];
$default_to           = (int) $attributes['range']['to'];

if ( ! empty( $range_filter_value ) && is_array( $range_filter_value ) ) {
	$from = ( isset( $range_filter_value[0] ) && is_numeric(
		$range_filter_value[0]
	) ) ? (int) $range_filter_value[0] : $default_from;
	$to   = ( isset( $range_filter_value[1] ) && '' !== $range_filter_value[1] ) ? (int) $range_filter_value[1] : $default_to;
} elseif ( isset( $range_filter_value ) ) {
	$from = (int) $range_filter_value;
	$to   = $default_to;
}

if ( $from < $min || $from > $max ) {
	$from = $default_from;
}

if ( $to < $min || $to > $max ) {
	$to = $default_to;
}

if ( $from > $to ) {
	$from = $default_from;
	$to   = $default_to;
}

if ( isset( $from ) ) {
	$attributes['range']['from'] = $from;
}

if ( isset( $to ) ) {
	$attributes['range']['to'] = $to;
}

$context = wp_interactivity_data_wp_context(
	[
		'attributes' => $attributes,
		'value'      => '',
	]
);
require __DIR__ . '/render-components/render-slider.php';
