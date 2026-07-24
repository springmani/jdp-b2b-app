<?php
/**
 * Template part for displaying results in search pages
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package jdpower
 */

$post_id = get_the_ID();
$parts   = jdpower_search_result_card_parts( $post_id );
$type    = function_exists( 'jdpower_insight_card_post_type_label' )
	? jdpower_insight_card_post_type_label( $post_id )
	: '';

$card_classes = array( 'post-filters__card', 'post-filters__card--search-text' );
?>

<article id="post-<?php the_ID(); ?>" <?php post_class( $card_classes ); ?>>
	<a class="post-filters__card-link" href="<?php the_permalink(); ?>">
		<div class="post-filters__card-body">
			<?php if ( '' !== $type ) : ?>
				<p class="post-filters__card-type"><?php echo esc_html( $type ); ?></p>
			<?php endif; ?>
			<h2 class="post-filters__card-title"><span class="post-filters__card-title-text"><?php echo $parts['title_html']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span></h2>
			<?php if ( '' !== trim( wp_strip_all_tags( (string) $parts['summary_html'] ) ) ) : ?>
				<div class="post-filters__card-excerpt"><?php echo $parts['summary_html']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
			<?php endif; ?>
		</div>
	</a>
</article>
