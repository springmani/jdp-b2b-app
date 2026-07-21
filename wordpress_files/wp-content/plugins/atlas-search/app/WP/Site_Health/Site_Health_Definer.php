<?php

declare(strict_types=1);

namespace WPE\AITK\WP\Site_Health;

use Psr\Container\ContainerInterface as Container;
use WPE\AITK\Core\Definer\Contracts\Service_Definer_Interface;
use WPE\AITK\Core\Timer\Performance_Timer;
use WPE\AITK\WP\Debug\Loggers\Composite_Logger;
use WPE\AITK\WP\Plugin\Plugin_Name_Resolver;
use WPE\AITK\WP\Site_Health\Debug_Info\Debug_Info_Registry as Debug_Registry;
use WPE\AITK\WP\Site_Health\Test\Async_Test_Controller as Controller;
use WPE\AITK\WP\Site_Health\Test\Direct_Test_Runner as Runner;
use WPE\AITK\WP\Site_Health\Test\Test_Registry as Registry;
use WPE\AITK\WP\Site_Health\Test\Test_Result_Formatter as Formatter;

final class Site_Health_Definer implements Service_Definer_Interface {
	/**
	 * @inheritDoc
	 */
	public function define(): array {
		return [
			Controller::class     => static fn ( Container $c ): Controller => new Controller(
				$c->get( Registry::class ),
				$c->get( Formatter::class )
			),
			Debug_Registry::class => static fn ( Container $c ): Debug_Registry => new Debug_Registry(
				$c->get( Plugin_Name_Resolver::class )
			),
			Formatter::class      => static fn ( Container $c ): Formatter => new Formatter(
				$c->get( Plugin_Name_Resolver::class )
			),
			Registry::class       => static fn ( Container $c ): Registry => new Registry(
				$c->get( Runner::class )
			),
			Runner::class         => static fn ( Container $c ): Runner => new Runner(
				$c->get( Performance_Timer::class ),
				$c->get( Composite_Logger::class ),
				$c->get( Formatter::class )
			),
		];
	}
}
