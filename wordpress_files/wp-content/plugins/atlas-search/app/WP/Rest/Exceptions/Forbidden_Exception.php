<?php

declare(strict_types=1);

namespace WPE\AITK\WP\Rest\Exceptions;

/**
 * Forbidden Exception.
 *
 * Thrown when the authenticated user lacks required permissions. Maps to HTTP 403.
 */
final class Forbidden_Exception extends Rest_Exception {
	/**
	 * @var int
	 */
	protected int $status_code = 403;

	/**
	 * @var string
	 */
	protected string $error_code = 'forbidden';
}
