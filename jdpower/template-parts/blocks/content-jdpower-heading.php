<?php
/**
 * Block Name: Heading
 */

$heading_text = get_field( 'block_heading_text' );

if ( ! is_string( $heading_text ) || '' === trim( $heading_text ) ) {
	return;
}

$classes = 'heading-block';

if ( ! empty( $block['className'] ) ) {
	$classes .= ' ' . $block['className'];
}

$align = get_field( 'block_heading_align' );
if ( 'center' === $align ) {
	$classes .= ' text-center';
} elseif ( 'right' === $align ) {
	$classes .= ' text-end';
} else {
	$classes .= ' text-start';
}

$max_width = get_field( 'block_heading_max_width' );
$valid_max_width = array( 'full', 'large', 'medium', 'small' );
if ( empty( $max_width ) || ! in_array( $max_width, $valid_max_width, true ) ) {
	$max_width = 'full';
}
if ( 'full' !== $max_width ) {
	$classes .= ' heading-block--max-' . $max_width;
}

$color_mode = get_field( 'block_heading_color_mode' );
if ( 'light' === $color_mode ) {
	$classes .= ' heading-block--light';
} elseif ( 'teal' === $color_mode ) {
	$classes .= ' heading-block--teal';
}

$level = get_field( 'block_heading_level' );
$allowed_levels = array( 'h2', 'h3', 'h4', 'preheading' );
if ( ! is_string( $level ) || ! in_array( $level, $allowed_levels, true ) ) {
	$level = 'h2';
}

$is_preheading = ( 'preheading' === $level );

$pre_size = get_field( 'block_heading_pre_heading_size' );
if ( empty( $pre_size ) || ! in_array( $pre_size, array( 'small', 'default', 'large' ), true ) ) {
	$pre_size = 'default';
}

$title_classes = 'heading-block__title';
if ( $is_preheading ) {
	$title_classes .= ' preheading';
	if ( 'small' === $pre_size ) {
		$title_classes .= ' heading-block__pre--small';
	} elseif ( 'large' === $pre_size ) {
		$title_classes .= ' heading-block__pre--large';
	}
}

$tag = $is_preheading ? 'p' : tag_escape( $level );
?>

<div class="<?php echo esc_attr( $classes ); ?>">
	<div class="heading-block__inner">
		<<?php echo esc_attr( $tag ); ?> class="<?php echo esc_attr( $title_classes ); ?>"><?php echo wp_kses_post( $heading_text ); ?></<?php echo esc_attr( $tag ); ?>>
	</div>
</div>
