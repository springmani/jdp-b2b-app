<?php

declare(strict_types=1);

namespace WPE\AITK\WP\Multisite;

use WPE\AITK\Core\Container\Container;
use WPE\AITK\Core\Definer\Contracts\Service_Definer_Interface;
use WPE\AITK\WP\Plugin\Config;

final class Multisite_Definer implements Service_Definer_Interface {
	/**
	 * @inheritDoc
	 */
	public function define(): array {
		return [
			WP_Multisite_Environment::class => static fn (
				Container $container
			): WP_Multisite_Environment => new WP_Multisite_Environment(
				$container->get( Config::class )->get_basename()
			),

			WP_Site_Context::class          => static fn (
				Container $container
			): WP_Site_Context => new WP_Site_Context(
				$container->get( WP_Multisite_Environment::class )
			),

			WP_Site_Repository::class       => static fn (
				Container $container
			): WP_Site_Repository => new WP_Site_Repository(
				$container->get( WP_Multisite_Environment::class )
			),
		];
	}
}
