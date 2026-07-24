<?php

declare(strict_types=1);

namespace WPE\AITK\WP\Nonce;

use WPE\AITK\Core\Definer\Contracts\Service_Definer_Interface;

final class Nonce_Definer implements Service_Definer_Interface {
	/**
	 * @inheritDoc
	 */
	public function define(): array {
		return [
			Public_Nonce::class => static fn (): Public_Nonce => new Public_Nonce(),
			WP_Nonce::class     => static fn (): WP_Nonce => new WP_Nonce(),
		];
	}
}
