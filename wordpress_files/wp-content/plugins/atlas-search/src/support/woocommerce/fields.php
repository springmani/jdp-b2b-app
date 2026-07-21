<?php

namespace AtlasSearch\Support\WooCommerce;

use WC_Product;
use WP_Post;

use function AtlasSearch\Index\get_term_fields;
use function AtlasSearch\Index\get_terms_with_parents;

/*
 * Maps WooCommerce internal meta keys (as stored in wp_postmeta) to
 * the corresponding indexed field names in the search index.
 *
 * This is the single source of truth: both the indexing logic and the
 * meta-query key normalizer reference this constant, so they never drift.
 *
 */
const WOO_META_KEY_TO_INDEX_FIELD_MAP = [
	'_price'             => 'price',
	'_regular_price'     => 'regular_price',
	'_sale_price'        => 'sale_price',
	'_stock_status'      => 'stock_status',
	'_sku'               => 'sku',
	'_weight'            => 'weight',
	'total_sales'        => 'total_sales',
	'_wc_average_rating' => 'average_rating',
	'_visibility'        => 'catalog_visibility',
];

/**
 * Adds extra fields to WooCommerce product data.
 *
 * @param array    $fields Existing fields.
 * @param \WP_Post $post WordPress post object.
 *
 * @return array Modified fields.
 */
function add_extra_fields_to_product( array $fields, WP_Post $post ): array {

	$wc_product = wc_get_product( $post->ID );

	if ( $wc_product ) {
		$data = $wc_product->get_data();

		$fields['name']               = $data['name'];
		$fields['post_date_gmt']      = $data['date_created']->__toString();
		$fields['post_modified_gmt']  = $data['date_modified']->__toString();
		$fields['post_content']       = $data['description'];
		$fields['post_excerpt']       = $data['short_description'];
		$fields['price']              = (float) $data['price'];
		$fields['regular_price']      = (float) $data['regular_price'];
		$fields['sale_price']         = (float) $data['sale_price'];
		$fields['stock_status']       = $data['stock_status'];
		$fields['sku']                = $data['sku'];
		$fields['weight']             = $data['weight'];
		$fields['total_sales']        = (int) $data['total_sales'];
		$fields['average_rating']     = (float) $data['average_rating'];
		$fields['catalog_visibility'] = $data['catalog_visibility'];

		// Fetch product options (variations).
		if ( $wc_product->is_type( 'variable' ) ) {
			$variations = [];

			foreach ( $wc_product->get_children() as $child_id ) {
				$variation = wc_get_product( $child_id );

				if ( ! $variation instanceof WC_Product ) {
					continue;
				}

				$variations[] = [
					'sku'               => $variation->get_sku(),
					'price'             => $variation->get_price(),
					'regular_price'     => $variation->get_regular_price(),
					'sale_price'        => $variation->get_sale_price(),
					'stock_status'      => $variation->get_stock_status(),
					'description'       => $variation->get_description(),
					'short_description' => $variation->get_short_description(),
					'weight'            => $variation->get_weight(),
					'attributes'        => $variation->get_attributes(),
				];
			}

			$fields['variations'] = $variations;
		}

		// Fetch product attributes.
		$attributes = [];

		foreach ( $data['attributes'] as $attribute ) {
			if ( ! empty( $attribute['name'] ) && taxonomy_exists( $attribute['name'] ) ) {
				// Fetch terms for the attribute.
				// We need to treat these as taxonomies.
				// So they must exist at the root level of the index.
				$terms              = wp_get_post_terms( $wc_product->get_id(), $attribute['name'] );
				$terms_with_parents = get_terms_with_parents( $terms, $attribute['name'] );

				$fields[ $attribute['name'] ] = array_map(
					static fn ( $term ) => get_term_fields( $term ),
					$terms_with_parents
				);
			} elseif ( ! empty( $attribute['name'] ) && isset( $attribute['options'] ) ) {
				$attributes[ $attribute['name'] ] = $attribute['options'];
			}
		}

		$fields['attributes'] = $attributes;

		// Fetch product downloads.
		$downloads = [];

		foreach ( $wc_product->get_downloads() as $download ) {
			$downloads[] = [
				'name' => $download['name'],
				'file' => $download['file'],
			];
		}

		$fields['downloads'] = $downloads;

		// Fetch product purchase note.
		$fields['purchase_note'] = $data['purchase_note'];

		// Fetch product dimensions.
		$fields['dimensions'] = [
			'length' => $data['length'],
			'width'  => $data['width'],
			'height' => $data['height'],
		];
	}

	return $fields;
}

/**
 * Adds extra search config fields for WooCommerce products.
 *
 * @param array  $fields Existing fields.
 * @param string $post_type Post type.
 *
 * @return array Modified fields.
 */
function add_extra_search_config_fields( array $fields, string $post_type ): array {
	if ( 'product' === $post_type ) {
		$fields[] = 'name';
		$fields[] = 'price';
		$fields[] = 'regular_price';
		$fields[] = 'sale_price';
		$fields[] = 'stock_status';
		$fields[] = 'sku';
		$fields[] = 'weight';
		$fields[] = 'variations.*';
		$fields[] = 'attributes.*';
		$fields[] = 'downloads.*';
		$fields[] = 'purchase_note';
		$fields[] = 'dimensions.*';
	}

	return $fields;
}
