<?php

namespace Wpe_Content_Engine\Helper;

use WP_Error;
use Wpe_Content_Engine\Helper\Exceptions\ClientQueryException;
use Wpe_Content_Engine\Helper\Exceptions\MissingSettingsException;

class CapabilitiesService {
	public const GET_CAPABILITIES = <<<'GRAPHQL'
		query GetCapabilities {
			capabilities
		}
	GRAPHQL;

	private const TRANSIENT_KEY = 'aitk_capabilities';

	/**
	 * Check if a specific capability exists.
	 *
	 * @param string $capability The capability to check for.
	 *
	 * @return bool True if capability exists, false otherwise.
	 */
	public static function has_capability( string $capability ): bool {
		$capabilities = self::get_capabilities();

		if ( is_wp_error( $capabilities ) ) {
			return false;
		}

		return in_array( $capability, $capabilities, true );
	}

	/**
	 * Retrieve capabilities from AtlasSearch GraphQL API with transient caching.
	 *
	 * @return array|\WP_Error Array of capabilities on success, WP_Error on failure
	 */
	public static function get_capabilities() {
		$cached_capabilities = get_transient( self::TRANSIENT_KEY );

		if ( false !== $cached_capabilities ) {
			if ( is_array( $cached_capabilities ) ) {
				return $cached_capabilities;
			}

			if ( is_wp_error( $cached_capabilities ) ) {
				return $cached_capabilities;
			}
		}

		try {
			$response = \AtlasSearch\Index\graphql( self::GET_CAPABILITIES, [] );

			if ( ! is_array( $response ) || ! isset( $response['data'] ) ) {
				$error = new WP_Error( 'invalid_response', 'Invalid response structure from capabilities API' );
				set_transient( self::TRANSIENT_KEY, $error, MINUTE_IN_SECONDS );

				return $error;
			}

			$capabilities = $response['data']['capabilities'] ?? [];

			if ( ! is_array( $capabilities ) ) {
				$error = new WP_Error( 'invalid_data_format', 'Capabilities data has invalid format' );
				set_transient( self::TRANSIENT_KEY, $error, MINUTE_IN_SECONDS );

				return $error;
			}

			set_transient( self::TRANSIENT_KEY, $capabilities, MINUTE_IN_SECONDS );

			return $capabilities;
		} catch ( ClientQueryException $e ) {
			$error = new WP_Error(
				'client_query_error',
				'Failed to query capabilities: ' . $e->getMessage()
			);

			// Set transient only for non-client errors.
			if ( ! in_array( (int) $e->getCode(), [ 400, 401, 404, 500 ], true ) ) {
				set_transient( self::TRANSIENT_KEY, $error, MINUTE_IN_SECONDS );
			}

			return $error;
		} catch ( MissingSettingsException $e ) {
			return new WP_Error( 'missing_settings_error', 'Settings are not configured properly: ' . $e->getMessage() );
		} catch ( \Throwable $e ) {
			return new WP_Error( 'unexpected_error', 'An unexpected error occurred: ' . $e->getMessage() );
		}
	}
}
