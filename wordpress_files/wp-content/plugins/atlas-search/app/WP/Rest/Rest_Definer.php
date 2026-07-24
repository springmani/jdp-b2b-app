<?php

declare(strict_types=1);

namespace WPE\AITK\WP\Rest;

use WPE\AITK\Core\Container\Container;
use WPE\AITK\Core\Definer\Contracts\Service_Definer_Interface;
use WPE\AITK\Core\Reflection\Property_Mapper;
use WPE\AITK\Core\Reflection\Property_Parser;
use WPE\AITK\WP\Schema\Schema_Builder;
use WPE\AITK\WP\User\Capability_Checker;

/**
 * REST Definer.
 *
 * Defines service bindings for the REST API infrastructure.
 */
final class Rest_Definer implements Service_Definer_Interface {
	/**
	 * @inheritDoc
	 */
	public function define(): array {
		return [
			Response_Envelope::class => static fn (): Response_Envelope => new Response_Envelope(),
			Rest_Args_Builder::class => static fn (
				Container $container
			): Rest_Args_Builder => new Rest_Args_Builder(
				$container->get( Property_Mapper::class ),
				$container->get( Property_Parser::class )
			),
			Route_Registrar::class   => static fn (
				Container $container
			): Route_Registrar => new Route_Registrar(
				$container->get( Rest_Args_Builder::class ),
				$container->get( Schema_Builder::class ),
				$container->get( Response_Envelope::class ),
				$container->get( Capability_Checker::class )
			),
		];
	}
}
