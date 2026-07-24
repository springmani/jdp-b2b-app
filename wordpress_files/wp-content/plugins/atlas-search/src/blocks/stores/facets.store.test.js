import { describe } from 'node:test';
import { FACETS_STORE_KEY, getFacetsStore, updateURL } from './facets.store';
import { store } from '@wordpress/interactivity';
// Mocking @wordpress/interactivity module
jest.mock('@wordpress/interactivity', () => ({
  store: jest.fn(),
  getElement: jest.fn(() => ({ ref: { value: 'test search value' } })),
}));

jest.mock('../config', () => ({
  config: { facetsStoreName: 'testFacetsStore' },
}));

jest.mock('../utils/getFiltersFromURL', () => ({
  getFiltersFromURL: jest.fn(() => ({ s: 'test', category: 'test-category' })),
}));

describe('facets.store.js', () => {
  describe('initFacets', () => {
    beforeEach(() => {
      globalThis[FACETS_STORE_KEY] = undefined;
      store.mockImplementation((name, storeConfig) => {
        return {
          state: storeConfig.state,
          actions: storeConfig.actions,
        };
      });
    });

    it('should initialize facets with filters from URL', () => {
      const facetsStore = getFacetsStore();
      facetsStore.actions.initFacets();

      const filters = facetsStore.state.filters;

      expect(filters['s']).toEqual('test');
      expect(filters['category']).toEqual('test-category');
      expect(facetsStore.state.searchValue).toBe('test');
    });
  });

  describe('updateURL function', () => {
    beforeEach(() => {
      // Mock window.location.origin and window.location.assign
      delete window.location;
      window.location = {
        origin: 'http://localhost:8000',
        href: 'http://localhost:8000/?source=google&category_name=uncategorized',
        assign: jest.fn(),
      };
    });

    afterEach(() => {
      jest.clearAllMocks();
    });

    it('should update the URL with given parameters', async () => {
      const params = {
        source: 'google',
        category_name: 'uncategorized',
        search: 'example',
      };

      await updateURL(params);

      expect(window.location.assign).toHaveBeenCalledWith(
        'http://localhost:8000/?source=google&category_name=uncategorized&search=example'
      );
    });

    it('should handle undefined and null values in parameters', async () => {
      const params = {
        source: 'google',
        category_name: 'uncategorized',
        search: null,
        anotherParam: undefined,
      };

      await updateURL(params);

      // Should only include 'category_name' in the URL
      expect(window.location.assign).toHaveBeenCalledWith(
        'http://localhost:8000/?source=google&category_name=uncategorized'
      );
    });

    it('should handle undefined and less parameters', async () => {
      const params = {
        source: 'google',
        anotherParam: undefined,
      };

      await updateURL(params);

      // Should only include 'category_name' in the URL
      expect(window.location.assign).toHaveBeenCalledWith(
        'http://localhost:8000/?source=google'
      );
    });

    it('should handle empty parameters', async () => {
      await updateURL({});

      expect(window.location.assign).toHaveBeenCalledWith(
        'http://localhost:8000/'
      );
    });

    it('should handle parameters with empty strings', async () => {
      const params = {
        category_name: '',
        search: 'test',
      };

      await updateURL(params);

      // Should set category_name to an empty string and include 'search' in the URL
      expect(window.location.assign).toHaveBeenCalledWith(
        'http://localhost:8000/?category_name=&search=test'
      );
    });
  });
});
