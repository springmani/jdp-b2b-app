<?php

declare(strict_types=1);

namespace WPE\AITK\Core\DTO\Contracts;

/**
 * Implementations should validate the array data and throw exceptions when required fields are missing or data is invalid.
 */
interface Array_Constructible_Interface {
	/**
	 * Creates an instance of this class from an associative array.
	 *
	 * @param array<array-key, scalar|array> $data Data to create the instance from.
	 *
	 * @return static
	 *
	 * @throws \WPE\AITK\Core\DTO\Exceptions\Invalid_Array_Data_Exception If required fields are missing or data is invalid.
	 */
	public static function from_array( array $data ): self;
}
