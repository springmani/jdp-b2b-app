<?php

declare(strict_types=1);

namespace WPE\AITK\WP\Rest\Exceptions;

/**
 * Service Unavailable Exception.
 *
 * Thrown when an external service or dependency is temporarily unavailable. Maps to HTTP 503.
 */
final class Service_Unavailable_Exception extends Rest_Exception {
	/**
	 * @var int
	 */
	protected int $status_code = 503;

	/**
	 * @var string
	 */
	protected string $error_code = 'service_unavailable';
}
