<?php
/**
 * Block Name: Media Contacts
 *
 * @package jdpower
 */

$classes = 'media-contacts-block';

if ( ! empty( $block['className'] ) ) {
	$classes .= ' ' . $block['className'];
}

$heading_raw = function_exists( 'get_field' ) ? get_field( 'media_contacts_heading' ) : '';
$selected    = function_exists( 'get_field' ) ? get_field( 'media_contacts_selected' ) : array();

if ( ! is_array( $selected ) ) {
	$selected = array();
}

$heading = is_string( $heading_raw ) ? trim( $heading_raw ) : '';
if ( '' === $heading ) {
	$heading = 'Media Contacts';
}

$contacts = array();
foreach ( $selected as $entry ) {
	$contact_id = 0;
	if ( is_object( $entry ) && isset( $entry->ID ) ) {
		$contact_id = (int) $entry->ID;
	} elseif ( is_numeric( $entry ) ) {
		$contact_id = (int) $entry;
	}
	if ( $contact_id <= 0 || 'media_contact' !== get_post_type( $contact_id ) || 'publish' !== get_post_status( $contact_id ) ) {
		continue;
	}

	$name  = get_the_title( $contact_id );
	$name  = is_string( $name ) ? trim( $name ) : '';
	$phone = function_exists( 'get_field' ) ? get_field( 'media_contact_phone', $contact_id ) : '';
	$email = function_exists( 'get_field' ) ? get_field( 'media_contact_email', $contact_id ) : '';
	$phone = is_string( $phone ) ? trim( $phone ) : '';
	$email = is_string( $email ) ? trim( $email ) : '';

	if ( '' === $name && '' === $phone && '' === $email ) {
		continue;
	}

	$contacts[] = array(
		'id'       => $contact_id,
		'name'     => $name,
		'phone'    => $phone,
		'email'    => $email,
		'image_id' => (int) get_post_thumbnail_id( $contact_id ),
	);
}

if ( empty( $contacts ) ) {
	return;
}

?>

<section class="<?php echo esc_attr( $classes ); ?>">
	<div class="container">
		<p class="media-contacts-block__heading preheading"><?php echo esc_html( $heading ); ?></p>

		<div class="media-contacts-block__list">
			<?php foreach ( $contacts as $contact ) : ?>
				<article class="media-contacts-block__contact">
					<?php if ( $contact['image_id'] > 0 ) : ?>
						<div class="media-contacts-block__media">
							<?php
							echo wp_get_attachment_image(
								$contact['image_id'],
								'thumbnail',
								false,
								array(
									'class'    => 'media-contacts-block__img',
									'loading'  => 'lazy',
									'decoding' => 'async',
									'alt'      => '' !== $contact['name'] ? $contact['name'] : '',
								)
							);
							?>
						</div>
					<?php endif; ?>

					<div class="media-contacts-block__details">
						<?php if ( '' !== $contact['name'] ) : ?>
							<h3 class="media-contacts-block__name"><?php echo esc_html( $contact['name'] ); ?></h3>
						<?php endif; ?>

						<?php if ( '' !== $contact['phone'] ) : ?>
							<p class="media-contacts-block__phone"><?php echo esc_html( $contact['phone'] ); ?></p>
						<?php endif; ?>

						<?php if ( '' !== $contact['email'] ) : ?>
							<p class="media-contacts-block__email">
								<a href="<?php echo esc_url( 'mailto:' . $contact['email'] ); ?>"><?php echo esc_html( $contact['email'] ); ?></a>
							</p>
						<?php endif; ?>
					</div>
				</article>
			<?php endforeach; ?>
		</div>
	</div>
</section>
