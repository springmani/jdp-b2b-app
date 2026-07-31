<?php
/**
 * Block Name: Author
 *
 * Dynamic: post author’s first name, last name, title, bio, and profile image.
 * Manual: custom photo, name, title, and bio.
 *
 * @package jdpower
 */

$classes = 'author-block';

if ( ! empty( $block['className'] ) ) {
	$classes .= ' ' . $block['className'];
}

$host_post_id = isset( $jdpower_acf_block_host_post_id ) ? (int) $jdpower_acf_block_host_post_id : 0;
if ( $host_post_id <= 0 ) {
	$host_post_id = (int) get_the_ID();
}

$heading_raw  = function_exists( 'get_field' ) ? get_field( 'author_heading' ) : '';
$display_mode = function_exists( 'get_field' ) ? get_field( 'author_display_mode' ) : 'dynamic';
$heading      = is_string( $heading_raw ) ? trim( $heading_raw ) : '';

if ( ! is_string( $display_mode ) || ! in_array( $display_mode, array( 'dynamic', 'manual' ), true ) ) {
	$display_mode = 'dynamic';
}

$author = array(
	'name'     => '',
	'title'    => '',
	'bio'      => '',
	'image_id' => 0,
);

if ( 'manual' === $display_mode ) {
	$name_raw  = function_exists( 'get_field' ) ? get_field( 'author_manual_name' ) : '';
	$title_raw = function_exists( 'get_field' ) ? get_field( 'author_manual_title' ) : '';
	$bio_raw   = function_exists( 'get_field' ) ? get_field( 'author_manual_bio' ) : '';
	$image     = function_exists( 'get_field' ) ? get_field( 'author_manual_image' ) : 0;

	$author['name']  = is_string( $name_raw ) ? trim( $name_raw ) : '';
	$author['title'] = is_string( $title_raw ) ? trim( $title_raw ) : '';
	$author['bio']   = is_string( $bio_raw ) ? trim( $bio_raw ) : '';
	if ( is_array( $image ) && ! empty( $image['ID'] ) ) {
		$author['image_id'] = (int) $image['ID'];
	} elseif ( is_numeric( $image ) ) {
		$author['image_id'] = (int) $image;
	}
} else {
	$author_id = $host_post_id > 0 ? (int) get_post_field( 'post_author', $host_post_id ) : 0;
	$first     = '';
	$last      = '';

	if ( $author_id > 0 ) {
		$first = trim( (string) get_the_author_meta( 'first_name', $author_id ) );
		$last  = trim( (string) get_the_author_meta( 'last_name', $author_id ) );
	}

	// Dynamic mode requires a first name before anything is shown. Last name is optional.
	if ( '' === $first ) {
		if ( ! empty( $is_preview ) ) {
			echo '<div class="' . esc_attr( $classes ) . '"><p class="author-block__placeholder">';
			echo esc_html__( 'This user does not have their First Name set. Add a First Name on their user profile for the Author block to display.', 'jdpower' );
			echo '</p></div>';
		}
		return;
	}

	$bio        = trim( (string) get_the_author_meta( 'description', $author_id ) );
	$title_raw  = function_exists( 'get_field' ) ? get_field( 'user_profile_title', 'user_' . $author_id ) : '';
	$title      = is_string( $title_raw ) ? trim( $title_raw ) : '';

	$profile_image = function_exists( 'get_field' ) ? get_field( 'user_profile_image', 'user_' . $author_id ) : 0;
	$image_id      = 0;
	if ( is_array( $profile_image ) && ! empty( $profile_image['ID'] ) ) {
		$image_id = (int) $profile_image['ID'];
	} elseif ( is_numeric( $profile_image ) ) {
		$image_id = (int) $profile_image;
	}

	$author['name']     = '' !== $last ? $first . ' ' . $last : $first;
	$author['title']    = $title;
	$author['bio']      = $bio;
	$author['image_id'] = $image_id;
}

if ( '' === $author['name'] && '' === $author['title'] && '' === $author['bio'] && $author['image_id'] <= 0 ) {
	if ( ! empty( $is_preview ) ) {
		echo '<div class="' . esc_attr( $classes ) . '"><p class="author-block__placeholder">';
		echo esc_html__( 'Add an author name, bio, or photo in the block sidebar.', 'jdpower' );
		echo '</p></div>';
	}
	return;
}

?>

<section class="<?php echo esc_attr( $classes ); ?>">
	<div class="container">
		<?php if ( '' !== $heading ) : ?>
			<p class="author-block__heading preheading"><?php echo esc_html( $heading ); ?></p>
		<?php endif; ?>

		<article class="author-block__author">
			<?php if ( $author['image_id'] > 0 ) : ?>
				<div class="author-block__media">
					<?php
					echo wp_get_attachment_image(
						$author['image_id'],
						'thumbnail',
						false,
						array(
							'class'    => 'author-block__img',
							'loading'  => 'lazy',
							'decoding' => 'async',
							'alt'      => '' !== $author['name'] ? $author['name'] : '',
						)
					);
					?>
				</div>
			<?php endif; ?>

			<div class="author-block__details">
				<?php if ( '' !== $author['name'] ) : ?>
					<h3 class="author-block__name"><?php echo esc_html( $author['name'] ); ?></h3>
				<?php endif; ?>

				<?php if ( '' !== $author['title'] ) : ?>
					<p class="author-block__title"><?php echo esc_html( $author['title'] ); ?></p>
				<?php endif; ?>

				<?php if ( '' !== $author['bio'] ) : ?>
					<div class="author-block__bio">
						<?php
						echo wp_kses_post( wpautop( $author['bio'] ) );
						?>
					</div>
				<?php endif; ?>
			</div>
		</article>
	</div>
</section>
