<?php

declare(strict_types=1);

namespace WPE\AITK\WP\Ajax\Contracts;

use WPE\AITK\WP\Ajax\Data\Base_Ajax_Response_Data;

interface Ajax_Response_Interface {
	public function get_success(): bool;

	public function get_message(): string;

	public function get_status_code(): int;

	/**
	 * Returns the data associated with the AJAX response. Can be null if no data needed.
	 */
	public function get_response_data(): ?Base_Ajax_Response_Data;
}
