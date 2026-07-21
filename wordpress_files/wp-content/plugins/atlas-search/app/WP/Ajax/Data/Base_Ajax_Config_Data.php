<?php

declare(strict_types=1);

namespace WPE\AITK\WP\Ajax\Data;

use WPE\AITK\Core\DTO\Contracts\Array_Convertible_Interface;
use WPE\AITK\WP\Ajax\Contracts\Ajax_Config_Interface;

/**
 * Base class for AJAX configuration data.
 * Should be extended to provide more specific configuration data when needed.
 * Should be added inline next to the JS script that makes the AJAX call.
 */
abstract class Base_Ajax_Config_Data implements Ajax_Config_Interface, Array_Convertible_Interface {
	/**
	 * Action name for the AJAX request.
	 *
	 * @var string
	 */
	protected string $action;

	/**
	 * Nonce for security verification.
	 *
	 * @var string
	 */
	protected string $nonce;

	public function __construct( string $action, string $nonce ) {
		$this->action = $action;
		$this->nonce  = $nonce;
	}

	public function get_action(): string {
		return $this->action;
	}

	public function get_nonce(): string {
		return $this->nonce;
	}

	/**
	 * @inheritdoc
	 */
	public function to_array(): array {
		return [
			'action' => $this->get_action(),
			'nonce'  => $this->get_nonce(),
		];
	}
}
