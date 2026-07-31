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

$has_heading = is_string( $cta_heading ) && '' !== trim( $cta_heading );
$has_copy    = is_string( $cta_copy ) && '' !== trim( wp_strip_all_tags( $cta_copy ) );
$has_link    = $cta_url && $cta_title;
$has_content = $has_heading || $has_copy || $has_link;

// Front end: hide empty block. Editor: keep a visible empty state so the block can be selected.
if ( ! $has_content && empty( $is_preview ) ) {
	return;
}
?>

<section class="<?php echo esc_attr( $classes ); ?>">
	<div class="container">
		<?php if ( $has_content ) : ?>
			<div class="cta-content">

				<?php if ( $has_heading ) : ?>
					<h2><?php echo esc_html( $cta_heading ); ?></h2>
				<?php endif; ?>

				<?php if ( $has_copy ) : ?>
					<p><?php echo wp_kses_post( $cta_copy ); ?></p>
				<?php endif; ?>

				<?php if ( $has_link ) : ?>
					<a class="btn btn-small mt-3" href="<?php echo esc_url( $cta_url ); ?>"<?php echo $target_attr . $rel_attr; ?>>
						<?php echo esc_html( $cta_title ); ?>
					</a>
				<?php endif; ?>
			</div>
		<?php else : ?>
			<?php
			jdpower_acf_block_editor_placeholder(
				__( 'Add a heading, copy, or link in the block sidebar.', 'jdpower' ),
				$block
			);
			?>
		<?php endif; ?>
	</div>
</section>
