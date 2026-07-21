/**
 * Use this file for JavaScript code that you want to run in the front-end
 * on posts/pages that contain this block.
 *
 * When this file is defined as the value of the `viewScript` property
 * in `block.json` it will be enqueued on the front end of the site.
 *
 * Example:
 *
 * ```js
 * {
 *   "viewScript": "file:./view.js"
 * }
 * ```
 *
 * If you're not making any changes to this file because your project doesn't need any
 * JavaScript running in the front-end, then you should delete this file and remove
 * the `viewScript` property from `block.json`.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-metadata/#view-script
 */

import { store, getContext } from '@wordpress/interactivity';
import { config } from '../config';
import '../stores/facets.store';
import { toInteger, applyPrefix } from '../utils/conversions';
import { delayedSearch } from '../utils/delayedSearch';
const RANGE_SLIDER_PREFIX = 'range_';

export const { actions, state } = store(config.facetsStoreName, {
  actions: {
    initRangeFacet: function () {
      actions.initFacets();
    },
    setFromRangeValue: function (e) {
      e.preventDefault();
      let value = toInteger(e.target.value);
      const { attributes } = getContext();
      const name = applyPrefix(
        attributes.selectedDataSource,
        RANGE_SLIDER_PREFIX
      );
      const toValue = attributes.range.to;

      const filterValueArray = [].concat(state.allFilters[name] || []);
      if (value > toValue) {
        attributes.range.from = toValue;
        e.target.value = toValue;
        value = toValue;
      } else {
        attributes.range.from = value;
      }
      actions.updateFilter({
        filterName: name,
        value: [value.toString(), filterValueArray[1]],
      });

      // Check if autoReload is enabled before reloading the page
      if (attributes.autoReload) {
        delayedSearch(e, 500);
      }
    },
    setToRangeValue: function (e) {
      e.preventDefault();
      let value = toInteger(e.target.value);
      const { attributes } = getContext();
      const name = applyPrefix(
        attributes.selectedDataSource,
        RANGE_SLIDER_PREFIX
      );
      const fromValue = attributes.range.from;

      const filterValueArray = [].concat(state.allFilters[name] || []);
      if (value < fromValue) {
        attributes.range.to = fromValue;
        e.target.value = fromValue;
        value = fromValue;
      } else {
        attributes.range.to = value;
      }
      if (value <= attributes.range.min) {
        e.target.style.zIndex = 2;
      } else {
        e.target.style.zIndex = 0;
      }
      actions.updateFilter({
        filterName: name,
        value: [filterValueArray[0], value.toString()],
      });

      // Check if autoReload is enabled before reloading the page
      if (attributes.autoReload) {
        delayedSearch(e, 500);
      }
    },
  },
});
