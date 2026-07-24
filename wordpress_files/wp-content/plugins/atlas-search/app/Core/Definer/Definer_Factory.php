<?php

declare(strict_types=1);

namespace WPE\AITK\Core\Definer;

use WPE\AITK\Core\Definer\Contracts\Definer_Factory_Interface;
use WPE\AITK\Core\Definer\Contracts\Service_Definer_Interface;
use WPE\AITK\Core\Definer\Exceptions\Definer_Factory_Exception;

final class Definer_Factory implements Definer_Factory_Interface {
	public function make( string $definer_class ): Service_Definer_Interface {
		if ( ! class_exists( $definer_class ) ) {
			throw new Definer_Factory_Exception( "Definer class [$definer_class] does not exist." );
		}

		$instance = new $definer_class();

		if ( ! $instance instanceof Service_Definer_Interface ) {
			throw new Definer_Factory_Exception( "Class [$definer_class] must implement Service_Definer_Interface." );
		}

		return $instance;
	}
}
