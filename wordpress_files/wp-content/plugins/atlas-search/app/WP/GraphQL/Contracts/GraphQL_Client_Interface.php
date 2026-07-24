<?php

declare(strict_types=1);

namespace WPE\AITK\WP\GraphQL\Contracts;

use WPE\AITK\WP\GraphQL\GraphQL_Endpoint;

/**
 * Contract for executing GraphQL operations against the backend API.
 */
interface GraphQL_Client_Interface {
	/**
	 * Executes a GraphQL query or mutation and returns the "data" portion of the response.
	 *
	 * @param string $query The GraphQL query or mutation string.
	 * @param array<string, scalar|array> $variables Serialized variables from a GraphQL_Request_Interface::to_array().
	 * @param string $endpoint The endpoint for the GraphQL execute.
	 *
	 * @return array<string, scalar|array> The decoded "data" key from the GraphQL response.
	 *
	 * @throws \WPE\AITK\WP\GraphQL\Exceptions\GraphQL_Connection_Exception On transport / HTTP errors.
	 * @throws \WPE\AITK\WP\GraphQL\Exceptions\GraphQL_Authentication_Exception On 401 / UNAUTHENTICATED.
	 * @throws \WPE\AITK\WP\GraphQL\Exceptions\GraphQL_Query_Exception On GraphQL-level errors.
	 */
	public function execute( string $query, array $variables = [], string $endpoint = GraphQL_Endpoint::SYNC ): array;
}
