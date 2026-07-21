<?php

declare(strict_types=1);

namespace WPE\AITK\WP\Site_Health\Debug_Info\Data;

use WPE\AITK\WP\Site_Health\Debug_Info\Contracts\Debug_Info_Field_Interface;

abstract class Abstract_Debug_Info_Field implements Debug_Info_Field_Interface {
	abstract public function get_key(): string;

	abstract public function get_label(): string;

	abstract public function get_value(): string;

	public function get_debug(): string {
		return '';
	}

	public function is_private(): bool {
		return false;
	}

	/**
	 * @return array<string, string|bool>
	 */
	public function to_array(): array {
		$data = [
			'label' => $this->get_label(),
			'value' => $this->get_value(),
		];

		if ( '' !== $this->get_debug() ) {
			$data['debug'] = $this->get_debug();
		}

		if ( $this->is_private() ) {
			$data['private'] = true;
		}

		return $data;
	}
}
