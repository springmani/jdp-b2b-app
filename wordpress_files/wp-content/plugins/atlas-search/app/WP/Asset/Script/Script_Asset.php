<?php

declare(strict_types=1);

namespace WPE\AITK\WP\Asset\Script;

use WPE\AITK\WP\Asset\Base_Asset;
use WPE\AITK\WP\Asset\Exceptions\Invalid_Asset_Argument_Exception;
use WPE\AITK\WP\Asset\Script\Contracts\Script_Asset_Interface;

final class Script_Asset extends Base_Asset implements Script_Asset_Interface {
	private const VALID_STRATEGIES = [
		'async',
		'defer',
	];

	/**
	 * @var bool
	 */
	private bool $in_footer;

	/**
	 * @var ''|'defer'|'async'
	 */
	private string $strategy;

	/**
	 * @param string             $handle
	 * @param string             $src
	 * @param bool $in_footer
	 * @param array<string>           $deps
	 * @param ''|'defer'|'async' $strategy
	 *
	 * @throws \WPE\AITK\WP\Asset\Exceptions\Invalid_Asset_Argument_Exception
	 */
	public function __construct(
		string $handle,
		string $src,
		array $deps = [],
		bool $in_footer = true,
		string $strategy = ''
	) {
		if ( ! empty( $strategy ) && ! in_array( $strategy, self::VALID_STRATEGIES, true ) ) {
			throw new Invalid_Asset_Argument_Exception(
				sprintf(
					'Invalid script strategy "%s". Allowed values: %s',
					$strategy,
					implode( ', ', self::VALID_STRATEGIES )
				)
			);
		}

		parent::__construct( $handle, $src, $deps );

		$this->in_footer = $in_footer;
		$this->strategy  = $strategy;
	}

	public function get_in_footer(): bool {
		return $this->in_footer;
	}

	public function get_strategy(): string {
		return $this->strategy;
	}
}
