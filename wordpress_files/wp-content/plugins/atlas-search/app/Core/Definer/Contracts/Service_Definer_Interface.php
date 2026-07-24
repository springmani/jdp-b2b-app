<?php

declare(strict_types=1);

namespace WPE\AITK\Core\Definer\Contracts;

interface Service_Definer_Interface {
	/**
	 * @return array<class-string, callable(\Psr\Container\ContainerInterface): object>
	 */
	public function define(): array;
}
