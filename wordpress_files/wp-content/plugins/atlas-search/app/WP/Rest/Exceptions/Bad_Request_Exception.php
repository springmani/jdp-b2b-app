<?php

declare(strict_types=1);

namespace WPE\AITK\WP\Rest\Exceptions;

/**
 * Bad Request Exception.
 *
 * Thrown for invalid input or malformed requests. Maps to HTTP 400.
 */
final class Bad_Request_Exception extends Rest_Exception {
	/**
	 * @var int
	 */
	protected int $status_code = 400;

	/**
	 * @var string
	 */
	protected string $error_code = 'bad_request';
}
