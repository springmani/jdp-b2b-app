<?php

declare(strict_types=1);

namespace WPE\AITK\WP\Ajax;

use WPE\AITK\Core\Definer\Contracts\Service_Definer_Interface;

final class Ajax_Definer implements Service_Definer_Interface {
	/**
	 * @inheritDoc
	 */
	public function define(): array {
		return [
			Ajax_Responder::class => static fn () => new Ajax_Responder(),
		];
	}
}
