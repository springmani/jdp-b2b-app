<?php

declare(strict_types=1);

namespace WPE\AITK\Core\Reflection\Docblock;

final class Float_Info extends Base_Range_Info {
	private ?float $min;

	private ?float $max;

	public function __construct( ?float $min = null, ?float $max = null ) {
		$this->validate_range( $min, $max );

		$this->min = $min;
		$this->max = $max;
	}

	public function get_min(): ?float {
		return $this->min;
	}

	public function get_max(): ?float {
		return $this->max;
	}

	public function has_min(): bool {
		return null !== $this->min;
	}

	public function has_max(): bool {
		return null !== $this->max;
	}
}
