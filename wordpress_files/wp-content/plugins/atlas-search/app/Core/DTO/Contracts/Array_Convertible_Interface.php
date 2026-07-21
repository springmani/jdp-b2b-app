<?php

declare(strict_types=1);

namespace WPE\AITK\Core\DTO\Contracts;

interface Array_Convertible_Interface {
	/**
	 * Converts this object into a normalized associative array.
	 *
	 * @return array<string, scalar>
	 */
	public function to_array(): array;
}
