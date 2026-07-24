<?php

declare(strict_types=1);

namespace WPE\AITK\WP\Capability\Data;

use WPE\AITK\WP\GraphQL\Contracts\GraphQL_Response_Interface;

final class Capabilities_Response implements GraphQL_Response_Interface {
	/**
	 * @var array<int, string>
	 */
	private array $capabilities;

	/**
	 * @param array<int, string> $capabilities List of capability strings.
	 */
	public function __construct( array $capabilities ) {
		$this->capabilities = $capabilities;
	}

	/**
	 * @inheritDoc
	 */
	public static function from_array( array $data ): self {
		$capabilities = $data['capabilities'] ?? [];

		return new self( is_array( $capabilities ) ? $capabilities : [] );
	}

	/**
	 * @return array<int, string>
	 */
	public function get_capabilities(): array {
		return $this->capabilities;
	}
}
