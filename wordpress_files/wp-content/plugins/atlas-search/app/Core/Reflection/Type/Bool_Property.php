<?php

declare(strict_types=1);

namespace WPE\AITK\Core\Reflection\Type;

use WPE\AITK\Core\Concerns\Without_Clone;
use WPE\AITK\Core\Concerns\Without_Sleep;
use WPE\AITK\Core\Concerns\Without_Wakeup;
use WPE\AITK\Core\Reflection\Docblock\Contracts\Info_Interface;
use WPE\AITK\Core\Reflection\Docblock\Null_Info;

/**
 * Value object representing a parsed boolean type.
 *
 * Boolean types have no constraints, so this always uses Null_Info.
 */
final class Bool_Property extends Base_Property {
	use Without_Clone;
	use Without_Sleep;
	use Without_Wakeup;

	private Info_Interface $info;

	public function __construct( bool $required = false ) {
		parent::__construct( $required );

		$this->info = new Null_Info();
	}

	public function get_type(): string {
		return 'bool';
	}

	public function get_info(): Info_Interface {
		return $this->info;
	}
}
