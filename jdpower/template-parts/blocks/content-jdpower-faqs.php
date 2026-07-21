<?php
/**
 * Block Name: Faqs
 *
 * @package jdpower
 */

$classes = 'faqs-block accordion-block';

if ( ! empty( $block['className'] ) ) {
	$classes .= ' ' . $block['className'];
}

$padding_top     = get_field( 'faqs_padding_top' );
$padding_bottom  = get_field( 'faqs_padding_bottom' );
$background_tone = get_field( 'faqs_background_tone' );
$pre_heading     = get_field( 'faqs_pre_heading' );
$heading         = get_field( 'faqs_heading' );
$source          = get_field( 'faqs_source' );
$show_type_head  = get_field( 'faqs_show_type_heading' );
$dyn_order_by    = get_field( 'faqs_dynamic_order_by' );
$dyn_order       = get_field( 'faqs_dynamic_order' );

$valid_post_order_by = array( 'title', 'date', 'menu_order' );
$valid_order         = array( 'ASC', 'DESC' );
if ( empty( $dyn_order_by ) || ! in_array( $dyn_order_by, $valid_post_order_by, true ) ) {
	$dyn_order_by = 'title';
}
if ( empty( $dyn_order ) || ! in_array( $dyn_order, $valid_order, true ) ) {
	$dyn_order = 'ASC';
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

$groups = array();

if ( 'manual' === $source ) {
	$manual_heading = get_field( 'faqs_manual_heading' );
	$manual_items   = get_field( 'faqs_manual_items' );

	$ids = array();
	if ( is_array( $manual_items ) ) {
		foreach ( $manual_items as $id ) {
			$ids[] = (int) $id;
		}
	}
	$ids = array_values( array_filter( array_unique( $ids ) ) );

	if ( count( $ids ) > 0 ) {
		$q = new WP_Query(
			array(
				'post_type'      => 'faqs',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'post__in'       => $ids,
				'orderby'        => 'post__in',
			)
		);

		if ( $q->have_posts() ) {
			$groups[] = array(
				'group_heading' => is_string( $manual_heading ) ? trim( $manual_heading ) : '',
				'query'         => $q,
			);
		}
	}
} else {
	$rows = get_field( 'faqs_dynamic_types' );
	if ( is_array( $rows ) ) {
		foreach ( $rows as $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}
			$term_id = isset( $row['faqs_dynamic_type'] ) ? (int) $row['faqs_dynamic_type'] : 0;
			if ( $term_id <= 0 ) {
				continue;
			}

			$term = get_term( $term_id, 'faq_type' );
			if ( ! ( $term instanceof WP_Term ) ) {
				continue;
			}

			$q = new WP_Query(
				array(
					'post_type'      => 'faqs',
					'post_status'    => 'publish',
					'posts_per_page' => -1,
					'orderby'        => $dyn_order_by,
					'order'          => $dyn_order,
					'tax_query'      => array(
						array(
							'taxonomy' => 'faq_type',
							'field'    => 'term_id',
							'terms'    => array( $term_id ),
						),
					),
				)
			);

			if ( ! $q->have_posts() ) {
				continue;
			}

			$groups[] = array(
				'group_heading' => ! empty( $show_type_head ) ? $term->name : '',
				'query'         => $q,
			);
		}
	}
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
			$group_heading = isset( $group['group_heading'] ) ? (string) $group['group_heading'] : '';
			$q             = $group['query'];
			?>
			<div class="faqs-block__group">
				<?php if ( '' !== trim( $group_heading ) ) : ?>
					<h3 class="faqs-block__group-heading"><?php echo esc_html( trim( $group_heading ) ); ?></h3>
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

