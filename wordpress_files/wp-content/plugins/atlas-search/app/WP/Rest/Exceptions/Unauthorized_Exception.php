<?php

declare(strict_types=1);

namespace WPE\AITK\WP\Rest\Exceptions;

/**
 * Unauthorized Exception.
 *
 * Thrown when authentication is missing or invalid. Maps to HTTP 401.
 */
final class Unauthorized_Exception extends Rest_Exception {
	/**
	 * @var int
	 */
	protected int $status_code = 401;

	/**
	 * @var string
	 */
	protected string $error_code = 'unauthorized';
}
