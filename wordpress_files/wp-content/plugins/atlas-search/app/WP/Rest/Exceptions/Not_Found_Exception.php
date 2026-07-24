<?php

declare(strict_types=1);

namespace WPE\AITK\WP\Rest\Exceptions;

/**
 * Not Found Exception.
 *
 * Thrown when the requested resource does not exist. Maps to HTTP 404.
 */
final class Not_Found_Exception extends Rest_Exception {
	/**
	 * @var int
	 */
	protected int $status_code = 404;

	/**
	 * @var string
	 */
	protected string $error_code = 'not_found';
}
