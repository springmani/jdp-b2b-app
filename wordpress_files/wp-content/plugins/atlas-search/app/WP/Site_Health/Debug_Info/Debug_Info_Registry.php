<?php

declare(strict_types=1);

namespace WPE\AITK\WP\Site_Health\Debug_Info;

use WPE\AITK\WP\Plugin\Contracts\Plugin_Name_Resolver_Interface;
use WPE\AITK\WP\Site_Health\Debug_Info\Contracts\Debug_Info_Section_Interface;

final class Debug_Info_Registry {
	private Plugin_Name_Resolver_Interface $name_resolver;

	/**
	 * @var array<string, \WPE\AITK\WP\Site_Health\Debug_Info\Contracts\Debug_Info_Section_Interface>
	 */
	private array $sections = [];

	public function __construct( Plugin_Name_Resolver_Interface $name_resolver ) {
		$this->name_resolver = $name_resolver;
	}

	public function add( Debug_Info_Section_Interface $section ): void {
		$this->sections[ $section->get_section_key() ] = $section;
	}

	/**
	 * @return array<string, array<string, string|array<string, array<string, string|bool>>>>
	 */
	public function get_info(): array {
		$info = [];

		foreach ( $this->sections as $section ) {
			$info[ $section->get_section_key() ] = $this->section_to_array( $section );
		}

		return $info;
	}

	/**
	 * @return array<string, string|array<string, array<string, string|bool>>>
	 */
	private function section_to_array( Debug_Info_Section_Interface $section ): array {
		$fields = [];

		$section_key = $section->get_section_key();

		foreach ( $section->get_fields() as $field ) {
			$fields[ $section_key . '_' . $field->get_key() ] = $field->to_array();
		}

		return [
			'fields' => $fields,
			'label'  => $this->name_resolver->resolve() . ': ' . $section->get_section_label(),
		];
	}
}
