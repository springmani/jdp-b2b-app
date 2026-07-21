<?php

declare(strict_types=1);

namespace WPE\AITK\Modules\Smart_Attachment;

use AtlasSearch\Index\Media\Content_Generator;
use WPE\AITK\Core\Subscriber\Hook_Subscriber;
use WPE\AITK\Modules\Smart_Attachment\Components\Admin_Notice;
use WPE\AITK\Modules\Smart_Attachment\Components\Frontend_Config;
use WPE\AITK\Modules\Smart_Attachment\Components\Media_View;
use WPE\AITK\Modules\Smart_Attachment\Components\Single_Attachment;
use WPE\AITK\Modules\Smart_Attachment\Components\Upload_Handler;
use WPE\AITK\Modules\Smart_Attachment\Data\Upload_Generation_Data;
use WPE\AITK\WP\Notice\Contracts\Notice_Renderer_Interface;
use WPE\AITK\WP\Notice\Notice_Renderer;
use WPE\AITK\WP\Script\Contracts\Script_Data_Injector_Interface;
use WPE\AITK\WP\Script\Window_Var_Injector;
use WPE\AITK\WP\Settings\Contracts\Settings_Manager_Interface;
use WPE\AITK\WP\Settings\Settings_Manager;
use WPE\AITK\WP\User\Capability_Checker;
use WPE\AITK\WP\User\Contracts\Capability_Checker_Interface;
use WPE\AITK\WP\User\Contracts\Current_User_Provider_Interface;
use WPE\AITK\WP\User\Current_User_Provider;
use Wpe_Content_Engine\Helper\Logging\Debug_Logger;

final class Smart_Attachment_Subscriber extends Hook_Subscriber {
	public const SUPPORTED_TYPES = [
		'gif'  => 'image/gif',
		'heic' => 'image/heic',
		'heif' => 'image/heif',
		'jpeg' => 'image/jpeg',
		'jpg'  => 'image/jpeg',
		'pdf'  => 'application/pdf',
		'png'  => 'image/png',
		'svg'  => 'image/svg+xml',
		'webp' => 'image/webp',
	];

	private const API_MAX_UPLOAD_SIZE = 50;

	private string $max_upload_size;

	private Capability_Checker_Interface $capability_checker;
	private Current_User_Provider_Interface $current_user_provider;
	private Notice_Renderer_Interface $notice_renderer;
	private Script_Data_Injector_Interface $script_injector;
	private Settings_Manager_Interface $setting_manager;

	public function register(): void {
		$this->capability_checker    = $this->container->get( Capability_Checker::class );
		$this->current_user_provider = $this->container->get( Current_User_Provider::class );
		$this->notice_renderer       = $this->container->get( Notice_Renderer::class );
		$this->script_injector       = $this->container->get( Window_Var_Injector::class );
		$this->setting_manager       = $this->container->get( Settings_Manager::class );

		$this->max_upload_size = min( (int) ( wp_max_upload_size() / 1024 / 1024 ), self::API_MAX_UPLOAD_SIZE ) . ' MB';

		$this->register_admin_notice_component();
		$this->register_single_attachment_component();
		$this->register_media_view_component();
		$this->register_on_upload_component();
		$this->register_frontend_config_component();
	}

	private function register_single_attachment_component(): void {
		add_action(
			'add_meta_boxes_attachment',
			[ new Single_Attachment( $this->script_injector ), 'add_generate_metadata_metabox' ]
		);
	}

	private function register_media_view_component(): void {
		$media_view = new Media_View();

		// Add bulk action to media library list view.
		add_filter( 'bulk_actions-upload', [ $media_view, 'add_list_view_generate_bulk_action' ] );

		// Add generate metadata button to media library grid view.
		add_filter(
			'attachment_fields_to_edit',
			[ $media_view, 'add_generate_metadata_button' ],
			999,
			2
		);
	}

	private function register_on_upload_component(): void {
		// Register setting.
		$this->setting_manager->add( Upload_Handler::SETTING_OPTION_NAME, Upload_Generation_Data::class );

		$debug_logger = new Debug_Logger();

		$upload_handler = new Upload_Handler(
			$this->setting_manager,
			new Components\Metadata_Field_Support( self::SUPPORTED_TYPES ),
			new Content_Generator( $debug_logger ),
			$debug_logger
		);

		// Handle attachment upload event for metadata generation.
		add_action( 'add_attachment', [ $upload_handler, 'handle_upload' ] );
	}

	private function register_admin_notice_component(): void {
		add_action( 'admin_notices', [
			new Admin_Notice(
				$this->current_user_provider,
				$this->capability_checker,
				$this->notice_renderer,
				array_keys( self::SUPPORTED_TYPES ),
				$this->max_upload_size
			),
			'enable_media_page_notice',
		] );
	}

	/**
	 * Register frontend config injection component.
	 */
	private function register_frontend_config_component(): void {
		add_action( 'admin_enqueue_scripts', [
			new Frontend_Config(
				$this->script_injector,
				new Components\Metadata_Field_Support( self::SUPPORTED_TYPES ),
				self::SUPPORTED_TYPES,
				$this->max_upload_size,
				Media_View::BULK_LIST_APP_ID,
				Media_View::MEDIA_EDIT_APP_ID,
				Single_Attachment::SINGLE_ATTACHMENT_APP_ID
			),
			'inject_config',
		], 999 );
	}
}
