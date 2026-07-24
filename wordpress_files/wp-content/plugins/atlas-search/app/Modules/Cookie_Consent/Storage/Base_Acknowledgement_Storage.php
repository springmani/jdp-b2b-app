<?php

declare(strict_types=1);

namespace WPE\AITK\Modules\Cookie_Consent\Storage;

use WPE\AITK\Modules\Cookie_Consent\Contracts\Acknowledgement_Storage_Interface;

abstract class Base_Acknowledgement_Storage implements Acknowledgement_Storage_Interface {
	protected string $option_name;

	public function __construct( string $option_name ) {
		$this->option_name = $option_name;
	}

	abstract public function is_acknowledged(): bool;

	abstract public function persist(): void;
}
