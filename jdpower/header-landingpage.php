<?php
/**
 * Landingpage header: minimal - nur HTML head + Logo als Overlay über dem ersten Block.
 * Kein WordPress Standard-Header, kein sticky/fixed Verhalten.
 *
 * @package jdpower
 */

$header_logo = get_field( 'header_logo', 'option' );
$logo_inner  = '';
$logo_style  = '';

if ( $header_logo && is_array( $header_logo ) && ! empty( $header_logo['ID'] ) ) {
	$logo_id   = (int) $header_logo['ID'];
	$max_width = absint( get_field( 'header_logo_max_width', 'option' ) );
	$logo_style = $max_width ? ' style="max-width: ' . esc_attr( $max_width ) . 'px;"' : '';

	$path = get_attached_file( $logo_id );
	$svg  = '';

	// SVG inline laden für richtige Farben
	if ( $path && is_readable( $path ) && 'svg' === strtolower( pathinfo( $path, PATHINFO_EXTENSION ) ) ) {
		$svg = file_get_contents( $path );
		$svg = ( false !== $svg ) ? $svg : '';
	}

	$logo_alt = ! empty( $header_logo['alt'] ) ? $header_logo['alt'] : get_bloginfo( 'name', 'display' );

	$logo_inner = $svg ? $svg : wp_get_attachment_image(
		$logo_id,
		'full',
		false,
		array(
			'alt'   => $logo_alt,
			'class' => 'landingpage-header__logo-img',
		)
	);
}
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class( 'landingpage-template' ); ?>>
<?php wp_body_open(); ?>

<div id="page" class="site landingpage-site">

	<?php // Logo als absolute-positioned Overlay - liegt oben links auf dem ersten Content-Block ?>
	<?php if ( $logo_inner ) : ?>
		<div class="landingpage-logo-overlay">
			<div class="container">
				<a class="landingpage-logo-overlay__link" href="<?php echo esc_url( jdpower_get_home_url() ); ?>" rel="home" aria-label="<?php esc_attr_e( 'Home', 'jdpower' ); ?>">
					<span class="landingpage-logo-overlay__logo"<?php echo $logo_style; ?>>
						<?php echo $logo_inner; ?>
					</span>
				</a>
			</div>
		</div>
	<?php endif; ?>
