<?php

declare(strict_types=1);

namespace WPE\AITK\Modules\Cookie_Consent\Contracts;

// TODO: Consider removing once Settings_Manager / Network_Settings_Manager supports simple string settings.
// At that point, acknowledgement can be stored via the settings layer instead of direct wp_option calls.
interface Acknowledgement_Storage_Interface {
	public function is_acknowledged(): bool;

	public function persist(): void;
}
