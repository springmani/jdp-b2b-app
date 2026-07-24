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

import { store } from '@wordpress/interactivity';
import { config } from '../config';
import '../stores/facets.store';
import { getFiltersFromURL } from '../utils/getFiltersFromURL';
import { delayedSearch } from '../utils/delayedSearch';

const RANGE_SLIDER_PREFIX = 'range_';

export const { actions, state } = store(config.facetsStoreName, {
  actions: {
    clearAllFilters: function (e) {
      const filters = getFiltersFromURL();
      const url = new URL(window.location.href);
      const searchParams = new URLSearchParams(url.search);
      const parentDiv = e.target.closest('[data-wp-context]');

      if (parentDiv) {
        // Get the value of the parent data-wp-context attribute
        const wpContext = JSON.parse(parentDiv.getAttribute('data-wp-context'));
        const selectedDataSources = wpContext?.attributes?.selectedDataSources;
        Array.from(searchParams.entries()).forEach(([key]) => {
          const dataSource = Object.keys(selectedDataSources).find(
            (dataSourcesKey) =>
              selectedDataSources[dataSourcesKey]?.filterName === key
          );
          if (dataSource !== undefined) {
            const filterName = selectedDataSources[dataSource]?.filterName;
            actions.updateFilter({ filterName, value: [] });
          }
        });
      } else {
        Object.entries(filters).forEach(([filterName]) => {
          actions.updateFilter({ filterName, value: [] });
        });
      }

      delayedSearch(e, 500);
    },
    hideAllFilters: function () {
      state.filtersHidden = !state.filtersHidden;
      actions.updateFilter({
        filterName: 'filtersHidden',
        value: state.filtersHidden,
      });
      const testElement = document.getElementById('hide-all-filters-btn-text');
      testElement.textContent = state.filtersHidden
        ? 'Show Filters'
        : 'Hide Filters';
    },
    removeFilter: function (e) {
      const filterData = JSON.parse(e.target.dataset.filterName);
      const filterName = filterData.name;
      const filterValue = filterData.value;

      const currentFilters = Array.isArray(state.filters[filterName])
        ? state.filters[filterName]
        : [];
      const updatedFilters = currentFilters.filter(
        (value) => value !== filterValue
      );

      actions.updateFilter({ filterName, value: updatedFilters });

      const url = new URL(window.location.href);
      const searchParams = new URLSearchParams(url.search);

      const filterParam = searchParams.get(filterName);
      if (filterParam) {
        const filterValues = filterParam.split(',');
        const updatedFilterValues = filterValues.filter(
          (value) => value !== filterValue
        );

        if (updatedFilterValues.length > 0) {
          searchParams.set(filterName, updatedFilterValues.join(','));
        } else {
          searchParams.delete(filterName);
        }
      }

      // Ensure range_price is removed from the state
      if (filterName.startsWith(RANGE_SLIDER_PREFIX)) {
        actions.updateFilter({ filterName, value: [] });
      }

      url.search = searchParams.toString();
      window.history.replaceState({}, document.title, url.toString());

      delayedSearch(e, 500);
    },
  },
});
