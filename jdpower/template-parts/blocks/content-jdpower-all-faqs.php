<?php
/**
 * Block Name: All Faqs
 *
 * @package jdpower
 */

$classes = 'all-faqs-block accordion-block';

if ( ! empty( $block['className'] ) ) {
	$classes .= ' ' . $block['className'];
}

$padding_top     = get_field( 'all_faqs_padding_top' );
$padding_bottom  = get_field( 'all_faqs_padding_bottom' );
$background_tone = get_field( 'all_faqs_background_tone' );
$pre_heading     = get_field( 'all_faqs_pre_heading' );
$heading         = get_field( 'all_faqs_heading' );
$show_type_head  = get_field( 'all_faqs_show_type_heading' );
$faqs_order_by   = get_field( 'all_faqs_order_by' );
$faqs_order      = get_field( 'all_faqs_order' );

$valid_post_order_by = array( 'title', 'date', 'menu_order' );
$valid_order         = array( 'ASC', 'DESC' );

if ( empty( $faqs_order_by ) || ! in_array( $faqs_order_by, $valid_post_order_by, true ) ) {
	$faqs_order_by = 'title';
}
if ( empty( $faqs_order ) || ! in_array( $faqs_order, $valid_order, true ) ) {
	$faqs_order = 'ASC';
}

$valid_padding = array( 'default', 'small', 'medium', 'large', 'none' );
if ( ! empty( $padding_top ) && in_array( $padding_top, $valid_padding, true ) ) {
	$classes .= ' pt-' . $padding_top;
} else {
	$classes .= ' pt-default';
}

if ( ! empty( $padding_bottom ) && in_array( $padding_bottom, $valid_padding, true ) ) {
	$classes .= ' pb-' . $padding_bottom;
} else {
	$classes .= ' pb-default';
}

if ( empty( $background_tone ) || ! in_array( $background_tone, array( 'dark', 'medium', 'light' ), true ) ) {
	$background_tone = 'light';
}
$classes .= ' bg-' . $background_tone;

if ( 'dark' === $background_tone ) {
	$classes .= ' accordion-block--tone-dark';
} elseif ( 'medium' === $background_tone ) {
	$classes .= ' accordion-block--tone-medium';
} else {
	$classes .= ' accordion-block--tone-light';
}

$has_pre     = is_string( $pre_heading ) && '' !== trim( $pre_heading );
$has_heading = is_string( $heading ) && '' !== trim( wp_strip_all_tags( $heading ) );

$terms = get_terms(
	array(
		'taxonomy'   => 'faq_type',
		'hide_empty' => true,
		'orderby'    => 'name',
		'order'      => 'ASC',
	)
);

if ( is_wp_error( $terms ) ) {
	$terms = array();
}

$groups = array();
foreach ( $terms as $term ) {
	if ( ! ( $term instanceof WP_Term ) ) {
		continue;
	}

	$q = new WP_Query(
		array(
			'post_type'      => 'faqs',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => $faqs_order_by,
			'order'          => $faqs_order,
			'tax_query'      => array(
				array(
					'taxonomy' => 'faq_type',
					'field'    => 'term_id',
					'terms'    => array( (int) $term->term_id ),
				),
			),
		)
	);

	if ( ! $q->have_posts() ) {
		continue;
	}

	$groups[] = array(
		'term'  => $term,
		'query' => $q,
	);
}

if ( ! $has_pre && ! $has_heading && count( $groups ) <= 0 ) {
	return;
}
?>

<section class="<?php echo esc_attr( $classes ); ?>">
	<div class="container">
		<?php if ( $has_pre || $has_heading ) : ?>
			<header class="accordion-block__header">
				<?php if ( $has_pre ) : ?>
					<p class="accordion-block__pre preheading"><?php echo esc_html( trim( $pre_heading ) ); ?></p>
				<?php endif; ?>
				<?php if ( $has_heading ) : ?>
					<h2 class="accordion-block__heading"><?php echo wp_kses_post( $heading ); ?></h2>
				<?php endif; ?>
			</header>
		<?php endif; ?>

		<?php foreach ( $groups as $group ) : ?>
			<?php
			$term = $group['term'];
			$q    = $group['query'];
			?>
			<div class="all-faqs-block__group">
				<?php if ( ! empty( $show_type_head ) ) : ?>
					<h3 class="all-faqs-block__group-heading"><?php echo esc_html( $term->name ); ?></h3>
				<?php endif; ?>
				<div class="accordion-block__items">
					<?php while ( $q->have_posts() ) : ?>
						<?php
						$q->the_post();
						$item_heading = get_the_title();
						$item_intro   = get_field( 'faq_short_description', get_the_ID() );
						$item_body    = apply_filters( 'the_content', get_the_content() );
						$has_body     = '' !== trim( wp_strip_all_tags( $item_body ) );
						$has_intro    = is_string( $item_intro ) && '' !== trim( $item_intro );
						?>
						<details class="accordion-block__item">
							<summary class="accordion-block__summary">
								<span class="accordion-block__summary-main">
									<?php if ( is_string( $item_heading ) && '' !== trim( $item_heading ) ) : ?>
										<h4 class="accordion-block__item-heading"><?php echo esc_html( trim( $item_heading ) ); ?></h4>
									<?php endif; ?>
									<?php if ( $has_intro ) : ?>
										<span class="accordion-block__item-intro"><?php echo esc_html( trim( $item_intro ) ); ?></span>
									<?php endif; ?>
								</span>
								<span class="accordion-block__toggle" aria-hidden="true"></span>
							</summary>
							<?php if ( $has_body ) : ?>
								<div class="accordion-block__panel">
									<div class="accordion-block__body">
										<?php echo wp_kses_post( $item_body ); ?>
									</div>
								</div>
							<?php endif; ?>
						</details>
					<?php endwhile; ?>
					<?php wp_reset_postdata(); ?>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
</section>

