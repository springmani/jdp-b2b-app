<?php

declare(strict_types=1);

namespace WPE\AITK\Core\Reflection\Type;

use WPE\AITK\Core\Reflection\Type\Contracts\Property_Interface;

abstract class Base_Property implements Property_Interface {
	private bool $required;

	public function __construct( bool $required = false ) {
		$this->required = $required;
	}

	public function get_required(): bool {
		return $this->required;
	}
}
