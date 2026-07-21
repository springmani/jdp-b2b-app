<?php

declare(strict_types=1);

namespace WPE\AITK\WP\Rest\Exceptions;

/**
 * Conflict Exception.
 *
 * Thrown when the request conflicts with the current state of the resource. Maps to HTTP 409.
 */
final class Conflict_Exception extends Rest_Exception {
	/**
	 * @var int
	 */
	protected int $status_code = 409;

	/**
	 * @var string
	 */
	protected string $error_code = 'conflict';
}
