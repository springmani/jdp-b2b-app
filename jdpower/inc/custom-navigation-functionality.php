<?php
/**
 * Custom Navigation Functionality
 * 
 * ACF fields and custom walker for mega menu functionality
 */

if ( ! function_exists( 'customnavfunctionality_register_mega_menu_fields' ) ) {
	/**
	 * Register ACF fields for mega menu functionality
	 */
	function customnavfunctionality_register_mega_menu_fields() {
		if ( function_exists( 'acf_add_local_field_group' ) ) {
			// Key must stay unique vs any older "Mega Menu" group saved in the DB; if keys match,
			// ACF uses the database copy and ignores new PHP fields.
			acf_add_local_field_group(
				array(
					'key'                   => 'group_jdpower_mega_menu',
					'title'                 => 'Mega Menu Settings',
					'fields'                => array(
						// Content Type Field (For all menu items)
						array(
							'key'               => 'field_content_type',
							'label'             => 'Content Type',
							'name'              => 'content_type',
							'type'              => 'select',
							'instructions'      => 'Choose how this item behaves. Extra notes for the selected type appear below.',
							'required'          => 0,
							'conditional_logic' => 0,
							'wrapper'           => array(
								'width' => '',
								'class' => '',
								'id'    => '',
							),
							'choices'           => array(
								'mega_menu'             => 'Mega Menu',
								'column'                => 'Column',
								'link'                  => 'Link (Default)',
								'link_with_description' => 'Link with description',
								'link_with_arrow'       => 'Link with arrow',
								'login_link'            => 'Login link',
								'button'                => 'Button',
								'search_icon'           => 'Search icon',
								'image'                 => 'Image',
								'pre_heading'           => 'Pre-heading',
								'heading'               => 'Heading',
								'content'               => 'Content',
								'html'                  => 'HTML',
								'featured_products'     => 'Featured products',
								'featured_insights'     => 'Featured insights',
							),
							'default_value'     => 'link',
							'allow_null'        => 0,
							'multiple'          => 0,
							'ui'                => 0,
							'return_format'     => 'value',
							'ajax'              => 0,
							'placeholder'       => '',
						),
						array(
							'key'               => 'field_mega_menu_hide_on_mobile',
							'label'             => 'Hide on mobile',
							'name'              => 'mega_menu_hide_on_mobile',
							'type'              => 'true_false',
							'instructions'      => 'Hide this row in the small-screen menu drawer (below the large breakpoint). Desktop wide mega panel and flyouts are unchanged.',
							'required'          => 0,
							'conditional_logic' => array(
								array(
									array(
										'field'    => 'field_content_type',
										'operator' => '!=',
										'value'    => 'mega_menu',
									),
								),
							),
							'wrapper'           => array(
								'width' => '',
								'class' => '',
								'id'    => '',
							),
							'default_value'     => 0,
							'ui'                => 1,
							'ui_on_text'        => '',
							'ui_off_text'       => '',
						),
						// Contextual help (no saved value) — types without their own settings fields
						array(
							'key'               => 'field_mm_note_mega_menu',
							'label'             => '',
							'name'              => '',
							'type'              => 'message',
							'message'           => 'This item is the mega menu trigger (wide panel). Choose <strong>Trigger style</strong> below (regular text link or button). Use <strong>Navigation Label</strong> for the trigger text. Add <strong>Column</strong> items as <em>direct children</em>, then nest links, headings, images, and other blocks inside each column—or add nested <strong>Column</strong> children inside a column to create sub-columns (e.g. 4/5 with three 4/12 columns inside).',
							'new_lines'         => 'wpautop',
							'esc_html'          => 0,
							'conditional_logic' => array(
								array(
									array(
										'field'    => 'field_content_type',
										'operator' => '==',
										'value'    => 'mega_menu',
									),
								),
							),
						),
						array(
							'key'               => 'field_mega_menu_trigger_style',
							'label'             => 'Trigger style',
							'name'              => 'mega_menu_trigger_style',
							'type'              => 'select',
							'instructions'      => 'How the top-level mega menu trigger appears in the navigation bar.',
							'required'          => 0,
							'conditional_logic' => array(
								array(
									array(
										'field'    => 'field_content_type',
										'operator' => '==',
										'value'    => 'mega_menu',
									),
								),
							),
							'wrapper'           => array(
								'width' => '',
								'class' => '',
								'id'    => '',
							),
							'choices'           => array(
								'regular' => 'Regular (link)',
								'button'  => 'Button',
							),
							'default_value'     => 'regular',
							'allow_null'        => 0,
							'multiple'          => 0,
							'ui'                => 0,
							'return_format'     => 'value',
							'ajax'              => 0,
							'placeholder'       => '',
						),
						array(
							'key'               => 'field_mm_note_link',
							'label'             => '',
							'name'              => '',
							'type'              => 'message',
							'message'           => 'Standard menu link: the <strong>Navigation Label</strong> and <strong>URL</strong> on this screen control the text and destination. Use inside a column (or anywhere a normal link is appropriate).',
							'new_lines'         => 'wpautop',
							'esc_html'          => 0,
							'conditional_logic' => array(
								array(
									array(
										'field'    => 'field_content_type',
										'operator' => '==',
										'value'    => 'link',
									),
								),
							),
						),
						array(
							'key'               => 'field_mm_note_button',
							'label'             => '',
							'name'              => '',
							'type'              => 'message',
							'message'           => 'Styled call-to-action: use <strong>Navigation Label</strong> and <strong>URL</strong> as usual. Choose a <strong>Button style</strong> below. Best for top-level items or links inside a column; avoid nesting a full submenu under a button unless intentional.',
							'new_lines'         => 'wpautop',
							'esc_html'          => 0,
							'conditional_logic' => array(
								array(
									array(
										'field'    => 'field_content_type',
										'operator' => '==',
										'value'    => 'button',
									),
								),
							),
						),
						array(
							'key'               => 'field_mega_menu_button_style',
							'label'             => 'Button style',
							'name'              => 'mega_menu_button_style',
							'type'              => 'select',
							'instructions'      => '',
							'required'          => 0,
							'conditional_logic' => array(
								array(
									array(
										'field'    => 'field_content_type',
										'operator' => '==',
										'value'    => 'button',
									),
								),
								array(
									array(
										'field'    => 'field_content_type',
										'operator' => '==',
										'value'    => 'mega_menu',
									),
									array(
										'field'    => 'field_mega_menu_trigger_style',
										'operator' => '==',
										'value'    => 'button',
									),
								),
							),
							'wrapper'           => array(
								'width' => '',
								'class' => '',
								'id'    => '',
							),
							'choices'           => array(
								'primary'   => 'Primary',
								'secondary' => 'Secondary',
							),
							'default_value'     => 'primary',
							'allow_null'        => 0,
							'multiple'          => 0,
							'ui'                => 0,
							'return_format'     => 'value',
							'ajax'              => 0,
							'placeholder'       => '',
						),
						array(
							'key'               => 'field_mm_note_link_arrow',
							'label'             => '',
							'name'              => '',
							'type'              => 'message',
							'message'           => 'Same as <strong>Link (Default)</strong>, with a trailing arrow on the front end. Label and URL come from this menu item.',
							'new_lines'         => 'wpautop',
							'esc_html'          => 0,
							'conditional_logic' => array(
								array(
									array(
										'field'    => 'field_content_type',
										'operator' => '==',
										'value'    => 'link_with_arrow',
									),
								),
							),
						),
						array(
							'key'               => 'field_mm_note_login_link',
							'label'             => '',
							'name'              => '',
							'type'              => 'message',
							'message'           => 'Gray card link with right-aligned label and chevron—intended for login URLs in flyouts or mega menu columns. Label and URL come from this menu item. Pair with a <strong>Pre-heading</strong> above; set pre-heading alignment to <strong>Right</strong> to match.',
							'new_lines'         => 'wpautop',
							'esc_html'          => 0,
							'conditional_logic' => array(
								array(
									array(
										'field'    => 'field_content_type',
										'operator' => '==',
										'value'    => 'login_link',
									),
								),
							),
						),
						array(
							'key'               => 'field_mm_note_search_icon',
							'label'             => '',
							'name'              => '',
							'type'              => 'message',
							'message'           => 'Shows a <strong>search</strong> icon on desktop; below the large breakpoint the drawer uses an inline <strong>search field</strong> that submits to your search page with <code>?keyword=</code>. Set the <strong>URL</strong> to that page (or leave URL empty if a published page uses the Search template). Use the <strong>Navigation Label</strong> for the field label / accessible name (e.g. “Search”); if you leave the label empty, “Search” is used.',
							'new_lines'         => 'wpautop',
							'esc_html'          => 0,
							'conditional_logic' => array(
								array(
									array(
										'field'    => 'field_content_type',
										'operator' => '==',
										'value'    => 'search_icon',
									),
								),
							),
						),
						// Column Size Field (Conditional on content_type == 'column')
						array(
							'key'               => 'field_column_size',
							'label'             => 'Column Size',
							'name'              => 'column_size',
							'type'              => 'select',
							'instructions'      => 'How wide this column is in its row. <strong>Twelfths</strong> (1/12–12/12) and <strong>fifths</strong> (20%–80%) can be mixed across nesting levels (e.g. 1/5 + 4/5 top row, then three 4/12 columns inside the 4/5). Within a single row, use only twelfths or only fifths—do not mix both in that row.',
							'required'          => 0,
							'conditional_logic' => array(
								array(
									array(
										'field'    => 'field_content_type',
										'operator' => '==',
										'value'    => 'column',
									),
								),
							),
							'wrapper'           => array(
								'width' => '',
								'class' => '',
								'id'    => '',
							),
							'choices'           => array(
								'1'   => '1/12 — 8.33%',
								'2'   => '2/12 — 16.67%',
								'3'   => '3/12 — 25%',
								'4'   => '4/12 — 33.33%',
								'5'   => '5/12 — 41.67%',
								'6'   => '6/12 — 50%',
								'7'   => '7/12 — 58.33%',
								'8'   => '8/12 — 66.67%',
								'9'   => '9/12 — 75%',
								'10'  => '10/12 — 83.33%',
								'11'  => '11/12 — 91.67%',
								'12'  => '12/12 — 100%',
								'1-5' => '1/5 — 20%',
								'2-5' => '2/5 — 40%',
								'3-5' => '3/5 — 60%',
								'4-5' => '4/5 — 80%',
							),
							'default_value'     => '12', // Full width
							'allow_null'        => 0,
							'multiple'          => 0,
							'ui'                => 0,
							'return_format'     => 'value',
							'ajax'              => 0,
							'placeholder'       => '',
						),
						array(
							'key'               => 'field_column_include_border',
							'label'             => 'Include border',
							'name'              => 'column_include_border',
							'type'              => 'true_false',
							'instructions'      => 'Adds a subtle vertical line along the left edge of this column’s link/content block (use between columns for separation).',
							'required'          => 0,
							'conditional_logic' => array(
								array(
									array(
										'field'    => 'field_content_type',
										'operator' => '==',
										'value'    => 'column',
									),
								),
							),
							'wrapper'           => array(
								'width' => '',
								'class' => '',
								'id'    => '',
							),
							'default_value'     => 0,
							'ui'                => 1,
							'ui_on_text'        => '',
							'ui_off_text'       => '',
						),
						// Image Content Type Fields
						array(
							'key'               => 'field_mega_menu_image',
							'label'             => 'Image',
							'name'              => 'mega_menu_image',
							'type'              => 'image',
							'instructions'      => 'Shown inside the column; scales to the column width. Use this menu item’s URL (and link target, if shown under Screen Options) to make the image clickable—leave the URL empty if the image should not be a link.',
							'required'          => 0,
							'conditional_logic' => array(
								array(
									array(
										'field'    => 'field_content_type',
										'operator' => '==',
										'value'    => 'image',
									),
								),
							),
							'wrapper'           => array(
								'width' => '',
								'class' => '',
								'id'    => '',
							),
							'return_format'     => 'array',
							'preview_size'      => 'medium',
							'library'           => 'all',
							'min_width'         => '',
							'min_height'        => '',
							'min_size'          => '',
							'max_width'         => '',
							'max_height'        => '',
							'max_size'          => '',
							'mime_types'        => '',
						),
						array(
							'key'               => 'field_mega_menu_image_mobile_heading_overlay',
							'label'             => 'Mobile Heading Overlay',
							'name'              => 'mega_menu_image_mobile_heading_overlay',
							'type'              => 'text',
							'instructions'      => 'Optional heading shown over the image in the mobile menu drawer only (below the large breakpoint). Leave empty to show the image without an overlay.',
							'required'          => 0,
							'conditional_logic' => array(
								array(
									array(
										'field'    => 'field_content_type',
										'operator' => '==',
										'value'    => 'image',
									),
								),
							),
							'wrapper'           => array(
								'width' => '',
								'class' => '',
								'id'    => '',
							),
							'default_value'     => '',
							'placeholder'       => '',
							'prepend'           => '',
							'append'            => '',
							'maxlength'         => '',
						),
						// Pre-heading: text from Navigation Label; optional link
						array(
							'key'               => 'field_mm_note_pre_heading',
							'label'             => '',
							'name'              => '',
							'type'              => 'message',
							'message'           => 'Copy normally comes from the <strong>Navigation Label</strong>. WordPress needs a label to save the item—use a short placeholder (e.g. “—” or “Spacer”) if you only want layout space, then turn on <strong>Hide label on front end</strong> below so that text is not shown.',
							'new_lines'         => 'wpautop',
							'esc_html'          => 0,
							'conditional_logic' => array(
								array(
									array(
										'field'    => 'field_content_type',
										'operator' => '==',
										'value'    => 'pre_heading',
									),
								),
							),
						),
						array(
							'key'               => 'field_mega_menu_pre_heading_hide_label',
							'label'             => 'Hide label on front end',
							'name'              => 'mega_menu_pre_heading_hide_label',
							'type'              => 'true_false',
							'instructions'      => 'Outputs only the spacing row (no visible text or link). Use when WordPress requires a navigation label but you want an empty slot for column alignment.',
							'required'          => 0,
							'conditional_logic' => array(
								array(
									array(
										'field'    => 'field_content_type',
										'operator' => '==',
										'value'    => 'pre_heading',
									),
								),
							),
							'wrapper'           => array(
								'width' => '',
								'class' => '',
								'id'    => '',
							),
							'default_value'     => 0,
							'ui'                => 1,
							'ui_on_text'        => '',
							'ui_off_text'       => '',
						),
						array(
							'key'               => 'field_mega_menu_pre_heading_is_link',
							'label'             => 'Link pre-heading',
							'name'              => 'mega_menu_pre_heading_is_link',
							'type'              => 'true_false',
							'instructions'      => 'When on, the label links to this item’s URL (target / title from Screen Options apply). Needs visible label text and a URL.',
							'required'          => 0,
							'conditional_logic' => array(
								array(
									array(
										'field'    => 'field_content_type',
										'operator' => '==',
										'value'    => 'pre_heading',
									),
									array(
										'field'    => 'field_mega_menu_pre_heading_hide_label',
										'operator' => '!=',
										'value'    => '1',
									),
								),
							),
							'wrapper'           => array(
								'width' => '',
								'class' => '',
								'id'    => '',
							),
							'default_value'     => 0,
							'ui'                => 1,
							'ui_on_text'        => '',
							'ui_off_text'       => '',
						),
						array(
							'key'               => 'field_mega_menu_pre_heading_alignment',
							'label'             => 'Alignment',
							'name'              => 'mega_menu_pre_heading_alignment',
							'type'              => 'select',
							'instructions'      => 'Horizontal alignment for this pre-heading label.',
							'required'          => 0,
							'conditional_logic' => array(
								array(
									array(
										'field'    => 'field_content_type',
										'operator' => '==',
										'value'    => 'pre_heading',
									),
								),
							),
							'wrapper'           => array(
								'width' => '',
								'class' => '',
								'id'    => '',
							),
							'choices'           => array(
								'left'  => 'Left',
								'right' => 'Right',
							),
							'default_value'     => 'left',
							'allow_null'        => 0,
							'multiple'          => 0,
							'ui'                => 0,
							'return_format'     => 'value',
							'ajax'              => 0,
							'placeholder'       => '',
						),
						// Link with description — optional body under the link
						array(
							'key'               => 'field_mega_menu_link_description',
							'label'             => 'Description',
							'name'              => 'mega_menu_link_description',
							'type'              => 'textarea',
							'instructions'      => 'Optional. Shown under the link title. The link itself still uses this item’s <strong>Navigation Label</strong> and <strong>URL</strong>.',
							'required'          => 0,
							'conditional_logic' => array(
								array(
									array(
										'field'    => 'field_content_type',
										'operator' => '==',
										'value'    => 'link_with_description',
									),
								),
							),
							'wrapper'           => array(
								'width' => '',
								'class' => '',
								'id'    => '',
							),
							'default_value'     => '',
							'placeholder'       => '',
							'maxlength'         => '',
							'rows'              => 3,
							'new_lines'         => '',
						),
						// Heading: title from Navigation Label; optional link
						array(
							'key'               => 'field_mm_note_heading',
							'label'             => '',
							'name'              => '',
							'type'              => 'message',
							'message'           => 'Title comes from this menu item’s <strong>Navigation Label</strong> (section heading inside the column, larger than pre-heading). Use <strong>Link heading</strong> below and set a URL to make it a link.',
							'new_lines'         => 'wpautop',
							'esc_html'          => 0,
							'conditional_logic' => array(
								array(
									array(
										'field'    => 'field_content_type',
										'operator' => '==',
										'value'    => 'heading',
									),
								),
							),
						),
						array(
							'key'               => 'field_mega_menu_heading_is_link',
							'label'             => 'Link heading',
							'name'              => 'mega_menu_heading_is_link',
							'type'              => 'true_false',
							'instructions'      => 'When on, the label links to this item’s URL (target / title from Screen Options apply). If the URL is empty, the heading stays plain text.',
							'required'          => 0,
							'conditional_logic' => array(
								array(
									array(
										'field'    => 'field_content_type',
										'operator' => '==',
										'value'    => 'heading',
									),
								),
							),
							'wrapper'           => array(
								'width' => '',
								'class' => '',
								'id'    => '',
							),
							'default_value'     => 0,
							'ui'                => 1,
							'ui_on_text'        => '',
							'ui_off_text'       => '',
						),
						// Content Content Type Field
						array(
							'key'               => 'field_mega_menu_content',
							'label'             => 'Content',
							'name'              => 'mega_menu_content',
							'type'              => 'wysiwyg',
							'instructions'      => 'Rich text block inside the column (paragraphs, lists, basic formatting). Good for short blurbs or calls to action.',
							'required'          => 0,
							'conditional_logic' => array(
								array(
									array(
										'field'    => 'field_content_type',
										'operator' => '==',
										'value'    => 'content',
									),
								),
							),
							'wrapper'           => array(
								'width' => '',
								'class' => '',
								'id'    => '',
							),
							'default_value'     => '',
							'tabs'              => 'all',
							'toolbar'           => 'full',
							'media_upload'      => 1,
							'delay'             => 0,
						),
						// HTML Content Type Field
						array(
							'key'               => 'field_mega_menu_html',
							'label'             => 'HTML Code',
							'name'              => 'mega_menu_html',
							'type'              => 'textarea',
							'instructions'      => 'Raw HTML output inside the column. Use only when the editor types above are not enough; incorrect markup can break layout or accessibility.',
							'required'          => 0,
							'conditional_logic'    => array(
								array(
									array(
										'field'    => 'field_content_type',
										'operator' => '==',
										'value'    => 'html',
									),
								),
							),
							'wrapper'           => array(
								'width' => '',
								'class' => '',
								'id'    => '',
							),
							'default_value'     => '',
							'placeholder'       => '',
							'maxlength'         => '',
							'rows'              => 4,
							'new_lines'         => '',
						),
						array(
							'key'               => 'field_mm_note_featured_products',
							'label'             => '',
							'name'              => '',
							'type'              => 'message',
							'message'           => 'Place this item inside a <strong>Column</strong>. <strong>Navigation Label</strong> and <strong>URL</strong> are not used on the front end. Choose products below; each card uses the product featured image, title (or Card Details heading override), and short description. The whole card links to the product.',
							'new_lines'         => 'wpautop',
							'esc_html'          => 0,
							'conditional_logic' => array(
								array(
									array(
										'field'    => 'field_content_type',
										'operator' => '==',
										'value'    => 'featured_products',
									),
								),
							),
						),
						array(
							'key'               => 'field_mega_menu_featured_products',
							'label'             => 'Featured products',
							'name'              => 'mega_menu_featured_products',
							'type'              => 'relationship',
							'instructions'      => 'Select products in display order.',
							'required'          => 0,
							'conditional_logic' => array(
								array(
									array(
										'field'    => 'field_content_type',
										'operator' => '==',
										'value'    => 'featured_products',
									),
								),
							),
							'wrapper'           => array(
								'width' => '',
								'class' => '',
								'id'    => '',
							),
							'post_type'         => array(
								'product',
							),
							'taxonomy'          => array(),
							'filters'           => array(
								'search',
							),
							'elements'          => array(
								'featured_image',
							),
							'min'               => 0,
							'max'               => 12,
							'return_format'     => 'id',
						),
						array(
							'key'               => 'field_mega_menu_featured_products_columns',
							'label'             => 'Columns per row',
							'name'              => 'mega_menu_featured_products_columns',
							'type'              => 'select',
							'instructions'      => 'Number of product cards per row in the grid.',
							'required'          => 0,
							'conditional_logic' => array(
								array(
									array(
										'field'    => 'field_content_type',
										'operator' => '==',
										'value'    => 'featured_products',
									),
								),
							),
							'wrapper'           => array(
								'width' => '',
								'class' => '',
								'id'    => '',
							),
							'choices'           => array(
								'1' => '1 column',
								'2' => '2 columns',
								'3' => '3 columns',
								'4' => '4 columns',
							),
							'default_value'     => '3',
							'allow_null'        => 0,
							'multiple'          => 0,
							'ui'                => 0,
							'return_format'     => 'value',
							'ajax'              => 0,
							'placeholder'       => '',
						),
						array(
							'key'               => 'field_mm_note_featured_insights',
							'label'             => '',
							'name'              => '',
							'type'              => 'message',
							'message'           => 'Place this item inside a <strong>Column</strong>. <strong>Navigation Label</strong> and <strong>URL</strong> are not used on the front end. <strong>Dynamic</strong> lists published items marked <strong>Featured</strong> in the editor from the post types you select (newest first). <strong>Manual</strong> uses your hand-picked posts in display order.',
							'new_lines'         => 'wpautop',
							'esc_html'          => 0,
							'conditional_logic' => array(
								array(
									array(
										'field'    => 'field_content_type',
										'operator' => '==',
										'value'    => 'featured_insights',
									),
								),
							),
						),
						array(
							'key'               => 'field_mega_menu_featured_insights_source',
							'label'             => 'Source',
							'name'              => 'mega_menu_featured_insights_source',
							'type'              => 'select',
							'instructions'      => '',
							'required'          => 0,
							'conditional_logic' => array(
								array(
									array(
										'field'    => 'field_content_type',
										'operator' => '==',
										'value'    => 'featured_insights',
									),
								),
							),
							'wrapper'           => array(
								'width' => '',
								'class' => '',
								'id'    => '',
							),
							'choices'           => array(
								'dynamic' => 'Dynamic',
								'manual'  => 'Manual',
							),
							'default_value'     => 'dynamic',
							'allow_null'        => 0,
							'multiple'          => 0,
							'ui'                => 0,
							'return_format'     => 'value',
							'ajax'              => 0,
							'placeholder'       => '',
						),
						array(
							'key'               => 'field_mm_featured_insights_post_types',
							'label'             => 'Post types',
							'name'              => 'mega_menu_featured_insights_post_types',
							'type'              => 'checkbox',
							'instructions'      => 'Include content from these types (Dynamic only). Featured items appear first; remaining slots use the latest posts.',
							'required'          => 0,
							'conditional_logic' => array(
								array(
									array(
										'field'    => 'field_content_type',
										'operator' => '==',
										'value'    => 'featured_insights',
									),
									array(
										'field'    => 'field_mega_menu_featured_insights_source',
										'operator' => '==',
										'value'    => 'dynamic',
									),
								),
							),
							'wrapper'           => array(
								'width' => '',
								'class' => '',
								'id'    => '',
							),
							'choices'           => array(),
							'allow_custom'      => 0,
							'default_value'     => '',
							'layout'            => 'vertical',
							'toggle'            => 0,
							'return_format'     => 'value',
						),
						array(
							'key'               => 'field_mega_menu_featured_insights_max',
							'label'             => 'Maximum to show',
							'name'              => 'mega_menu_featured_insights_max',
							'type'              => 'number',
							'instructions'      => 'Maximum cards when Source is Dynamic (1–12).',
							'required'          => 0,
							'conditional_logic' => array(
								array(
									array(
										'field'    => 'field_content_type',
										'operator' => '==',
										'value'    => 'featured_insights',
									),
									array(
										'field'    => 'field_mega_menu_featured_insights_source',
										'operator' => '==',
										'value'    => 'dynamic',
									),
								),
							),
							'wrapper'           => array(
								'width' => '',
								'class' => '',
								'id'    => '',
							),
							'default_value'     => 3,
							'min'               => 1,
							'max'               => 12,
							'step'              => 1,
							'placeholder'       => '',
							'prepend'           => '',
							'append'            => '',
						),
						array(
							'key'               => 'field_mega_menu_featured_insights_posts',
							'label'             => 'Featured insights',
							'name'              => 'mega_menu_featured_insights_posts',
							'type'              => 'relationship',
							'instructions'      => 'Select insights in display order (Manual only).',
							'required'          => 0,
							'conditional_logic' => array(
								array(
									array(
										'field'    => 'field_content_type',
										'operator' => '==',
										'value'    => 'featured_insights',
									),
									array(
										'field'    => 'field_mega_menu_featured_insights_source',
										'operator' => '==',
										'value'    => 'manual',
									),
								),
							),
							'wrapper'           => array(
								'width' => '',
								'class' => '',
								'id'    => '',
							),
							'post_type'         => array(
								'post',
								'press_release',
								'podcast',
								'event_webinar',
								'resource',
							),
							'taxonomy'          => array(),
							'filters'           => array(
								'search',
								'post_type',
							),
							'elements'          => array(
								'featured_image',
							),
							'min'               => 0,
							'max'               => 12,
							'return_format'     => 'id',
						),
						array(
							'key'               => 'field_mega_menu_featured_insights_columns',
							'label'             => 'Columns per row',
							'name'              => 'mega_menu_featured_insights_columns',
							'type'              => 'select',
							'instructions'      => 'Number of insight cards per row in the grid.',
							'required'          => 0,
							'conditional_logic' => array(
								array(
									array(
										'field'    => 'field_content_type',
										'operator' => '==',
										'value'    => 'featured_insights',
									),
								),
							),
							'wrapper'           => array(
								'width' => '',
								'class' => '',
								'id'    => '',
							),
							'choices'           => array(
								'1' => '1 column',
								'2' => '2 columns',
								'3' => '3 columns',
								'4' => '4 columns',
							),
							'default_value'     => '3',
							'allow_null'        => 0,
							'multiple'          => 0,
							'ui'                => 0,
							'return_format'     => 'value',
							'ajax'              => 0,
							'placeholder'       => '',
						),
					),
					'location'              => array(
						array(
							array(
								'param'    => 'nav_menu_item',
								'operator' => '==',
								'value'    => 'all',
							),
						),
					),
					'menu_order'            => 0,
					'position'              => 'normal',
					'style'                 => 'default',
					'label_placement'       => 'top',
					'instruction_placement' => 'label',
					'hide_on_screen'        => '',
					'active'                => true,
					'description'           => '<p><strong>Mega menu basics:</strong> For a wide panel, set a <strong>top-level</strong> item to <strong>Mega Menu</strong>, then add <strong>Column</strong> children, then nest your links and blocks inside each column. For a normal flyout submenu, keep the parent as <strong>Link (Default)</strong>.</p>',
				)
			);
		}
	}
	add_action( 'acf/init', 'customnavfunctionality_register_mega_menu_fields', 5 );
}

/**
 * Custom Walker for Mega Menu
 */
class CustomNavFunctionality_Mega_Menu_Walker extends Walker_Nav_Menu {

	/**
	 * Store parent items for mega menu detection
	 *
	 * @var array
	 */
	private $parent_items = array();

	/**
	 * Current parent item ID being processed
	 *
	 * @var int
	 */
	private $current_parent_id = 0;

	/**
	 * Whether depth-0 mega submenu opened the fluid + container wrappers (for end_lvl).
	 *
	 * @var bool
	 */
	private $mega_dropdown_fluid_open = false;

	/**
	 * Bootstrap / custom grid class for a column_size ACF value.
	 *
	 * @param string $column_size Field value.
	 * @return string Class name or empty.
	 */
	private function get_column_grid_class( $column_size ) {
		if ( ! $column_size ) {
			return '';
		}
		$fifths = array( '1-5', '2-5', '3-5', '4-5' );
		if ( in_array( $column_size, $fifths, true ) ) {
			return 'col-md-' . $column_size;
		}
		if ( preg_match( '/^(1[0-2]|[1-9])$/', (string) $column_size ) ) {
			return 'col-md-' . $column_size;
		}
		return '';
	}

	/**
	 * Append mega menu column grid classes to a menu item <li>.
	 *
	 * @param array  $classes Class list (by reference).
	 * @param object $item    Menu item data object.
	 * @param bool   $nested  Whether this column sits inside another column.
	 */
	private function append_mega_menu_column_classes( array &$classes, $item, $nested = false ) {
		$column_size = $this->get_item_meta( $item->ID, 'column_size' );
		$grid_class  = $this->get_column_grid_class( $column_size );
		if ( $grid_class ) {
			$classes[] = $grid_class;
		}
		$classes[] = 'mega-menu-column';
		if ( $nested ) {
			$classes[] = 'mega-menu-column--nested';
		}
	}

	/**
	 * Whether a menu item has direct children set to the Column content type.
	 *
	 * @param int $parent_item_id Nav menu item post ID.
	 * @return bool
	 */
	private function menu_item_has_column_children( $parent_item_id ) {
		static $cache = array();

		$parent_item_id = (int) $parent_item_id;
		if ( $parent_item_id <= 0 ) {
			return false;
		}
		if ( array_key_exists( $parent_item_id, $cache ) ) {
			return $cache[ $parent_item_id ];
		}

		$child_ids = get_posts(
			array(
				'post_type'      => 'nav_menu_item',
				'posts_per_page' => -1,
				'post_status'    => 'publish',
				'fields'         => 'ids',
				'meta_key'       => '_menu_item_menu_item_parent',
				'meta_value'     => $parent_item_id,
			)
		);

		foreach ( $child_ids as $child_id ) {
			if ( 'column' === $this->get_item_content_type_for_id( (int) $child_id ) ) {
				$cache[ $parent_item_id ] = true;
				return true;
			}
		}

		$cache[ $parent_item_id ] = false;
		return false;
	}

	/**
	 * UL classes for submenu lists opened under a Column menu item.
	 *
	 * @param int $parent_item_id Nav menu item post ID.
	 * @return string[] Class names.
	 */
	private function get_mega_menu_column_submenu_classes( $parent_item_id ) {
		$parent_item_id = (int) $parent_item_id;
		$ul_classes     = array( 'sub-menu', 'mega-menu-column-content' );

		if ( $this->menu_item_has_column_children( $parent_item_id ) ) {
			$ul_classes[] = 'mega-menu-nested-columns';
			$ul_classes[] = 'row';
		} elseif (
			filter_var( $this->get_item_meta( $parent_item_id, 'column_include_border' ), FILTER_VALIDATE_BOOLEAN )
		) {
			$ul_classes[] = 'mega-menu-column-content--border';
		}

		return $ul_classes;
	}

	/**
	 * Content type stored on a nav menu item (get_field + post_meta fallback).
	 *
	 * @param int $item_id Nav menu item post ID.
	 * @return string Empty if unset.
	 */
	private function get_item_content_type_for_id( $item_id ) {
		$item_id = (int) $item_id;
		if ( ! $item_id ) {
			return '';
		}
		if ( function_exists( 'get_field' ) ) {
			$v = get_field( 'content_type', $item_id );
			if ( $v !== false && $v !== null && $v !== '' ) {
				return is_string( $v ) ? trim( $v ) : $v;
			}
		}
		$meta = get_post_meta( $item_id, 'content_type', true );
		if ( $meta !== '' && $meta !== false && $meta !== null ) {
			return is_string( $meta ) ? trim( $meta ) : $meta;
		}
		return '';
	}

	/**
	 * @param object $item Menu item.
	 * @return string
	 */
	private function get_item_content_type( $item ) {
		$id = ( is_object( $item ) && isset( $item->ID ) ) ? (int) $item->ID : 0;
		return $this->get_item_content_type_for_id( $id );
	}

	/**
	 * Whether a content type is a top-level mega menu panel parent.
	 *
	 * @param string $content_type Content type value.
	 * @return bool
	 */
	private function is_mega_menu_parent_type( $content_type ) {
		return 'mega_menu' === $content_type;
	}

	/**
	 * Mega menu trigger style for a nav menu item (regular link or button).
	 *
	 * @param int $item_id Nav menu item post ID.
	 * @return string regular|button
	 */
	private function get_mega_menu_trigger_style_for_item( $item_id ) {
		$raw = $this->get_item_meta( $item_id, 'mega_menu_trigger_style' );
		$raw = is_string( $raw ) ? strtolower( trim( $raw ) ) : '';
		if ( 'button' === $raw ) {
			return 'button';
		}
		return 'regular';
	}

	/**
	 * Whether a mega menu top-level item uses a button-styled trigger.
	 *
	 * @param object $item Menu item data object.
	 * @return bool
	 */
	private function mega_menu_uses_button_trigger( $item ) {
		return 'mega_menu' === $this->get_item_content_type( $item )
			&& 'button' === $this->get_mega_menu_trigger_style_for_item( $item->ID );
	}

	/**
	 * Field value for a nav menu item (get_field + post_meta fallback).
	 *
	 * @param int    $item_id Nav menu item post ID.
	 * @param string $field_name ACF field name.
	 * @return mixed Empty string if unset (non-array fields).
	 */
	private function get_item_meta( $item_id, $field_name ) {
		$item_id = (int) $item_id;
		if ( ! $item_id ) {
			return '';
		}
		if ( function_exists( 'get_field' ) ) {
			$v = get_field( $field_name, $item_id );
			if ( $v !== false && $v !== null && $v !== '' ) {
				return $v;
			}
		}
		$meta = get_post_meta( $item_id, $field_name, true );
		if ( $meta === false || $meta === null ) {
			return '';
		}
		return $meta;
	}

	/**
	 * Mobile heading overlay text for an image menu item (trimmed).
	 *
	 * @param object $item Menu item data object.
	 * @return string
	 */
	private function get_item_image_mobile_heading_overlay( $item ) {
		return trim( (string) $this->get_item_meta( $item->ID, 'mega_menu_image_mobile_heading_overlay' ) );
	}

	/**
	 * Whether an image menu item has a mobile heading overlay.
	 *
	 * @param object $item Menu item data object.
	 * @return bool
	 */
	private function item_has_image_mobile_heading_overlay( $item ) {
		return '' !== $this->get_item_image_mobile_heading_overlay( $item );
	}

	/**
	 * Add mobile overlay class when an image item has overlay copy.
	 *
	 * @param string[] $classes CSS classes (passed by reference).
	 * @param object   $item    Menu item data object.
	 */
	private function append_image_mobile_overlay_class( array &$classes, $item ) {
		if ( 'image' === $this->get_item_content_type( $item ) && $this->item_has_image_mobile_heading_overlay( $item ) ) {
			$classes[] = 'mega-menu-image--has-mobile-overlay';
		}
	}

	/**
	 * Remove WordPress "current" menu classes (no active-state highlight in theme nav).
	 *
	 * @param string[] $classes CSS classes.
	 * @return string[]
	 */
	private function strip_nav_menu_current_css_classes( $classes ) {
		$strip = array(
			'current-menu-item',
			'current-menu-ancestor',
			'current-menu-parent',
			'current_page_item',
			'current_page_ancestor',
			'current_page_parent',
		);

		return array_values( array_diff( $classes, $strip ) );
	}

	/**
	 * Menu item title after the_title, stripped to plain text (trimmed).
	 *
	 * @param object $item Menu item data object.
	 * @return string
	 */
	private function get_item_menu_title_plain( $item ) {
		$raw = apply_filters( 'the_title', $item->title, $item->ID );
		return trim( wp_strip_all_tags( (string) $raw ) );
	}

	/**
	 * Whether the menu item has no display title (after filters).
	 *
	 * @param object $item Menu item data object.
	 * @return bool
	 */
	private function item_menu_title_is_empty( $item ) {
		return '' === $this->get_item_menu_title_plain( $item );
	}

	/**
	 * Escaped menu title for safe HTML output.
	 *
	 * @param object $item Menu item data object.
	 * @return string
	 */
	private function get_item_menu_title_escaped( $item ) {
		return esc_html( $this->get_item_menu_title_plain( $item ) );
	}

	/**
	 * Build HTML attribute string for a menu item link.
	 *
	 * @param object $item Menu item data object.
	 * @return string
	 */
	private function get_item_link_attributes_string( $item ) {
		$attributes  = ! empty( $item->attr_title ) ? ' title="' . esc_attr( $item->attr_title ) . '"' : '';
		$attributes .= ! empty( $item->target ) ? ' target="' . esc_attr( $item->target ) . '"' : '';
		$attributes .= ! empty( $item->xfn ) ? ' rel="' . esc_attr( $item->xfn ) . '"' : '';
		$attributes .= ! empty( $item->url ) ? ' href="' . esc_attr( $item->url ) . '"' : '';
		return $attributes;
	}

	/**
	 * Menu item output: before + anchor + after (same as Link Default).
	 *
	 * @param object $item       Menu item data object.
	 * @param string $attributes Attribute string for the opening <a> tag.
	 * @param object $args       wp_nav_menu() args object.
	 * @return string
	 */
	private function render_menu_item_anchor_block( $item, $attributes, $args ) {
		$output  = isset( $args->before ) ? $args->before : '';
		$output .= '<a' . $attributes . '>';
		$output .= ( isset( $args->link_before ) ? $args->link_before : '' ) . apply_filters( 'the_title', $item->title, $item->ID ) . ( isset( $args->link_after ) ? $args->link_after : '' );
		$output .= '</a>';
		$output .= isset( $args->after ) ? $args->after : '';
		return $output;
	}

	/**
	 * Same anchor as Link (Default) + optional description below.
	 *
	 * @param object $item       Menu item data object.
	 * @param string $attributes Attribute string for the opening <a> tag.
	 * @param object $args       wp_nav_menu() args object.
	 * @return string
	 */
	private function render_link_with_description_item( $item, $attributes, $args ) {
		$desc = $this->get_item_meta( $item->ID, 'mega_menu_link_description' );
		$out  = $this->render_menu_item_anchor_block( $item, $attributes, $args );
		if ( $desc ) {
			$out .= '<span class="mega-menu-link-description">' . nl2br( esc_html( $desc ) ) . '</span>';
		}
		return $out;
	}

	/**
	 * Same as Link (Default) with a trailing arrow (styled in CSS).
	 *
	 * @param object $item       Menu item data object.
	 * @param string $attributes Attribute string for the opening <a> tag.
	 * @param object $args       wp_nav_menu() args object.
	 * @return string
	 */
	private function render_link_with_arrow_item( $item, $attributes, $args ) {
		$output  = isset( $args->before ) ? $args->before : '';
		$output .= '<a class="mega-menu-link-with-arrow"' . $attributes . '>';
		$output .= ( isset( $args->link_before ) ? $args->link_before : '' ) . apply_filters( 'the_title', $item->title, $item->ID ) . ( isset( $args->link_after ) ? $args->link_after : '' );
		$output .= '</a>';
		$output .= isset( $args->after ) ? $args->after : '';
		return $output;
	}

	/**
	 * Login card link: gray background, right-aligned label with chevron.
	 *
	 * @param object $item       Menu item data object.
	 * @param string $attributes Attribute string for the opening <a> tag.
	 * @param object $args       wp_nav_menu() args object.
	 * @return string
	 */
	private function render_login_link_item( $item, $attributes, $args ) {
		$title   = ( isset( $args->link_before ) ? $args->link_before : '' ) . apply_filters( 'the_title', $item->title, $item->ID ) . ( isset( $args->link_after ) ? $args->link_after : '' );
		$output  = isset( $args->before ) ? $args->before : '';
		$output .= '<a class="mega-menu-login-link"' . $attributes . '>';
		$output .= '<span class="mega-menu-login-link__label">' . $title . '</span>';
		$output .= '<span class="mega-menu-login-link__chevron" aria-hidden="true"></span>';
		$output .= '</a>';
		$output .= isset( $args->after ) ? $args->after : '';
		return $output;
	}

	/**
	 * Pre-heading text alignment (left default).
	 *
	 * @param object $item Menu item data object.
	 * @return string left|right
	 */
	private function get_item_pre_heading_alignment( $item ) {
		$raw = $this->get_item_meta( $item->ID, 'mega_menu_pre_heading_alignment' );
		$raw = is_string( $raw ) ? strtolower( trim( $raw ) ) : '';
		return in_array( $raw, array( 'left', 'right' ), true ) ? $raw : 'left';
	}

	/**
	 * Pre-heading row used only for column spacing (label hidden on front end).
	 *
	 * @param object $item Menu item data object.
	 * @return bool
	 */
	private function item_pre_heading_hides_label( $item ) {
		return filter_var( $this->get_item_meta( $item->ID, 'mega_menu_pre_heading_hide_label' ), FILTER_VALIDATE_BOOLEAN );
	}

	/**
	 * Sanitized button style for nav menu items (ACF mega_menu_button_style).
	 *
	 * @param object $item Menu item data object.
	 * @return string primary|secondary
	 */
	private function get_item_button_style( $item ) {
		$raw = $this->get_item_meta( $item->ID, 'mega_menu_button_style' );
		$raw = is_string( $raw ) ? strtolower( trim( $raw ) ) : '';
		// Legacy "outline" saved in DB maps to primary (outline style removed).
		if ( 'outline' === $raw ) {
			$raw = 'primary';
		}
		$allowed = array( 'primary', 'secondary' );
		if ( in_array( $raw, $allowed, true ) ) {
			return $raw;
		}
		return 'primary';
	}

	/**
	 * Link styled as a theme button (.btn + variant classes).
	 *
	 * @param object $item Menu item data object.
	 * @param object $args wp_nav_menu() args object.
	 * @return string
	 */
	private function render_button_item( $item, $args ) {
		$style   = $this->get_item_button_style( $item );
		$classes = 'btn nav-menu-button nav-menu-button--' . $style;
		$attr = $this->get_item_link_attributes_string( $item );
		if ( '' !== $attr && ! str_starts_with( $attr, ' ' ) ) {
			$attr = ' ' . $attr;
		}
		$output  = isset( $args->before ) ? $args->before : '';
		$output .= '<a class="' . esc_attr( $classes ) . '"' . $attr . '>';
		$output .= ( isset( $args->link_before ) ? $args->link_before : '' ) . apply_filters( 'the_title', $item->title, $item->ID ) . ( isset( $args->link_after ) ? $args->link_after : '' );
		$output .= '</a>';
		$output .= isset( $args->after ) ? $args->after : '';
		return $output;
	}

	/**
	 * Search icon (ACF content type search_icon): desktop = icon link; mobile drawer = GET form (?keyword=) to the Search hub.
	 *
	 * @param object $item Menu item data object.
	 * @param object $args wp_nav_menu() args object.
	 * @return string
	 */
	private function render_search_icon_item( $item, $args ) {
		$label_plain = $this->get_item_menu_title_plain( $item );
		// Shorter on-screen / AT label when the menu still uses the old navigation label copy.
		if ( '' !== $label_plain && 0 === strcasecmp( 'Search Our Site', trim( $label_plain ) ) ) {
			$label_plain = '';
		}
		$label = '' !== $label_plain ? $label_plain : __( 'Search', 'jdpower' );

		$svg = $this->get_search_magnifier_svg_markup( 'nav-menu-search-icon__svg' );

		$search_url = '';
		if ( ! empty( $item->url ) ) {
			$search_url = $item->url;
		} elseif ( function_exists( 'jdpower_get_search_page_id' ) ) {
			$search_page_id = jdpower_get_search_page_id();
			if ( $search_page_id > 0 ) {
				$search_url = get_permalink( $search_page_id );
			}
		}

		$output = isset( $args->before ) ? $args->before : '';

		if ( '' !== $search_url ) {
			$link_attr  = ! empty( $item->attr_title ) ? ' title="' . esc_attr( $item->attr_title ) . '"' : '';
			$link_attr .= ! empty( $item->target ) ? ' target="' . esc_attr( $item->target ) . '"' : '';
			$link_attr .= ! empty( $item->xfn ) ? ' rel="' . esc_attr( $item->xfn ) . '"' : '';
			$link_attr .= ' href="' . esc_url( $search_url ) . '"';

			$output .= '<a class="nav-menu-search-icon nav-menu-search-item__link-desktop"' . $link_attr . '>';
			$output .= '<span class="screen-reader-text">' . esc_html( $label ) . '</span>';
			$output .= $svg;
			$output .= '</a>';

			$input_id = 'nav-menu-search-keyword-' . (int) $item->ID;
			$action   = esc_url( $search_url );

			$output .= '<form class="nav-menu-search-form nav-menu-search-item__form-mobile" method="get" action="' . $action . '" role="search">';
			$output .= '<label class="screen-reader-text" for="' . esc_attr( $input_id ) . '">' . esc_html( $label ) . '</label>';
			$output .= '<div class="nav-menu-search-form__field">';
			$output .= '<input type="search" id="' . esc_attr( $input_id ) . '" class="nav-menu-search-form__input" name="keyword" value="" autocomplete="off" placeholder="' . esc_attr( $label ) . '" />';
			$output .= '<button type="submit" class="nav-menu-search-form__submit" aria-label="' . esc_attr( $label ) . '">';
			$output .= $this->get_search_magnifier_svg_markup( 'nav-menu-search-form__submit-icon nav-menu-search-icon__svg' );
			$output .= '</button>';
			$output .= '</div>';
			$output .= '</form>';
		} else {
			$output .= '<span class="nav-menu-search-icon nav-menu-search-icon--static">';
			$output .= '<span class="screen-reader-text">' . esc_html( $label ) . '</span>';
			$output .= $svg;
			$output .= '</span>';
		}

		$output .= isset( $args->after ) ? $args->after : '';
		return $output;
	}

	/**
	 * Magnifying-glass SVG (same asset as nav search icon).
	 *
	 * @param string $svg_class Class on the root SVG element (e.g. nav-menu-search-icon__svg).
	 * @return string HTML (path uses currentColor).
	 */
	private function get_search_magnifier_svg_markup( $svg_class ) {
		$d = 'M3.14665 9.43685C3.14665 7.76831 3.80944 6.16811 4.98921 4.98828C6.16898 3.80844 7.76909 3.14562 9.43754 3.14562C11.106 3.14562 12.7061 3.80844 13.8859 4.98828C15.0656 6.16811 15.7284 7.76831 15.7284 9.43685C15.7284 11.1054 15.0656 12.7056 13.8859 13.8854C12.7061 15.0653 11.106 15.7281 9.43754 15.7281C7.76909 15.7281 6.16898 15.0653 4.98921 13.8854C3.80944 12.7056 3.14665 11.1054 3.14665 9.43685ZM9.43754 1.78453e-07C7.95243 -0.00018935 6.48823 0.350174 5.16405 1.0226C3.83986 1.69502 2.69307 2.67051 1.81695 3.86973C0.940833 5.06896 0.36012 6.45805 0.122042 7.92404C-0.116035 9.39003 -0.00475352 10.8915 0.446837 12.3064C0.898427 13.7213 1.67757 15.0096 2.72091 16.0665C3.76424 17.1235 5.04231 17.9192 6.45115 18.3891C7.85999 18.8589 9.35984 18.9896 10.8287 18.7704C12.2976 18.5513 13.694 17.9885 14.9043 17.1279L19.3347 21.5585C19.6313 21.845 20.0286 22.0035 20.4409 21.9999C20.8533 21.9964 21.2478 21.8309 21.5394 21.5393C21.831 21.2477 21.9964 20.8532 21.9999 20.4409C22.0035 20.0285 21.845 19.6312 21.5585 19.3345L17.1297 14.9055C18.1336 13.4937 18.7294 11.8328 18.852 10.1048C18.9747 8.37679 18.6193 6.64838 17.8248 5.10896C17.0304 3.56953 15.8276 2.27851 14.3481 1.37735C12.8687 0.476184 11.1698 -0.000336661 9.43754 1.78453e-07Z';

		$class = trim( (string) $svg_class );
		$html  = '<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 22 22" fill="none" class="' . esc_attr( $class ) . '" aria-hidden="true" focusable="false">';
		$html .= '<path fill-rule="evenodd" clip-rule="evenodd" d="' . esc_attr( $d ) . '" fill="currentColor"/>';
		$html .= '</svg>';

		return $html;
	}

	/**
	 * Start the element output.
	 *
	 * @param string $output Used to append additional content (passed by reference).
	 * @param object $item   Menu item data object.
	 * @param int    $depth  Depth of menu item. Used for padding.
	 * @param array  $args   An array of arguments. @see wp_nav_menu().
	 * @param int    $id     Current item ID.
	 */
	public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
		$indent = ( $depth ) ? str_repeat( "\t", $depth ) : '';

		$classes   = empty( $item->classes ) ? array() : (array) $item->classes;
		$classes[] = 'menu-item-' . $item->ID;

		// Depth 0: Check if this is a mega menu parent
		if ( $depth === 0 ) {
			$content_type = $this->get_item_content_type( $item );
			if ( $this->is_mega_menu_parent_type( $content_type ) ) {
				$classes[] = 'mega-menu-parent';
				// Store parent item for later reference
				$this->parent_items[ $item->ID ] = $item;
				if ( $this->mega_menu_uses_button_trigger( $item ) ) {
					$classes[] = 'mega-menu-content-type-button';
				}
			}
			if ( 'link_with_description' === $content_type ) {
				$classes[] = 'mega-menu-content-type-link_with_description';
			}
			if ( 'link_with_arrow' === $content_type ) {
				$classes[] = 'mega-menu-content-type-link_with_arrow';
			}
			if ( 'login_link' === $content_type ) {
				$classes[] = 'mega-menu-content-type-login_link';
			}
			if ( 'pre_heading' === $content_type ) {
				$classes[] = 'mega-menu-content-type-pre_heading';
				if ( 'right' === $this->get_item_pre_heading_alignment( $item ) ) {
					$classes[] = 'mega-menu-pre-heading-align-right';
				}
			}
			if ( 'button' === $content_type ) {
				$classes[] = 'mega-menu-content-type-button';
			}
			if ( 'search_icon' === $content_type ) {
				$classes[] = 'mega-menu-content-type-search_icon';
			}
			// Track current parent for child items
			$this->current_parent_id = $item->ID;
		}

		// Depth 1: Direct children of mega menu - columns or regular content
		if ( $depth === 1 ) {
			$content_type = $this->get_item_content_type( $item );
			if ( 'column' === $content_type ) {
				$this->append_mega_menu_column_classes( $classes, $item );
			} else {
				if ( $content_type ) {
					$classes[] = 'mega-menu-content-type-' . $content_type;
				} else {
					$classes[] = 'mega-menu-content-type-link';
				}
				if ( 'pre_heading' === $content_type && 'right' === $this->get_item_pre_heading_alignment( $item ) ) {
					$classes[] = 'mega-menu-pre-heading-align-right';
				}
				$this->append_image_mobile_overlay_class( $classes, $item );
			}
		}

		// Depth 1: Update parent tracking for depth 2 items
		if ( $depth === 1 ) {
			$this->current_parent_id = $item->ID;
		}

		// Depth 2+: Items inside columns (or deeper nested submenus)
		if ( $depth >= 2 ) {
			$content_type = $this->get_item_content_type( $item );
			if ( 'column' === $content_type ) {
				$this->append_mega_menu_column_classes( $classes, $item, true );
			} elseif ( $content_type ) {
				$classes[] = 'mega-menu-content-type-' . $content_type;
			} else {
				$classes[] = 'mega-menu-content-type-link';
			}
			if ( 'pre_heading' === $content_type && 'right' === $this->get_item_pre_heading_alignment( $item ) ) {
				$classes[] = 'mega-menu-pre-heading-align-right';
			}
			$this->append_image_mobile_overlay_class( $classes, $item );
			if ( $depth === 2 ) {
				$this->current_parent_id = $item->ID;
			}
		}

		if ( filter_var( $this->get_item_meta( $item->ID, 'mega_menu_hide_on_mobile' ), FILTER_VALIDATE_BOOLEAN ) ) {
			$classes[] = 'mega-menu-hide-on-mobile';
		}

		$item_content_type = $this->get_item_content_type( $item );
		if ( 'pre_heading' === $item_content_type && $this->item_pre_heading_hides_label( $item ) ) {
			$classes[] = 'mega-menu-pre-heading--spacer';
		}

		$classes = $this->strip_nav_menu_current_css_classes( $classes );

		$class_names = join( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item, $args ) );
		$class_names = $class_names ? ' class="' . esc_attr( $class_names ) . '"' : '';

		$li_id_attr = apply_filters( 'nav_menu_item_id', 'menu-item-' . $item->ID, $item, $args );
		$li_id_attr = $li_id_attr ? ' id="' . esc_attr( $li_id_attr ) . '"' : '';

		$output .= $indent . '<li' . $li_id_attr . $class_names . '>';

		$attributes = $this->get_item_link_attributes_string( $item );

		// Render content based on depth and type
		$item_output = '';
		
		// Depth 1: Direct children - column container or content
		if ( $depth === 1 ) {
			$content_type = $this->get_item_content_type( $item );
			if ( 'column' === $content_type ) {
				$item_output = isset( $args->before ) ? $args->before : '';
				$item_output .= isset( $args->after ) ? $args->after : '';
			} else {
				if ( ! $content_type ) {
					$content_type = 'link';
				}
				if ( 'link' === $content_type ) {
					$item_output = $this->render_menu_item_anchor_block( $item, $attributes, $args );
				} elseif ( 'link_with_description' === $content_type ) {
					$item_output = $this->render_link_with_description_item( $item, $attributes, $args );
				} elseif ( 'link_with_arrow' === $content_type ) {
					$item_output = $this->render_link_with_arrow_item( $item, $attributes, $args );
				} elseif ( 'login_link' === $content_type ) {
					$item_output = $this->render_login_link_item( $item, $attributes, $args );
				} elseif ( 'button' === $content_type ) {
					$item_output = $this->render_button_item( $item, $args );
				} else {
					$item_output = $this->render_custom_content( $item, $content_type, $args );
				}
			}
		} elseif ( $depth >= 2 ) {
			$content_type = $this->get_item_content_type( $item );
			if ( 'column' === $content_type ) {
				$item_output = isset( $args->before ) ? $args->before : '';
				$item_output .= isset( $args->after ) ? $args->after : '';
			} else {
				if ( ! $content_type ) {
					$content_type = 'link';
				}
				if ( 'link' === $content_type ) {
					$item_output = $this->render_menu_item_anchor_block( $item, $attributes, $args );
				} elseif ( 'link_with_description' === $content_type ) {
					$item_output = $this->render_link_with_description_item( $item, $attributes, $args );
				} elseif ( 'link_with_arrow' === $content_type ) {
					$item_output = $this->render_link_with_arrow_item( $item, $attributes, $args );
				} elseif ( 'login_link' === $content_type ) {
					$item_output = $this->render_login_link_item( $item, $attributes, $args );
				} elseif ( 'button' === $content_type ) {
					$item_output = $this->render_button_item( $item, $args );
				} else {
					$item_output = $this->render_custom_content( $item, $content_type, $args );
				}
			}
		} else {
			// Depth 0: plain link, link with description, or other content types (e.g. pre-heading row)
			$content_type_d0 = $this->get_item_content_type( $item );
			if ( ! $content_type_d0 ) {
				$content_type_d0 = 'link';
			}
			if ( 'link_with_description' === $content_type_d0 ) {
				$item_output = $this->render_link_with_description_item( $item, $attributes, $args );
			} elseif ( 'link_with_arrow' === $content_type_d0 ) {
				$item_output = $this->render_link_with_arrow_item( $item, $attributes, $args );
			} elseif ( 'button' === $content_type_d0 ) {
				$item_output = $this->render_button_item( $item, $args );
			} elseif ( 'mega_menu' === $content_type_d0 ) {
				if ( $this->mega_menu_uses_button_trigger( $item ) ) {
					$item_output = $this->render_button_item( $item, $args );
				} else {
					$item_output = $this->render_menu_item_anchor_block( $item, $attributes, $args );
				}
			} elseif ( in_array( 'pll-parent-menu-item', $classes, true ) ) {
				$item_output = function_exists( 'jdpower_pll_render_language_switcher_trigger' )
					? jdpower_pll_render_language_switcher_trigger( $item, $attributes, $args )
					: $this->render_menu_item_anchor_block( $item, $attributes, $args );
			} elseif ( 'link' === $content_type_d0 ) {
				$item_output = $this->render_menu_item_anchor_block( $item, $attributes, $args );
			} else {
				$item_output = $this->render_custom_content( $item, $content_type_d0, $args );
			}
		}

		$output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args );
	}

	/**
	 * Columns per row for featured insights (1–4, default 3).
	 *
	 * @param int $item_id Nav menu item post ID.
	 * @return int
	 */
	private function get_featured_insights_columns_for_item( $item_id ) {
		$raw = $this->get_item_meta( $item_id, 'mega_menu_featured_insights_columns' );
		$cols = (int) $raw;
		if ( $cols < 1 || $cols > 4 ) {
			$cols = 3;
		}
		return $cols;
	}

	/**
	 * Resolve featured insights cards for a mega menu item.
	 *
	 * @param object $item Menu item data object.
	 * @return array<int, array<string, mixed>>
	 */
	private function get_featured_insights_cards_for_item( $item ) {
		if ( ! function_exists( 'jdpower_featured_insights_card_from_post' ) ) {
			return array();
		}

		$source = $this->get_item_meta( $item->ID, 'mega_menu_featured_insights_source' );
		if ( ! is_string( $source ) || '' === $source ) {
			$source = 'dynamic';
		}

		if ( 'manual' === $source ) {
			$post_ids = $this->get_item_meta( $item->ID, 'mega_menu_featured_insights_posts' );
			if ( function_exists( 'jdpower_mega_menu_featured_insights_cards_from_ids' ) ) {
				return jdpower_mega_menu_featured_insights_cards_from_ids( $post_ids );
			}
			return array();
		}

		$post_types = $this->get_item_meta( $item->ID, 'mega_menu_featured_insights_post_types' );
		$max_raw    = $this->get_item_meta( $item->ID, 'mega_menu_featured_insights_max' );
		$limit      = (int) $max_raw;
		if ( $limit < 1 || $limit > 12 ) {
			$limit = 3;
		}

		if ( function_exists( 'jdpower_mega_menu_featured_insights_normalize_post_types' ) ) {
			$post_types = jdpower_mega_menu_featured_insights_normalize_post_types( $post_types );
		}
		if ( empty( $post_types ) && function_exists( 'jdpower_mega_menu_featured_insights_allowed_post_types' ) ) {
			$post_types = jdpower_mega_menu_featured_insights_allowed_post_types();
		}

		if ( function_exists( 'jdpower_mega_menu_featured_insights_featured_only_cards' ) ) {
			return jdpower_mega_menu_featured_insights_featured_only_cards( $post_types, $limit );
		}

		return array();
	}

	/**
	 * Render featured insights grid for a mega menu item.
	 *
	 * @param object $item Menu item data object.
	 * @return string HTML output.
	 */
	private function render_featured_insights_item( $item ) {
		if ( ! function_exists( 'jdpower_featured_insights_columns_render_card' ) ) {
			return '';
		}

		$cards = $this->get_featured_insights_cards_for_item( $item );
		if ( empty( $cards ) ) {
			return '';
		}

		$cols       = $this->get_featured_insights_columns_for_item( $item->ID );
		$cards_html = '';

		foreach ( $cards as $card ) {
			$url = isset( $card['url'] ) ? $card['url'] : '';
			if ( '' === $url ) {
				continue;
			}
			ob_start();
			jdpower_featured_insights_columns_render_card( $card, true, $url, false );
			$cards_html .= ob_get_clean();
		}

		if ( '' === $cards_html ) {
			return '';
		}

		$grid_class = 'mega-menu-featured-insights__grid mega-menu-featured-insights__grid--cols-' . $cols;

		return '<div class="mega-menu-featured-insights"><div class="' . esc_attr( $grid_class ) . '">' . $cards_html . '</div></div>';
	}

	/**
	 * Columns per row for featured products (1–4, default 3).
	 *
	 * @param int $item_id Nav menu item post ID.
	 * @return int
	 */
	private function get_featured_products_columns_for_item( $item_id ) {
		$raw = $this->get_item_meta( $item_id, 'mega_menu_featured_products_columns' );
		$cols = (int) $raw;
		if ( $cols < 1 || $cols > 4 ) {
			$cols = 3;
		}
		return $cols;
	}

	/**
	 * Render featured products grid for a mega menu item.
	 *
	 * @param object $item Menu item data object.
	 * @return string HTML output.
	 */
	private function render_featured_products_item( $item ) {
		$product_ids = $this->get_item_meta( $item->ID, 'mega_menu_featured_products' );
		if ( empty( $product_ids ) || ! is_array( $product_ids ) ) {
			return '';
		}

		$cols = $this->get_featured_products_columns_for_item( $item->ID );
		$cards_html = '';

		foreach ( $product_ids as $entry ) {
			if ( is_object( $entry ) && isset( $entry->ID ) ) {
				$post_id = (int) $entry->ID;
			} else {
				$post_id = (int) $entry;
			}
			if ( $post_id <= 0 || 'product' !== get_post_type( $post_id ) || 'publish' !== get_post_status( $post_id ) ) {
				continue;
			}

			$url = get_permalink( $post_id );
			if ( ! $url ) {
				continue;
			}

			$image_id = (int) get_post_thumbnail_id( $post_id );

			if ( function_exists( 'jdpower_post_filters_product_card_data' ) ) {
				$detail = jdpower_post_filters_product_card_data( $post_id );
			} else {
				$detail = array(
					'heading'   => get_the_title( $post_id ),
					'copy_html' => '',
				);
			}

			$heading   = isset( $detail['heading'] ) ? (string) $detail['heading'] : '';
			$copy_html = isset( $detail['copy_html'] ) ? (string) $detail['copy_html'] : '';

			$cards_html .= '<a class="mega-menu-featured-products__card" href="' . esc_url( $url ) . '">';
			$cards_html .= '<span class="mega-menu-featured-products__media">';
			if ( $image_id > 0 ) {
				$cards_html .= wp_get_attachment_image(
					$image_id,
					'medium',
					false,
					array(
						'class' => 'mega-menu-featured-products__img',
					)
				);
			} else {
				$cards_html .= '<span class="mega-menu-featured-products__placeholder" aria-hidden="true"></span>';
			}
			$cards_html .= '</span>';
			$cards_html .= '<span class="mega-menu-featured-products__body">';
			if ( '' !== $heading ) {
				$cards_html .= '<span class="mega-menu-featured-products__title small">' . $heading . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- jdpower_sanitize_inline_html()
			}
			if ( '' !== trim( wp_strip_all_tags( $copy_html ) ) ) {
				$cards_html .= '<span class="mega-menu-featured-products__copy">' . $copy_html . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
			$cards_html .= '</span>';
			$cards_html .= '</a>';
		}

		if ( '' === $cards_html ) {
			return '';
		}

		$grid_class = 'mega-menu-featured-products__grid mega-menu-featured-products__grid--cols-' . $cols;

		return '<div class="mega-menu-featured-products"><div class="' . esc_attr( $grid_class ) . '">' . $cards_html . '</div></div>';
	}

	/**
	 * Render an image mega menu item (optional mobile heading overlay).
	 *
	 * @param object $item Menu item data object.
	 * @return string HTML output.
	 */
	private function render_image_menu_item( $item ) {
		$image = get_field( 'mega_menu_image', $item->ID );
		if ( ! $image ) {
			return '';
		}

		$image_id    = isset( $image['ID'] ) ? (int) $image['ID'] : 0;
		$image_alt   = $image['alt'] ? $image['alt'] : $item->title;
		$image_title = $image['title'] ? $image['title'] : $item->title;
		$overlay     = $this->get_item_image_mobile_heading_overlay( $item );
		$has_overlay = '' !== $overlay;
		$has_link    = ! empty( $item->url );
		$attrs       = $this->get_item_link_attributes_string( $item );
		$output      = '';

		if ( $has_overlay ) {
			$output .= '<div class="mega-menu-image-card">';
		}

		if ( $has_link ) {
			$output .= '<a class="mega-menu-image-link"' . $attrs . '>';
		} elseif ( $has_overlay ) {
			$output .= '<span class="mega-menu-image-link mega-menu-image-link--static">';
		}

		if ( $has_overlay ) {
			$output .= '<span class="mega-menu-image-card__media">';
		}

		$output .= wp_get_attachment_image(
			$image_id,
			'full',
			false,
			array(
				'alt'   => $image_alt,
				'title' => $image_title,
				'class' => 'mega-menu-image',
			)
		);

		if ( $has_overlay ) {
			$output .= '<span class="mega-menu-image-card__overlay" aria-hidden="true"></span>';
			$output .= '<span class="mega-menu-image-card__caption">';
			$output .= '<span class="mega-menu-image-card__heading">' . esc_html( $overlay ) . '</span>';
			$output .= '<span class="mega-menu-image-card__chevron" aria-hidden="true"></span>';
			$output .= '</span>';
			$output .= '</span>';
		}

		if ( $has_link ) {
			$output .= '</a>';
		} elseif ( $has_overlay ) {
			$output .= '</span>';
		}

		if ( $has_overlay ) {
			$output .= '</div>';
		}

		return $output;
	}

	/**
	 * Render custom content types for mega menu items
	 *
	 * @param object $item        Menu item data object.
	 * @param string $content_type Content type.
	 * @param array  $args        An array of arguments.
	 * @return string HTML output.
	 */
	private function render_custom_content( $item, $content_type, $args ) {
		$output = '';

		switch ( $content_type ) {
			case 'image':
				$output .= $this->render_image_menu_item( $item );
				break;

			case 'heading':
				if ( ! $this->item_menu_title_is_empty( $item ) ) {
					$as_link    = filter_var( $this->get_item_meta( $item->ID, 'mega_menu_heading_is_link' ), FILTER_VALIDATE_BOOLEAN );
					$title_html = $this->get_item_menu_title_escaped( $item );
					$output    .= '<h3 class="mega-menu-heading">';
					if ( $as_link && ! empty( $item->url ) ) {
						$attrs = $this->get_item_link_attributes_string( $item );
						$output .= '<a class="mega-menu-heading-link"' . $attrs . '>' . $title_html . '</a>';
					} else {
						$output .= $title_html;
					}
					$output .= '</h3>';
				}
				break;

			case 'pre_heading':
				$hide_label   = $this->item_pre_heading_hides_label( $item );
				$aria         = $hide_label ? ' aria-hidden="true"' : '';
				$align        = $this->get_item_pre_heading_alignment( $item );
				$align_class  = ( 'right' === $align ) ? ' mega-menu-pre-heading--align-right' : '';
				$output      .= '<span class="mega-menu-pre-heading' . $align_class . '"' . $aria . '>';
				if ( ! $hide_label ) {
					$as_link = filter_var( $this->get_item_meta( $item->ID, 'mega_menu_pre_heading_is_link' ), FILTER_VALIDATE_BOOLEAN );
					if ( ! $this->item_menu_title_is_empty( $item ) ) {
						if ( $as_link && ! empty( $item->url ) ) {
							$attrs = $this->get_item_link_attributes_string( $item );
							$output .= '<a class="mega-menu-pre-heading-link"' . $attrs . '>' . $this->get_item_menu_title_escaped( $item ) . '</a>';
						} else {
							$output .= $this->get_item_menu_title_escaped( $item );
						}
					}
				}
				$output .= '</span>';
				break;

			case 'content':
				$content = get_field( 'mega_menu_content', $item->ID );
				if ( $content ) {
					$output .= '<div class="mega-menu-content">' . wp_kses_post( $content ) . '</div>';
				}
				break;

			case 'html':
				$html = get_field( 'mega_menu_html', $item->ID );
				if ( $html ) {
					$output .= '<div class="mega-menu-html">' . $html . '</div>';
				}
				break;

			case 'link_with_description':
				$attr    = $this->get_item_link_attributes_string( $item );
				$output .= $this->render_link_with_description_item( $item, $attr, $args );
				break;

			case 'link_with_arrow':
				$attr    = $this->get_item_link_attributes_string( $item );
				$output .= $this->render_link_with_arrow_item( $item, $attr, $args );
				break;

			case 'login_link':
				$attr    = $this->get_item_link_attributes_string( $item );
				$output .= $this->render_login_link_item( $item, $attr, $args );
				break;

			case 'button':
				$output .= $this->render_button_item( $item, $args );
				break;

			case 'search_icon':
				$output .= $this->render_search_icon_item( $item, $args );
				break;

			case 'featured_products':
				$output .= $this->render_featured_products_item( $item );
				break;

			case 'featured_insights':
				$output .= $this->render_featured_insights_item( $item );
				break;

			default:
				// Fallback to default link
				$attr   = $this->get_item_link_attributes_string( $item );
				$output = $this->render_menu_item_anchor_block( $item, $attr, $args );
				break;
		}

		return $output;
	}

	/**
	 * Start the list before the elements are added.
	 *
	 * @param string $output Used to append additional content (passed by reference).
	 * @param int    $depth  Depth of menu item. Used for padding.
	 * @param array  $args   An array of arguments. @see wp_nav_menu().
	 */
	public function start_lvl( &$output, $depth = 0, $args = null ) {
		$indent = str_repeat( "\t", $depth );
		
		// Depth 0 -> 1: Check if parent is a mega menu
		if ( $depth === 0 ) {
			$is_mega_menu = false;
			if ( $this->current_parent_id > 0 ) {
				$parent_content_type = $this->get_item_content_type_for_id( $this->current_parent_id );
				$is_mega_menu = ( 'mega_menu' === $parent_content_type );
			}

			if ( $is_mega_menu ) {
				$this->mega_dropdown_fluid_open = true;
				$output .= "\n$indent<div class=\"mega-menu-dropdown-fluid\">\n";
				$output .= "$indent<div class=\"container mega-menu-dropdown-container\">\n";
				$output .= "$indent<ul class=\"sub-menu mega-menu-dropdown row\">\n";
			} else {
				$this->mega_dropdown_fluid_open = false;
				$output .= "\n$indent<ul class=\"sub-menu\">\n";
			}
		} elseif ( $depth >= 1 ) {
			$parent_for_lvl = (int) $this->current_parent_id;
			$parent_type    = $parent_for_lvl ? $this->get_item_content_type_for_id( $parent_for_lvl ) : '';

			if ( 'column' === $parent_type ) {
				$ul_classes = $this->get_mega_menu_column_submenu_classes( $parent_for_lvl );
			} else {
				$ul_classes = array( 'sub-menu' );
			}
			$output .= "\n$indent<ul class=\"" . esc_attr( join( ' ', $ul_classes ) ) . "\">\n";
		} else {
			// Default sub-menu
			$output .= "\n$indent<ul class=\"sub-menu\">\n";
		}
	}

	/**
	 * End the list of after the elements are added.
	 *
	 * @param string $output Used to append additional content (passed by reference).
	 * @param int    $depth  Depth of menu item. Used for padding.
	 * @param array  $args   An array of arguments. @see wp_nav_menu().
	 */
	public function end_lvl( &$output, $depth = 0, $args = null ) {
		$indent = str_repeat( "\t", $depth );

		if ( $depth === 0 ) {
			$output .= "$indent</ul>\n";
			if ( $this->mega_dropdown_fluid_open ) {
				$output .= "$indent</div>\n";
				$output .= "$indent</div>\n";
				$this->mega_dropdown_fluid_open = false;
			}
		} else {
			$output .= "$indent</ul>\n";
		}
	}
}

/**
 * Filter to use custom walker for navigation menus
 */
function customnavfunctionality_mega_menu_walker( $args ) {
	// Only apply walker on frontend (not in admin)
	if ( is_admin() ) {
		return $args;
	}
	
	// Only apply to primary menu (theme_location 'primary')
	if ( isset( $args['theme_location'] ) && 'primary' === $args['theme_location'] ) {
		$args['walker'] = new CustomNavFunctionality_Mega_Menu_Walker();
	}
	
	return $args;
}
add_filter( 'wp_nav_menu_args', 'customnavfunctionality_mega_menu_walker', 999, 1 );
