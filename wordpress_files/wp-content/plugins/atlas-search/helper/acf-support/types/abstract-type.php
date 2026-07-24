<?php

namespace Wpe_Content_Engine\Helper\Acf_Support\Types;

use Wpe_Content_Engine\Helper\Json_Schema\Property;

abstract class Abstract_Type {
	/**
	 * @var string
	 */
	protected $name;

	abstract public function to_json_schema_property(): Property;

	public function __construct( string $name ) {
		$this->name = $name;
	}
}
