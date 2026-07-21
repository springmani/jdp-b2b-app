<?php

declare(strict_types=1);

namespace WPE\AITK\WP\Asset\Style;

use WPE\AITK\WP\Asset\Base_Asset_Handler;
use WPE\AITK\WP\Asset\Style\Contracts\Style_Asset_Interface;
use WPE\AITK\WP\Asset\Style\Contracts\Style_Handler_Interface;
use WPE\AITK\WP\Asset\Style\Exceptions\Style_Registration_Exception;

final class Style_Handler extends Base_Asset_Handler implements Style_Handler_Interface {
	public function register( Style_Asset_Interface $asset ): void {
		$handle = $asset->get_handle();

		$result = wp_register_style(
			$handle,
			$this->get_plugin_src( $asset->get_src() ),
			$asset->get_deps(),
			$this->config->get_version(),
			$asset->get_media()
		);

		if ( ! $result ) {
			throw new Style_Registration_Exception(
				sprintf( 'Failed to register style asset with handle "%s".', $handle )
			);
		}
	}

	public function enqueue( Style_Asset_Interface $asset ): void {
		if ( ! wp_style_is( $asset->get_handle(), self::REGISTERED ) ) {
			$this->register( $asset );
		}

		wp_enqueue_style( $asset->get_handle() );
	}
}
