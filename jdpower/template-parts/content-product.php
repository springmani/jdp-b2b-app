<?php
/**
 * Template part for displaying products.
 *
 * Singular: page-like full-width content (mirrors content-page). Archives/search/tax: list/card layout.
 *
 * @package jdpower
 */

if ( is_singular( 'product' ) ) :
	?>
	<article id="post-<?php the_ID(); ?>" <?php post_class( 'product-single' ); ?>>
		<div class="entry-content product-single__content">
			<?php
			the_content();

			wp_link_pages(
				array(
					'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'jdpower' ),
					'after'  => '</div>',
				)
			);
			?>
		</div>
	</article>
	<?php
else :
	?>
	<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
		<header class="entry-header">
			<div class="container">
				<?php
				the_title( '<h2 class="entry-title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h2>' );
				?>
			</div>
		</header>

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
	</article>
	<?php
endif;
