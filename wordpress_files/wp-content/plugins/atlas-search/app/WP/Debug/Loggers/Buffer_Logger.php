<?php

declare(strict_types=1);

namespace WPE\AITK\WP\Debug\Loggers;

use WPE\AITK\WP\Debug\Concerns\With_Logger_Level;
use WPE\AITK\WP\Debug\Contracts\Logger_Interface;
use WPE\AITK\WP\Multisite\Contracts\Multisite_Environment_Interface;

final class Buffer_Logger implements Logger_Interface {
	use With_Logger_Level;

	/**
	 * @var array<int, string>
	 */
	private array $buffer = [];

	private Multisite_Environment_Interface $multisite;

	public function __construct( Multisite_Environment_Interface $multisite ) {
		$this->multisite = $multisite;
	}

	/**
	 * @return array<int, string>
	 */
	public function get_entries(): array {
		return $this->buffer;
	}

	public function clear(): void {
		$this->buffer = [];
	}

	/**
	 * @inheritDoc
	 */
	public function log( string $level, string $channel, string $message, array $context = [] ): void {
		$this->assert_valid_level( $level );

		$encoded = wp_json_encode(
			[
				'channel'   => $channel,
				'context'   => $this->add_multisite_context( $context ),
				'level'     => $level,
				'message'   => $message,
				'timestamp' => gmdate( 'c' ),
			],
			JSON_UNESCAPED_SLASHES
		);

		if ( false === $encoded ) {
			$encoded = sprintf( '[%s] %s.%s: %s', gmdate( 'c' ), $channel, $level, $message );
		}

		$this->buffer[] = $encoded . "\n";
	}

	/**
	 * @param array<string, scalar> $context
	 *
	 * @return array<string, scalar>
	 */
	private function add_multisite_context( array $context ): array {
		if ( ! isset( $context['site_id'] ) && $this->multisite->is_multisite() ) {
			$context['site_id'] = $this->multisite->get_current_blog_id();
		}

		return $context;
	}
}
