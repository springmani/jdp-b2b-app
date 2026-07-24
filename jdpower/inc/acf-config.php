<?php
/**
 * Register Theme Options Page
 */
function jdpower_acf_register_options_page() {
	if ( function_exists( 'acf_add_options_page' ) ) {
		acf_add_options_page(
			array(
				'page_title' => 'Theme Settings',
				'menu_title' => 'Theme Settings',
				'menu_slug'  => 'theme-settings',
				'capability' => 'edit_posts',
				'redirect'   => false,
			)
		);
	}
}
add_action( 'acf/init', 'jdpower_acf_register_options_page' );

/**
 * Block category for ACF-registered blocks.
 */
function jdpower_block_category( $categories, $post ) {
	return array_merge(
		array(
			array(
				'slug'  => 'jdpower',
				'title' => 'jdpower',
			),
		),
		$categories,
	);
}
add_filter( 'block_categories_all', 'jdpower_block_category', 10, 2 );

/**
 * Register ACF Blocks
 */
add_action('acf/init', 'jdpower_gutenburg_acf_blocks');
function jdpower_gutenburg_acf_blocks() {
	// check function exists
	if( function_exists('acf_register_block') ) {
		// Block registrations sorted alphabetically by title.

		// register Accordion block
		acf_register_block(array(
			'name'				=> 'jdpower-accordion',
			'title'				=> __('Accordion'),
			'description'		=> __('Section heading with expandable items, optional two-column lists, and footnotes'),
			'render_callback'	=> 'jdpower_render_acf_block',
			'category'			=> 'jdpower',
			'keywords'			=> array( 'Accordion', 'FAQ', 'Expand' ),
		));

		// register Page anchor block
		acf_register_block(array(
			'name'				=> 'jdpower-anchor',
			'title'				=> __('Page anchor'),
			'description'		=> __('Sets an HTML id for in-page links (e.g. #my-section). No visible content on the site.'),
			'render_callback'	=> 'jdpower_render_acf_block',
			'category'			=> 'jdpower',
			'keywords'			=> array( 'Anchor', 'Link', 'Jump', 'Hash', 'Id', 'CTA' ),
			'supports'			=> array(
				'align'  => false,
				'anchor' => false,
				'mode'   => true,
			),
		));

		// register All Faqs block
		acf_register_block(array(
			'name'				=> 'jdpower-all-faqs',
			'title'				=> __('All Faqs'),
			'description'		=> __('Groups all FAQs by FAQ Type and renders each as an accordion'),
			'render_callback'	=> 'jdpower_render_acf_block',
			'category'			=> 'jdpower',
			'keywords'			=> array( 'FAQ', 'Accordion', 'Faqs' ),
		));

		// register Content Banner block
		acf_register_block(array(
			'name'				=> 'jdpower-content-banner',
			'title'				=> __('Content Banner'),
			'description'		=> __('Centered pre-heading, heading, and body copy with optional dark teal section'),
			'render_callback'	=> 'jdpower_render_acf_block',
			'category'			=> 'jdpower',
			'keywords'			=> array( 'Banner', 'About', 'Intro', 'Content' ),
		));

		// register Content Columns block
		acf_register_block(array(
			'name'				=> 'jdpower-content-columns',
			'title'				=> __('Content Columns'),
			'description'		=> __('Pre-heading and a row of columns with heading and copy'),
			'render_callback'	=> 'jdpower_render_acf_block',
			'category'			=> 'jdpower',
			'keywords'			=> array( 'Columns', 'Content', 'Text' ),
		));

		// register Content With Image block
		acf_register_block(array(
			'name'				=> 'jdpower-content-with-image',
			'title'				=> __('Content With Image'),
			'description'		=> __('Two columns with image, pre-heading, heading, copy, and CTA'),
			'render_callback'	=> 'jdpower_render_acf_block',
			'category'			=> 'jdpower',
			'keywords'			=> array( 'Image', 'Content', 'CTA' ),
		));

		// register CTA block
		acf_register_block(array(
			'name'				=> 'jdpower-cta',
			'title'				=> __('CTA'),
			'description'		=> __('One column with text and CTA'),
			'render_callback'	=> 'jdpower_render_acf_block',
			'category'			=> 'jdpower',
			'keywords'			=> array( 'CTA' ),
		));

		// register CTA Banner block
		acf_register_block(array(
			'name'				=> 'jdpower-cta-banner',
			'title'				=> __('CTA Banner'),
			'description'		=> __('Banner with padding, background, heading, copy, and up to two CTAs'),
			'render_callback'	=> 'jdpower_render_acf_block',
			'category'			=> 'jdpower',
			'keywords'			=> array( 'CTA', 'Banner' ),
		));

		// register CTA Button block
		acf_register_block(array(
			'name'				=> 'jdpower-cta-button',
			'title'				=> __('CTA Button'),
			'description'		=> __('One or more CTA links (button styles)'),
			'render_callback'	=> 'jdpower_render_acf_block',
			'category'			=> 'jdpower',
			'keywords'			=> array( 'CTA', 'Button', 'Link' ),
		));

		// register Faqs block
		acf_register_block(array(
			'name'				=> 'jdpower-faqs',
			'title'				=> __('Faqs'),
			'description'		=> __('FAQ accordion list sourced dynamically by type(s) or via manual selection'),
			'render_callback'	=> 'jdpower_render_acf_block',
			'category'			=> 'jdpower',
			'keywords'			=> array( 'FAQ', 'Accordion', 'Faqs' ),
		));

		// register Feature Columns block
		acf_register_block(array(
			'name'				=> 'jdpower-feature-columns',
			'title'				=> __('Feature Columns'),
			'description'		=> __('Repeating columns with image, pre-heading, heading, and bulleted copy'),
			'render_callback'	=> 'jdpower_render_acf_block',
			'category'			=> 'jdpower',
			'keywords'			=> array( 'Columns', 'Features', 'Image' ),
		));

		// register Featured Insights block
		acf_register_block(array(
			'name'				=> 'jdpower-featured-insights',
			'title'				=> __('Featured Insights'),
			'description'		=> __('Featured post cards from manual selection or custom content; heading and intro copy'),
			'render_callback'	=> 'jdpower_render_acf_block',
			'category'			=> 'jdpower',
			'keywords'			=> array( 'Posts', 'Insights', 'Blog', 'Featured', 'Cards', 'Grid' ),
		));

		// register Featured Insights Columns block
		acf_register_block(array(
			'name'				=> 'jdpower-featured-insights-columns',
			'title'				=> __('Featured Insights Columns'),
			'description'		=> __('Multiple insight columns; each column has a pre-heading and its own card source (dynamic, manual, or custom)'),
			'render_callback'	=> 'jdpower_render_acf_block',
			'category'			=> 'jdpower',
			'keywords'			=> array( 'Posts', 'Insights', 'Blog', 'Featured', 'Cards', 'Grid', 'Columns' ),
		));

		// register Featured Industries block
		acf_register_block(array(
			'name'				=> 'jdpower-featured-industries',
			'title'				=> __('Featured Industries'),
			'description'		=> __('Industry cards from dynamic list or manual selection; heading and intro copy'),
			'render_callback'	=> 'jdpower_render_acf_block',
			'category'			=> 'jdpower',
			'keywords'			=> array( 'Industries', 'Featured', 'Cards', 'Grid' ),
		));

		// register Featured Pages block
		acf_register_block(array(
			'name'				=> 'jdpower-featured-pages',
			'title'				=> __('Featured Pages'),
			'description'		=> __('Featured page cards from manual selection or custom content; heading and intro copy'),
			'render_callback'	=> 'jdpower_render_acf_block',
			'category'			=> 'jdpower',
			'keywords'			=> array( 'Pages', 'Featured', 'Cards', 'Grid' ),
		));

		// register Featured Segments block
		acf_register_block(array(
			'name'				=> 'jdpower-featured-segments',
			'title'				=> __('Featured Segments'),
			'description'		=> __('Segment cards from manual selection, dynamic industry list, or custom content; heading and intro copy'),
			'render_callback'	=> 'jdpower_render_acf_block',
			'category'			=> 'jdpower',
			'keywords'			=> array( 'Segments', 'Featured', 'Cards', 'Grid', 'Industry' ),
		));

		// register Featured Solutions block
		acf_register_block(array(
			'name'				=> 'jdpower-featured-solutions',
			'title'				=> __('Featured Solutions'),
			'description'		=> __('Solution page cards with region filter; manual selection, by segment, or custom cards'),
			'render_callback'	=> 'jdpower_render_acf_block',
			'category'			=> 'jdpower',
			'keywords'			=> array( 'Solutions', 'Pages', 'Featured', 'Cards', 'Grid', 'Region' ),
		));

		// register Heading block
		acf_register_block(array(
			'name'				=> 'jdpower-heading',
			'title'				=> __('Heading'),
			'description'		=> __('Custom heading with alignment and max width'),
			'render_callback'	=> 'jdpower_render_acf_block',
			'category'			=> 'jdpower',
			'keywords'			=> array( 'Heading', 'Title' ),
		));

		// register Hero block
		acf_register_block(array(
			'name'				=> 'jdpower-hero',
			'title'				=> __('Hero'),
			'description'		=> __('Hero with image, headline and optional CTA'),
			'render_callback'	=> 'jdpower_render_acf_block',
			'category'			=> 'jdpower',
			'keywords'			=> array( 'Hero' ),
		));

		// register Icon Columns block
		acf_register_block(array(
			'name'				=> 'jdpower-icon-columns',
			'title'				=> __('Icon Columns'),
			'description'		=> __('Stacked or two-column layout; optional intro copy; icon rows with heading and copy'),
			'render_callback'	=> 'jdpower_render_acf_block',
			'category'			=> 'jdpower',
			'keywords'			=> array( 'Icons', 'Columns', 'Features' ),
		));

		// register Info Banner block
		acf_register_block(array(
			'name'				=> 'jdpower-info-banner',
			'title'				=> __('Info Banner'),
			'description'		=> __('Slim strip with icon, heading, copy.'),
			'render_callback'	=> 'jdpower_render_acf_block',
			'category'			=> 'jdpower',
			'keywords'			=> array( 'Banner', 'Notice', 'Alert', 'Info', 'Availability' ),
		));

		// register Key Features block
		acf_register_block(array(
			'name'				=> 'jdpower-key-features',
			'title'				=> __('Key Features'),
			'description'		=> __('Section pre-heading and a grid of features with image, heading, and copy'),
			'render_callback'	=> 'jdpower_render_acf_block',
			'category'			=> 'jdpower',
			'keywords'			=> array( 'Features', 'Columns', 'Image' ),
		));

		// register Leaders block
		acf_register_block(array(
			'name'				=> 'jdpower-leaders',
			'title'				=> __('Leaders'),
			'description'		=> __('Leader grid by type or manual sections; bio popup with optional file download'),
			'render_callback'	=> 'jdpower_render_acf_block',
			'category'			=> 'jdpower',
			'keywords'			=> array( 'Leaders', 'Team', 'People', 'Bio' ),
		));

		// register Podcast block (name matches migrated acf/podcast + field keys m13_*).
		acf_register_block(array(
			'name'				=> 'podcast',
			'title'				=> __('Podcast'),
			'description'		=> __('Embed a podcast player (e.g. Spotify iframe) with optional caption'),
			'render_callback'	=> 'jdpower_render_acf_block',
			'category'			=> 'jdpower',
			'keywords'			=> array( 'Podcast', 'Audio', 'Spotify', 'Embed' ),
		));

		// register Product Breadcrumbs block
		acf_register_block(array(
			'name'				=> 'jdpower-product-breadcrumbs',
			'title'				=> __('Product Breadcrumbs'),
			'description'		=> __('Industry / Segment / Solution breadcrumb trail for products'),
			'render_callback'	=> 'jdpower_render_acf_block',
			'category'			=> 'jdpower',
			'keywords'			=> array( 'Product', 'Breadcrumb', 'Industry' ),
		));

		// register Related Products block
		acf_register_block(array(
			'name'				=> 'jdpower-related-products',
			'title'				=> __('Related Products'),
			'description'		=> __('Product cards from industry taxonomy or manual entries; full card links'),
			'render_callback'	=> 'jdpower_render_acf_block',
			'category'			=> 'jdpower',
			'keywords'			=> array( 'Products', 'Related', 'Grid' ),
		));

		// register Stats block
		acf_register_block(array(
			'name'				=> 'jdpower-stats',
			'title'				=> __('Stats'),
			'description'		=> __('Stats grid with optional heading, copy, and layout options'),
			'render_callback'	=> 'jdpower_render_acf_block',
			'category'			=> 'jdpower',
			'keywords'			=> array( 'Stats', 'Figures' ),
		));

		// register Sub Brand Logos block
		acf_register_block(array(
			'name'				=> 'jdpower-sub-brand-logos',
			'title'				=> __('Sub Brand Logos'),
			'description'		=> __('Pre-heading, heading, copy, and a configurable logo grid'),
			'render_callback'	=> 'jdpower_render_acf_block',
			'category'			=> 'jdpower',
			'keywords'			=> array( 'Logos', 'Brands', 'Sub Brand', 'Grid' ),
		));

		// register Values block
		acf_register_block(array(
			'name'				=> 'jdpower-values',
			'title'				=> __('Values'),
			'description'		=> __('Pre-heading, large heading, and a grid of value cards'),
			'render_callback'	=> 'jdpower_render_acf_block',
			'category'			=> 'jdpower',
			'keywords'			=> array( 'Values', 'Cards', 'Grid', 'Culture' ),
		));

		// register Logins block
		acf_register_block(array(
			'name'				=> 'jdpower-logins',
			'title'				=> __('Logins'),
			'description'		=> __('Region-grouped login cards with search and region filter'),
			'render_callback'	=> 'jdpower_render_acf_block',
			'category'			=> 'jdpower',
			'keywords'			=> array( 'Logins', 'Region', 'Search' ),
		));

		// register Media Contacts block
		acf_register_block(array(
			'name'				=> 'jdpower-media-contacts',
			'title'				=> __('Media Contacts'),
			'description'		=> __('Media contact cards with photo, phone, and email from the Media Contacts library'),
			'render_callback'	=> 'jdpower_render_acf_block',
			'category'			=> 'jdpower',
			'keywords'			=> array( 'Media', 'Contacts', 'Press', 'Phone', 'Email' ),
		));
	}
}

/**
 * Callbacks for Custom Gutenburg blocks using ACF
 *
 * Must match ACF’s render_callback arguments: ( $block, $content, $is_preview, $post_id, $wp_block, $context ).
 * The old two-parameter signature put inner HTML in the $is_preview slot.
 */
function jdpower_render_acf_block( $block, $content = '', $is_preview = false, $post_id = 0, $wp_block = null, $context = null ) {
	// convert name ("acf/intro") into path friendly slug ("intro")
	$slug = str_replace( 'acf/', '', $block['name'] );

	// Post that owns this block (ACF passes this; $GLOBALS post during render can disagree with get_the_ID() in the template).
	$jdpower_acf_block_host_post_id = (int) $post_id;
	if ( $jdpower_acf_block_host_post_id <= 0 ) {
		$jdpower_acf_block_host_post_id = (int) get_the_ID();
	}
	if ( $jdpower_acf_block_host_post_id <= 0 && is_singular() ) {
		$jdpower_acf_block_host_post_id = (int) get_queried_object_id();
	}
	if ( $jdpower_acf_block_host_post_id <= 0 && is_object( $wp_block ) && ! empty( $wp_block->context['postId'] ) ) {
		$jdpower_acf_block_host_post_id = (int) $wp_block->context['postId'];
	}

	$path = get_theme_file_path( "/template-parts/blocks/content-{$slug}.php" );
	if ( file_exists( $path ) ) {
		include $path;
	}
}

/**
 * Tags allowed in Podcast block embed HTML (wp_kses context `podcast_embed`).
 * Includes script/iframe so provider snippets survive output; only use trusted embed code in the field.
 *
 * @param array<string, array<string, bool>> $tags    Default tags from wp_kses_allowed_html().
 * @param string                             $context KSES context string.
 * @return array<string, array<string, bool>>
 */
function jdpower_podcast_embed_kses_allowed( $tags, $context ) {
	if ( 'podcast_embed' !== $context ) {
		return $tags;
	}

	$allowed = array(
		'iframe'   => array(
			'src'             => true,
			'width'           => true,
			'height'          => true,
			'style'           => true,
			'frameborder'     => true,
			'allowfullscreen' => true,
			'allow'           => true,
			'loading'         => true,
			'title'           => true,
			'class'           => true,
			'id'              => true,
			'data-testid'     => true,
			'referrerpolicy'  => true,
			'sandbox'         => true,
			'scrolling'       => true,
		),
		'script'   => array(
			'src'         => true,
			'async'       => true,
			'defer'       => true,
			'type'        => true,
			'charset'     => true,
			'id'          => true,
			'class'       => true,
			'crossorigin' => true,
			'integrity'   => true,
			'nomodule'    => true,
			'nonce'       => true,
		),
		'noscript' => array(),
		'div'      => array(
			'id'          => true,
			'class'       => true,
			'style'       => true,
			'role'        => true,
			'tabindex'    => true,
			'aria-label'  => true,
			'aria-hidden' => true,
			'data-testid' => true,
			'data-url'    => true,
			'data-src'    => true,
			'data-id'     => true,
			'data-theme'  => true,
		),
		'span'     => array(
			'id'    => true,
			'class' => true,
			'style' => true,
		),
		'p'        => array(
			'class' => true,
			'style' => true,
		),
		// Tableau (and similar) embed snippets use object + param inside a placeholder div.
		'object'   => array(
			'class'  => true,
			'id'     => true,
			'style'  => true,
			'width'  => true,
			'height' => true,
			'type'   => true,
			'data'   => true,
		),
		'param'    => array(
			'name'  => true,
			'value' => true,
		),
	);

	return apply_filters( 'jdpower_podcast_embed_allowed_html', $allowed, $context );
}

add_filter( 'wp_kses_allowed_html', 'jdpower_podcast_embed_kses_allowed', 10, 2 );

/**
 * Output ACF-injected scripts in head, body, footer
 */
function jdpower_output_acf_script( $field_name ) {
	if ( ! function_exists( 'get_field' ) ) {
		return;
	}

	$scripts = get_field( $field_name, 'option' );

	if ( ! empty( trim( $scripts ) ) ) {
		echo "\n" . $scripts . "\n";
	}
}
add_action( 'wp_head', function() {
	jdpower_output_acf_script( 'head_scripts' );
}, 999 );

add_action( 'wp_body_open', function() {
	jdpower_output_acf_script( 'body_open_scripts' );
}, 5 );

add_action( 'wp_footer', function() {
	jdpower_output_acf_script( 'footer_scripts' );
}, 999 );


/**
 * ACF Local JSON
 */
add_filter('acf/settings/save_json', function ($path) {
  	return get_template_directory() . '/acf-json';
});
add_filter('acf/settings/load_json', function ($paths) {
	$paths[] = get_template_directory() . '/acf-json';
	return $paths;
});

/**
 * Featured Insights block: Content Type checkbox — choices match Insight Center (`jdpower_post_filters_insight_post_type_slugs`).
 *
 * @param array<string, mixed> $field ACF field.
 * @return array<string, mixed>
 */
function jdpower_featured_insights_load_post_type_field( $field ) {
	if ( empty( $field['key'] ) || ! in_array( $field['key'], array( 'field_70fc0bb1b0121', 'field_71f0cc03a012', 'field_mm_featured_insights_post_types' ), true ) ) {
		return $field;
	}
	if ( ! function_exists( 'jdpower_post_filters_insight_post_type_slugs' ) ) {
		return $field;
	}
	$choices = array();
	foreach ( jdpower_post_filters_insight_post_type_slugs() as $slug ) {
		$pto = get_post_type_object( $slug );
		$choices[ $slug ] = ( $pto && ! empty( $pto->labels->singular_name ) )
			? $pto->labels->singular_name
			: $slug;
	}
	$field['choices'] = $choices;
	return $field;
}
add_filter( 'acf/load_field/key=field_70fc0bb1b0121', 'jdpower_featured_insights_load_post_type_field' );
add_filter( 'acf/load_field/key=field_71f0cc03a012', 'jdpower_featured_insights_load_post_type_field' );
add_filter( 'acf/load_field/key=field_mm_featured_insights_post_types', 'jdpower_featured_insights_load_post_type_field' );

/**
 * Transparent nav: first top-level block is Hero and Transparent Nav is on (no Group/Column unwrapping).
 *
 * @param string[] $classes Body classes.
 * @return string[]
 */
function jdpower_body_class_hero_transparent_nav( $classes ) {
	if ( ! is_singular() || post_password_required() ) {
		return $classes;
	}

	$post = get_post();
	if ( ! $post || empty( $post->post_content ) ) {
		return $classes;
	}

	if ( false === strpos( $post->post_content, 'acf/jdpower-hero' ) ) {
		return $classes;
	}

	$blocks = parse_blocks( $post->post_content );

	$first_block = null;
	foreach ( $blocks as $block ) {
		if ( ! empty( trim( (string) ( $block['blockName'] ?? '' ) ) ) ) {
			$first_block = $block;
			break;
		}
	}

	if ( $first_block && 'acf/jdpower-hero' === ( $first_block['blockName'] ?? '' ) ) {
		$data           = $first_block['attrs']['data'] ?? array();
		$is_transparent = ! empty( $data['hero_transparent_nav'] ) || ! empty( $data['field_69e8b2a09'] );
		if ( $is_transparent ) {
			$classes[] = 'has-transparent-nav';
		}
	}

	return $classes;
}
add_filter( 'body_class', 'jdpower_body_class_hero_transparent_nav' );


