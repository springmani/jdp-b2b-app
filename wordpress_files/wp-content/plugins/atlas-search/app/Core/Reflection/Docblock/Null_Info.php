<?php

declare(strict_types=1);

namespace WPE\AITK\Core\Reflection\Docblock;

use WPE\AITK\Core\Reflection\Docblock\Contracts\Info_Interface;

/**
 * Null object implementation for type constraints.
 *
 * Represents absence of constraints in a type-safe way, eliminating the need for null checks in consuming code.
 */
final class Null_Info implements Info_Interface {
	public function has_constraints(): bool {
		// Always returns false as this represents no constraints.
		return false;
	}
}
