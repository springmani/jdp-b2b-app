<?php

declare(strict_types=1);

namespace WPE\AITK\WP\Debug\Contracts;

interface Log_Flusher_Interface {
	/**
	 * @param array<int, string> $entries
	 */
	public function flush( array $entries ): void;
}
