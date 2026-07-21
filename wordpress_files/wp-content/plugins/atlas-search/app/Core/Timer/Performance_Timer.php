<?php

declare(strict_types=1);

namespace WPE\AITK\Core\Timer;

final class Performance_Timer {
	/**
	 * @return callable(): \WPE\AITK\Core\Timer\Timer_Result
	 */
	public function start( string $operation ): callable {
		$start_ns = $this->now();

		return fn (): Timer_Result => new Timer_Result( $operation, $this->now() - $start_ns );
	}

	/**
	 * @template T
	 *
	 * @param callable(): T $callback
	 *
	 * @return array{0: T, 1: \WPE\AITK\Core\Timer\Timer_Result}
	 */
	public function measure( string $operation, callable $callback ): array {
		$stop   = $this->start( $operation );
		$result = $callback();

		return [ $result, $stop() ];
	}

	private function now(): int {
		return hrtime( true );
	}
}
