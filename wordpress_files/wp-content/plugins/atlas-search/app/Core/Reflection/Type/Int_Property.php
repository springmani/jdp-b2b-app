<?php

declare(strict_types=1);

namespace WPE\AITK\Core\Reflection\Type;

use WPE\AITK\Core\Concerns\Without_Clone;
use WPE\AITK\Core\Concerns\Without_Sleep;
use WPE\AITK\Core\Concerns\Without_Wakeup;
use WPE\AITK\Core\Reflection\Docblock\Contracts\Info_Interface;
use WPE\AITK\Core\Reflection\Docblock\Int_Info;
use WPE\AITK\Core\Reflection\Docblock\Null_Info;

/**
 * Value object representing a parsed integer type with optional range constraints.
 *
 * Holds information about minimum and maximum bounds for an integer, parsed from
 * docblock annotations like:
 * - `int<0, 100>` - integer between 0 and 100
 * - `int<min, 100>` - integer with maximum 100, no minimum
 * - `int<50, max>` - integer with minimum 50, no maximum
 * - `int<min, max>` - any integer (no constraints)
 */
final class Int_Property extends Base_Property {
	use Without_Clone;
	use Without_Sleep;
	use Without_Wakeup;

	private Info_Interface $info;

	public function __construct( bool $required = false, ?Int_Info $info = null ) {
		parent::__construct( $required );

		$this->info = $info ?? new Null_Info();
	}

	public function get_type(): string {
		return 'int';
	}

	public function get_info(): Info_Interface {
		return $this->info;
	}
}
