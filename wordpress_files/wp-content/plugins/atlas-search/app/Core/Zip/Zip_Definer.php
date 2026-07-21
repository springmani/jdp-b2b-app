<?php

declare(strict_types=1);

namespace WPE\AITK\Core\Zip;

use Psr\Container\ContainerInterface as Container;
use WPE\AITK\Core\Definer\Contracts\Service_Definer_Interface;
use WPE\AITK\Core\Filesystem\Filesystem_Provider;
use WPE\AITK\WP\Filesystem\WP_File_Name_Sanitizer;
use WPE\AITK\WP\Filesystem\WP_Temp_File_Manager;

final class Zip_Definer implements Service_Definer_Interface {
	/**
	 * @inheritDoc
	 */
	public function define(): array {
		return [
			Zip_Creator::class         => static fn ( Container $c ): Zip_Creator => new Zip_Creator(
				$c->get( Filesystem_Provider::class ),
				$c->get( WP_Temp_File_Manager::class ),
				$c->get( Zip_Entry_Validator::class )
			),
			Zip_Entry_Validator::class => static fn ( Container $c ): Zip_Entry_Validator => new Zip_Entry_Validator(
				$c->get( Filesystem_Provider::class ),
				$c->get( WP_File_Name_Sanitizer::class )
			),
		];
	}
}
