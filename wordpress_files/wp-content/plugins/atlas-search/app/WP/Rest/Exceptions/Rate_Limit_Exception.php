<?php

declare(strict_types=1);

namespace WPE\AITK\WP\Rest\Exceptions;

/**
 * Rate Limit Exception.
 *
 * Thrown when the client has exceeded the allowed request rate. Maps to HTTP 429.
 */
final class Rate_Limit_Exception extends Rest_Exception {
	/**
	 * @var int
	 */
	protected int $status_code = 429;

	/**
	 * @var string
	 */
	protected string $error_code = 'rate_limit_exceeded';
}
