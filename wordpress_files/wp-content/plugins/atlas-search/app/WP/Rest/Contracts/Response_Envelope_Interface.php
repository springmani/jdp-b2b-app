<?php

declare(strict_types=1);

namespace WPE\AITK\WP\Rest\Contracts;

use WP_REST_Response;

/**
 * Response Envelope Interface.
 *
 * Provides a consistent API for building REST API responses.
 */
interface Response_Envelope_Interface {
	/**
	 * Creates a success response from a typed response data object.
	 *
	 * @param \WPE\AITK\WP\Rest\Contracts\Rest_Response_Interface $data        Typed response data.
	 * @param int                                               $status_code HTTP status code.
	 */
	public function success( Rest_Response_Interface $data, int $status_code = 200 ): WP_REST_Response;

	/**
	 * Creates an error response.
	 *
	 * @param string $message     Human-readable error message.
	 * @param int    $status_code HTTP status code.
	 * @param string $code        Machine-readable error code.
	 */
	public function error( string $message, int $status_code, string $code = 'rest_error' ): WP_REST_Response;
}
