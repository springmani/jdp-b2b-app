<?php

namespace Wpe_Content_Engine\Helper\API;

use WP_REST_Controller;
use WP_REST_Response;
use Wpe_Content_Engine\Helper\Acf_Support\Acf_Factory;

use function AtlasSearch\Support\WooCommerce\is_woo_enabled;
use function Wpe_Content_Engine\Helper\Acf_Support\get_acf_fields;
use function Wpe_Content_Engine\Helper\Acf_Support\is_acf_loaded;

/**
 * Attributes controller allowing getting and setting of
 * Atlas search attributes
 */
class Range_Facet_Controller extends WP_REST_Controller {
	public function __construct() {
		$this->namespace = 'wpengine-smart-search/v1';
	}

	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/range-data-sources',
			[
				[
					'methods'             => 'GET',
					'callback'            => [
						$this,
						'get_range_data_sources',
					],
					'permission_callback' => [
						$this,
						'permission_callback',
					],
				],
				'schema' => [
					$this,
					'get_schema',
				],
			]
		);
	}

	/**
	 * Returns the Smart Search Range Facet data sources.
	 *
	 * @return \WP_REST_Response
	 */
	public function get_range_data_sources() {
		$range_slider_data_sources = [];

		if ( is_woo_enabled() ) {
			$range_slider_data_sources[] = [
				'name' => 'Product Price',
				'slug' => 'price',
			];
		}

		if ( is_acf_loaded() ) {
			// TODO: If we want to support nested fields, we need to first save inthe index the nested subfields as numeric first.
			$fields = get_acf_fields( [], [], [ Acf_Factory::NUMBER, Acf_Factory::RANGE ] );

			foreach ( $fields as $field ) {
				$range_slider_data_sources[] = [
					'name' => $field['label'],
					'slug' => urlencode( $field['path_name'] ),
				];
			}
		}

		if ( empty( $range_slider_data_sources ) ) {
			$range_slider_data_sources[] = [
				'name' => 'No valid data sources found',
				'slug' => 'no_data_sources',
			];
		}

		return new WP_REST_Response( $range_slider_data_sources );
	}

	/**
	 * Check permissions.
	 *
	 * @return bool
	 */
	public function permission_callback(): bool {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Schema of the REST Endpoints
	 *
	 * @return array
	 */
	public function get_schema(): array {
		$properties = [
			'name' => [ 'type' => 'string' ],
			'slug' => [ 'type' => 'string' ],
		];

		return [
			'$schema'              => 'http://json-schema.org/draft-04/schema#',
			'title'                => 'range-facet',
			'type'                 => 'object',
			'properties'           => $properties,
			'additionalProperties' => false,
			'required'             => [ 'name', 'slug' ],
		];
	}
}
