<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function pmae_admin_notices() {
	// notify user if history folder is not writable		
	if ( ! class_exists( 'PMXE_Plugin' ) /*|| PMXE_EDITION == 'free' */ ) {
		?>
		<div class="error"><p>
				<?php
				printf(
					wp_kses_post(
						sprintf(
						/* translators: %1$s is the plugin name, %2$s is the download URL. */
							__( '<b>%1$s Plugin</b>: WP All Export must be installed and activated. You can download it here <a href="%2$s" target="_blank">https://www.wpallimport.com/portal/</a>', 'wp-all-export-csv-excel-xml-for-acf' ),
							esc_html( PMAE_Plugin::getInstance()->getName() ),
							esc_url( 'https://www.wpallimport.com/portal' )
						)
					)
				);
				?>
		</p></div>
		<?php

		return;

	}

	if ( class_exists( 'PMXE_Plugin' ) and ( version_compare(PMXE_VERSION, '1.5.6') < 0 and PMXE_EDITION != 'paid') and 0 ) {
		?>
		<div class="error"><p>
				<?php
				printf(
					wp_kses_post(
						sprintf(
						/* translators: %s is the plugin name, e.g., "WP All Export Pro". */
							__(
								'<b>%s Plugin</b>: Please update your WP All Export to the latest version',
								'wp-all-export-csv-excel-xml-for-acf'
							),
							esc_html(PMAE_Plugin::getInstance()->getName())
						)
					)
				);
				?>
		</p></div>
		<?php

	}

    // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$messages = isset($_GET['pmae_nt']) ? sanitize_text_field(wp_unslash($_GET['pmae_nt'])) : false;
	if ($messages) {
		is_array($messages) or $messages = array($messages);
		foreach ($messages as $type => $m) {
			in_array((string)$type, array('updated', 'error')) or $type = 'updated';
			?>
			<div class="<?php echo esc_attr($type); ?>"><p><?php echo esc_html($m); ?></p></div>
			<?php
		}
	}
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( ! empty($_GET['type']) and $_GET['type'] == 'user'){
		?>
		<script type="text/javascript">
			(function($){$(function () {
				$('#toplevel_page_pmxi-admin-home').find('.wp-submenu').find('li').removeClass('current');
				$('#toplevel_page_pmxi-admin-home').find('.wp-submenu').find('a').removeClass('current');
				$('#toplevel_page_pmxi-admin-home').find('.wp-submenu').find('li').eq(2).addClass('current').find('a').addClass('current');
			});})(jQuery);
		</script>
		<?php
	}
}
