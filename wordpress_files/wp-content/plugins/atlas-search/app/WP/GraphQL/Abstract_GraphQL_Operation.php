<?php

declare(strict_types=1);

namespace WPE\AITK\WP\GraphQL;

use WPE\AITK\WP\GraphQL\Contracts\GraphQL_Client_Interface;
use WPE\AITK\WP\GraphQL\Contracts\GraphQL_Response_Interface;
use WPE\AITK\WP\GraphQL\Exceptions\GraphQL_Connection_Exception;

/**
 * Base class for all GraphQL operations (queries and mutations).
 *
 * Subclasses define the query string, variables, and response path.
 */
abstract class Abstract_GraphQL_Operation {
	/**
	 * Executes the operation and maps the response to a typed DTO.
	 *
	 * @template TResponse of \WPE\AITK\WP\GraphQL\Contracts\GraphQL_Response_Interface
	 *
	 * @param \WPE\AITK\WP\GraphQL\Contracts\GraphQL_Client_Interface $client The GraphQL transport.
	 * @param class-string<TResponse> $response_class The response DTO class.
	 *
	 * @throws \WPE\AITK\WP\GraphQL\Exceptions\GraphQL_Exception On any transport or query error.
	 */
	public function execute( GraphQL_Client_Interface $client, string $response_class ): GraphQL_Response_Interface {
		$response = $client->execute( $this->query(), $this->variables(), $this->endpoint() );

		return $response_class::from_array( $this->resolve_path( $response ) );
	}

	/**
	 * Returns the GraphQL query or mutation string.
	 */
	abstract protected function query(): string;

	/**
	 * Returns the dot-separated path to the relevant data in the response.
	 *
	 * Example: 'config.synonyms.rules' resolves $data['config']['synonyms']['rules'].
	 */
	abstract protected function response_path(): string;

	protected function endpoint(): string {
		return GraphQL_Endpoint::SYNC;
	}

	/**
	 * Returns the serialized variables to bind into the operation.
	 *
	 * @return array<string, scalar|array>
	 */
	protected function variables(): array {
		return [];
	}

	/**
	 * Walks the response array along the dot-separated response_path().
	 *
	 * @param array<string, scalar|array> $data The full "data" portion of the GraphQL response.
	 *
	 * @return array<string, scalar|array> The sub-array at the target path.
	 *
	 * @throws \WPE\AITK\WP\GraphQL\Exceptions\GraphQL_Connection_Exception If the path does not exist in the response.
	 */
	private function resolve_path( array $data ): array {
		$path = $this->response_path();

		if ( '' === $path ) {
			return $data;
		}

		$current = $data;

		foreach ( explode( '.', $path ) as $segment ) {
			if ( ! is_array( $current ) || ! array_key_exists( $segment, $current ) ) {
				throw new GraphQL_Connection_Exception(
					sprintf( 'Response path "%s" not found in GraphQL response.', $path )
				);
			}

			$current = $current[ $segment ];
		}

		if ( ! is_array( $current ) ) {
			throw new GraphQL_Connection_Exception(
				sprintf( 'Response path "%s" resolved to a non-array value.', $path )
			);
		}

		return $current;
	}
}
