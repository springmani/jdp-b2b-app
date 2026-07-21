<?php

declare(strict_types=1);

namespace WPE\AITK\Core\Timer;

use WPE\AITK\Core\Definer\Contracts\Service_Definer_Interface;

final class Timer_Definer implements Service_Definer_Interface {
	/**
	 * @inheritDoc
	 */
	public function define(): array {
		return [
			Performance_Timer::class => static fn (): Performance_Timer => new Performance_Timer(),
		];
	}
}
