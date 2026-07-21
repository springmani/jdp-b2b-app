<?php

namespace AtlasSearch\Blocks\Support\WooCommerce;

use function AtlasSearch\Support\WooCommerce\is_woo_enabled;

use const AtlasSearch\Blocks\WordPress\ATTRIBUTES_PREFIX;

/**
 * This file enables WP Engine Facets to support WooCommerce fields.
 *
 * @package    Wpe_Content_Engine
 */
class Callbacks {
	private $loader;

	public function __construct( \Wpe_Content_Engine_Loader $loader ) {
		$this->loader = $loader;
	}

	public function init() {
		$this->loader->add_action( 'pre_get_posts', $this, 'filter_woo_attributes', 10, 1 );
	}

	public function filter_woo_attributes( $query ) {
		if ( ! is_woo_enabled() ) {
			return;
		}

		// Ensure we only modify the main query, and avoid unintended modification of admin queries.
		if ( ! $query->is_main_query() || is_admin() ) {
			return;
		}

		$attributes = wc_get_attribute_taxonomies();

		foreach ( $attributes as $attribute ) {
			$attribute_name  = $attribute->attribute_name;
			$attribute_slug  = ATTRIBUTES_PREFIX . $attribute_name;
			$attribute_value = filter_input( INPUT_GET, $attribute_slug, FILTER_SANITIZE_FULL_SPECIAL_CHARS );

			if ( ! isset( $attribute_value ) || '' === trim( $attribute_value ) ) {
				continue;
			}

			$attr_arr       = explode( ',', $attribute_value );
			$tax_query_args = [
				[
					'taxonomy' => $attribute_slug,
					'field'    => 'slug',
					'terms'    => $attr_arr,
					'operator' => 'IN',
				],
			];

			$tax_query = $query->get( 'tax_query' );

			if ( ! $tax_query ) {
				$tax_query = [];
			}

			$tax_query = array_merge( $tax_query, $tax_query_args );
			$query->set( 'tax_query', $tax_query );
		}
	}
}
