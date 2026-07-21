<?php

declare(strict_types=1);

namespace WPE\AITK\Core\Utils;

use WPE\AITK\Core\Definer\Contracts\Service_Definer_Interface;

final class Utils_Definer implements Service_Definer_Interface {
	/**
	 * @inheritDoc
	 */
	public function define(): array {
		return [
			Json_Decoder::class => static fn (): Json_Decoder => new Json_Decoder(),
		];
	}
}
