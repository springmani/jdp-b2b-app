<?php

declare(strict_types=1);

namespace WPE\AITK\WP\Debug;

final class Log_Gate {
	public function is_enabled(): bool {
		$wp_debug_enabled = defined( 'WP_DEBUG' ) && WP_DEBUG
			&& defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG;

		return (bool) apply_filters( 'wpe_aitk_logging_enabled', $wp_debug_enabled );
	}
}
