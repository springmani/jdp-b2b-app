<?php

declare(strict_types=1);

namespace WPE\AITK\WP\GraphQL\Exceptions;

use Throwable;

/**
 * Thrown when the HTTP transport fails (WP errors, timeouts, non-2xx status codes).
 */
final class GraphQL_Connection_Exception extends GraphQL_Exception {
	/**
	 * @var int
	 */
	private int $status_code;

	public function __construct( string $message, int $status_code = 0, ?Throwable $previous = null ) {
		$this->status_code = $status_code;

		parent::__construct( $message, $status_code, $previous );
	}

	public function get_status_code(): int {
		return $this->status_code;
	}
}
