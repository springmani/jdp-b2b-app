<?php

declare(strict_types=1);

namespace WPE\AITK\Modules\Cookie_Consent\Storage;

final class Site_Acknowledgement_Storage extends Base_Acknowledgement_Storage {
	public function is_acknowledged(): bool {
		return (bool) get_option( $this->option_name );
	}

	public function persist(): void {
		update_option( $this->option_name, current_time( 'mysql' ) );
	}
}
