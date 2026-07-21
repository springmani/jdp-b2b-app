<?php

declare(strict_types=1);

namespace WPE\AITK\Core\Reflection\Type;

use WPE\AITK\Core\Concerns\Without_Clone;
use WPE\AITK\Core\Concerns\Without_Sleep;
use WPE\AITK\Core\Concerns\Without_Wakeup;
use WPE\AITK\Core\Reflection\Docblock\Contracts\Info_Interface;
use WPE\AITK\Core\Reflection\Docblock\Null_Info;
use WPE\AITK\Core\Reflection\Docblock\String_Info;

/**
 * Value object representing a parsed string type with optional constraints.
 *
 * Holds information about string constraints, parsed from docblock annotations like:
 * - `string` - any string
 * - `non-empty-string` - non-empty string
 * - `'foo'|'bar'|'baz'` - enum of specific string values
 */
final class String_Property extends Base_Property {
	use Without_Clone;
	use Without_Sleep;
	use Without_Wakeup;

	private Info_Interface $info;

	public function __construct( bool $required = false, ?String_Info $info = null ) {
		parent::__construct( $required );

		$this->info = $info ?? new Null_Info();
	}

	public function get_type(): string {
		return 'string';
	}

	public function get_info(): Info_Interface {
		return $this->info;
	}
}
