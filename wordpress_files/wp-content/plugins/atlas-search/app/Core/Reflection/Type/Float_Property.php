<?php

declare(strict_types=1);

namespace WPE\AITK\Core\Reflection\Type;

use WPE\AITK\Core\Concerns\Without_Clone;
use WPE\AITK\Core\Concerns\Without_Sleep;
use WPE\AITK\Core\Concerns\Without_Wakeup;
use WPE\AITK\Core\Reflection\Docblock\Contracts\Info_Interface;
use WPE\AITK\Core\Reflection\Docblock\Float_Info;
use WPE\AITK\Core\Reflection\Docblock\Null_Info;

/**
 * Value object representing a parsed float type with optional range constraints.
 *
 * Holds information about minimum and maximum bounds for a float, parsed from
 * docblock annotations like:
 * - `float<0.0, 100.0>` - float between 0.0 and 100.0
 * - `float<min, 100.0>` - float with maximum 100.0, no minimum
 * - `float<50.0, max>` - float with minimum 50.0, no maximum
 * - `float<min, max>` - any float (no constraints)
 */
final class Float_Property extends Base_Property {
	use Without_Clone;
	use Without_Sleep;
	use Without_Wakeup;

	private Info_Interface $info;

	public function __construct( bool $required = false, ?Float_Info $info = null ) {
		parent::__construct( $required );

		$this->info = $info ?? new Null_Info();
	}

	public function get_type(): string {
		return 'float';
	}

	public function get_info(): Info_Interface {
		return $this->info;
	}
}
