<?php

declare(strict_types=1);

namespace WPE\AITK\WP\Ajax;

use WPE\AITK\WP\Ajax\Contracts\Ajax_Responder_Interface;
use WPE\AITK\WP\Ajax\Contracts\Ajax_Response_Interface;

final class Ajax_Responder implements Ajax_Responder_Interface {
	public function respond( Ajax_Response_Interface $response ): void {
		$response_data = $response->get_response_data();
		$data          = $response_data ? $response_data->to_array() : [];
		$message       = $response->get_message();

		// Add message to data if present.
		if ( ! empty( $message ) ) {
			$data['message'] = $message;
		}

		if ( $response->get_success() ) {
			wp_send_json_success( $data, $response->get_status_code() );

			return;
		}

		wp_send_json_error( $data, $response->get_status_code() );
	}
}
