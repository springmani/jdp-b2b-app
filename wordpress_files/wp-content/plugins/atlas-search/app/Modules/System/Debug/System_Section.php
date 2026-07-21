<?php

declare(strict_types=1);

namespace WPE\AITK\Modules\System\Debug;

use WPE\AITK\Modules\System\Data\Content_Engine_Connection;
use WPE\AITK\Modules\System\Debug\Fields\Access_Token_Field;
use WPE\AITK\Modules\System\Debug\Fields\Api_Url_Field;
use WPE\AITK\Modules\System\Debug\Fields\Block_Editor_Field;
use WPE\AITK\Modules\System\Debug\Fields\Installation_Type_Field;
use WPE\AITK\Modules\System\Debug\Fields\Plugin_Version_Field;
use WPE\AITK\Modules\System\Debug\Fields\Rest_Api_Field;
use WPE\AITK\Modules\System\Debug\Fields\WordPress_Version_Field;
use WPE\AITK\WP\Multisite\Contracts\Multisite_Environment_Interface;
use WPE\AITK\WP\Site_Health\Debug_Info\Contracts\Debug_Info_Section_Interface;
use Wpe_Content_Engine\WPSettings;

final class System_Section implements Debug_Info_Section_Interface {
	private Multisite_Environment_Interface $multisite;

	public function __construct( Multisite_Environment_Interface $multisite ) {
		$this->multisite = $multisite;
	}

	public function get_section_key(): string {
		return 'wpengine-aitk-system';
	}

	public function get_section_label(): string {
		return __( 'System', 'wpe-aitk' );
	}

	/**
	 * @return array<\WPE\AITK\WP\Site_Health\Debug_Info\Contracts\Debug_Info_Field_Interface>
	 */
	public function get_fields(): array {

		$connection = Content_Engine_Connection::from_array(
			get_option( WPSettings::WPE_CONTENT_ENGINE_OPTION_NAME, [] )
		);

		return [
			new Access_Token_Field( $connection ),
			new Api_Url_Field( $connection ),
			new Block_Editor_Field(),
			new Rest_Api_Field(),
			new Plugin_Version_Field(),
			new WordPress_Version_Field(),
			new Installation_Type_Field( $this->multisite ),
		];
	}
}
