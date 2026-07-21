<?php

declare(strict_types=1);

namespace WPE\AITK\Modules\Cookie_Consent;

use WPE\AITK\Core\Subscriber\Hook_Subscriber;
use WPE\AITK\Modules\Cookie_Consent\Data\Cookie_Consent_Config;

final class Cookie_Consent_Subscriber extends Hook_Subscriber {
	public function register(): void {
		$config  = $this->container->get( Cookie_Consent_Config::class );
		$handler = $this->container->get( Cookie_Consent_Handler::class );

		add_action( 'wp_ajax_' . $config->get_ajax_action(), [ $handler, 'acknowledge_data_collection' ] );
		add_action( 'admin_enqueue_scripts', [ $handler, 'enqueue_admin_scripts' ] );
		add_action( $config->get_notices_action(), [ $handler, 'display_admin_notice' ] );
	}
}
