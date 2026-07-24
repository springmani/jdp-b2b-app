<?php

declare(strict_types=1);

namespace WPE\AITK\Modules\Smart_Attachment\Components;

use WPE\AITK\WP\Context\Page_Context;
use WPE\AITK\WP\Notice\Contracts\Notice_Renderer_Interface;
use WPE\AITK\WP\Notice\Notice_Type;
use WPE\AITK\WP\Notice\WP_Engine_Notice;
use WPE\AITK\WP\User\Contracts\Capability_Checker_Interface;
use WPE\AITK\WP\User\Contracts\Current_User_Provider_Interface;
use WP_Screen;
use WP_User;

final class Admin_Notice {
	private const MEDIA_NOTICE_PAGE_SCREENS = [
		'upload',
		'media',
	];

	private const USER_CAPABILITY = 'upload_files';

	/**
	 * @var \WPE\AITK\WP\User\Contracts\Current_User_Provider_Interface
	 */
	private Current_User_Provider_Interface $user_provider;

	/**
	 * @var \WPE\AITK\WP\User\Contracts\Capability_Checker_Interface
	 */
	private Capability_Checker_Interface $capability_checker;

	/**
	 * @var \WPE\AITK\WP\Notice\Contracts\Notice_Renderer_Interface
	 */
	private Notice_Renderer_Interface $renderer;

	/**
	 * @var array<string>
	 */
	private array $supported_types;
	private string $max_upload_size;

	/**
	 * @param \WPE\AITK\WP\User\Contracts\Current_User_Provider_Interface $user_provider
	 * @param \WPE\AITK\WP\User\Contracts\Capability_Checker_Interface $capability_checker
	 * @param \WPE\AITK\WP\Notice\Contracts\Notice_Renderer_Interface $renderer
	 * @param array<string> $supported_types
	 * @param string $max_upload_size
	 */
	public function __construct(
		Current_User_Provider_Interface $user_provider,
		Capability_Checker_Interface $capability_checker,
		Notice_Renderer_Interface $renderer,
		array $supported_types,
		string $max_upload_size
	) {
		$this->user_provider      = $user_provider;
		$this->capability_checker = $capability_checker;
		$this->renderer           = $renderer;
		$this->supported_types    = $supported_types;
		$this->max_upload_size    = $max_upload_size;
	}

	public function enable_media_page_notice(): void {
		if ( ! $this->verify_notice_screens() || ! $this->verify_notice_user() ) {
			return;
		}

		$this->renderer->render( $this->get_media_notice() );

		// Todo: move inline styline into enqueued CSS file.
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $this->add_styling();
	}

	/**
	 * Verify if the current admin screen is one of the specified media-related screens.
	 */
	private function verify_notice_screens(): bool {
		$screen = get_current_screen();

		if ( ! $screen instanceof WP_Screen ) {
			return false;
		}

		return (bool) array_filter(
			array_map(
				static fn ( string $screen ) => new Page_Context( $screen ),
				self::MEDIA_NOTICE_PAGE_SCREENS
			),
			static fn ( $item ) => $item->matches_current( $screen )
		);
	}

	/**
	 * Verify if the current user has the 'upload_files' capability.
	 * Also verifies if the user has not already dismissed the notice.
	 *
	 * @return bool True if the user has the capability, false otherwise.
	 */
	private function verify_notice_user(): bool {
		$user = $this->user_provider->get_current_user();

		if ( ! $user instanceof WP_User ) {
			return false;
		}

		return $this->capability_checker->has( $user, self::USER_CAPABILITY );
	}

	/**
	 * Create the WP Engine styled notice for media page.
	 *
	 * @return \WPE\AITK\WP\Notice\WP_Engine_Notice The constructed notice object.
	 */
	private function get_media_notice(): WP_Engine_Notice {
		return new WP_Engine_Notice(
			$this->get_notice_message(),
			Notice_Type::WP_ENGINE,
			$this->get_notice_svg(),
			false,
			'',
			[ 'update-nag', 'inline' ]
		);
	}

	/**
	 * Get the HTML content for the notice message.
	 *
	 * @return string The HTML content of the notice.
	 */
	private function get_notice_message(): string {
		// Todo: utilize message builder/provider functionality.
		$html = '<p><b>Metadata generation [BETA]</b></p>';

		$html .= '<p>Automatically generate media metadata on upload with WP Engine\'s AI Toolkit.<br>';
		$html .= sprintf(
			'This beta feature currently supports %s files up to %s. ',
			strtoupper( implode( ', ', $this->supported_types ) ),
			$this->max_upload_size
		);
		$html .= '<br><i>This feature uses generative AI, which can make mistakes. Check output for accuracy.</i></p>';

		// phpcs:ignore SlevomatCodingStandard.ControlStructures.BlockControlStructureSpacing.IncorrectLinesCountAfterControlStructure,Squiz.WhiteSpace.ControlStructureSpacing.SpacingAfterOpen
		if ( current_user_can( 'manage_options' ) ) {
			$html .= '<div style="margin-top: 8px; display: flex; align-items: center; gap: 12px;">';
			$html .= $this->get_change_settings_button();
			$html .= $this->get_learn_more_link();
			$html .= '</div>';

			return $html;
		}
		$html .= '<p><b>To change this setting, contact the Administrator for this site. </b></p>';
		$html .= '<div style="margin-top:8px; margin-bottom:6px; display: flex; align-items: center; gap: 12px;">';
		$html .= $this->get_learn_more_link();
		$html .= '</div>';

		return $html;
	}

	/**
	 * Get the Change Settings button HTML.
	 *
	 * @return string The button HTML.
	 */
	private function get_change_settings_button(): string {
		// phpcs:ignore SlevomatCodingStandard.Files.LineLength.LineTooLong
		return '<button id="wpe-metadata-settings-btn" type="button" class="button button-primary">Change settings</button>';
	}

	private function get_learn_more_link(): string {
		// phpcs:ignore SlevomatCodingStandard.Files.LineLength.LineTooLong
		return '<a href="https://wpengine.com/support/ai-toolkit/#AI_Generated_Metadata_for_Media" target="_blank" rel="noopener noreferrer" style="text-decoration: none;">Learn More</a>';
	}

	/**
	 * Get the SVG icon for the notice.
	 *
	 * @return string The SVG markup as a string.
	 */
	private function get_notice_svg(): string {
		// Todo: utilize SVG provider functionality.
		// phpcs:ignore SlevomatCodingStandard.Files.LineLength.LineTooLong
		return '<svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M11.3005 17.25C11.4322 17.25 11.5385 17.1437 11.5385 17.012V13.6398C11.5385 13.5137 11.4885 13.3923 11.3988 13.3035L10.4081 12.3127C10.3184 12.2231 10.1978 12.1731 10.0717 12.1731H7.9275C7.80137 12.1731 7.68 12.2231 7.59115 12.3127L6.60036 13.3035C6.51072 13.3931 6.46074 13.5137 6.46074 13.6398V17.012C6.46074 17.1437 6.56704 17.25 6.69873 17.25H11.3005Z" fill="#0ECAD4"/><path d="M13.3027 6.60115L12.3119 7.59195C12.2223 7.68159 12.1723 7.80216 12.1723 7.92829V10.0725C12.1723 10.1986 12.2223 10.32 12.3119 10.4088L13.3027 11.3996C13.3923 11.4893 13.5129 11.5393 13.639 11.5393H17.0112C17.1429 11.5393 17.2492 11.433 17.2492 11.3013V6.70031C17.2492 6.56863 17.1429 6.46233 17.0112 6.46233H13.639C13.5129 6.46233 13.3915 6.51231 13.3027 6.60195V6.60115Z" fill="#0ECAD4"/><path d="M6.69952 0.75C6.56784 0.75 6.46154 0.856298 6.46154 0.987981V4.36017C6.46154 4.4863 6.51151 4.60767 6.60115 4.69651L7.59195 5.68731C7.68159 5.77695 7.80216 5.82692 7.92829 5.82692H10.0725C10.1986 5.82692 10.32 5.77695 10.4088 5.68731L11.3996 4.69651C11.4893 4.60687 11.5393 4.4863 11.5393 4.36017V0.987981C11.5393 0.856298 11.433 0.75 11.3013 0.75H6.69952Z" fill="#0ECAD4"/><path d="M17.012 12.1731H13.6398C13.5137 12.1731 13.3923 12.2231 13.3035 12.3127L12.3127 13.3035C12.2231 13.3931 12.1731 13.5137 12.1731 13.6398V17.012C12.1731 17.1437 12.2794 17.25 12.4111 17.25H17.012C17.1437 17.25 17.25 17.1437 17.25 17.012V12.4111C17.25 12.2794 17.1437 12.1731 17.012 12.1731Z" fill="#0ECAD4"/><path d="M5.58894 0.75H2.21675C2.08983 0.75 1.96925 0.799976 1.87962 0.889615L0.889615 1.87962C0.799976 1.96925 0.75 2.08983 0.75 2.21675V5.58894C0.75 5.72062 0.856298 5.82692 0.987981 5.82692H4.36017C4.4863 5.82692 4.60767 5.77695 4.69651 5.68731L5.68731 4.69651C5.77695 4.60687 5.82692 4.4863 5.82692 4.36017V0.987981C5.82692 0.856298 5.72062 0.75 5.58894 0.75Z" fill="#0ECAD4"/><path d="M12.1731 0.987981V4.36017C12.1731 4.4863 12.2231 4.60767 12.3127 4.69651L13.3035 5.68731C13.3931 5.77695 13.5137 5.82692 13.6398 5.82692H17.012C17.1437 5.82692 17.25 5.72062 17.25 5.58894V0.987981C17.25 0.856298 17.1437 0.75 17.012 0.75H12.4111C12.2794 0.75 12.1731 0.856298 12.1731 0.987981Z" fill="#0ECAD4"/><path d="M9 10.2692C8.29875 10.2692 7.73077 9.70125 7.73077 9C7.73077 8.29875 8.29954 7.73077 9 7.73077C9.70046 7.73077 10.2692 8.29875 10.2692 9C10.2692 9.70125 9.70046 10.2692 9 10.2692Z" fill="#0ECAD4"/><path d="M0.75 12.4111V17.012C0.75 17.1437 0.856298 17.25 0.987981 17.25H5.58894C5.72062 17.25 5.82692 17.1437 5.82692 17.012V13.6398C5.82692 13.5137 5.77695 13.3923 5.68731 13.3035L4.69651 12.3127C4.60687 12.2231 4.4863 12.1731 4.36017 12.1731H0.987981C0.856298 12.1731 0.75 12.2794 0.75 12.4111Z" fill="#0ECAD4"/><path d="M5.68731 7.59115L4.69651 6.60036C4.60687 6.51072 4.4863 6.46074 4.36017 6.46074H0.987981C0.856298 6.46154 0.75 6.56784 0.75 6.69952V11.3005C0.75 11.4322 0.856298 11.5385 0.987981 11.5385H4.4149C4.54103 11.5385 4.6624 11.4885 4.75125 11.3988L5.68731 10.4636C5.77695 10.3739 5.82692 10.2534 5.82692 10.1272V7.92829C5.82692 7.80216 5.77695 7.68079 5.68731 7.59195V7.59115Z" fill="#0ECAD4"/></svg>';
	}

	/**
	 * Add inline CSS styling for the notice.
	 *
	 * @return string The style tag with CSS rules.
	 */
	private function add_styling(): string {
		// phpcs:ignore SlevomatCodingStandard.Files.LineLength.LineTooLong
		return '<style>.notice.notice-wpengine{border-left:4px solid #0ECAD4;padding:12px 16px;display:inline-flex;align-items:center;flex-wrap:wrap}.notice.notice-wpengine>svg{flex:0 0 auto;margin-right:8px}.notice.notice-wpengine>p{margin:0}.notice.notice-wpengine>p:first-of-type{flex:1 1 auto}.notice.notice-wpengine>p:not(:first-of-type){flex:0 0 100%;margin-top:6px}</style>';
	}
}
