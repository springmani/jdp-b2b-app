<?php

declare(strict_types=1);

namespace WPE\AITK\WP\Site_Health\Debug_Info\Contracts;

interface Debug_Info_Field_Interface {
	public function get_key(): string;

	public function get_label(): string;

	public function get_value(): string;

	public function get_debug(): string;

	public function is_private(): bool;

	/**
	 * @return array<string, string|bool>
	 */
	public function to_array(): array;
}
