<?php

declare(strict_types=1);

namespace WPE\AITK\WP\Notice\Contracts;

interface Notice_Interface {
	public function get_message(): string;

	public function get_type(): string;

	public function get_dismissible(): bool;

	public function get_id(): string;

	/**
	 * @return array<string>
	 */
	public function get_additional_classes(): array;

	/**
	 * @return array<string, scalar>
	 */
	public function get_attributes(): array;
}
