<?php

declare(strict_types=1);

namespace WPE\AITK\WP\GraphQL;

use WPE\AITK\Core\Utils\Json_Decoder;
use WPE\AITK\Modules\System\Data\Content_Engine_Connection;
use WPE\AITK\WP\GraphQL\Contracts\GraphQL_Client_Interface;
use WPE\AITK\WP\GraphQL\Exceptions\GraphQL_Authentication_Exception;
use WPE\AITK\WP\GraphQL\Exceptions\GraphQL_Connection_Exception;
use WPE\AITK\WP\GraphQL\Exceptions\GraphQL_Query_Exception;

/**
 * GraphQL client backed by WordPress HTTP API.
 *
 * Encapsulates endpoint, authentication, request building, response parsing,
 * and maps every failure mode to a specific exception type.
 */
final class GraphQL_Client implements GraphQL_Client_Interface {
	private const TIMEOUT_SECONDS = 30;

	private const AGENT_NAME = 'wpe-content-engine';

	private Content_Engine_Connection $connection;

	private string $version;

	private Json_Decoder $json_decoder;

	public function __construct( Content_Engine_Connection $connection, string $version, Json_Decoder $json_decoder ) {
		$this->connection   = $connection;
		$this->version      = $version;
		$this->json_decoder = $json_decoder;
	}

	/**
	 * @inheritDoc
	 */
	public function execute( string $query, array $variables = [], string $endpoint = GraphQL_Endpoint::SYNC ): array {
		$body = [ 'query' => $query ];

		if ( [] !== $variables ) {
			$body['variables'] = $variables;
		}

		$response = wp_remote_post(
			$this->resolve_url( $endpoint ),
			[
				'body'          => wp_json_encode( $body ),
				'headers'       => $this->build_headers(),
				'ignore_errors' => true,
				'timeout'       => self::TIMEOUT_SECONDS,
			]
		);

		$this->guard_wp_error( $response );

		$status_code   = (int) wp_remote_retrieve_response_code( $response );
		$raw_body      = wp_remote_retrieve_body( $response );
		$response_data = $this->json_decoder->decode( $raw_body );

		$this->guard_authentication( $status_code, $response_data, $raw_body );
		$this->guard_http_status( $status_code );
		$this->guard_response_format( $raw_body, $response_data, $status_code );
		$this->guard_graphql_errors( $response_data );

		return is_array( $response_data['data'] ?? null ) ? $response_data['data'] : [];
	}

	private function resolve_url( string $endpoint ): string {
		switch ( $endpoint ) {
			case GraphQL_Endpoint::SYNC:
				return $this->connection->get_sync_url();
			case GraphQL_Endpoint::SITES:
				return $this->connection->get_sites_url();
			default:
				throw new GraphQL_Connection_Exception(
					sprintf( 'Unknown GraphQL endpoint "%s".', $endpoint )
				);
		}
	}

	/**
	 * @return array<string, string>
	 */
	private function build_headers(): array {
		return [
			'Authorization'          => "Bearer {$this->connection->get_access_token()}",
			'Content-Type'           => 'application/json',
			'X-CONTENT-ENGINE-AGENT' => self::AGENT_NAME . '/' . $this->version,
		];
	}

	/**
	 * @param array<array-key, array<array-key, bool|int|string>|bool|int|string>|\WP_Error $response wp_remote_post result.
	 *
	 * @throws \WPE\AITK\WP\GraphQL\Exceptions\GraphQL_Connection_Exception
	 */
	private function guard_wp_error( $response ): void {
		if ( ! is_wp_error( $response ) ) {
			return;
		}

		throw new GraphQL_Connection_Exception(
			sprintf( 'WordPress request error: %s.', wp_kses_data( $response->get_error_message() ) )
		);
	}

	/**
	 * @param array<array-key, array<array-key, bool|int|string>|bool|int|string> $response_data Decoded JSON.
	 *
	 * @throws \WPE\AITK\WP\GraphQL\Exceptions\GraphQL_Authentication_Exception
	 */
	private function guard_authentication( int $status_code, array $response_data, string $raw_body ): void {
		$graphql_code = $response_data['errors'][0]['extensions']['code'] ?? '';
		$is_auth_fail = 401 === $status_code || 'UNAUTHENTICATED' === $graphql_code;

		if ( ! $is_auth_fail ) {
			return;
		}

		$trimmed = trim( $raw_body );

		if ( 'Authentication failed' === $trimmed || 'Authentication header not present or malformed' === $trimmed ) {
			throw new GraphQL_Authentication_Exception( 'Please verify your WP Engine Smart Search access token.' );
		}

		throw new GraphQL_Authentication_Exception(
			'Please try again in a minute. Some of our systems are still initializing.'
		);
	}

	/**
	 * @throws \WPE\AITK\WP\GraphQL\Exceptions\GraphQL_Connection_Exception
	 */
	private function guard_http_status( int $status_code ): void {
		if ( $status_code >= 200 && $status_code < 300 ) {
			return;
		}

		if ( 400 === $status_code ) {
			throw new GraphQL_Connection_Exception(
				'The server rejected the request. Please upgrade WP Engine Smart Search plugin to the latest version.',
				$status_code
			);
		}

		if ( 404 === $status_code ) {
			throw new GraphQL_Connection_Exception( 'Please verify your WP Engine Smart Search URL.', $status_code );
		}

		if ( $status_code >= 500 ) {
			throw new GraphQL_Connection_Exception(
				'An unexpected server error occurred. Please contact support.',
				$status_code
			);
		}

		throw new GraphQL_Connection_Exception( "Unexpected HTTP response status: {$status_code}.", $status_code );
	}

	/**
	 * @param array<array-key, array<array-key, bool|int|string>|bool|int|string> $response_data Decoded JSON.
	 *
	 * @throws \WPE\AITK\WP\GraphQL\Exceptions\GraphQL_Connection_Exception
	 */
	private function guard_response_format( string $raw_body, array $response_data, int $status_code ): void {
		if ( '' !== $raw_body && [] !== $response_data ) {
			return;
		}

		throw new GraphQL_Connection_Exception( 'Empty or malformed server response.', $status_code );
	}

	/**
	 * @param array<array-key, array<array-key, bool|int|string>|bool|int|string> $response_data Decoded response.
	 *
	 * @throws \WPE\AITK\WP\GraphQL\Exceptions\GraphQL_Query_Exception
	 */
	private function guard_graphql_errors( array $response_data ): void {
		if ( empty( $response_data['errors'] ) ) {
			return;
		}

		$errors = $response_data['errors'];

		throw new GraphQL_Query_Exception(
			wp_kses_data( $errors[0]['message'] ?? 'Unknown GraphQL error' ),
			is_array( $errors ) ? $errors : []
		);
	}
}
