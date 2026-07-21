<?php

declare(strict_types=1);

namespace WPE\AITK\WP\GraphQL;

use WPE\AITK\Core\Container\Container;
use WPE\AITK\Core\Definer\Contracts\Service_Definer_Interface;
use WPE\AITK\Core\Utils\Json_Decoder;
use WPE\AITK\Modules\System\Data\Content_Engine_Connection;
use WPE\AITK\WP\GraphQL\Contracts\GraphQL_Client_Interface;
use WPE\AITK\WP\Plugin\Config;
use Wpe_Content_Engine\WPSettings;

use function AtlasSearch\Support\WordPress\get_option;

/**
 * GraphQL Definer.
 *
 * Registers the GraphQL client and its dependencies in the service container.
 */
final class GraphQL_Definer implements Service_Definer_Interface {
	/**
	 * @inheritDoc
	 */
	public function define(): array {
		return [
			GraphQL_Client::class => static fn ( Container $container ): GraphQL_Client_Interface => new GraphQL_Client(
				Content_Engine_Connection::from_array(
					get_option( WPSettings::WPE_CONTENT_ENGINE_OPTION_NAME, [] )
				),
				$container->get( Config::class )->get_version(),
				$container->get( Json_Decoder::class )
			),
		];
	}
}
