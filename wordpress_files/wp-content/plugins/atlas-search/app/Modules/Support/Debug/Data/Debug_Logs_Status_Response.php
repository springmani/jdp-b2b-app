<?php

declare(strict_types=1);

namespace WPE\AITK\Modules\Support\Debug\Data;

use WPE\AITK\WP\Rest\Contracts\Rest_Response_Interface;

final class Debug_Logs_Status_Response implements Rest_Response_Interface {
	private bool $available;

	public function __construct( bool $available ) {
		$this->available = $available;
	}

	/**
	 * @inheritDoc
	 */
	public function to_array(): array {
		return [
			'available' => $this->available,
		];
	}
}
