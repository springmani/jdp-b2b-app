<?php

namespace Wpe_Content_Engine\Helper\API;

use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use Wpe_Content_Engine\WPSettings;

/**
 * Settings controller allowing getting and setting of
 * Atlas search settings
 */
class Settings_Controller extends WP_REST_Controller {
	private string $resource_name;

	public function __construct() {
		$this->namespace     = 'wpengine-smart-search/v1';
		$this->resource_name = '/settings';
	}

	public function register_routes() {
		register_rest_route(
			$this->namespace,
			$this->resource_name,
			[
				[
					'methods'             => 'GET',
					'callback'            => [
						$this,
						'get_settings',
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

		register_rest_route(
			$this->namespace,
			$this->resource_name,
			[
				[
					'methods'             => 'POST',
					'callback'            => [
						$this,
						'set_settings',
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
	 * Returns the Smart Search settings.
	 *
	 * @param  \WP_REST_Request $request WP Rest request.
	 *
	 * @return false|mixed|void
	 */
	public function get_settings( WP_REST_Request $request ) {
		return \AtlasSearch\Support\WordPress\get_option( WPSettings::WPE_CONTENT_ENGINE_OPTION_NAME );
	}

	/**
	 * Set the settings.
	 *
	 * @param \WP_REST_Request $request WP Rest request.
	 *
	 * @return \WP_REST_Response
	 */
	public function set_settings( WP_REST_Request $request ): WP_REST_Response {
		$body   = $request->get_json_params();
		$result = rest_validate_value_from_schema( $body, $this->get_schema(), 'settings' );

		if ( is_wp_error( $result ) ) {
			return new WP_REST_Response(
				[ 'error' => $result->get_error_message() ],
				'400'
			);
		}

		$previous = \AtlasSearch\Support\WordPress\get_option( WPSettings::WPE_CONTENT_ENGINE_OPTION_NAME );
		$updated  = \AtlasSearch\Support\WordPress\update_option( WPSettings::WPE_CONTENT_ENGINE_OPTION_NAME, $body );
		$changed  = serialize( $previous ) !== serialize( $body );

		if ( $changed && ! $updated ) {
			$changed_text = 'yes';

			return new WP_REST_Response(
				[ 'error' => "{$changed_text}" ],
				'400'
			);
		}

		if ( $changed ) {
			delete_transient( 'aitk_capabilities' );
		}

		return new WP_REST_Response( $body );
	}

	/**
	 * Check permissions.
	 *
	 * @param \WP_REST_Request $request The WP Rest request.
	 *
	 * @return bool
	 */
	public function permission_callback( WP_REST_Request $request ): bool {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Schema of the REST Endpoints
	 *
	 * @return array
	 */
	public function get_schema(): array {
		$properties = [
			'url'          => [ 'type' => 'string' ],
			'access_token' => [ 'type' => 'string' ],
		];

		return [
			'$schema'              => 'http://json-schema.org/draft-04/schema#',
			'title'                => 'settings',
			'type'                 => 'object',
			'properties'           => $properties,
			'additionalProperties' => false,
			'required'             => [ 'url', 'access_token' ],
		];
	}
}
