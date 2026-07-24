<?php

declare(strict_types=1);

namespace WPE\AITK\Modules\Smart_Attachment\Components;

use WPE\AITK\WP\Context\Post_Context;
use WP_Screen;

/**
 * Handles UI elements for media library views (list and grid).
 */
final class Media_View {
	public const BULK_LIST_APP_ID  = 'wpe-aitk-list-generate';
	public const MEDIA_EDIT_APP_ID = 'wpe-aitk-grid-view-edit';

	/**
	 * Add bulk action to media library list view.
	 *
	 * @param array<string, string> $actions The existing bulk actions.
	 *
	 * @return array<string, string> Modified bulk actions.
	 */
	public function add_list_view_generate_bulk_action( array $actions ): array {
		$actions[ self::BULK_LIST_APP_ID ] = __( 'Generate Metadata', 'wpe-aitk' ) . ' [Beta]';

		return $actions;
	}

	/**
	 * Add custom fields to the media grid view edit form.
	 *
	 * @param array<string, scalar|array> $form_fields The existing form fields.
	 * @param object $post The attachment post object.
	 *
	 * @return array<string, scalar|array> Modified form fields.
	 */
	public function add_generate_metadata_button( array $form_fields, object $post ): array {
		$context = new Post_Context( 'attachment', Post_Context::MODE_EDIT );
		$screen  = get_current_screen();

		// Skip adding the field if we're on the attachment single edit screen.
		if ( $screen instanceof WP_Screen && $context->matches_current( $screen ) ) {
			return $form_fields;
		}

		$form_fields[ self::MEDIA_EDIT_APP_ID ] = [
			'html'  => sprintf( '<div id="%s" data-attachment-id="%d"></div>', self::MEDIA_EDIT_APP_ID, $post->ID ),
			'input' => 'html',
			'label' => 'Metadata Generation <br> [BETA]',
		];

		return $form_fields;
	}
}
