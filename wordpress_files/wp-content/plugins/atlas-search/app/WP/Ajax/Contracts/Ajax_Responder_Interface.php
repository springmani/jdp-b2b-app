<?php

declare(strict_types=1);

namespace WPE\AITK\WP\Ajax\Contracts;

interface Ajax_Responder_Interface {
	public function respond( Ajax_Response_Interface $response ): void;
}
