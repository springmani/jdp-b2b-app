<?php

declare(strict_types=1);

namespace WPE\AITK\Core\Timer;

final class Timer_Result {
	private string $operation;

	private int $elapsed_ns;

	public function __construct( string $operation, int $elapsed_ns ) {
		$this->operation  = $operation;
		$this->elapsed_ns = $elapsed_ns;
	}

	public function operation(): string {
		return $this->operation;
	}

	public function elapsed_ms(): float {
		return $this->elapsed_ns / 1e6;
	}

	public function formatted(): string {
		$ms = round( $this->elapsed_ms(), 2 );

		if ( $ms < 1000.0 ) {
			return sprintf( '%.2fms', $ms );
		}

		$seconds = round( $ms / 1000.0, 2 );

		if ( $seconds < 60.0 ) {
			return sprintf( '%.2fs', $seconds );
		}

		$minutes        = (int) floor( $seconds / 60.0 );
		$remaining_secs = $seconds - ( $minutes * 60.0 );

		return sprintf( '%dm %.2fs', $minutes, $remaining_secs );
	}

	public function __toString(): string {
		return sprintf( '%s took %s', $this->operation, $this->formatted() );
	}
}
