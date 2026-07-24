<?php

declare(strict_types=1);

namespace WPE\AITK\WP\GraphQL\Exceptions;

use Throwable;

/**
 * Thrown when the server returns GraphQL-level errors in the response payload.
 */
final class GraphQL_Query_Exception extends GraphQL_Exception {
	/**
	 * @var array<int, array{message: string, extensions?: array<string, scalar|array>}>
	 */
	private array $graphql_errors;

	/**
	 * @param string                                                                $message        Human-readable summary.
	 * @param array<int, array{message: string, extensions?: array<string, scalar|array>}> $graphql_errors Raw errors array from the response.
	 */
	public function __construct( string $message, array $graphql_errors = [], ?Throwable $previous = null ) {
		$this->graphql_errors = $graphql_errors;

		parent::__construct( $message, 0, $previous );
	}

	/**
	 * @return array<int, array{message: string, extensions?: array<string, scalar|array>}>
	 */
	public function get_graphql_errors(): array {
		return $this->graphql_errors;
	}
}
