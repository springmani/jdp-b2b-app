<?php

declare(strict_types=1);

namespace WPE\AITK\Core\Reflection\Docblock;

final class Int_Info extends Base_Range_Info {
	private ?int $min;

	private ?int $max;

	public function __construct( ?int $min = null, ?int $max = null ) {
		$this->validate_range( null === $min ? null : (float) $min, null === $max ? null : (float) $max );

		$this->min = $min;
		$this->max = $max;
	}

	public function get_min(): ?int {
		return $this->min;
	}

	public function get_max(): ?int {
		return $this->max;
	}

	public function has_min(): bool {
		return null !== $this->min;
	}

	public function has_max(): bool {
		return null !== $this->max;
	}
}
