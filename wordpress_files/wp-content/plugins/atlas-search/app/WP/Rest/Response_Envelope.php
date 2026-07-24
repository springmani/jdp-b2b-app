<?php

declare(strict_types=1);

namespace WPE\AITK\WP\Rest;

use WPE\AITK\WP\Rest\Contracts\Response_Envelope_Interface;
use WPE\AITK\WP\Rest\Contracts\Rest_Response_Interface;
use WPE\AITK\WP\Rest\Exceptions\Rest_Exception;
use WP_REST_Response;

/**
 * Response Envelope.
 *
 * Provides a consistent API for building REST API success and error responses.
 */
final class Response_Envelope implements Response_Envelope_Interface {
	/**
	 * Creates a success response from a typed response data object.
	 *
	 * @param \WPE\AITK\WP\Rest\Contracts\Rest_Response_Interface $data        Typed response data.
	 * @param int                                               $status_code HTTP status code.
	 */
	public function success( Rest_Response_Interface $data, int $status_code = 200 ): WP_REST_Response {
		return new WP_REST_Response( $data->to_array(), $status_code );
	}

	/**
	 * Creates an error response.
	 *
	 * @param string $message     Human-readable error message.
	 * @param int    $status_code HTTP status code.
	 * @param string $code        Machine-readable error code.
	 */
	public function error( string $message, int $status_code, string $code = 'rest_error' ): WP_REST_Response {
		return new WP_REST_Response(
			[
				'code'    => $code,
				'data'    => [ 'status' => $status_code ],
				'message' => $message,
			],
			$status_code
		);
	}

	/**
	 * Creates an error response from a REST exception.
	 *
	 * @param \WPE\AITK\WP\Rest\Exceptions\Rest_Exception $exception The REST exception.
	 */
	public function from_exception( Rest_Exception $exception ): WP_REST_Response {
		return $this->error(
			$exception->getMessage(),
			$exception->get_status_code(),
			$exception->get_error_code()
		);
	}
}
