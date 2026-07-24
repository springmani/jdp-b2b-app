<?php

namespace Wpe_Content_Engine\Helper\Acf_Support\Types;

use Wpe_Content_Engine\Helper\Json_Schema\Geo_Point_Property;
use Wpe_Content_Engine\Helper\Json_Schema\Property;

class Google_Map extends Abstract_Type {
	public function to_json_schema_property(): Property {
		return new Geo_Point_Property( $this->name, true );
	}
}
