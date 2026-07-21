import { store } from '@wordpress/interactivity';
import { config } from '../config';

// Debounce utility function
function debounce(func, wait) {
  let timeout;
  return function (...args) {
    const context = this;
    clearTimeout(timeout);
    timeout = setTimeout(() => func.apply(context, args), wait);
  };
}

// Original delayedSearch function
function performSearch(event) {
  const { actions } = store(config.facetsStoreName);
  actions.performSearch(event);
}

// Debounced version of delayedSearch
export const delayedSearch = debounce(performSearch, 500);
