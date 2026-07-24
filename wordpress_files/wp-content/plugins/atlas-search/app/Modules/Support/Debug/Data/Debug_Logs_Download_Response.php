<?php

declare(strict_types=1);

namespace WPE\AITK\Modules\Support\Debug\Data;

use WPE\AITK\WP\Rest\Contracts\Rest_Response_Interface;

final class Debug_Logs_Download_Response implements Rest_Response_Interface {
	private bool $success;

	private string $filename;

	private string $content;

	private string $error;

	public function __construct( bool $success, string $filename, string $content, string $error = '' ) {
		$this->success  = $success;
		$this->filename = $filename;
		$this->content  = $content;
		$this->error    = $error;
	}

	/**
	 * @inheritDoc
	 */
	public function to_array(): array {
		return [
			'content'  => $this->content,
			'error'    => $this->error,
			'filename' => $this->filename,
			'success'  => $this->success,
		];
	}
}
