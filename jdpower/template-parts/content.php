<?php
/**
 * Template part for displaying posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package jdpower
 */

?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
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
				the_title( '<h1 class="entry-title">', '</h1>' );
			else :
				the_title( '<h2 class="entry-title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h2>' );
			endif;
			?>
		</div>
	</header>

	<div class="container">
		<?php if ( is_singular() ) : ?>
			<div class="entry-meta">
				<?php jdpower_the_insight_entry_meta(); ?>
			</div>
		<?php endif; ?>
	</div>

	<?php jdpower_the_insight_single_layout_open(); ?>
	<?php jdpower_post_thumbnail(); ?>

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
					wp_kses_post( get_the_title() )
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
