<?php

declare(strict_types=1);

namespace WPE\AITK\WP\Site_Health\Test;

use Throwable;
use WPE\AITK\Core\Timer\Performance_Timer;
use WPE\AITK\WP\Debug\Contracts\Logger_Interface;
use WPE\AITK\WP\Site_Health\Test\Contracts\Test_Interface;
use WPE\AITK\WP\Site_Health\Test\Data\Test_Result;
use WPE\AITK\WP\Site_Health\Test\Test_Result_Formatter as Result_Formatter;

final class Direct_Test_Runner {
	private Performance_Timer $timer;

	private Logger_Interface $logger;

	private Result_Formatter $formatter;

	public function __construct( Performance_Timer $timer, Logger_Interface $logger, Result_Formatter $formatter ) {
		$this->timer     = $timer;
		$this->logger    = $logger;
		$this->formatter = $formatter;
	}

	/**
	 * @return array{label: string, status: string, badge: array{label: string, color: string}, description: string, actions: string, test: string}
	 */
	public function run( Test_Interface $test ): array {
		return $this->formatter->format( $this->execute( $test ) );
	}

	private function execute( Test_Interface $test ): Test_Result {
		try {
			[ $result, $timing ] = $this->timer->measure(
				$test->get_label(),
				static fn (): Test_Result => $test->run()
			);

			$this->logger->info( 'site_health', $test->get_label(), [ 'timing' => $timing->formatted() ] );

			return $result;
		} catch ( Throwable $e ) {
			return Test_Result::critical(
				sprintf(
					'%s: Error',
					$test->get_label()
				),
				sprintf(
					'<p>%s</p><p><code>%s</code></p>',
					esc_html__( 'An error occurred while running this health check.', 'wpe-aitk' ),
					esc_html( $e->getMessage() )
				),
				$test->get_identifier()
			);
		}
	}
}
