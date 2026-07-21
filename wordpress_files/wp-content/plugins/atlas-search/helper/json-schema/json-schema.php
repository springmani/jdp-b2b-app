<?php

namespace Wpe_Content_Engine\Helper\Json_Schema;

use Wpe_Content_Engine\Helper\Constants\Json_Schema_Type;

class Json_Schema extends Property {
	/**
	 * @var array
	 */
	private $required = [];

	/**
	 * @var \Wpe_Content_Engine\Helper\Json_Schema\Property[]
	 */
	private $object_properties = [];

	/**
	 * @param \Wpe_Content_Engine\Helper\Json_Schema\Property $property Property.
	 * @param bool                                            $is_required Is required.
	 *
	 * @return \Wpe_Content_Engine\Helper\Json_Schema\Json_Schema
	 */
	public function add_property( Property $property, bool $is_required ): self {
		$this->object_properties[] = $property;

		if ( $is_required && ! in_array( $property->get_name(), $this->required, true ) ) {
			$this->required[] = $property->get_name();
		}

		return $this;
	}

	protected function load_type(): void {
		$this->type = Json_Schema_Type::OBJECT;
	}

	protected function build(): void {
		$this->json_properties = [
			'$id'                  => 'https://aql.wpengine.com/wordpress/custom_type/' . $this->get_name(),
			'title'                => $this->get_name(),
			'type'                 => $this->get_type(),
			'required'             => $this->required,
			'properties'           => [],
			'additionalProperties' => false,
		];

		if ( ! empty( $this->object_properties ) ) {
			foreach ( $this->object_properties as $property ) {
				$this->json_properties['properties'][ $property->get_name() ] = $property->generate();
			}
		} else {
			$this->json_properties['properties'] = new \stdClass();
		}
	}
}
