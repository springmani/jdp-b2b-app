<?php

declare(strict_types=1);

namespace WPE\AITK\WP\Site_Health\Test;

use WPE\AITK\WP\Site_Health\Test\Contracts\Test_Interface;

/**
 * Registry for Site Health tests.
 *
 * Modules call add() during subscriber registration to store tests.
 * The subscriber hooks into site_status_tests and calls register()
 * to build the WordPress array from all registered tests.
 */
final class Test_Registry {
	private const REST_NAMESPACE = 'wpengine-aitk/v1';

	private Direct_Test_Runner $runner;

	/**
	 * @var array<string, \WPE\AITK\WP\Site_Health\Test\Contracts\Test_Interface>
	 */
	private array $tests = [];

	public function __construct( Direct_Test_Runner $runner ) {
		$this->runner = $runner;
	}

	/**
	 * Registers a test with the registry.
	 */
	public function add( Test_Interface $test ): void {
		$this->tests[ $test->get_identifier() ] = $test;
	}

	/**
	 * Builds the WordPress site_status_tests array from all registered tests.
	 *
	 * @param array<string, array<string, array<string, scalar|array|callable>>> $tests The WordPress tests array.
	 *
	 * @return array<string, array<string, array<string, scalar|array|callable>>> The modified tests array.
	 */
	public function register( array $tests ): array {
		foreach ( $this->tests as $test ) {
			if ( $test->is_async() ) {
				$tests = $this->register_async( $tests, $test );

				continue;
			}

			$tests = $this->register_direct( $tests, $test );
		}

		return $tests;
	}

	/**
	 * Returns a test by identifier, or null if not found.
	 */
	public function get_test( string $identifier ): ?Test_Interface {
		return $this->tests[ $identifier ] ?? null;
	}

	/**
	 * @return array{label: string, status: string, badge: array{label: string, color: string}, description: string, actions: string, test: string}
	 */
	public function execute( Test_Interface $test ): array {
		return $this->runner->run( $test );
	}

	/**
	 * @param array<string, array<string, array<string, scalar|array|callable>>> $tests The WordPress tests array.
	 *
	 * @return array<string, array<string, array<string, scalar|array|callable>>> The modified tests array.
	 */
	private function register_direct( array $tests, Test_Interface $test ): array {
		$runner = $this->runner;

		$tests['direct'][ $test->get_identifier() ] = [
			'label' => $test->get_label(),
			'test'  => static fn (): array => $runner->run( $test ),
		];

		return $tests;
	}

	/**
	 * @param array<string, array<string, array<string, scalar|array|callable>>> $tests The WordPress tests array.
	 *
	 * @return array<string, array<string, array<string, scalar|array|callable>>> The modified tests array.
	 */
	private function register_async( array $tests, Test_Interface $test ): array {
		$runner = $this->runner;

		$tests['async'][ $test->get_identifier() ] = [
			'async_direct_test' => static fn (): array => $runner->run( $test ),
			'has_rest'          => true,
			'label'             => $test->get_label(),
			'test'              => rest_url( self::REST_NAMESPACE . '/site-health/' . $test->get_identifier() ),
		];

		return $tests;
	}
}
