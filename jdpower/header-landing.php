<?php
/**
 * The header for our theme
 *
 * This is the template that displays all of the <head> section and everything up until <div id="content">
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package jdpower
 */

?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo('charset'); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<div id="page" class="site">
	<a class="skip-link screen-reader-text" href="#primary"><?php esc_html_e( 'Skip to content', 'jdpower' ); ?></a>

	<header id="masthead" class="site-header">
		<div class="container nav-wrapper">
			<div class="site-branding">
				<?php
				$header_logo = get_field( 'header_logo_landing_page', 'option' );
				$logo_style  = '';
				$logo_inner  = '';
				if ( $header_logo && is_array( $header_logo ) && ! empty( $header_logo['ID'] ) ) {
					$mw = absint( get_field( 'header_logo_max_width', 'option' ) );
					$logo_style = $mw ? ' style="' . esc_attr( "max-width: {$mw}px;" ) . '"' : '';
					$id   = (int) $header_logo['ID'];
					$path = get_attached_file( $id );
					$svg  = '';
					if ( $path && is_readable( $path ) && 'svg' === strtolower( pathinfo( $path, PATHINFO_EXTENSION ) ) ) {
						$svg = file_get_contents( $path );
						$svg = ( false !== $svg ) ? $svg : '';
					}
					$logo_img_alt = ! empty( $header_logo['alt'] ) ? $header_logo['alt'] : get_bloginfo( 'name', 'display' );
					$logo_inner   = $svg ? $svg : wp_get_attachment_image(
						$id,
						'full',
						false,
						array(
							'alt' => $logo_img_alt,
						)
					);
				}
				if ( $logo_inner ) :
					$header_logo_link_label = sprintf(
						/* translators: %s: Site title */
						__( '%s logo', 'jdpower' ),
						get_bloginfo( 'name', 'display' )
					);
					?>
					<a href="<?php echo esc_url( jdpower_get_home_url() ); ?>" rel="home" aria-label="<?php echo esc_attr( $header_logo_link_label ); ?>">
						<div class="logo"<?php echo $logo_style;?>>
							<?php echo $logo_inner; ?>
						</div>
					</a>
					<?php
				endif;
				?>
			</div>

		</div>
	</header>
