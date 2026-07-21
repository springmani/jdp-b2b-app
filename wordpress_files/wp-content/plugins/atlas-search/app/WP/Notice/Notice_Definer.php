<?php

declare(strict_types=1);

namespace WPE\AITK\WP\Notice;

use WPE\AITK\Core\Definer\Contracts\Service_Definer_Interface;

final class Notice_Definer implements Service_Definer_Interface {
	/**
	 * @inheritDoc
	 */
	public function define(): array {
		return [
			Notice_Renderer::class => static fn (): Notice_Renderer => new Notice_Renderer(),
		];
	}
}
