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

import { store, getContext, getElement } from '@wordpress/interactivity';
import { config } from '../config';
import '../stores/facets.store';
import { getFiltersFromURL } from '../utils/getFiltersFromURL';
import { delayedSearch } from '../utils/delayedSearch';

export const { actions, state } = store(config.facetsStoreName, {
  actions: {
    initDropdownFacet: function () {
      actions.initFacets();
      const context = getContext();
      const filters = getFiltersFromURL();
      if (
        filters[context.selected_data_source_mapped] &&
        !Array.isArray(filters[context.selected_data_source_mapped])
      ) {
        context.value = filters[context.selected_data_source_mapped];
      }
      // Initialize filter mode
      actions.initFacetMode();
    },
    initCheckboxFacet: function () {
      actions.initFacets();
      const context = getContext();
      const filters = getFiltersFromURL();

      // If on a taxonomy archive page (e.g. /category/slug), the archive term
      // lives in the URL path — not the query string — so initFacets() won't
      // pick it up. Seed it into the global store so subsequent filter clicks
      // accumulate rather than replace.
      if (context.archiveFilter) {
        const existing = [
          state.allFilters[context.selected_data_source_mapped] || [],
        ].flat();
        if (!existing.includes(context.archiveFilter)) {
          existing.push(context.archiveFilter);
          actions.updateFilter({
            filterName: context.selected_data_source_mapped,
            value: existing,
          });
        }
      }

      if (filters[context.selected_data_source_mapped]) {
        context.checkedValues = Array.isArray(
          filters[context.selected_data_source_mapped]
        )
          ? filters[context.selected_data_source_mapped]
          : [filters[context.selected_data_source_mapped]];
      }

      // Initialize expansion state for hierarchical display
      if (!context.expandedTerms) {
        context.expandedTerms = {};
      }

      const showHierarchy = context.showHierarchy ?? false;

      if (showHierarchy) {
        // Capture element reference before setTimeout
        const element = getElement();
        const container = element?.ref;

        if (container) {
          // Use setTimeout to ensure DOM is ready
          setTimeout(() => {
            const allTerms = container.querySelectorAll('[data-term-id]');

            // Initialize parent expansion state from PHP-rendered attributes
            allTerms.forEach((termDiv) => {
              const termId = termDiv.getAttribute('data-term-id');
              const hasChildren =
                termDiv.getAttribute('data-has-children') === 'true';

              if (hasChildren) {
                // Read expansion state from PHP-rendered aria-expanded attribute
                const toggleButton = termDiv.querySelector(
                  '.term-expand-toggle'
                );
                if (toggleButton) {
                  const isExpanded =
                    toggleButton.getAttribute('aria-expanded') === 'true';
                  context.expandedTerms[termId] = isExpanded;
                }
              }
            });

            // Auto-expand parents of selected/checked terms
            const checkedInputs = container.querySelectorAll(
              'input[type="checkbox"]:checked'
            );
            checkedInputs.forEach((input) => {
              const termDiv = input.closest('[data-term-id]');
              if (!termDiv) return;

              const parentId = termDiv.getAttribute('data-parent-id');
              if (parentId) {
                // Expand all ancestors
                let ancestorId = parentId;
                while (ancestorId) {
                  // Mark ancestor as expanded
                  context.expandedTerms[ancestorId] = true;

                  const ancestorDiv = container.querySelector(
                    `[data-term-id="${ancestorId}"]`
                  );
                  if (ancestorDiv) {
                    // Update toggle button
                    const toggleButton = ancestorDiv.querySelector(
                      '.term-expand-toggle'
                    );
                    if (toggleButton) {
                      toggleButton.setAttribute('aria-expanded', 'true');
                    }

                    // Show this ancestor's children
                    const ancestorChildren = container.querySelectorAll(
                      `[data-parent-id="${ancestorId}"]`
                    );
                    ancestorChildren.forEach((child) => {
                      child.classList.remove('term-collapsed');
                      child.classList.add('term-expanded');
                    });

                    ancestorId = ancestorDiv.getAttribute('data-parent-id');
                  } else {
                    break;
                  }
                }
              }
            });
          }, 0);
        }
      }

      // Initialize filter mode
      actions.initFacetMode();
    },
    initRadioFacet: function () {
      actions.initFacets();
      const context = getContext();
      const filters = getFiltersFromURL();
      if (
        filters[context.selected_data_source_mapped] &&
        !Array.isArray(filters[context.selected_data_source_mapped])
      ) {
        context.value = filters[context.selected_data_source_mapped];
      }
      // Initialize filter mode
      actions.initFacetMode();
    },
    setDropdownValue: function (e) {
      e.preventDefault();
      const { value, name } = e.target;
      actions.updateFilter({ filterName: name, value });

      // Check if autoReload is enabled before reloading the page
      const context = getContext();
      if (context?.attributes?.autoReload) {
        delayedSearch(e, 500);
      }
    },
    setCheckboxValue: function (e) {
      e.preventDefault();
      const { value, name, checked } = e.target;

      // Update the filter value array
      const filterValueArray = [].concat(state.allFilters[name] || []);
      if (checked) {
        filterValueArray.includes(value) || filterValueArray.push(value);
      } else {
        // remove value from array
        filterValueArray.splice(filterValueArray.indexOf(value), 1);
      }

      // Update the filter state
      actions.updateFilter({ filterName: name, value: filterValueArray });

      // Check if autoReload is enabled before reloading the page
      const { attributes } = getContext();
      if (attributes.autoReload) {
        delayedSearch(e, 500);
      }
    },
    setRadioValue: function (e) {
      e.preventDefault();
      const { value, name } = e.target;
      actions.updateFilter({ filterName: name, value });

      // Check if autoReload is enabled before reloading the page
      const context = getContext();
      if (context?.attributes?.autoReload) {
        delayedSearch(e, 500);
      }
    },
    filterCheckboxTerms: function (e) {
      const context = getContext();
      const searchValue = e.target.value.toLowerCase();
      const container = e.target.closest('[data-wp-interactive]');
      const checkboxFields = container.querySelectorAll(
        '.wpengine-smart-checkbox-facet__field'
      );

      const visibleFields = [];

      checkboxFields.forEach((field) => {
        const termName = field.getAttribute('data-term-name');
        if (termName && termName.includes(searchValue)) {
          field.style.display = '';
          visibleFields.push(field);
        } else {
          field.style.display = 'none';
        }
      });

      // Auto-expand parents of matched children
      if (context.showHierarchy && visibleFields.length > 0) {
        visibleFields.forEach((field) => {
          const parentId = field.getAttribute('data-parent-id');
          if (parentId) {
            // Expand all ancestors
            let ancestorId = parentId;
            while (ancestorId) {
              if (!context.expandedTerms[ancestorId]) {
                context.expandedTerms[ancestorId] = true;

                const ancestorDiv = container.querySelector(
                  `[data-term-id="${ancestorId}"]`
                );
                if (ancestorDiv) {
                  const toggleButton = ancestorDiv.querySelector(
                    '.term-expand-toggle'
                  );
                  if (toggleButton) {
                    toggleButton.setAttribute('aria-expanded', 'true');
                  }

                  // Show this ancestor's children
                  const ancestorChildren = container.querySelectorAll(
                    `[data-parent-id="${ancestorId}"]`
                  );
                  ancestorChildren.forEach((child) => {
                    child.classList.remove('term-collapsed');
                    child.classList.add('term-expanded');
                  });

                  ancestorId = ancestorDiv.getAttribute('data-parent-id');
                } else {
                  break;
                }
              } else {
                break;
              }
            }
          }
        });
      }
    },
    applyFilters: function (e) {
      e.preventDefault();
      delayedSearch(e, 0);
    },
    toggleTermExpansion: function (event) {
      event.preventDefault();
      event.stopPropagation();

      const button = event.target.closest('button[data-term-id]');
      if (!button) return;

      const termId = button.getAttribute('data-term-id');
      const context = getContext();

      if (!context.expandedTerms) {
        context.expandedTerms = {};
      }

      // Toggle state
      context.expandedTerms[termId] = !context.expandedTerms[termId];
      const isExpanded = context.expandedTerms[termId];

      // Update aria-expanded
      button.setAttribute('aria-expanded', isExpanded.toString());

      // Find all descendants (children, grandchildren, etc.)
      const container = button.closest('[data-wp-interactive]');
      const toggleDescendants = (parentId, shouldExpand) => {
        const directChildren = container.querySelectorAll(
          `[data-parent-id="${parentId}"]`
        );

        directChildren.forEach((child) => {
          const childId = child.getAttribute('data-term-id');

          if (shouldExpand) {
            child.classList.remove('term-collapsed');
            child.classList.add('term-expanded');
          } else {
            child.classList.remove('term-expanded');
            child.classList.add('term-collapsed');

            // Recursively collapse grandchildren
            if (childId) {
              context.expandedTerms[childId] = false;
              const childToggle = child.querySelector('.term-expand-toggle');
              if (childToggle) {
                childToggle.setAttribute('aria-expanded', 'false');
              }
              toggleDescendants(childId, false);
            }
          }
        });
      };

      toggleDescendants(termId, isExpanded);
    },
  },
});
