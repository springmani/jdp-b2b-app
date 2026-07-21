<?php

declare(strict_types=1);

namespace WPE\AITK\Modules\Smart_Search;

use WPE\AITK\Core\Container\Container;
use WPE\AITK\Core\Definer\Contracts\Service_Definer_Interface;
use WPE\AITK\Modules\Smart_Search\Search_Item\Search_Item_Cleanup_Handler;
use WPE\AITK\Modules\Smart_Search\Search_Item\Search_Item_Remover;
use WPE\AITK\WP\Post\Post_Visibility;

/**
 * Smart Search Definer.
 *
 * Defines service bindings for Smart Search module.
 */
final class Smart_Search_Definer implements Service_Definer_Interface {
	/**
	 * @inheritDoc
	 */
	public function define(): array {
		return [
			Search_Item_Cleanup_Handler::class => static fn (
				Container $container
			): Search_Item_Cleanup_Handler => new Search_Item_Cleanup_Handler(
				$container->get( Search_Item_Remover::class ),
				$container->get( Post_Visibility::class )
			),

			Search_Item_Remover::class         =>
				static fn (): Search_Item_Remover => new Search_Item_Remover(),
		];
	}
}
