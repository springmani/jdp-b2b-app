<?php

declare(strict_types=1);

namespace WPE\AITK\WP\Ajax;

use WPE\AITK\WP\Ajax\Contracts\Ajax_Response_Interface;
use WPE\AITK\WP\Ajax\Data\Base_Ajax_Response_Data as Response_Data;

final class Ajax_Response implements Ajax_Response_Interface {
	/**
	 * Indicates whether the AJAX request was successful.
	 *
	 * @var bool
	 */
	protected bool $success;

	/**
	 * Message providing additional information about the AJAX response.
	 *
	 * @var string
	 */
	protected string $message;

	/**
	 * @var int
	 */
	protected int $status_code;

	/**
	 * Additional data associated with the AJAX response.
	 *
	 * @var \WPE\AITK\WP\Ajax\Data\Base_Ajax_Response_Data|null
	 */
	protected ?Response_Data $data;

	public function __construct( bool $success, string $message, int $status_code, ?Response_Data $data = null ) {
		$this->success     = $success;
		$this->message     = $message;
		$this->status_code = $status_code;
		$this->data        = $data;
	}

	public function get_success(): bool {
		return $this->success;
	}

	public function get_message(): string {
		return $this->message;
	}

	public function get_status_code(): int {
		return $this->status_code;
	}

	public function get_response_data(): ?Response_Data {
		return $this->data;
	}
}
