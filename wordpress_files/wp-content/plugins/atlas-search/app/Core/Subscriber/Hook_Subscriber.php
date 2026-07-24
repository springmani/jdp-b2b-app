<?php

declare(strict_types=1);

namespace WPE\AITK\Core\Subscriber;

use Psr\Container\ContainerInterface;
use WPE\AITK\Core\Subscriber\Contracts\Hook_Subscriber_Interface;

abstract class Hook_Subscriber implements Hook_Subscriber_Interface {
	/**
	 * The psr-11 container.
	 *
	 * @var \Psr\Container\ContainerInterface
	 */
	protected ContainerInterface $container;

	public function __construct( ContainerInterface $container ) {
		$this->container = $container;
	}
}
