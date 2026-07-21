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

declare(strict_types=1);

use function AtlasSearch\Blocks\WordPress\extract_filters;
// Default attributes.
use function AtlasSearch\Blocks\WordPress\filters_from_query_string;
use function AtlasSearch\Blocks\WordPress\get_available_post_types;
use function AtlasSearch\Blocks\WordPress\get_query_string;
use function AtlasSearch\Blocks\WordPress\get_taxonomy_terms;

use const AtlasSearch\Blocks\WordPress\TAXONOMY_MAPPINGS;

$attributes = wp_parse_args(
	$attributes,
	[
		'hasLabel'    => true,
		'label'       => __( 'Dropdown', 'wpengine-smart-search' ),
		'placeholder' => __( 'Dropdown...', 'wpengine-smart-search' ),
	]
);

// Get data source configuration.
$data_source_type    = $attributes['dataSourceType'] ?? 'taxonomy';
$excluded_terms      = $attributes['excludedTerms'] ?? [];
$excluded_post_types = $attributes['excludedPostTypes'] ?? [];
$show_hierarchy      = $attributes['showHierarchy'] ?? false;
$query_params        = filters_from_query_string( get_query_string() ) ?? [];

$selected_type = $attributes['selectedType'] ?? 'select';

// Force radio for post types.
if ( 'post_type' === $data_source_type && 'radio' !== $selected_type ) {
	$selected_type = 'radio';
}

$selected_type_abs = 'select' === $selected_type ? 'dropdown' : ( 'radio' === $selected_type ? 'radio' : 'checkbox' );

// Wrapper attributes.
$wrapper_attributes = get_block_wrapper_attributes(
	[
		'class' => implode(
			' ',
			array_filter(
				[
					'wpengine-smart-' . $selected_type_abs . '-facet',
				]
			)
		),
	]
);

$archive_filter_slug = null;

if ( 'post_type' === $data_source_type ) {
	// Post type mode.
	$source_data_records         = get_available_post_types( $excluded_post_types );
	$selected_data_source_mapped = 'post_type';
	$selected_taxonomy           = $query_params['post_type'] ?? '';
} else {
	// Taxonomy mode.
	$selected_data_source        = $attributes['selectedDataSource'] ?? 'category';
	$source_data_records         = get_taxonomy_terms( $selected_data_source, $excluded_terms );
	$selected_data_source_mapped = TAXONOMY_MAPPINGS[ $selected_data_source ] ?? $selected_data_source;
	$selected_taxonomy           = $query_params[ $selected_data_source_mapped ] ?? '';

	// Detect WordPress taxonomy archive pages (e.g. /category/slug, /tag/slug).
	// When no filter is already in the query string, pre-seed the archive term so
	// it is treated as an active filter and preserved when additional filters are added.
	if ( ! isset( $query_params[ $selected_data_source_mapped ] ) ) {
		$queried_object = get_queried_object();

		if ( $queried_object instanceof \WP_Term && $queried_object->taxonomy === $selected_data_source ) {
			$archive_filter_slug                          = $queried_object->slug;
			$query_params[ $selected_data_source_mapped ] = $archive_filter_slug;
			$selected_taxonomy                            = $archive_filter_slug;
		}
	}
}

$context = wp_interactivity_data_wp_context(
	[
		'attributes'                  => $attributes,
		'selected_data_source_mapped' => $selected_data_source_mapped,
		'value'                       => '',
		'filterMode'                  => $attributes['filterMode'] ?? 'or',
		'showHierarchy'               => $show_hierarchy,
		'dataSourceType'              => $data_source_type,
		'defaultExpandedHierarchy'    => $attributes['defaultExpandedHierarchy'] ?? false,
		'archiveFilter'               => $archive_filter_slug,
	]
);

$filters = extract_filters( $query_params );

require __DIR__ . "/render-components/render-{$selected_type}.php";
