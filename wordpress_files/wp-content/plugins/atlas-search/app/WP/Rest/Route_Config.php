<?php

declare(strict_types=1);

namespace WPE\AITK\WP\Rest;

use InvalidArgumentException;
use WPE\AITK\WP\Rest\Contracts\Rest_Request_Interface;
use WPE\AITK\WP\Rest\Contracts\Rest_Response_Interface;

/**
 * Route Configuration.
 *
 * Immutable data object describing a single REST API route registration.
 * Supports optional request and response data classes for automated
 * schema and argument generation via the Schema_Builder.
 */
final class Route_Config {
	/**
	 * Route pattern, e.g. '/items' or '/items/(?P<id>\d+)'.
	 *
	 * @var string
	 */
	private string $route;

	/**
	 * HTTP method(s), e.g. WP_REST_Server::READABLE or WP_REST_Server::CREATABLE.
	 *
	 * @var string
	 */
	private string $methods;

	/**
	 * The handler callback.
	 *
	 * Expected signatures:
	 *   - `callable(): Rest_Response_Interface`                            (no request data)
	 *   - `callable( Rest_Request_Interface ): Rest_Response_Interface`  (with request data)
	 *
	 * @var callable
	 */
	private $callback;

	/**
	 * WordPress capability required for this route, or null for public access.
	 *
	 * @var string|null
	 */
	private ?string $capability;

	/**
	 * Fully qualified class name for request data (used to auto-generate args).
	 *
	 * @var class-string<\WPE\AITK\WP\Rest\Contracts\Rest_Request_Interface>|null
	 */
	private ?string $request;

	/**
	 * Fully qualified class name for response data (used to auto-generate schema).
	 *
	 * @var class-string<\WPE\AITK\WP\Rest\Contracts\Rest_Response_Interface>
	 */
	private string $response;

	/**
	 * Constructor.
	 *
	 * @param string            $route      Route pattern.
	 * @param string            $methods    HTTP method(s).
	 * @param callable          $callback   Handler callback.
	 * @param string|null       $capability WordPress capability required, or null for public access.
	 * @param class-string<\WPE\AITK\WP\Rest\Contracts\Rest_Response_Interface> $response Response data class for auto-generated schema.
	 * @param class-string<\WPE\AITK\WP\Rest\Contracts\Rest_Request_Interface>|null $request Request data class for auto-generated args.
	 */
	public function __construct(
		string $route,
		string $methods,
		callable $callback,
		?string $capability,
		string $response,
		?string $request = null
	) {
		$this->validate_class( $response, Rest_Response_Interface::class, 'response' );

		if ( null !== $request ) {
			$this->validate_class( $request, Rest_Request_Interface::class, 'request' );
		}

		$this->route      = $route;
		$this->methods    = $methods;
		$this->callback   = $callback;
		$this->capability = $capability;
		$this->response   = $response;
		$this->request    = $request;
	}

	/**
	 * Returns the route pattern.
	 */
	public function get_route(): string {
		return $this->route;
	}

	/**
	 * Returns the HTTP method(s).
	 */
	public function get_methods(): string {
		return $this->methods;
	}

	/**
	 * Returns the handler callback.
	 */
	public function get_callback(): callable {
		return $this->callback;
	}

	/**
	 * Returns the required capability, or null for public access.
	 */
	public function get_capability(): ?string {
		return $this->capability;
	}

	/**
	 * Returns whether this route has a request data class.
	 */
	public function has_request(): bool {
		return null !== $this->request;
	}

	/**
	 * Returns the request data class name, or null if not set.
	 *
	 * @return class-string<\WPE\AITK\WP\Rest\Contracts\Rest_Request_Interface>|null
	 */
	public function get_request(): ?string {
		return $this->request;
	}

	/**
	 * Returns the response data class name.
	 *
	 * @return class-string<\WPE\AITK\WP\Rest\Contracts\Rest_Response_Interface>
	 */
	public function get_response(): string {
		return $this->response;
	}

	/**
	 * Validates that a class exists and implements the expected interface.
	 *
	 * @param string $class_name    The class to validate.
	 * @param string $contract      The expected interface.
	 * @param string $param_name    Parameter name for the error message.
	 *
	 * @throws \InvalidArgumentException If the class does not exist or does not implement the interface.
	 */
	private function validate_class( string $class_name, string $contract, string $param_name ): void {
		if ( ! class_exists( $class_name ) ) {
			throw new InvalidArgumentException(
				sprintf( 'Route %s class "%s" does not exist.', $param_name, $class_name )
			);
		}

		if ( ! is_subclass_of( $class_name, $contract ) ) {
			throw new InvalidArgumentException(
				sprintf( 'Route %s class "%s" must implement %s.', $param_name, $class_name, $contract )
			);
		}
	}
}
