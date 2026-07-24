<?php
/**
 * Block Name: CTA
 */

// Base classes
$classes = 'cta';

if ( ! empty( $block['className'] ) ) {
	$classes .= ' ' . $block['className'];
}

// Fields
$cta_heading = get_field( 'cta_heading' );
$cta_copy    = get_field( 'cta_copy' );
$cta_link = get_field( 'cta_link' );

$cta_url    = is_array( $cta_link ) ? ( $cta_link['url'] ?? '' ) : '';
$cta_title  = is_array( $cta_link ) ? ( $cta_link['title'] ?? '' ) : '';
$cta_target = is_array( $cta_link ) ? ( $cta_link['target'] ?? '' ) : '';

$target_attr = '';
$rel_attr    = '';

if ( '_blank' === $cta_target ) {
	$target_attr = ' target="_blank"';
	$rel_attr    = ' rel="noopener noreferrer"';
}
?>

<section class="<?php echo esc_attr( $classes ); ?>">
	<div class="container">
		<div class="cta-content">

			<?php if ( ! empty( $cta_heading ) ) : ?>
				<h2><?php echo esc_html( $cta_heading ); ?></h2>
			<?php endif; ?>

			<?php if ( ! empty( $cta_copy ) ) : ?>
				<p><?php echo wp_kses_post( $cta_copy ); ?></p>
			<?php endif; ?>

            <?php if ( $cta_url && $cta_title ) : ?>
                <a class="btn btn-small mt-3" href="<?php echo esc_url( $cta_url ); ?>"<?php echo $target_attr . $rel_attr; ?>>
                    <?php echo esc_html( $cta_title ); ?>
                </a>
            <?php endif; ?>
		</div>
	</div>
</section>