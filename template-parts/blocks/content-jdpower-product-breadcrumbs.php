<?php
/**
 * Block Name: Product Breadcrumbs
 *
 * @package jdpower
 */

if ( ! function_exists( 'jdpower_product_breadcrumbs_levels' ) ) {
	/**
	 * Taxonomy to CPT level map for breadcrumb resolution.
	 *
	 * @return array<int, array{taxonomy: string, post_type: string}>
	 */
	function jdpower_product_breadcrumbs_levels() {
		return array(
			array(
				'taxonomy'  => 'product_industry',
				'post_type' => 'industries',
			),
			array(
				'taxonomy'  => 'product_segment',
				'post_type' => 'segments',
			),
			array(
				'taxonomy'  => 'product_solution',
				'post_type' => 'solution',
			),
		);
	}
}

if ( ! function_exists( 'jdpower_product_breadcrumbs_cpt_by_title' ) ) {
	/**
	 * Find a published CPT post by exact post title.
	 *
	 * @param string $title     Post title to match.
	 * @param string $post_type Post type slug.
	 * @return WP_Post|null
	 */
	function jdpower_product_breadcrumbs_cpt_by_title( $title, $post_type ) {
		$title     = trim( (string) $title );
		$post_type = sanitize_key( (string) $post_type );

		if ( '' === $title || '' === $post_type ) {
			return null;
		}

		$posts = get_posts(
			array(
				'post_type'              => $post_type,
				'title'                  => $title,
				'post_status'            => 'publish',
				'posts_per_page'         => 1,
				'no_found_rows'          => true,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
			)
		);

		if ( empty( $posts ) || ! ( $posts[0] instanceof WP_Post ) ) {
			return null;
		}

		return $posts[0];
	}
}

if ( ! function_exists( 'jdpower_product_breadcrumbs_crumb_from_post_id' ) ) {
	/**
	 * Build a crumb array from a post ID.
	 *
	 * @param int $post_id Post ID.
	 * @return array{label: string, url: string}|null
	 */
	function jdpower_product_breadcrumbs_crumb_from_post_id( $post_id ) {
		$post_id = (int) $post_id;
		if ( $post_id <= 0 ) {
			return null;
		}

		$post = get_post( $post_id );
		if ( ! ( $post instanceof WP_Post ) || 'publish' !== $post->post_status ) {
			return null;
		}

		$url = get_permalink( $post_id );
		if ( ! is_string( $url ) || '' === $url ) {
			return null;
		}

		return array(
			'label' => get_the_title( $post_id ),
			'url'   => $url,
		);
	}
}

if ( ! function_exists( 'jdpower_product_breadcrumbs_from_product' ) ) {
	/**
	 * Build breadcrumbs from product taxonomy terms matched to CPT posts by title.
	 *
	 * @param int $product_id Product post ID.
	 * @return array<int, array{label: string, url: string}>
	 */
	function jdpower_product_breadcrumbs_from_product( $product_id ) {
		$product_id = (int) $product_id;
		if ( $product_id <= 0 || 'product' !== get_post_type( $product_id ) ) {
			return array();
		}

		$crumbs = array();

		foreach ( jdpower_product_breadcrumbs_levels() as $level ) {
			if ( ! function_exists( 'jdpower_get_post_primary_taxonomy_term' ) ) {
				continue;
			}

			$term = jdpower_get_post_primary_taxonomy_term( $product_id, $level['taxonomy'] );
			if ( ! ( $term instanceof WP_Term ) ) {
				continue;
			}

			$cpt = jdpower_product_breadcrumbs_cpt_by_title( $term->name, $level['post_type'] );
			if ( ! ( $cpt instanceof WP_Post ) ) {
				continue;
			}

			$crumb = jdpower_product_breadcrumbs_crumb_from_post_id( $cpt->ID );
			if ( null !== $crumb ) {
				$crumbs[] = $crumb;
			}
		}

		return $crumbs;
	}
}

if ( ! function_exists( 'jdpower_product_breadcrumbs_from_manual' ) ) {
	/**
	 * Build breadcrumbs from manually selected CPT posts.
	 *
	 * @param int $industry_id Industry post ID.
	 * @param int $segment_id  Segment post ID.
	 * @param int $solution_id Solution post ID.
	 * @return array<int, array{label: string, url: string}>
	 */
	function jdpower_product_breadcrumbs_from_manual( $industry_id, $segment_id, $solution_id ) {
		$crumbs = array();

		foreach ( array( (int) $industry_id, (int) $segment_id, (int) $solution_id ) as $post_id ) {
			$crumb = jdpower_product_breadcrumbs_crumb_from_post_id( $post_id );
			if ( null !== $crumb ) {
				$crumbs[] = $crumb;
			}
		}

		return $crumbs;
	}
}

$classes = 'product-breadcrumbs-block';

if ( ! empty( $block['className'] ) ) {
	$classes .= ' ' . $block['className'];
}

$padding_top    = get_field( 'product_breadcrumbs_padding_top' );
$padding_bottom = get_field( 'product_breadcrumbs_padding_bottom' );
$source         = get_field( 'product_breadcrumbs_source' );

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

if ( empty( $source ) || ! in_array( $source, array( 'dynamic', 'manual' ), true ) ) {
	$source = 'dynamic';
}

$crumbs = array();

if ( 'dynamic' === $source ) {
	$product_id = (int) get_the_ID();
	if ( $product_id > 0 ) {
		$crumbs = jdpower_product_breadcrumbs_from_product( $product_id );
	}
} else {
	$crumbs = jdpower_product_breadcrumbs_from_manual(
		(int) get_field( 'product_breadcrumbs_industry' ),
		(int) get_field( 'product_breadcrumbs_segment' ),
		(int) get_field( 'product_breadcrumbs_solution' )
	);
}

if ( empty( $crumbs ) ) {
	return;
}

$current_title = trim( (string) get_the_title() );

$classes .= ' product-breadcrumbs-block--source-' . $source;
?>
<section class="<?php echo esc_attr( $classes ); ?>">
	<div class="container">
		<nav class="product-breadcrumbs-block__nav" aria-label="<?php esc_attr_e( 'Breadcrumb', 'jdpower' ); ?>">
			<ol class="product-breadcrumbs-block__list">
				<?php foreach ( $crumbs as $crumb ) : ?>
					<?php
					$label = isset( $crumb['label'] ) ? (string) $crumb['label'] : '';
					$url   = isset( $crumb['url'] ) ? (string) $crumb['url'] : '';
					if ( '' === $label || '' === $url ) {
						continue;
					}
					?>
					<li class="product-breadcrumbs-block__item">
						<a class="product-breadcrumbs-block__link" href="<?php echo esc_url( $url ); ?>">
							<?php echo esc_html( $label ); ?>
						</a>
					</li>
				<?php endforeach; ?>
				<?php if ( '' !== $current_title ) : ?>
					<li class="product-breadcrumbs-block__item product-breadcrumbs-block__item--current" aria-current="page">
						<span class="product-breadcrumbs-block__current"><?php echo esc_html( $current_title ); ?></span>
					</li>
				<?php endif; ?>
			</ol>
		</nav>
	</div>
</section>
