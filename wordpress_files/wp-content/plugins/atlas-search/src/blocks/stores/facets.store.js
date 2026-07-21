import { store, getElement, getContext } from '@wordpress/interactivity';
import { config } from '../config';
import { getFiltersFromURL } from '../utils/getFiltersFromURL';

export const updateURL = async (params) => {
  const url = new URL(window.location.href);
  const filterModes = getInstance()?.state?.filterModes || {};

  Array.from(url.searchParams.keys()).forEach((key) => {
    url.searchParams.delete(key);
  });

  // Set search parameters
  Object.keys(params).forEach((key) => {
    const value = params[key];
    if (
      (!Array.isArray(value) && value !== undefined && value !== null) ||
      (Array.isArray(value) && value !== null && value.length > 0)
    ) {
      const mode = filterModes[key];
      let urlValue = value;

      if (Array.isArray(value)) {
        urlValue = mode === 'and' ? value.join('+') : value.join(',');
      }

      url.searchParams.set(key, urlValue);
    }
  });
  window.location.assign(url.toString());
};

export const FACETS_STORE_KEY = '__wpengine-smart-search_FacetsStoreInstance__';

// Method to get the singleton instance
export function getInstance() {
  return globalThis[FACETS_STORE_KEY];
}

// Method to set the singleton instance
export function setInstance(instance) {
  globalThis[FACETS_STORE_KEY] = instance;
}

export const getFacetsStore = () => {
  if (!getInstance()) {
    setInstance(
      store(config.facetsStoreName, {
        state: {
          filters: [],
          filtersHidden: false,
          filterModes: {},
          get allFilters() {
            return getInstance().state.filters;
          },
        },
        actions: {
          initFacets: function () {
            const { actions, state } = getInstance();
            const filters = getFiltersFromURL();
            for (const key in filters) {
              const value = filters[key];
              if (key === 's') {
                state.searchValue = value; // Set the state to the search query
              } else if (key === 'filtersHidden') {
                const hidden = value === 'true';
                state.filtersHidden = hidden;
                actions.updateFilter({ filterName: key, value: hidden });
                continue;
              }
              actions.updateFilter({ filterName: key, value });
            }
          },
          updateFilter({ filterName, value }) {
            if (
              value === '' ||
              value === null ||
              value === undefined ||
              (Array.isArray(value) && value.length === 0)
            ) {
              delete getInstance().state.filters[filterName];
            } else {
              getInstance().state.filters[filterName] = value;
            }
          },
          initFacetMode: function () {
            const context = getContext();
            const { filterMode, selected_data_source_mapped } = context;

            if (selected_data_source_mapped && filterMode) {
              getInstance().state.filterModes[selected_data_source_mapped] =
                filterMode;
            }
          },
          setSearchValue() {
            const { ref } = getElement();
            const { value } = ref;
            getInstance().state.searchValue = value;
          },
          *performSearch(e) {
            e.preventDefault();
            const hiddenInputs = e.target.querySelectorAll(
              'input[type="hidden"]'
            );
            const hiddenParams = {};
            hiddenInputs.forEach((input) => {
              hiddenParams[input.name] = input.value;
            });

            const params = {
              ...getInstance().state.filters,
              ...hiddenParams,
            };

            // Always include search value if it exists
            const searchValue = getInstance().state.searchValue;
            if (searchValue) {
              params.s = searchValue;
            }

            yield updateURL(params);
          },
        },
      })
    );
  }
  return getInstance();
};
getFacetsStore();
