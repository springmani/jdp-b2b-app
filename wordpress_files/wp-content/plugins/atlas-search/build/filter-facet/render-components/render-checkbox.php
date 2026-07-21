<?php
// phpcs:ignoreFile

/**
 * Build hierarchical term tree from flat term list
 */
if ( ! function_exists( 'build_term_tree' ) ) {
	function build_term_tree( $terms ) {
		$tree    = [];
		$indexed = [];

		// Index all terms by ID.
		foreach ( $terms as $term ) {
			$indexed[ $term->term_id ]           = $term;
			$indexed[ $term->term_id ]->children = [];
		}

		// Build parent-child relationships.
		foreach ( $indexed as $term ) {
			if ( $term->parent === 0 ) {
				$tree[] = $term;
			} else {
				if ( isset( $indexed[ $term->parent ] ) ) {
					$indexed[ $term->parent ]->children[] = $term;
				}
			}
		}

		return $tree;
	}
}

/**
 * Render hierarchical checkbox tree
 */
if ( ! function_exists( 'render_term_tree_checkbox' ) ) {
	function render_term_tree_checkbox( $tree, $selected_data_source_mapped, $filters, $depth = 0, $parent_id = null, $default_expanded = false ) {
		foreach ( $tree as $term ) {
			// Calculate padding: base (8px/0.5rem) + depth-based indentation (20px per level)
			$indent_style = 'padding-left: ' . ( 8 + ( $depth * 20 ) ) . 'px;';

			// Build CSS classes
			$css_classes = [ 'wpengine-smart-checkbox-facet__field', 'term-depth-' . $depth ];
			if ( $parent_id ) {
				// Child terms get collapsed/expanded class based on setting
				$css_classes[] = $default_expanded ? 'term-expanded' : 'term-collapsed';
			}
			?>
			<div class="<?php echo esc_attr( implode( ' ', $css_classes ) ); ?>"
				data-term-name="<?php echo esc_attr( strtolower( $term->name ) ); ?>"
				data-term-id="<?php echo esc_attr( $term->term_id ); ?>"
				<?php if ( $parent_id ) : ?>
					data-parent-id="<?php echo esc_attr( $parent_id ); ?>"
				<?php endif; ?>
				<?php if ( ! empty( $term->children ) ) : ?>
					data-has-children="true"
				<?php endif; ?>
				style="<?php echo esc_attr( $indent_style ); ?>">
				<label>
					<?php if ( ! empty( $term->children ) ) : ?>
						<button
							type="button"
							class="term-expand-toggle"
							data-wp-on--click="actions.toggleTermExpansion"
							data-term-id="<?php echo esc_attr( $term->term_id ); ?>"
							aria-label="<?php echo esc_attr__( 'Toggle child terms', 'wpengine-smart-search' ); ?>"
							aria-expanded="<?php echo esc_attr( $default_expanded ? 'true' : 'false' ); ?>"
						>
							<span class="expand-icon" aria-hidden="true"></span>
						</button>
					<?php else : ?>
						<span class="term-spacer"></span>
					<?php endif; ?>
					<input
						type="checkbox"
						name="<?php echo esc_attr( $selected_data_source_mapped ); ?>"
						data-wp-bind--checked-args="<?php echo esc_attr( $term->slug ); ?>"
						data-wp-on--change="actions.setCheckboxValue"
						value="<?php echo esc_attr( $term->slug ); ?>"<?php echo \AtlasSearch\Blocks\WordPress\in_filter( esc_attr( $term->slug ), $filters[ esc_attr( $selected_data_source_mapped ) ] ?? array() ) ? ' checked ' : ''; ?>
						data-checkbox-name="<?php echo esc_attr( $selected_data_source_mapped ); ?>"/>
					<?php echo esc_html( $term->name ); ?> (<?php echo esc_html( $term->count ); ?>)
				</label>
			</div>
			<?php
			// Recursively render children.
			if ( ! empty( $term->children ) ) {
				render_term_tree_checkbox( $term->children, $selected_data_source_mapped, $filters, $depth + 1, $term->term_id, $default_expanded );
			}
		}
	}
}
?>

<div
		data-wp-bind--hidden="state.filtersHidden"
		data-wp-interactive='wpengine-smart-search-facets__store'
		data-wp-init="actions.initCheckboxFacet"
	<?php echo wp_kses_data( $wrapper_attributes ); ?>
	<?php echo wp_kses_data( $context ); ?>
>
	<span
			data-wp-bind--hidden="!context.attributes.hasLabel"
			class="wpengine-smart-search-checkbox-facet__label">
		<?php echo esc_html( $attributes['label'] ); ?>
	</span>

	<?php if ( $attributes['isSearchable'] ?? true ) : ?>
		<div class="wpengine-smart-checkbox-facet__search">
			<input
				type="text"
				class="wpengine-smart-checkbox-facet__search-input wp-block-search__input"
				placeholder="<?php echo esc_attr__( 'Search...', 'wpengine-smart-search' ); ?>"
				data-wp-on--input="actions.filterCheckboxTerms"
			/>
		</div>
	<?php endif; ?>

	<div
		class="wpengine-smart-checkbox-facet__fields <?php echo ( $attributes['isScrollable'] ?? true ) ? 'scrollable' : ''; ?>"
		<?php if ( $attributes['isScrollable'] ?? true ) : ?>
			style="max-height: <?php echo esc_attr( $attributes['maxHeight'] ?? 300 ); ?>px; overflow-y: auto;"
		<?php endif; ?>
	>
		<?php
		// Hierarchical rendering for taxonomy with showHierarchy enabled.
		if ( $show_hierarchy && 'taxonomy' === $data_source_type ) :
			$tree = build_term_tree( $source_data_records );
			$default_expanded = $attributes['defaultExpandedHierarchy'] ?? false;
			render_term_tree_checkbox( $tree, $selected_data_source_mapped, $filters, 0, null, $default_expanded );
		else :
			// Flat rendering for post types or non-hierarchical display.
			foreach ( $source_data_records as $record ) :
				?>
				<div class="wpengine-smart-checkbox-facet__field" data-term-name="<?php echo esc_attr( strtolower( $record->name ) ); ?>">
					<label>
					<input
							type="checkbox"
							name="<?php echo esc_attr( $selected_data_source_mapped ); ?>"
							data-wp-bind--checked-args="<?php echo esc_attr( $record->slug ); ?>"
							data-wp-on--change="actions.setCheckboxValue"
							value="<?php echo esc_attr( $record->slug ); ?>"<?php echo \AtlasSearch\Blocks\WordPress\in_filter( esc_attr( $record->slug ), $filters[ esc_attr( $selected_data_source_mapped ) ] ?? array() ) ? ' checked ' : ''; ?>
							data-checkbox-name="<?php echo esc_attr( $selected_data_source_mapped ); ?>"/>
					<?php echo esc_html( $record->name ); ?> (<?php echo esc_html( $record->count ); ?>)</label>
				</div>
				<?php
			endforeach;
		endif;
		?>
	</div>

	<?php if ( ! ( $attributes['autoReload'] ?? true ) ) : ?>
		<button
			type="button"
			class="wpengine-smart-checkbox-facet__apply-button wp-element-button"
			data-wp-on--click="actions.applyFilters"
		>
			<?php echo esc_html__( 'Apply Filters', 'wpengine-smart-search' ); ?>
		</button>
	<?php endif; ?>

</div>
