<?php

declare(strict_types=1);

namespace Wpe_Content_Engine\Helper\API;

use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Post Types controller for retrieving available post types
 */
final class Post_Types_Controller extends WP_REST_Controller {
	public function __construct() {
		$this->namespace = 'wpengine-smart-search/v1';
	}

	public function register_routes(): void {
		register_rest_route(
			$this->namespace,
			'/post-types',
			[
				[
					'callback'            => [
						$this,
						'get_post_types',
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
	}

	/**
	 * Returns available post types with published post counts.
	 *
	 * @param \WP_REST_Request $request WP Rest request.
	 */
	public function get_post_types( WP_REST_Request $request ): WP_REST_Response {
		$post_types = get_post_types(
			[
				'public'  => true,
				'show_ui' => true,
			],
			'objects'
		);

		$formatted = [];

		foreach ( $post_types as $pt ) {
			// Skip internal types.
			if ( in_array( $pt->name, [ 'attachment', 'nav_menu_item' ], true ) ) {
				continue;
			}

			$count_obj = wp_count_posts( $pt->name );
			$count     = $count_obj->publish ?? 0;

			// Hide post types with 0 published posts.
			if ( 0 === $count ) {
				continue;
			}

			$formatted[] = [
				'slug'  => $pt->name,
				'name'  => $pt->labels->name,
				'count' => $count,
			];
		}

		return new WP_REST_Response( array_values( $formatted ) );
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
			'slug'  => [ 'type' => 'string' ],
			'name'  => [ 'type' => 'string' ],
		];

		return [
			'$schema'              => 'http://json-schema.org/draft-04/schema#',
			'additionalProperties' => false,
			'properties'           => $properties,
			'required'             => [ 'slug', 'name', 'count' ],
			'title'                => 'post_type',
			'type'                 => 'object',
		];
	}
}
