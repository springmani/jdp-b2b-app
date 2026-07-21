<?php

declare(strict_types=1);

namespace WPE\AITK\WP\Rest\Exceptions;

/**
 * Unprocessable Entity Exception.
 *
 * Thrown when the request is well-formed but contains semantic validation errors. Maps to HTTP 422.
 */
final class Unprocessable_Entity_Exception extends Rest_Exception {
	/**
	 * @var int
	 */
	protected int $status_code = 422;

	/**
	 * @var string
	 */
	protected string $error_code = 'unprocessable_entity';
}
