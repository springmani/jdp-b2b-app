<?php

declare(strict_types=1);

namespace WPE\AITK\Modules\Smart_Search\Synonyms;

use WPE\AITK\Core\Subscriber\Hook_Subscriber;
use WPE\AITK\WP\Rest\Route_Registrar;

/**
 * Synonyms Module Subscriber.
 *
 * Registers REST API routes for synonym rule management.
 */
final class Synonyms_Subscriber extends Hook_Subscriber {
	/**
	 * @throws \Psr\Container\ContainerExceptionInterface Exception.
	 * @throws \Psr\Container\NotFoundExceptionInterface Exception.
	 */
	public function register(): void {
		$this->container->get( Route_Registrar::class )->add_controller(
			$this->container->get( Synonyms_Rest_Controller::class )
		);
	}
}
