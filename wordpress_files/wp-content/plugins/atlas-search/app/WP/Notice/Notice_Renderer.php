<?php

declare(strict_types=1);

namespace WPE\AITK\WP\Notice;

use WPE\AITK\WP\Notice\Contracts\Notice_Interface;
use WPE\AITK\WP\Notice\Contracts\Notice_Renderer_Interface;
use WPE\AITK\WP\Notice\Contracts\Notice_With_SVG_Interface;

final class Notice_Renderer implements Notice_Renderer_Interface {
	public function render( Notice_Interface $notice ): void {
		$args                   = $notice->to_array();
		$args['paragraph_wrap'] = false;

		if ( $notice instanceof Notice_With_SVG_Interface ) {
			// Todo: fix escaping by providing svg wp kses helper.
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo wp_get_admin_notice( $notice->get_svg() . $notice->get_message(), $args );

			return;
		}

		wp_admin_notice( $notice->get_message(), $args );
	}
}
