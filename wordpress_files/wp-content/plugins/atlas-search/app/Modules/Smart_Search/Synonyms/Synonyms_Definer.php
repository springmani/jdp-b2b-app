<?php

declare(strict_types=1);

namespace WPE\AITK\Modules\Smart_Search\Synonyms;

use WPE\AITK\Core\Container\Container;
use WPE\AITK\Core\Definer\Contracts\Service_Definer_Interface;
use WPE\AITK\WP\Debug\Loggers\Composite_Logger;
use WPE\AITK\WP\GraphQL\GraphQL_Client;

final class Synonyms_Definer implements Service_Definer_Interface {
	/**
	 * @inheritDoc
	 */
	public function define(): array {
		return [
			Synonyms_GraphQL_Handler::class => static fn (
				Container $c
			): Synonyms_GraphQL_Handler => new Synonyms_GraphQL_Handler(
				$c->get( GraphQL_Client::class ),
				$c->get( Composite_Logger::class )
			),

			Synonyms_Rest_Controller::class => static fn (
				Container $c
			): Synonyms_Rest_Controller => new Synonyms_Rest_Controller(
				$c->get( Synonyms_GraphQL_Handler::class )
			),
		];
	}
}
