<?php

declare(strict_types=1);

namespace WPE\AITK\Core\Reflection\Docblock\Contracts;

/**
 * Common interface for all type constraint information objects.
 *
 * Represents parsed constraint information from docblock annotations.
 * Implementations include Int_Info, Float_Info, String_Info and Null_Info.
 */
interface Info_Interface {
	/**
	 * Checks if this type has any constraints.
	 *
	 * @return bool True if constraints are defined, false otherwise.
	 */
	public function has_constraints(): bool;
}
