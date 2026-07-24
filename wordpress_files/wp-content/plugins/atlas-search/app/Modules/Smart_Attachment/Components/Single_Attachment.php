<?php

declare(strict_types=1);

namespace WPE\AITK\Modules\Smart_Attachment\Components;

use WPE\AITK\Modules\Smart_Attachment\Data\Attachment_Data;
use WPE\AITK\WP\Script\Contracts\Script_Data_Injector_Interface;
use WPE\AITK\WP\Script\Exceptions\Script_Injection_Exception;
use WPE_Atlas_Search_Settings_Page;
use WP_Post;

final class Single_Attachment {
	public const SINGLE_ATTACHMENT_APP_ID       = 'wpe-aitk-media-edit-root';
	private const SINGLE_ATTACHMENT_OBJECT_NAME = 'wpeAitkSingleAttachmentDetails';

	/**
	 * @var \WPE\AITK\WP\Script\Contracts\Script_Data_Injector_Interface
	 */
	private Script_Data_Injector_Interface $injector;

	public function __construct( Script_Data_Injector_Interface $injector ) {
		$this->injector = $injector;
	}

	public function add_generate_metadata_metabox(): void {
		add_meta_box(
			'ai-toolkit-media-edit-box',
			'Generate Metadata <br> [BETA]',
			[ $this, 'init_metabox' ],
			'attachment',
			'side'
		);
	}

	public function init_metabox( WP_Post $post ): void {
		$result = printf( '<div id="%s"></div>', esc_attr( self::SINGLE_ATTACHMENT_APP_ID ) );

		if ( $result < 1 ) {
			return;
		}

		try {
			$this->injector->inject(
				WPE_Atlas_Search_Settings_Page::SETTINGS_SCRIPT,
				new Attachment_Data(
					$post->ID,
					$post->post_title,
					$post->post_mime_type,
					$this->get_thumbnail_url( $post->ID, $post->post_mime_type )
				),
				self::SINGLE_ATTACHMENT_OBJECT_NAME
			);
		} catch ( Script_Injection_Exception $e ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			error_log( $e->getMessage() );
		}
	}

	private function get_thumbnail_url( int $post_id, string $mime_type ): string {
		$thumbnail_url = wp_get_attachment_image_url( $post_id );

		if ( empty( $thumbnail_url ) ) {
			$thumbnail_url = wp_mime_type_icon( $mime_type );
		}

		return (string) $thumbnail_url;
	}
}
