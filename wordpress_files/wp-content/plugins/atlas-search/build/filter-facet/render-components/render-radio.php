<?php // phpcs:ignoreFile ?>
<div
		data-wp-bind--hidden="state.filtersHidden"
		data-wp-interactive='wpengine-smart-search-facets__store'
		data-wp-init="actions.initRadioFacet"
	<?php echo wp_kses_data( $wrapper_attributes ); ?>
	<?php echo wp_kses_data( $context ); ?>
>
	<span
			data-wp-bind--hidden="!context.attributes.hasLabel"
			class="wpengine-smart-search-radio-facet__label">
		<?php echo esc_html( $attributes['label'] ); ?>
	</span>

	<div class="wpengine-smart-radio-facet__fields">
		<div class="wpengine-smart-radio-facet__field">
			<label>
				<input
					type="radio"
					name="<?php echo esc_attr( $selected_data_source_mapped ); ?>"
					data-wp-on--change="actions.setRadioValue"
					value=""
					<?php echo empty( $selected_taxonomy ) ? 'checked' : ''; ?>
					data-radio-name="<?php echo esc_attr( $selected_data_source_mapped ); ?>"
				/>
				<?php echo esc_html__( 'All', 'wpengine-smart-search' ); ?>
			</label>
		</div>
		<?php foreach ( $source_data_records as $record ) : ?>
			<div class="wpengine-smart-radio-facet__field">
				<label>
					<input
						type="radio"
						name="<?php echo esc_attr( $selected_data_source_mapped ); ?>"
						data-wp-on--change="actions.setRadioValue"
						value="<?php echo esc_attr( $record->slug ); ?>"
						<?php echo ( esc_attr( $record->slug ) === $selected_taxonomy ) ? 'checked' : ''; ?>
						data-radio-name="<?php echo esc_attr( $selected_data_source_mapped ); ?>"
					/>
					<?php echo esc_html( $record->name ); ?> (<?php echo esc_html( $record->count ); ?>)
				</label>
			</div>
		<?php endforeach; ?>
	</div>

	<?php if ( ! ( $attributes['autoReload'] ?? true ) ) : ?>
		<button
			type="button"
			class="wpengine-smart-radio-facet__apply-button wp-element-button"
			data-wp-on--click="actions.applyFilters"
		>
			<?php echo esc_html__( 'Apply Filters', 'wpengine-smart-search' ); ?>
		</button>
	<?php endif; ?>

</div>
