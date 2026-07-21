<?php

declare(strict_types=1);

namespace WPE\AITK\Core\Container\Contracts;

use Psr\Container\ContainerInterface;

interface Container_Factory_Interface {
	/**
	 * @param array<class-string<\WPE\AITK\Core\Definer\Contracts\Service_Definer_Interface>> $definers Functionality definers.
	 *
	 * @return \Psr\Container\ContainerInterface Returns the created DI container.
	 *
	 * @throws \WPE\AITK\Core\Container\Exceptions\Container_Creation_Exception Throws if the container cannot be created.
	 */
	public function create_container( array $definers ): ContainerInterface;
}
