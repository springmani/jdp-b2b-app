// view.test.js
import { getContext } from '@wordpress/interactivity';

// Mock initFacets from facets.store.js so it is available on actions
const mockInitFacets = jest.fn();

// Mock only the dependencies we're not testing
jest.mock('@wordpress/interactivity', () => ({
  store: jest.fn((_, storeConfig) => {
    // Simulate WP Interactivity store merging: inject initFacets so the view's
    // initRangeFacet can call it just as it would at runtime.
    if (storeConfig.actions) {
      storeConfig.actions.initFacets = mockInitFacets;
    }
    return { ...storeConfig, state: {} };
  }),
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

describe('Range Facet View', () => {
  let mockContext = {
    attributes: {
      selectedDataSource: 'price',
      range: { min: 0, max: 100, from: 0, to: 100 },
    },
  };
  beforeEach(() => {
    mockContext = {
      attributes: {
        selectedDataSource: 'price',
        range: { min: 0, max: 100, from: 0, to: 100 },
      },
    };
    getContext.mockReturnValue(mockContext);
    state.allFilters = {
      range_price: ['0', '100'],
    };
  });

  afterEach(() => {
    jest.clearAllMocks();
  });

  describe('setFromRangeValue', () => {
    it('should update range.from and call updateFilter with correct parameters', () => {
      // Setup
      const mockEvent = {
        preventDefault: jest.fn(),
        target: { style: { zIndex: 0 }, value: '10' },
      };

      actions.updateFilter = jest.fn();

      // Execute
      actions.setFromRangeValue(mockEvent);

      // Assert
      expect(mockEvent.preventDefault).toHaveBeenCalled();
      expect(mockContext.attributes.range.from).toBe(10);
      expect(actions.updateFilter).toHaveBeenCalledWith({
        filterName: 'range_price',
        value: ['10', '100'],
      });
    });

    it('should not update range.from if value is greater than range.to', () => {
      // Setup
      const mockEvent = {
        preventDefault: jest.fn(),
        target: { style: { zIndex: 0 }, value: '110' },
      };

      actions.updateFilter = jest.fn();

      // Execute
      actions.setFromRangeValue(mockEvent);

      // Assert
      expect(mockEvent.preventDefault).toHaveBeenCalled();
      expect(mockContext.attributes.range.from).toBe(100);
      expect(actions.updateFilter).toHaveBeenCalledWith({
        filterName: 'range_price',
        value: ['100', '100'],
      });
    });
  });

  describe('setToRangeValue', () => {
    it('should update range.to and call updateFilter with correct parameters', () => {
      // Setup
      const mockEvent = {
        preventDefault: jest.fn(),
        target: { style: { zIndex: 0 }, value: '90' },
      };

      actions.updateFilter = jest.fn();

      // Execute
      actions.setToRangeValue(mockEvent);

      // Assert
      expect(mockEvent.preventDefault).toHaveBeenCalled();
      expect(mockContext.attributes.range.to).toBe(90);
      expect(actions.updateFilter).toHaveBeenCalledWith({
        filterName: 'range_price',
        value: ['0', '90'],
      });
    });

    it('should not update range.to if value is less than range.from', () => {
      // Setup
      const mockEvent = {
        preventDefault: jest.fn(),
        target: { style: { zIndex: 0 }, value: '-10' },
      };

      actions.updateFilter = jest.fn();

      // Execute
      actions.setToRangeValue(mockEvent);

      // Assert
      expect(mockEvent.preventDefault).toHaveBeenCalled();
      expect(mockContext.attributes.range.to).toBe(0);
      expect(actions.updateFilter).toHaveBeenCalledWith({
        filterName: 'range_price',
        value: ['0', '0'],
      });
    });
  });

  describe('store initialization', () => {
    it('should have required action methods', () => {
      expect(typeof actions.setFromRangeValue).toBe('function');
      expect(typeof actions.setToRangeValue).toBe('function');
      expect(typeof actions.initRangeFacet).toBe('function');
    });
  });

  describe('initRangeFacet', () => {
    it('should call initFacets to seed URL state into the store', () => {
      actions.initRangeFacet();
      expect(mockInitFacets).toHaveBeenCalledTimes(1);
    });
  });
});

describe('DOM Integration', () => {
  beforeEach(() => {
    document.body.innerHTML = `
      <input
        class="wpengine-smart-range-facet__field-from"
        name="range_from"
        type="number"
        value="0"
      />
      <input
        class="wpengine-smart-range-facet__field-to"
        name="range_to"
        type="number"
        value="100"
      />
    `;
  });

  afterEach(() => {
    document.body.innerHTML = '';
  });

  it('should handle range from change events', () => {
    // Setup
    const fromInput = document.querySelector(
      '.wpengine-smart-range-facet__field-from'
    );
    expect(fromInput).not.toBeNull();
    actions.updateFilter = jest.fn();

    // Create and dispatch change event
    const inputEvent = new Event('input');
    Object.defineProperty(inputEvent, 'target', {
      value: {
        style: { zIndex: 1 },
        value: '10',
      },
      enumerable: true,
    });

    // Execute
    actions.setFromRangeValue(inputEvent);

    // Assert
    expect(actions.updateFilter).toHaveBeenCalledWith({
      filterName: 'range_price',
      value: ['10', '100'],
    });
  });

  it('should handle range to change events', () => {
    // Setup
    const toInput = document.querySelector(
      '.wpengine-smart-range-facet__field-to'
    );
    expect(toInput).not.toBeNull();
    actions.updateFilter = jest.fn();

    // Create and dispatch change event
    const inputEvent = new Event('input');
    Object.defineProperty(inputEvent, 'target', {
      value: {
        style: { zIndex: 0 },
        value: '90',
      },
      enumerable: true,
    });

    // Execute
    actions.setToRangeValue(inputEvent);

    // Assert
    expect(actions.updateFilter).toHaveBeenCalledWith({
      filterName: 'range_price',
      value: ['0', '90'],
    });
  });
});

describe('autoReload', () => {
  const setupMocks = (autoReload) => {
    const preventDefault = jest.fn();

    const mockEvent = {
      preventDefault,
      target: {
        name: 'category',
        value: 'books',
        checked: true,
      },
    };

    const state = {
      allFilters: {
        category: [],
      },
    };

    const actions = {
      updateFilter: jest.fn(),
      performSearch: jest.fn(),
    };

    const getContext = () => ({
      attributes: {
        autoReload,
      },
    });

    const setCheckboxValue = function (e) {
      e.preventDefault();
      const { value, name, checked } = e.target;

      const filterValueArray = [].concat(state.allFilters[name] || []);
      if (checked) {
        filterValueArray.includes(value) || filterValueArray.push(value);
      } else {
        filterValueArray.splice(filterValueArray.indexOf(value), 1);
      }

      actions.updateFilter({ filterName: name, value: filterValueArray });

      const { attributes } = getContext();
      if (attributes.autoReload) {
        setTimeout(() => {
          actions.performSearch(e);
        }, 500);
      }
    };

    return { preventDefault, mockEvent, state, actions, setCheckboxValue };
  };

  it('should update filters and perform search when autoReload is true', () => {
    const { preventDefault, mockEvent, actions, setCheckboxValue } =
      setupMocks(true);

    jest.useFakeTimers(); // Control timers

    setCheckboxValue(mockEvent);

    expect(preventDefault).toHaveBeenCalled();
    expect(actions.updateFilter).toHaveBeenCalledWith({
      filterName: 'category',
      value: ['books'],
    });

    jest.advanceTimersByTime(500); // Fast-forward the timer
    expect(actions.performSearch).toHaveBeenCalledWith(mockEvent);
  });
  it('should update filters and perform search when autoReload is false', () => {
    const { preventDefault, mockEvent, actions, setCheckboxValue } =
      setupMocks(false);

    jest.useFakeTimers(); // Control timers

    setCheckboxValue(mockEvent);

    expect(preventDefault).toHaveBeenCalled();
    expect(actions.updateFilter).toHaveBeenCalledWith({
      filterName: 'category',
      value: ['books'],
    });

    jest.advanceTimersByTime(500); // Fast-forward the timer
    expect(actions.performSearch).not.toHaveBeenCalledWith(mockEvent);
  });
});
