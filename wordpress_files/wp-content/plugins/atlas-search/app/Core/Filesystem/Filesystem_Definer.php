<?php

declare(strict_types=1);

namespace WPE\AITK\Core\Filesystem;

use Psr\Container\ContainerInterface;
use WPE\AITK\Core\Definer\Contracts\Service_Definer_Interface;

final class Filesystem_Definer implements Service_Definer_Interface {
	/**
	 * @inheritDoc
	 */
	public function define(): array {
		return [
			Filesystem_Provider::class => static fn (): Filesystem_Provider => new Filesystem_Provider(),
			File_Handler::class        => static fn ( ContainerInterface $c ): File_Handler => new File_Handler(
				$c->get( Filesystem_Provider::class )
			),
		];
	}
}
