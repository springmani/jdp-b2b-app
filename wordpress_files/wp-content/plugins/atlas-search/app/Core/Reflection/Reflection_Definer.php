<?php

declare(strict_types=1);

namespace WPE\AITK\Core\Reflection;

use WPE\AITK\Core\Container\Container;
use WPE\AITK\Core\Definer\Contracts\Service_Definer_Interface;
use WPE\AITK\Core\Reflection\Parser\Float_Parser;
use WPE\AITK\Core\Reflection\Parser\Int_Parser;
use WPE\AITK\Core\Reflection\Parser\Range_Parser;
use WPE\AITK\Core\Reflection\Parser\String_Parser;

final class Reflection_Definer implements Service_Definer_Interface {
	/**
	 * @inheritDoc
	 */
	public function define(): array {
		return [
			Float_Parser::class    => static fn ( Container $container ): Float_Parser => new Float_Parser(
				$container->get( Range_Parser::class )
			),
			Int_Parser::class      => static fn ( Container $container ): Int_Parser => new Int_Parser(
				$container->get( Range_Parser::class )
			),
			Property_Mapper::class => static fn (): Property_Mapper => new Property_Mapper(),
			Property_Parser::class => static fn ( Container $container ): Property_Parser => new Property_Parser(
				$container->get( Type_Factory::class )
			),
			Range_Parser::class    => static fn (): Range_Parser => new Range_Parser(),
			String_Parser::class   => static fn (): String_Parser => new String_Parser(),
			Type_Factory::class    => static fn ( Container $container ): Type_Factory => new Type_Factory(
				$container->get( Int_Parser::class ),
				$container->get( String_Parser::class ),
				$container->get( Float_Parser::class )
			),
		];
	}
}
