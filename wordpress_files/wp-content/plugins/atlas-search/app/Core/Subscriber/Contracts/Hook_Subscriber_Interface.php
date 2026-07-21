<?php

declare(strict_types=1);

namespace WPE\AITK\Core\Subscriber\Contracts;

interface Hook_Subscriber_Interface {
	/**
	 * Register the subscriber with the event dispatcher.
	 *
	 * @throws \Psr\Container\ContainerExceptionInterface Might be thrown if the container cannot resolve a dependency.
	 */
	public function register(): void;
}
