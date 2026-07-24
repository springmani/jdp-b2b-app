<?php

declare(strict_types=1);

namespace WPE\AITK\Modules\Smart_Search\Recommendations;

use WPE\AITK\Core\Container\Container;
use WPE\AITK\Core\Definer\Contracts\Service_Definer_Interface;
use WPE\AITK\WP\Asset\Script\Script_Handler;
use WPE\AITK\WP\Debug\Loggers\Composite_Logger;
use WPE\AITK\WP\Nonce\Public_Nonce;
use WPE\AITK\WP\Script\Inline_Var_Injector;
use WPE\AITK\WP\Settings\Network_Settings_Manager;
use WPE\AITK\WP\Settings\Settings_Manager;

/**
 * Recommendations Definer.
 *
 * Defines service bindings for the Recommendations submodule.
 */
final class Recommendations_Definer implements Service_Definer_Interface {
	/**
	 * @inheritDoc
	 */
	public function define(): array {
		return [
			Recommendations_Analytics_Handler::class       => static fn (
				Container $container
			): Recommendations_Analytics_Handler => new Recommendations_Analytics_Handler(
				$container->get( Script_Handler::class ),
				$container->get( Inline_Var_Injector::class ),
				$container->get( Public_Nonce::class ),
				$container->get( Composite_Logger::class ),
				$container->get( Settings_Manager::class ),
				$container->get( Network_Settings_Manager::class )
			),

			Recommendations_Network_Rest_Controller::class => static fn (
				Container $container
			): Recommendations_Network_Rest_Controller => new Recommendations_Network_Rest_Controller(
				$container->get( Network_Settings_Manager::class )
			),
		];
	}
}
