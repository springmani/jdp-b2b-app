<?php

declare(strict_types=1);

namespace WPE\AITK\WP\Script;

use WPE\AITK\Core\Definer\Contracts\Service_Definer_Interface;

final class Script_Definer implements Service_Definer_Interface {
	/**
	 * @inheritDoc
	 */
	public function define(): array {
		return [
			Inline_Var_Injector::class => static fn (): Inline_Var_Injector => new Inline_Var_Injector(),
			Window_Var_Injector::class => static fn (): Window_Var_Injector => new Window_Var_Injector(),
		];
	}
}
