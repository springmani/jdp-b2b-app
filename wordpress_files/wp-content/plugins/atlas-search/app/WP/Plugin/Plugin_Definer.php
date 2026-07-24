<?php

declare(strict_types=1);

namespace WPE\AITK\WP\Plugin;

use WPE\AITK\Core\Container\Container;
use WPE\AITK\Core\Definer\Contracts\Service_Definer_Interface;
use WPE\AITK\WP\Capability\Capability_Service;

final class Plugin_Definer implements Service_Definer_Interface {
	/**
	 * @inheritDoc
	 */
	public function define(): array {
		return [
			Config::class               => static fn (): Config => new Config(
				WPE_SMART_SEARCH_VERSION,
				WPE_SMART_SEARCH_PATH
			),
			Plugin_Name_Resolver::class => static fn (
				Container $container
			): Plugin_Name_Resolver => new Plugin_Name_Resolver(
				$container->get( Capability_Service::class )
			),
		];
	}
}
