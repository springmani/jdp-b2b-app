<?php

declare(strict_types=1);

namespace WPE\AITK\Core\Zip\Data;

final class Validated_Zip_Entry {
	private string $real_path;

	private string $safe_name;

	public function __construct( string $real_path, string $safe_name ) {
		$this->real_path = $real_path;
		$this->safe_name = $safe_name;
	}

	public function get_real_path(): string {
		return $this->real_path;
	}

	public function get_safe_name(): string {
		return $this->safe_name;
	}
}
