<?php

declare(strict_types=1);

namespace WPE\AITK\WP\Asset\Style;

use WPE\AITK\WP\Asset\Base_Asset;
use WPE\AITK\WP\Asset\Exceptions\Invalid_Asset_Argument_Exception;
use WPE\AITK\WP\Asset\Style\Contracts\Style_Asset_Interface;

final class Style_Asset extends Base_Asset implements Style_Asset_Interface {
	private const VALID_MEDIA_TYPES = [
		'all',
		'screen',
		'print',
	];

	/**
	 * @var 'all'|'screen'|'print'
	 */
	private string $media;

	/**
	 * @param string                 $handle
	 * @param string                 $src
	 * @param array<string>                  $deps
	 * @param 'all'|'screen'|'print' $media
	 *
	 * @throws \WPE\AITK\WP\Asset\Exceptions\Invalid_Asset_Argument_Exception
	 */
	public function __construct( string $handle, string $src, array $deps = [], string $media = 'all' ) {
		if ( ! in_array( $media, self::VALID_MEDIA_TYPES, true ) ) {
			throw new Invalid_Asset_Argument_Exception(
				sprintf(
					'Invalid media type "%s". Allowed values: %s',
					$media,
					implode( ', ', self::VALID_MEDIA_TYPES )
				)
			);
		}

		parent::__construct( $handle, $src, $deps );

		$this->media = $media;
	}

	public function get_media(): string {
		return $this->media;
	}
}
