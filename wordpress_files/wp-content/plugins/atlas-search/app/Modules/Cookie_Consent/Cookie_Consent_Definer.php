<?php

declare(strict_types=1);

namespace WPE\AITK\Modules\Cookie_Consent;

use WPE\AITK\Core\Container\Container;
use WPE\AITK\Core\Definer\Contracts\Service_Definer_Interface;
use WPE\AITK\Modules\Cookie_Consent\Contracts\Acknowledgement_Storage_Interface;
use WPE\AITK\Modules\Cookie_Consent\Data\Cookie_Consent_Config;
use WPE\AITK\Modules\Cookie_Consent\Storage\Network_Acknowledgement_Storage;
use WPE\AITK\Modules\Cookie_Consent\Storage\Site_Acknowledgement_Storage;
use WPE\AITK\WP\Ajax\Ajax_Responder;
use WPE\AITK\WP\Asset\Script\Script_Handler;
use WPE\AITK\WP\Context\Page_Context;
use WPE\AITK\WP\Debug\Loggers\Composite_Logger;
use WPE\AITK\WP\GraphQL\GraphQL_Client;
use WPE\AITK\WP\Multisite\WP_Multisite_Environment;
use WPE\AITK\WP\Nonce\WP_Nonce;
use WPE\AITK\WP\Notice\Notice_Renderer;
use WPE\AITK\WP\Script\Inline_Var_Injector;
use WPE\AITK\WP\User\Capability_Checker;
use WPE\AITK\WP\User\Current_User_Provider;

final class Cookie_Consent_Definer implements Service_Definer_Interface {
	/**
	 * @inheritDoc
	 */
	public function define(): array {
		return [
			Cookie_Consent_Config::class  => static fn ( Container $c ) => self::create_config(
				$c->get( WP_Multisite_Environment::class )
			),

			Cookie_Consent_Handler::class => static fn ( Container $c ) => new Cookie_Consent_Handler(
				$c->get( Cookie_Consent_Config::class ),
				self::resolve_storage( $c->get( WP_Multisite_Environment::class ) ),
				$c->get( GraphQL_Client::class ),
				$c->get( Ajax_Responder::class ),
				$c->get( Capability_Checker::class ),
				$c->get( Current_User_Provider::class ),
				$c->get( WP_Nonce::class ),
				$c->get( Notice_Renderer::class ),
				$c->get( Script_Handler::class ),
				$c->get( Inline_Var_Injector::class ),
				$c->get( Composite_Logger::class )
			),
		];
	}

	private static function create_config( WP_Multisite_Environment $env ): Cookie_Consent_Config {
		if ( $env->is_multisite() ) {
			return new Cookie_Consent_Config(
				'network_acknowledge_data_collection',
				'hybrid-settings',
				new Page_Context( 'toplevel_page_wpengine-smart-search-network', null, true ),
				'manage_network',
				'network_admin_notices'
			);
		}

		return new Cookie_Consent_Config(
			'acknowledge_data_collection',
			'recommendations',
			new Page_Context( 'toplevel_page_wpengine-ai-toolkit' ),
			'manage_options',
			'admin_notices'
		);
	}

	private static function resolve_storage( WP_Multisite_Environment $env ): Acknowledgement_Storage_Interface {
		return $env->is_multisite()
			? new Network_Acknowledgement_Storage( 'wpe_aitk_network_data_collection_acknowledged' )
			: new Site_Acknowledgement_Storage( 'wpe_aitk_data_collection_acknowledged' );
	}
}
