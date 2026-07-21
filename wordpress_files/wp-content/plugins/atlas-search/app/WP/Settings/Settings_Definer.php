<?php

declare(strict_types=1);

namespace WPE\AITK\WP\Settings;

use WPE\AITK\Core\Container\Container;
use WPE\AITK\Core\Definer\Contracts\Service_Definer_Interface;
use WPE\AITK\WP\Debug\Loggers\Composite_Logger;
use WPE\AITK\WP\Schema\Schema_Builder;
use WPE\AITK\WP\Settings\Contracts\Settings_Registrar_Interface;
use WPE\AITK\WP\Settings\Contracts\Settings_Repository_Interface;

final class Settings_Definer implements Service_Definer_Interface {
	/**
	 * @inheritDoc
	 */
	public function define(): array {
		return [
			Network_Settings_Manager::class =>
				static fn ( Container $c ): Network_Settings_Manager => new Network_Settings_Manager(
					new Settings_Repository(),
					$c->get( Composite_Logger::class )
				),

			Settings_Manager::class         => static fn ( Container $c ): Settings_Manager => new Settings_Manager(
				new Settings_Repository(),
				$c->get( Composite_Logger::class )
			),

			Settings_Registrar::class       =>
				static fn ( Container $container ): Settings_Registrar_Interface => new Settings_Registrar(
					$container->get( Schema_Builder::class )
				),

			Settings_Repository::class      =>
				static fn (): Settings_Repository_Interface => new Settings_Repository(),
		];
	}
}
