<?php
/**
 * Landingpage 2 Footer - mit Menu Location + Copyright
 * Nutzt gleiche CSS-Klassen wie normaler Footer für automatisches Styling.
 *
 * @package jdpower
 */

$footer_logo           = get_field( 'footer_logo', 'option' );
$footer_copyright_text = get_field( 'copyright_text', 'option' );
$footer_logo_style     = '';
$footer_logo_inner     = '';

if ( $footer_logo && is_array( $footer_logo ) && ! empty( $footer_logo['ID'] ) ) {
	$mw = absint( get_field( 'footer_logo_max_width', 'option' ) );
	$footer_logo_style = $mw ? ' style="' . esc_attr( "max-width: {$mw}px;" ) . '"' : '';

	$id   = (int) $footer_logo['ID'];
	$path = get_attached_file( $id );
	$svg  = '';

	// SVG inline laden für richtige Farben
	if ( $path && is_readable( $path ) && 'svg' === strtolower( pathinfo( $path, PATHINFO_EXTENSION ) ) ) {
		$svg = file_get_contents( $path );
		$svg = ( false !== $svg ) ? $svg : '';
	}

	$footer_logo_img_alt = ! empty( $footer_logo['alt'] ) ? $footer_logo['alt'] : get_bloginfo( 'name', 'display' );
	$footer_logo_inner   = $svg ? $svg : wp_get_attachment_image(
		$id,
		'full',
		false,
		array(
			'alt' => $footer_logo_img_alt,
		)
	);
}

// Menu location für Landing Page 2 Footer
$landingpage_menu_location = 'landingpage_footer_menu';
$assigned_nav_locations    = get_nav_menu_locations();
$menu_id                   = isset( $assigned_nav_locations[ $landingpage_menu_location ] )
	? (int) $assigned_nav_locations[ $landingpage_menu_location ]
	: 0;
$menu_obj                  = $menu_id ? wp_get_nav_menu_object( $menu_id ) : null;
?>

	<footer id="colophon" class="site-footer landingpage-menu-footer">
		<div class="container">
			<div class="row footer-wrapper footer-columns">
				<div class="col-12 col-md-1-5 footer-logo-col">
					<?php if ( $footer_logo_inner ) : ?>
						<?php
						$footer_logo_link_label = sprintf(
							/* translators: %s: Site title */
							__( '%s logo', 'jdpower' ),
							get_bloginfo( 'name', 'display' )
						);
						?>
						<a href="<?php echo esc_url( jdpower_get_home_url() ); ?>" rel="home" aria-label="<?php echo esc_attr( $footer_logo_link_label ); ?>">
							<div class="footer-logo"<?php echo $footer_logo_style; ?>>
								<?php echo $footer_logo_inner; ?>
							</div>
						</a>
					<?php endif; ?>
				</div>

				<?php if ( $menu_obj ) : ?>
					<div class="col-12 col-md-4-5 footer-nav">
						<nav class="footer-nav__inner" aria-label="<?php echo esc_attr( $menu_obj->name ); ?>">
							<?php
							wp_nav_menu(
								array(
									'theme_location' => $landingpage_menu_location,
									'container'      => false,
									'menu_class'     => 'footer-nav__menu',
									'fallback_cb'    => false,
									'depth'          => 1,
								)
							);
							?>
						</nav>
					</div>
				<?php endif; ?>
			</div>

			<div class="row footer-copyright-row">
				<div class="col-12">
					<p class="copyright">
						<?php
						echo esc_html( '©' . gmdate( 'Y' ) );
						if ( $footer_copyright_text ) {
							echo ' ';
							echo wp_kses_post( $footer_copyright_text );
						}
						?>
					</p>
				</div>
			</div>
		</div>
	</footer>

</div><!-- /#page -->

<?php wp_footer(); ?>
</body>
</html>
