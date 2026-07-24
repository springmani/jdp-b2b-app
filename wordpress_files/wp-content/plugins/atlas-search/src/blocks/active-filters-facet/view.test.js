// view.test.js

// Mock only the dependencies we're not testing
jest.mock('@wordpress/interactivity', () => ({
  store: jest.fn((_, storeConfig) => ({ ...storeConfig, state: {} })),
  getContext: jest.fn(),
}));

jest.mock('../utils/getFiltersFromURL', () => ({
  getFiltersFromURL: jest.fn(),
}));

jest.mock('../config', () => ({
  config: {
    facetsStoreName: 'test-store',
  },
}));

jest.mock('../stores/facets.store', () => ({
  config: {
    facetsStoreName: 'test-store',
  },
}));

const { actions, state } = require('./view');

actions.updateFilter = jest.fn();

describe('view actions', () => {
  describe('clearAllFilters', () => {
    let mockEvent;

    beforeEach(() => {
      mockEvent = {
        target: {
          closest: jest.fn(() => null),
        },
      };

      jest
        .spyOn(require('../utils/getFiltersFromURL'), 'getFiltersFromURL')
        .mockReturnValue({
          category: ['electronics', 'furniture'],
          price: ['low', 'high'],
        });

      state.filters = {
        category: ['electronics', 'furniture'],
        price: ['low', 'high'],
      };

      jest.spyOn(window.history, 'replaceState').mockImplementation(() => {});
    });

    afterEach(() => {
      jest.clearAllMocks();
    });

    it('should clear all filters and update the state when', () => {
      actions.clearAllFilters(mockEvent);

      expect(actions.updateFilter).toHaveBeenCalledWith({
        filterName: 'category',
        value: [],
      });
      expect(actions.updateFilter).toHaveBeenCalledWith({
        filterName: 'price',
        value: [],
      });
    });

    it('should call delayedSearch with the correct delay', () => {
      const delayedSearchMock = jest.spyOn(
        require('../utils/delayedSearch'),
        'delayedSearch'
      );
      actions.clearAllFilters(mockEvent);

      expect(delayedSearchMock).toHaveBeenCalledWith(mockEvent, 500);
    });
  });

  describe('removeFilter', () => {
    let mockEvent;

    beforeEach(() => {
      mockEvent = {
        target: {
          dataset: {
            filterName: JSON.stringify({
              name: 'category',
              value: 'electronics',
            }),
          },
        },
      };

      state.filters = {
        category: ['electronics', 'furniture'],
      };

      jest.spyOn(window.history, 'replaceState').mockImplementation(() => {});
    });

    afterEach(() => {
      jest.clearAllMocks();
    });

    it('should remove the filter value and update the state', () => {
      actions.removeFilter(mockEvent);

      expect(actions.updateFilter).toHaveBeenCalledWith({
        filterName: 'category',
        value: ['furniture'],
      });
    });

    it('should update the URL by removing the filter value', () => {
      const url = new URL(window.location.href);
      url.searchParams.set('category', 'electronics,furniture');
      window.history.replaceState({}, document.title, url.toString());

      actions.removeFilter(mockEvent);

      expect(window.history.replaceState).toHaveBeenCalledWith(
        {},
        document.title,
        expect.not.stringContaining('electronics')
      );
    });

    it('should clear range filters if the filter name starts with RANGE_SLIDER_PREFIX', () => {
      mockEvent.target.dataset.filterName = JSON.stringify({
        name: 'range_price',
        value: '100',
      });

      state.filters = {
        range_price: ['100', '200'],
      };

      actions.removeFilter(mockEvent);

      expect(actions.updateFilter).toHaveBeenCalledWith({
        filterName: 'range_price',
        value: [],
      });
    });

    it('should call delayedSearch with the correct delay', () => {
      const delayedSearchMock = jest.spyOn(
        require('../utils/delayedSearch'),
        'delayedSearch'
      );
      actions.removeFilter(mockEvent);

      expect(delayedSearchMock).toHaveBeenCalledWith(mockEvent, 500);
    });
  });
});
