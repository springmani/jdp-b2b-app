<?php

declare(strict_types=1);

namespace WPE\AITK\WP\Schema;

use WPE\AITK\Core\Container\Container;
use WPE\AITK\Core\Definer\Contracts\Service_Definer_Interface;
use WPE\AITK\Core\Reflection\Property_Mapper;
use WPE\AITK\Core\Reflection\Property_Parser;
use WPE\AITK\WP\Schema\Contracts\Schema_Builder_Interface;

final class Schema_Definer implements Service_Definer_Interface {
	/**
	 * @inheritDoc
	 */
	public function define(): array {
		return [
			Schema_Builder::class => static fn ( Container $container ): Schema_Builder_Interface => new Schema_Builder(
				$container->get( Property_Mapper::class ),
				$container->get( Property_Parser::class )
			),
		];
	}
}
