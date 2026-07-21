<?php
/**
 * Template part for displaying press releases (no featured image)
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package jdpower
 */

?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<?php
	$heading_clean = function_exists( 'get_field' ) ? jdpower_sanitize_inline_html( get_field( 'page_card_heading_override' ) ) : '';
	$sub_heading   = function_exists( 'get_field' ) ? jdpower_sanitize_inline_html( get_field( 'page_card_sub_heading' ) ) : '';

	$display_title_plain = '' !== $heading_clean
		? wp_strip_all_tags( $heading_clean )
		: get_the_title();
	?>
	<header class="entry-header">
		<div class="container">
			<?php
			if ( is_singular() ) :
				$post_type_obj = get_post_type_object( get_post_type() );
				$type_eyebrow  = '';
				if ( $post_type_obj ) {
					if ( ! empty( $post_type_obj->labels->singular_name ) ) {
						$type_eyebrow = $post_type_obj->labels->singular_name;
					} elseif ( ! empty( $post_type_obj->labels->name ) ) {
						$type_eyebrow = $post_type_obj->labels->name;
					}
				}
				if ( '' !== $type_eyebrow ) :
					?>
					<p class="preheading entry-header__post-type"><?php echo esc_html( $type_eyebrow ); ?></p>
					<?php
				endif;

				echo '<h1 class="entry-title">';
				if ( '' !== $heading_clean ) {
					echo $heading_clean; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- jdpower_sanitize_inline_html()
				} else {
					echo esc_html( get_the_title() );
				}
				echo '</h1>';
				if ( '' !== $sub_heading ) :
					echo '<p class="entry-header__subheading">' . $sub_heading . '</p>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- jdpower_sanitize_inline_html()
				endif;
			else :
				echo '<h2 class="entry-title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">';
				if ( '' !== $heading_clean ) {
					echo $heading_clean; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- jdpower_sanitize_inline_html()
				} else {
					echo esc_html( get_the_title() );
				}
				echo '</a></h2>';
			endif;
			?>
		</div>
	</header>

	<?php if ( is_singular() ) : ?>
		<div class="container">
			<div class="entry-meta">
				<?php jdpower_the_insight_entry_meta(); ?>
			</div>
		</div>
	<?php endif; ?>

	<?php jdpower_the_insight_single_layout_open(); ?>

	<div class="entry-content">
		<div class="container">
			<?php
			the_content(
				sprintf(
					wp_kses(
						/* translators: %s: Name of current post. Only visible to screen readers */
						__( 'Continue reading<span class="screen-reader-text"> "%s"</span>', 'jdpower' ),
						array(
							'span' => array(
								'class' => array(),
							),
						)
					),
					esc_html( $display_title_plain )
				)
			);

			wp_link_pages(
				array(
					'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'jdpower' ),
					'after'  => '</div>',
				)
			);
			?>
		</div>
	</div>

	<?php jdpower_the_insight_single_layout_close(); ?>
	<?php jdpower_the_insight_single_footer(); ?>
</article>
