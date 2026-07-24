<?php
/**
 * The template for displaying the footer
 *
 * Contains the closing of the #content div and all content after.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
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
$footer_social_links    = get_field( 'footer_social_links', 'option' );
$footer_nav_locations    = array( 'footer_column_1', 'footer_column_2', 'footer_column_3', 'footer_column_4' );
$assigned_nav_locations  = get_nav_menu_locations();
?>
	<footer id="colophon" class="site-footer">
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
							<div class="footer-logo"<?php echo $footer_logo_style;?>>
								<?php echo $footer_logo_inner; ?>
							</div>
						</a>
					<?php endif; ?>
					<?php
					if ( $footer_social_links && is_array( $footer_social_links ) ) :
						$social_items = array();
						foreach ( $footer_social_links as $social_row ) {
							if ( ! is_array( $social_row ) ) {
								continue;
							}
							$link = isset( $social_row['footer_social_link'] ) ? $social_row['footer_social_link'] : null;
							if ( ! is_array( $link ) || empty( $link['url'] ) ) {
								continue;
							}
							$icon = isset( $social_row['footer_social_icon'] ) ? $social_row['footer_social_icon'] : null;
							$icon_id = is_array( $icon ) && ! empty( $icon['ID'] ) ? (int) $icon['ID'] : 0;
							$inline_svg = isset( $social_row['footer_social_icon_svg'] ) ? trim( (string) $social_row['footer_social_icon_svg'] ) : '';
							$icon_markup = '';
							if ( $inline_svg ) {
								$icon_markup = '<span class="footer-social__icon" aria-hidden="true">' . $inline_svg . '</span>';
							} elseif ( $icon_id ) {
								$path = get_attached_file( $icon_id );
								$svg  = '';
								if ( $path && is_readable( $path ) && 'svg' === strtolower( pathinfo( $path, PATHINFO_EXTENSION ) ) ) {
									$svg = file_get_contents( $path );
									$svg = ( false !== $svg ) ? $svg : '';
								}
								if ( $svg ) {
									$icon_markup = '<span class="footer-social__icon" aria-hidden="true">' . $svg . '</span>';
								} else {
									$icon_markup = '<span class="footer-social__icon" aria-hidden="true">'
										. wp_get_attachment_image(
											$icon_id,
											'thumbnail',
											false,
											array(
												'class' => 'footer-social__icon-img',
												'alt'   => '',
											)
										)
										. '</span>';
								}
							}
							if ( ! $icon_markup ) {
								continue;
							}
							// Icon-only links need an accessible name (link title, icon alt, or URL host).
							$label = '';
							if ( ! empty( $link['title'] ) ) {
								$label = $link['title'];
							} elseif ( is_array( $icon ) && ! empty( $icon['alt'] ) ) {
								$label = $icon['alt'];
							} elseif ( $icon_id ) {
								$icon_alt = get_post_meta( $icon_id, '_wp_attachment_image_alt', true );
								if ( is_string( $icon_alt ) && '' !== trim( $icon_alt ) ) {
									$label = trim( $icon_alt );
								}
							}
							if ( '' === $label ) {
								$host = wp_parse_url( $link['url'], PHP_URL_HOST );
								if ( is_string( $host ) && '' !== $host ) {
									$host  = preg_replace( '/^www\./', '', $host );
									$label = sprintf(
										/* translators: %s: website hostname, e.g. linkedin.com */
										__( 'Visit %s', 'jdpower' ),
										$host
									);
								} else {
									$label = __( 'Social media link', 'jdpower' );
								}
							}
							if ( ! empty( $link['target'] ) && '_blank' === $link['target'] ) {
								$label .= ' ' . __( '(opens in a new tab)', 'jdpower' );
							}
							$social_items[] = array(
								'url'         => $link['url'],
								'target'      => ! empty( $link['target'] ) ? $link['target'] : '',
								'label'       => $label,
								'icon_markup' => $icon_markup,
							);
						}
						if ( $social_items ) :
							?>
							<nav class="footer-social" aria-label="<?php esc_attr_e( 'Social links', 'jdpower' ); ?>">
								<ul class="footer-social__list">
									<?php foreach ( $social_items as $social_item ) : ?>
										<li class="footer-social__item">
											<a
												class="footer-social__link"
												href="<?php echo esc_url( $social_item['url'] ); ?>"
												<?php if ( $social_item['target'] ) : ?>
													target="<?php echo esc_attr( $social_item['target'] ); ?>"
													rel="noopener noreferrer"
												<?php endif; ?>
												aria-label="<?php echo esc_attr( $social_item['label'] ); ?>"
											>
												<?php echo $social_item['icon_markup']; ?>
											</a>
										</li>
									<?php endforeach; ?>
								</ul>
							</nav>
							<?php
						endif;
					endif;
					?>
				</div>
				<?php
				foreach ( $footer_nav_locations as $footer_location ) {
					$menu_id = isset( $assigned_nav_locations[ $footer_location ] ) ? (int) $assigned_nav_locations[ $footer_location ] : 0;
					if ( ! $menu_id ) {
						continue;
					}
					$menu_obj = wp_get_nav_menu_object( $menu_id );
					if ( ! $menu_obj ) {
						continue;
					}
					?>
					<div class="col-12 col-md-1-5 footer-nav">
						<nav class="footer-nav__inner" aria-label="<?php echo esc_attr( $menu_obj->name ); ?>">
							<h2 class="footer-nav__title"><?php echo esc_html( $menu_obj->name ); ?></h2>
							<?php
							wp_nav_menu(
								array(
									'theme_location' => $footer_location,
									'container'      => false,
									'menu_class'     => 'footer-nav__menu',
									'fallback_cb'    => false,
									'depth'          => 1,
								)
							);
							?>
						</nav>
					</div>
					<?php
				}
				?>
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
</div>

<?php wp_footer(); ?>
</body>
</html>
