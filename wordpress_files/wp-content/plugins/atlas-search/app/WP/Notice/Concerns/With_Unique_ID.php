<?php

declare(strict_types=1);

namespace WPE\AITK\WP\Notice\Concerns;

use LogicException;

trait With_Unique_ID {
	public function get_unique_id(): string {
		if ( ! method_exists( $this, 'to_array' ) ) {
			throw new LogicException(
				sprintf( '%s uses %s but does not implement to_array().', static::class, __TRAIT__ )
			);
		}

		$data = $this->to_array();

		ksort( $data );

		return wp_hash( wp_json_encode( [
			'class' => static::class,
			'state' => $data,
		] ) );
	}
}
