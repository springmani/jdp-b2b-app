<?php

declare(strict_types=1);

namespace WPE\AITK\WP\Asset\Script;

use WPE\AITK\WP\Asset\Base_Asset_Handler;
use WPE\AITK\WP\Asset\Script\Contracts\Script_Asset_Interface;
use WPE\AITK\WP\Asset\Script\Contracts\Script_Handler_Interface;
use WPE\AITK\WP\Asset\Script\Exceptions\Script_Registration_Exception;

final class Script_Handler extends Base_Asset_Handler implements Script_Handler_Interface {
	public function register( Script_Asset_Interface $asset ): void {
		$handle    = $asset->get_handle();
		$strategy  = $asset->get_strategy();
		$in_footer = $asset->get_in_footer();

		$result = wp_register_script(
			$handle,
			$this->get_plugin_src( $asset->get_src() ),
			$asset->get_deps(),
			$this->config->get_version(),
			! empty( $strategy ) ? [
				'in_footer' => $in_footer,
				'strategy'  => $strategy,
			] : $in_footer
		);

		if ( ! $result ) {
			throw new Script_Registration_Exception(
				sprintf( 'Failed to register script asset with handle "%s".', $handle )
			);
		}
	}

	public function enqueue( Script_Asset_Interface $asset ): void {
		if ( ! wp_script_is( $asset->get_handle(), self::REGISTERED ) ) {
			$this->register( $asset );
		}

		wp_enqueue_script( $asset->get_handle() );
	}
}
