<?php

namespace Wpe_Content_Engine\Helper\Json_Schema;

abstract class Property {
	/**
	 * @var string
	 */
	protected $name;

	/**
	 * @var string
	 */
	protected $type;

	/**
	 * @var string
	 */
	protected $format;

	/**
	 * @var array
	 */
	protected $json_properties = [];

	abstract protected function load_type(): void;

	abstract protected function build(): void;

	/**
	 * @param string $name Name of the property.
	 * @param bool   $nullable Allows property to be null.
	 */
	public function __construct( string $name, bool $nullable = false ) {
		$this->name = $name;
		$this->load_type();

		if ( $nullable ) {
			$this->type = [ $this->type, 'null' ];
		}
	}

	public function get_name(): string {
		return $this->name;
	}

	/**
	 * @return array
	 */
	public function get_type() {
		return $this->type;
	}

	public function generate(): array {
		$this->build();

		return $this->json_properties;
	}

	/**
	 * @param string $key Key.
	 * @param mixed  $value Value.
	 *
	 * @return $this
	 */
	public function add_json_property( $key, $value ): self {
		$this->json_properties[ $key ] = $value;

		return $this;
	}
}
