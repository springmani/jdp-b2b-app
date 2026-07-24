<?php

declare(strict_types=1);

namespace WPE\AITK\WP\User;

use WPE\AITK\Core\Container\Container;
use WPE\AITK\Core\Definer\Contracts\Service_Definer_Interface;

final class User_Definer implements Service_Definer_Interface {
	/**
	 * @inheritDoc
	 */
	public function define(): array {
		return [
			Capability_Checker::class    => static fn (
				Container $container
			): Capability_Checker => new Capability_Checker(
				$container->get( Current_User_Provider::class )
			),
			Current_User_Provider::class => static fn (): Current_User_Provider => new Current_User_Provider(),
			Role_Checker::class          => static fn (): Role_Checker => new Role_Checker(),
		];
	}
}
