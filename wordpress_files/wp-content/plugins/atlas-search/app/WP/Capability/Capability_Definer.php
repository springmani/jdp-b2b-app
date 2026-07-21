<?php

declare(strict_types=1);

namespace WPE\AITK\WP\Capability;

use WPE\AITK\Core\Container\Container;
use WPE\AITK\Core\Definer\Contracts\Service_Definer_Interface;
use WPE\AITK\WP\Capability\Contracts\Capability_Service_Interface;
use WPE\AITK\WP\GraphQL\GraphQL_Client;

final class Capability_Definer implements Service_Definer_Interface {
	/**
	 * @inheritDoc
	 */
	public function define(): array {
		return [
			Capability_Service::class => static fn (
				Container $container
			): Capability_Service_Interface => new Capability_Service(
				$container->get( GraphQL_Client::class )
			),
		];
	}
}
