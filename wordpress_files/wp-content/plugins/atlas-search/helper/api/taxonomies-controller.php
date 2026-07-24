<?php

declare(strict_types=1);

namespace Wpe_Content_Engine\Helper\API;

use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Attributes controller allowing getting and setting of
 * Atlas search attributes
 */
final class Taxonomies_Controller extends WP_REST_Controller {
	public function __construct() {
		$this->namespace = 'wpengine-smart-search/v1';
	}

	public function register_routes(): void {
		register_rest_route(
			$this->namespace,
			'/taxonomies',
			[
				[
					'callback'            => [
						$this,
						'get_taxonomies',
					],
					'methods'             => 'GET',
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

		// Register the get_terms_for_taxonomy endpoint.
		register_rest_route(
			$this->namespace,
			'/taxonomies/(?P<taxonomy>[a-zA-Z0-9_-]+)/terms',
			[
				[
					'args'                => [
						'taxonomy' => [
							'required'          => true,
							'validate_callback' => static fn ( $param, $request, $key ) => is_string( $param ),
						],
					],
					'callback'            => [
						$this,
						'get_terms_for_taxonomy',
					],
					'methods'             => 'GET',
					'permission_callback' => [
						$this,
						'permission_callback',
					],
				],
			]
		);
	}

	/**
	 * Returns the Smart Search taxonomies.
	 *
	 * @param \WP_REST_Request $request WP Rest request.
	 *
	 * @return false|mixed|void
	 */
	public function get_taxonomies( WP_REST_Request $request ) {
		// get all taxonomies.
		$taxes = get_taxonomies( [], 'objects' );
		// Filter taxonomies that start with 'pa_' and remove taxonomies that are not public or are 'post_format' or 'product_shipping_class'.
		$taxes = array_filter(
			$taxes,
			static fn ( $taxonomy ) => 0 === strpos(
				$taxonomy->name,
				'pa_'
			) || ( $taxonomy->public && $taxonomy->show_ui )
		);

		return new WP_REST_Response( array_values( $taxes ) );
	}

	/**
	 * Returns the terms for a given taxonomy.
	 *
	 * @param \WP_REST_Request $request WP Rest request.
	 */
	public function get_terms_for_taxonomy( WP_REST_Request $request ): WP_REST_Response {
		$taxonomy = $request->get_param( 'taxonomy' );

		if ( empty( $taxonomy ) ) {
			return new WP_REST_Response( 'Taxonomy parameter is required', 400 );
		}

		$terms = get_terms(
			[
				'hide_empty' => false,
				'taxonomy'   => $taxonomy,
			]
		);

		if ( is_wp_error( $terms ) ) {
			return new WP_REST_Response( $terms->get_error_message(), 400 );
		}

		$formatted_terms = array_map(
			static fn ( $term ) => [
				'count'   => $term->count,
				'name'    => $term->name,
				'slug'    => $term->slug,
				'parent'  => $term->parent,
				'term_id' => $term->term_id,
			],
			$terms
		);

		return new WP_REST_Response( array_values( $formatted_terms ) );
	}

	/**
	 * Check permissions.
	 *
	 * @param \WP_REST_Request $request The WP Rest request.
	 */
	public function permission_callback( WP_REST_Request $request ): bool {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Schema of the REST Endpoints
	 */
	public function get_schema(): array {
		$properties = [
			'count' => [ 'type' => 'integer' ],
			'name'  => [ 'type' => 'string' ],
			'slug'  => [ 'type' => 'string' ],
		];

		return [
			'$schema'              => 'http://json-schema.org/draft-04/schema#',
			'additionalProperties' => false,
			'properties'           => $properties,
			'required'             => [ 'name', 'slug', 'count' ],
			'title'                => 'term',
			'type'                 => 'object',
		];
	}
}
