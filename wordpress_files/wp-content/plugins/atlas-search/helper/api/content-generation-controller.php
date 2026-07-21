<?php

namespace Wpe_Content_Engine\Helper\API;

use AtlasSearch\Index\Media\Content_Generator;
use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use Wpe_Content_Engine\Helper\Exceptions\ClientQueryException;

/**
 * Content Generation controller for generating attachment descriptions
 */
class Content_Generation_Controller extends WP_REST_Controller {
	private string $resource_name;
	private Content_Generator $content_generator;

	public function __construct() {
		$this->namespace         = 'wpengine-smart-search/v1/media';
		$this->resource_name     = '/generate-content';
		$this->content_generator = new Content_Generator(
			new \Wpe_Content_Engine\Helper\Logging\Debug_Logger()
		);
	}

	public function register_routes() {
		register_rest_route(
			$this->namespace,
			$this->resource_name,
			[
				[
					'methods'             => 'POST',
					'callback'            => [
						$this,
						'generate_content',
					],
					'permission_callback' => [
						$this,
						'permission_callback',
					],
					'args'                => $this->get_endpoint_args(),
				],
				'schema' => [
					$this,
					'get_schema',
				],
			]
		);
	}

	/**
	 * Generate content for an attachment.
	 *
	 * @param  \WP_REST_Request $request WP Rest request.
	 *
	 * @return \WP_REST_Response
	 */
	public function generate_content( WP_REST_Request $request ): WP_REST_Response {
		$body          = $request->get_json_params();
		$attachment_id = $body['attachment_id'];

		// Validate that the attachment exists.
		if ( ! get_post( $attachment_id ) || 'attachment' !== get_post_type( $attachment_id ) ) {
			return new WP_REST_Response(
				[ 'error' => 'Invalid attachment ID provided' ],
				400
			);
		}

		try {
			$result = $this->content_generator->generate_content( $attachment_id );

			if ( ! $result['success'] ) {
				return new WP_REST_Response(
					[ 'error' => $result['error'] ],
					400
				);
			}

			return new WP_REST_Response( [
				'id'   => $attachment_id,
				'data' => $result['data'],
			] );
		} catch ( ClientQueryException $e ) {
			return new WP_REST_Response(
				[ 'error' => $e->getMessage() ],
				400
			);
		} catch ( \Throwable $e ) {
			return new WP_REST_Response(
				[ 'error' => $e->getMessage() ],
				500
			);
		}
	}

	/**
	 * Check permissions.
	 *
	 * @param \WP_REST_Request $request The WP Rest request.
	 *
	 * @return bool
	 */
	public function permission_callback( WP_REST_Request $request ): bool {
		return current_user_can( 'upload_files' );
	}

	/**
	 * Get the endpoint arguments for validation.
	 *
	 * @return array
	 */
	public function get_endpoint_args(): array {
		return [
			'attachment_id' => [
				'required'    => true,
				'type'        => 'integer',
				'description' => 'The ID of the attachment to generate content for',
				'minimum'     => 1,
			],
		];
	}

	/**
	 * Schema of the REST Endpoints
	 *
	 * @return array
	 */
	public function get_schema(): array {
		$properties = [
			'attachment_id' => [
				'type'        => 'integer',
				'description' => 'The ID of the attachment to generate content for',
				'minimum'     => 1,
			],
		];

		return [
			'$schema'              => 'http://json-schema.org/draft-04/schema#',
			'title'                => 'contentGeneration',
			'type'                 => 'object',
			'properties'           => $properties,
			'additionalProperties' => false,
			'required'             => [ 'attachment_id' ],
		];
	}
}
