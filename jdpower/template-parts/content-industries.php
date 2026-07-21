<?php
/**
 * Template part for displaying single industry posts in single.php
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package jdpower
 */

?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<?php // jdpower_post_thumbnail(); ?>

	<div class="entry-content">
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
