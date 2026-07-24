<?php

declare(strict_types=1);

namespace WPE\AITK\WP\Script\Exceptions;

use Exception;

final class Script_Injection_Exception extends Exception {
	public static function empty_handle(): self {
		return new self( 'Script handle cannot be empty.' );
	}

	public static function empty_object_name( string $handle ): self {
		return new self( sprintf( 'Empty object name provided for script with handle "%s".', $handle ) );
	}

	public static function empty_data_array( string $handle, string $object_name ): self {
		return new self(
			sprintf(
				'Empty data array provided for script with handle "%s" and object name "%s".',
				$handle,
				$object_name
			)
		);
	}

	public static function json_encode_failed( string $handle, string $object_name ): self {
		return new self(
			sprintf( 'Failed to JSON encode data for script handle "%s" with object name "%s".', $handle, $object_name )
		);
	}

	public static function inline_script_failed( string $handle, string $object_name ): self {
		return new self(
			sprintf( 'Failed to add inline script with handle "%s" and object name "%s".', $handle, $object_name )
		);
	}

	public static function localize_script_failed( string $handle, string $object_name ): self {
		return new self(
			sprintf( 'Failed to localize script with handle "%s" and object name "%s".', $handle, $object_name )
		);
	}
}
