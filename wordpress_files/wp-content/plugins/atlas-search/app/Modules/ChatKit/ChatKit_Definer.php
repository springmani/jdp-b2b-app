<?php

declare(strict_types=1);

namespace WPE\AITK\Modules\ChatKit;

use WPE\AITK\Core\Container\Container;
use WPE\AITK\Core\Definer\Contracts\Service_Definer_Interface;
use WPE\AITK\Core\Utils\Json_Decoder;
use WPE\AITK\Modules\ChatKit\ChatKit_Block_Renderer as Block_Renderer;
use WPE\AITK\Modules\ChatKit\ChatKit_Script_Enqueuer as Script_Enqueuer;
use WPE\AITK\Modules\ChatKit\ChatKit_Settings_Handler as Settings_Handler;
use WPE\AITK\WP\Ajax\Ajax_Responder;
use WPE\AITK\WP\Nonce\WP_Nonce;
use WPE\AITK\WP\Script\Inline_Var_Injector;
use WPE\AITK\WP\User\Capability_Checker;
use WPE\AITK\WP\User\Current_User_Provider;

/**
 * ChatKit Definer.
 *
 * Defines service bindings for ChatKit module.
 */
final class ChatKit_Definer implements Service_Definer_Interface {
	/**
	 * @inheritDoc
	 */
	public function define(): array {
		return [

			API_Handler::class      => static fn ( Container $container ): API_Handler => new API_Handler(
				$container->get( Current_User_Provider::class ),
				$container->get( Json_Decoder::class )
			),

			Block_Renderer::class   => static fn (): Block_Renderer => new Block_Renderer(),

			Script_Enqueuer::class  => static fn ( Container $container ): Script_Enqueuer => new Script_Enqueuer(
				$container->get( Inline_Var_Injector::class ),
				$container->get( WP_Nonce::class )
			),

			Settings_Handler::class => static fn ( Container $container ): Settings_Handler => new Settings_Handler(
				$container->get( Current_User_Provider::class ),
				$container->get( Capability_Checker::class ),
				$container->get( WP_Nonce::class ),
				$container->get( Ajax_Responder::class )
			),
		];
	}
}
