<?php

declare(strict_types=1);

namespace WPE\AITK\WP\Rest\Exceptions;

use RuntimeException;

/**
 * Base REST Exception.
 *
 * All REST-specific exceptions extend this class and carry
 * HTTP status code and machine-readable error code metadata.
 */
abstract class Rest_Exception extends RuntimeException {
	/**
	 * HTTP status code for this exception.
	 *
	 * @var int
	 */
	protected int $status_code = 500;

	/**
	 * Machine-readable error code.
	 *
	 * @var string
	 */
	protected string $error_code = 'rest_error';

	/**
	 * Returns the HTTP status code associated with this exception.
	 */
	public function get_status_code(): int {
		return $this->status_code;
	}

	/**
	 * Returns the machine-readable error code.
	 */
	public function get_error_code(): string {
		return $this->error_code;
	}
}
