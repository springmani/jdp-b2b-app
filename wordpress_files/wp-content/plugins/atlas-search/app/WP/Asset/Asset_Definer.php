<?php

declare(strict_types=1);

namespace WPE\AITK\WP\Asset;

use Psr\Container\ContainerInterface;
use WPE\AITK\Core\Definer\Contracts\Service_Definer_Interface;
use WPE\AITK\WP\Asset\Script\Script_Handler;
use WPE\AITK\WP\Asset\Style\Style_Handler;
use WPE\AITK\WP\Plugin\Config;

final class Asset_Definer implements Service_Definer_Interface {
	/**
	 * @inheritDoc
	 */
	public function define(): array {
		return [
			Script_Handler::class => static fn ( ContainerInterface $container ): Script_Handler => new Script_Handler(
				$container->get( Config::class )
			),
			Style_Handler::class  => static fn ( ContainerInterface $container ): Style_Handler => new Style_Handler(
				$container->get( Config::class )
			),
		];
	}
}
