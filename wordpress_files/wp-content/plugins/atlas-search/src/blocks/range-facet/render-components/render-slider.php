<div
		data-wp-bind--hidden="state.filtersHidden"
		data-wp-interactive='wpengine-smart-search-facets__store'
		data-wp-init="actions.initRangeFacet"
	<?php echo wp_kses_data( $wrapper_attributes ); ?>
	<?php echo wp_kses_data( $context ); ?>
>
	<span
			data-wp-bind--hidden="!context.attributes.hasLabel"
			class="wpengine-smart-search-slider-facet__label"
	>
			<?php echo esc_html( $attributes['label'] ); ?>
	</span>
	<div class="form_control">
		<div class="form_control_container">
			<input type="number" id="fromInput"
					onkeydown="return false"
					data-wp-on--input="actions.setFromRangeValue"
					data-wp-bind--value="context.attributes.range.from"
					data-wp-bind--min="context.attributes.range.min" data-wp-bind--max="context.attributes.range.max"/>
		</div>
		<div class="form_control_container">
			<input type="number" id="toInput"
					onkeydown="return false"
					data-wp-on--input="actions.setToRangeValue"
					data-wp-bind--value="context.attributes.range.to"
					data-wp-bind--min="context.attributes.range.min" data-wp-bind--max="context.attributes.range.max"/>
		</div>
	</div>
	<div class="sliders_control">
		<input class="slider" id="fromSlider" type="range"
				data-wp-on--input="actions.setFromRangeValue"
				data-wp-bind--value="context.attributes.range.from"
				data-wp-bind--min="context.attributes.range.min" data-wp-bind--max="context.attributes.range.max"/>
		<input class="slider"  id="toSlider"  type="range"
				data-wp-on--input="actions.setToRangeValue"
				data-wp-bind--value="context.attributes.range.to"
				data-wp-bind--min="context.attributes.range.min" data-wp-bind--max="context.attributes.range.max"/>
	</div>
</div>
