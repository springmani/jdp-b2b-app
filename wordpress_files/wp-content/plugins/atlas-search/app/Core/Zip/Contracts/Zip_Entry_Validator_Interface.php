<?php

declare(strict_types=1);

namespace WPE\AITK\Core\Zip\Contracts;

use WPE\AITK\Core\Zip\Data\Validated_Zip_Entry;

interface Zip_Entry_Validator_Interface {
	public function validate( string $file_path, string $name_in_zip ): ?Validated_Zip_Entry;
}
