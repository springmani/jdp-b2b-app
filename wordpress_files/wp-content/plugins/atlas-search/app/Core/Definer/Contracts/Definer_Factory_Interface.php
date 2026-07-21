<?php

declare(strict_types=1);

namespace WPE\AITK\Core\Definer\Contracts;

interface Definer_Factory_Interface {
	/**
	 * Create a service definer instance for the given class.
	 *
	 * @param string $definer_class The class name of the service definer interface implementation.
	 *
	 * @phpstan-param class-string<\WPE\AITK\Core\Definer\Contracts\Service_Definer_Interface> $definer_class Service definer class name.
	 */
	public function make( string $definer_class ): Service_Definer_Interface;
}
