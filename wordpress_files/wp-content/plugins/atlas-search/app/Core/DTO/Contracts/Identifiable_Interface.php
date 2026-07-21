<?php

declare(strict_types=1);

namespace WPE\AITK\Core\DTO\Contracts;

/**
 * Represents an object that can provide a deterministic unique identifier.
 * For most of the cases, the trait 'With_Unique_ID' can be used to implement this interface.
 */
interface Identifiable_Interface {
	/**
	 * Returns a unique string identifier representing this object's state or identity.
	 */
	public function get_unique_id(): string;
}
