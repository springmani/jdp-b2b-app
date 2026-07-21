<?php

declare(strict_types=1);

namespace WPE\AITK\WP\Rest\Exceptions;

/**
 * Server Exception.
 *
 * Thrown for internal server errors. Maps to HTTP 500.
 */
final class Server_Exception extends Rest_Exception {
	/**
	 * @var int
	 */
	protected int $status_code = 500;

	/**
	 * @var string
	 */
	protected string $error_code = 'server_error';
}
