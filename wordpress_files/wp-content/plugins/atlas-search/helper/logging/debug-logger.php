<?php

namespace Wpe_Content_Engine\Helper\Logging;

class Debug_Logger {
	/**
	 * @param mixed $log Item to log.
	 */
	public function log( $log ) {
		if ( ! WP_DEBUG ) {
			return;
		}

		$sanitized_log = $this->sanitize_log( $log );

		if ( is_array( $sanitized_log ) || is_object( $sanitized_log ) ) {
			error_log( print_r( $sanitized_log, true ) );
		} else {
			error_log( $sanitized_log );
		}
	}

	/**
	 * Sanitize the log data.
	 *
	 * @param mixed $log Item to sanitize.
	 *
	 * @return mixed Sanitized log item.
	 */
	private function sanitize_log( $log ) {
		return is_array( $log ) || is_object( $log ) ? wp_json_encode( $log ) : sanitize_text_field( $log );
	}
}
