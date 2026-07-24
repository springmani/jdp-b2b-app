<?php

namespace Wpe_Content_Engine\Helper\Json_Schema;

use Wpe_Content_Engine\Helper\Constants\Json_Schema_Type;

class Geo_Point_Property extends Property {
	protected function load_type(): void {
		$this->type = Json_Schema_Type::OBJECT;
	}

	protected function build(): void {
		$this->add_json_property( 'type', $this->get_type() );
		$this->add_json_property( 'properties', [
			'lat' => [
				'type' => Json_Schema_Type::NUMBER,
			],
			'lng' => [
				'type' => Json_Schema_Type::NUMBER,
			],
		] );
	}
}
