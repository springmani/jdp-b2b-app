<?php

declare(strict_types=1);

namespace WPE\AITK\WP\Site_Health\Debug_Info\Contracts;

interface Debug_Info_Section_Interface {
	/**
	 * Unique key for this section, used as the array key in WordPress debug_information.
	 */
	public function get_section_key(): string;

	/**
	 * Human-readable section label shown in the Site Health Info tab.
	 */
	public function get_section_label(): string;

	/**
	 * @return array<\WPE\AITK\WP\Site_Health\Debug_Info\Contracts\Debug_Info_Field_Interface>
	 */
	public function get_fields(): array;
}
