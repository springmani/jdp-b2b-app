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

use function AtlasSearch\Blocks\WordPress\convert_url_filter_to_data_source;
use function AtlasSearch\Blocks\WordPress\filters_from_query_string;
use function AtlasSearch\Blocks\WordPress\get_query_string;

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
$wrapper_attributes    = get_block_wrapper_attributes(
	[
		'class' => implode(
			' ',
			array_filter(
				[
					'wpengine-smart-active-filter-container',
				]
			)
		),
	]
);
$query_params          = filters_from_query_string( get_query_string() ) ?? [];
$query_params          = \AtlasSearch\Blocks\WordPress\extract_filters( $query_params );
$selected_data_sources = $attributes['selectedDataSources'] ?? [];

$filters = [];

if ( ! empty( $selected_data_sources ) ) {
	$selected_data_sources_names = array_keys( $selected_data_sources );

	foreach ( $query_params as $key => $value ) {
		$data_source = convert_url_filter_to_data_source( $key );

		if ( ! in_array( $data_source, $selected_data_sources_names ) ) {
			continue;
		}

		$filters[ $key ] = [
			'label'       => $selected_data_sources[ $data_source ]['label'] ?? '',
			'data_source' => $data_source,
			'value'       => $value,
		];
		$attributes['selectedDataSources'][ $data_source ]['filterName'] = $key;
	}
}

$filters_hidden_text = 'true' === ( $query_params['filtersHidden'] ?? '' ) ? 'Show Filters' : 'Hide Filters';
$context             = wp_interactivity_data_wp_context(
	[
		'attributes' => $attributes,
		'value'      => '',
	]
);
require __DIR__ . '/render-components/render-active-filters.php';
