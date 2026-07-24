<?php

declare(strict_types=1);

namespace WPE\AITK\Modules\ChatKit\Data;

use WPE\AITK\Core\DTO\Contracts\Array_Convertible_Interface;

/**
 * ChatKit Configuration Data.
 *
 * Data object for ChatKit settings used in both frontend and block editor.
 */
final class ChatKit_Config_Data implements Array_Convertible_Interface {
	/**
	 * Whether ChatKit is properly enabled and configured.
	 *
	 * @var bool
	 */
	private bool $is_enabled;

	/**
	 * WordPress nonce for REST API authentication.
	 *
	 * @var string
	 */
	private string $nonce;

	/**
	 * REST API base URL.
	 *
	 * @var string
	 */
	private string $rest_url;

	/**
	 * Constructor.
	 *
	 * @param bool   $is_enabled Whether ChatKit is enabled.
	 * @param string $nonce      WordPress nonce.
	 * @param string $rest_url   REST API URL.
	 */
	public function __construct( bool $is_enabled, string $nonce, string $rest_url ) {
		$this->is_enabled = $is_enabled;
		$this->nonce      = $nonce;
		$this->rest_url   = $rest_url;
	}

	/**
	 * Convert to array for JavaScript consumption.
	 *
	 * @inheritDoc
	 *
	 * @see wordpress/wpengine-smart-search/src/blocks/chatkit-block/view.js
	 * @see wordpress/wpengine-smart-search/src/blocks/chatkit-block/edit.js
	 */
	public function to_array(): array {
		return [
			'isEnabled' => $this->is_enabled,
			'nonce'     => $this->nonce,
			'restUrl'   => $this->rest_url,
		];
	}
}
