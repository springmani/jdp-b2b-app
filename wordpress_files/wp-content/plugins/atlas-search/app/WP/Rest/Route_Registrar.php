<?php

declare(strict_types=1);

namespace WPE\AITK\WP\Rest;

use Throwable;
use WPE\AITK\WP\Rest\Contracts\Rest_Controller_Interface;
use WPE\AITK\WP\Rest\Contracts\Rest_Response_Interface;
use WPE\AITK\WP\Rest\Exceptions\Duplicate_Route_Exception;
use WPE\AITK\WP\Rest\Exceptions\Rest_Exception;
use WPE\AITK\WP\Schema\Contracts\Schema_Builder_Interface;
use WPE\AITK\WP\User\Capability_Checker;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Route Registrar.
 *
 * Collects REST controllers and registers their routes with WordPress
 * via register_rest_route(). Owns the full dispatch pipeline: permission
 * checks, request hydration, handler invocation, response wrapping,
 * and exception-to-response conversion.
 *
 * This is the single point where WordPress route registration happens,
 * called on the 'rest_api_init' action.
 */
final class Route_Registrar {
	/**
	 * REST API namespace for all registered routes.
	 */
	private const REST_NAMESPACE = 'wpengine-aitk/v1';

	/**
	 * Builds REST argument definitions from request data classes.
	 *
	 * @var \WPE\AITK\WP\Rest\Rest_Args_Builder
	 */
	private Rest_Args_Builder $args_builder;

	/**
	 * Schema builder for generating response schemas from data classes.
	 *
	 * @var \WPE\AITK\WP\Schema\Contracts\Schema_Builder_Interface
	 */
	private Schema_Builder_Interface $schema_builder;

	/**
	 * Response envelope for building consistent responses.
	 *
	 * @var \WPE\AITK\WP\Rest\Response_Envelope
	 */
	private Response_Envelope $envelope;

	/**
	 * Capability checker for building permission guards.
	 *
	 * @var \WPE\AITK\WP\User\Capability_Checker
	 */
	private Capability_Checker $capability_checker;

	/**
	 * Registered controllers.
	 *
	 * @var array<int, \WPE\AITK\WP\Rest\Contracts\Rest_Controller_Interface>
	 */
	private array $controllers = [];

	/**
	 * Tracks registered route+method combinations to detect duplicates.
	 *
	 * Keys are "{route}:{methods}" strings.
	 *
	 * @var array<string, true>
	 */
	private array $registered_routes = [];

	/**
	 * Constructor.
	 *
	 * @param \WPE\AITK\WP\Rest\Rest_Args_Builder                    $args_builder       REST args builder.
	 * @param \WPE\AITK\WP\Schema\Contracts\Schema_Builder_Interface $schema_builder     Schema builder service.
	 * @param \WPE\AITK\WP\Rest\Response_Envelope                    $envelope           Response envelope service.
	 * @param \WPE\AITK\WP\User\Capability_Checker                   $capability_checker Capability checker.
	 */
	public function __construct(
		Rest_Args_Builder $args_builder,
		Schema_Builder_Interface $schema_builder,
		Response_Envelope $envelope,
		Capability_Checker $capability_checker
	) {
		$this->args_builder       = $args_builder;
		$this->schema_builder     = $schema_builder;
		$this->envelope           = $envelope;
		$this->capability_checker = $capability_checker;
	}

	/**
	 * Adds a controller to the registrar.
	 *
	 * @param \WPE\AITK\WP\Rest\Contracts\Rest_Controller_Interface $controller The controller to register.
	 */
	public function add_controller( Rest_Controller_Interface $controller ): void {
		$this->controllers[] = $controller;
	}

	/**
	 * Registers all collected routes with WordPress.
	 *
	 * This method should be called on the 'rest_api_init' action hook.
	 */
	public function register_routes(): void {
		$this->registered_routes = [];

		foreach ( $this->controllers as $controller ) {
			$this->register_controller_routes( $controller );
		}
	}

	/**
	 * Registers routes for a single controller.
	 *
	 * @param \WPE\AITK\WP\Rest\Contracts\Rest_Controller_Interface $controller The controller whose routes to register.
	 */
	private function register_controller_routes( Rest_Controller_Interface $controller ): void {
		foreach ( $controller->get_route_configs() as $config ) {
			$this->guard_duplicate( $config );

			$endpoint_args = [
				'callback'            => fn (
					WP_REST_Request $request
				): WP_REST_Response => $this->dispatch( $config, $request ),
				'methods'             => $config->get_methods(),
				'permission_callback' => $this->capability_checker->guard( $config->get_capability() ),
			];

			if ( $config->has_request() ) {
				$endpoint_args['args'] = $this->args_builder->build( $config->get_request() );
			}

			$response = $config->get_response();
			$route    = $config->get_route();

			$route_args = [
				$endpoint_args,
				'schema' => fn (): array => $this->schema_builder->build_rest_schema( $response, $route ),
			];

			register_rest_route( self::REST_NAMESPACE, $route, $route_args );
		}
	}

	/**
	 * Dispatches a request through the pipeline.
	 *
	 * 1. Hydrates a typed request DTO from request params (if route has a request class).
	 * 2. Invokes the handler callback with the typed input.
	 * 3. Wraps the returned response DTO via the Response_Envelope.
	 * 4. Catches REST exceptions and unexpected errors, converting them to HTTP responses.
	 *
	 * @param \WPE\AITK\WP\Rest\Route_Config $config  The route configuration.
	 * @param \WP_REST_Request            $request The incoming REST request.
	 */
	private function dispatch( Route_Config $config, WP_REST_Request $request ): WP_REST_Response {
		try {
			return $this->envelope->success( $this->invoke_handler( $config, $request ) );
		} catch ( Rest_Exception $e ) {
			return $this->envelope->from_exception( $e );
		} catch ( Throwable $e ) {
			return $this->envelope->error(
				__( 'An unexpected error occurred.', 'wpe-aitk' ),
				500,
				'internal_error'
			);
		}
	}

	/**
	 * Invokes the handler callback, hydrating the request DTO when applicable.
	 *
	 * @param \WPE\AITK\WP\Rest\Route_Config $config  The route configuration.
	 * @param \WP_REST_Request            $request The incoming REST request.
	 *
	 * @return \WPE\AITK\WP\Rest\Contracts\Rest_Response_Interface The handler's response.
	 */
	private function invoke_handler( Route_Config $config, WP_REST_Request $request ): Rest_Response_Interface {
		$callback = $config->get_callback();

		if ( ! $config->has_request() ) {
			return $callback();
		}

		/** @var class-string<\WPE\AITK\WP\Rest\Contracts\Rest_Request_Interface> $class */
		$class = $config->get_request();

		return $callback( $class::from_array( $request->get_params() ) );
	}

	/**
	 * Prevents duplicate route+method registrations.
	 *
	 * @param \WPE\AITK\WP\Rest\Route_Config $config The route configuration to check.
	 *
	 * @throws \WPE\AITK\WP\Rest\Exceptions\Duplicate_Route_Exception If the route+method is already registered.
	 */
	private function guard_duplicate( Route_Config $config ): void {
		$key = $config->get_route() . ':' . $config->get_methods();

		if ( isset( $this->registered_routes[ $key ] ) ) {
			throw new Duplicate_Route_Exception(
				sprintf(
					'Route "%s %s/%s" is already registered.',
					$config->get_methods(),
					self::REST_NAMESPACE,
					$config->get_route()
				)
			);
		}

		$this->registered_routes[ $key ] = true;
	}
}
