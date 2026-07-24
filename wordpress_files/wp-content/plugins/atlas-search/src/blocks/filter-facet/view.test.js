// view.test.js
import { getContext } from '@wordpress/interactivity';
import { getFiltersFromURL } from '../utils/getFiltersFromURL';

// Mock the initFacetMode, initFacets and updateFilter actions from facets.store.js
const mockInitFacetMode = jest.fn();
const mockInitFacets = jest.fn();
const mockUpdateFilter = jest.fn();

// Mock only the dependencies we're not testing
jest.mock('@wordpress/interactivity', () => ({
  store: jest.fn((_, storeConfig) => {
    // Simulate WP Interactivity store merging: inject actions defined in
    // facets.store.js so they are available on the view's actions object
    if (storeConfig.actions) {
      storeConfig.actions.initFacetMode = mockInitFacetMode;
      storeConfig.actions.initFacets = mockInitFacets;
      storeConfig.actions.updateFilter = mockUpdateFilter;
    }
    return { ...storeConfig, state: { allFilters: {} } };
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

const { actions, state } = require('./view');

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

describe('Dropdown Facet View', () => {
  let mockContext = {
    selected_data_source_mapped: 'category_name',
    value: '',
  };

  beforeEach(() => {
    mockContext = {
      selected_data_source_mapped: 'category_name',
      value: '',
      filterMode: 'or',
    };
    getContext.mockReturnValue(mockContext);
  });

  afterEach(() => {
    jest.clearAllMocks();
    mockInitFacetMode.mockClear();
    mockInitFacets.mockClear();
    mockUpdateFilter.mockClear();
  });

  describe('initDropdownFacet', () => {
    it('should initialize dropdown with filter value if it exists', () => {
      // Setup
      const mockFilters = {
        category_name: 'test-category',
      };
      getFiltersFromURL.mockReturnValue(mockFilters);

      // Execute - using the actual function
      actions.initDropdownFacet();

      // Assert
      expect(mockInitFacets).toHaveBeenCalled();
      expect(getContext).toHaveBeenCalled();
      expect(getFiltersFromURL).toHaveBeenCalled();
      expect(mockContext.value).toBe('test-category');
      expect(mockInitFacetMode).toHaveBeenCalled();
    });

    it('should not set value if filter does not exist', () => {
      // Setup
      const mockFilters = {
        other_filter: 'test-value',
      };
      getFiltersFromURL.mockReturnValue(mockFilters);

      // Execute
      actions.initDropdownFacet();

      // Assert
      expect(mockInitFacets).toHaveBeenCalled();
      expect(mockContext.value).toBe('');
      expect(mockInitFacetMode).toHaveBeenCalled();
    });

    it('should not set value if filter an array', () => {
      // Setup
      const mockFilters = {
        category_name: ['test-category', 'test-category2'],
      };
      getFiltersFromURL.mockReturnValue(mockFilters);

      // Execute
      actions.initDropdownFacet();

      // Assert
      expect(mockInitFacets).toHaveBeenCalled();
      expect(mockContext.value).toBe('');
      expect(mockInitFacetMode).toHaveBeenCalled();
    });
  });

  describe('setDropdownValue', () => {
    it('should call updateFilter with correct parameters', () => {
      // Setup
      const mockEvent = {
        preventDefault: jest.fn(),
        target: {
          name: 'category',
          value: 'test-value',
        },
      };

      // Mock updateFilter since it's not part of what we're testing
      actions.updateFilter = jest.fn();

      // Execute
      actions.setDropdownValue(mockEvent);

      // Assert
      expect(mockEvent.preventDefault).toHaveBeenCalled();
      expect(actions.updateFilter).toHaveBeenCalledWith({
        filterName: 'category',
        value: 'test-value',
      });
    });

    it('should prevent default event behavior', () => {
      // Setup
      const mockEvent = {
        preventDefault: jest.fn(),
        target: {
          name: 'category',
          value: 'test-value',
        },
      };

      // Execute
      actions.setDropdownValue(mockEvent);

      // Assert
      expect(mockEvent.preventDefault).toHaveBeenCalled();
    });
  });

  describe('store initialization', () => {
    it('should initialize with correct state', () => {
      expect(state).toEqual({ allFilters: {} });
    });

    it('should have required action methods', () => {
      expect(typeof actions.initDropdownFacet).toBe('function');
      expect(typeof actions.setDropdownValue).toBe('function');
    });
  });
});

describe('Checkbox Facet View', () => {
  let mockContext;

  beforeEach(() => {
    mockContext = {
      selected_data_source_mapped: 'category_name',
      filterMode: 'or',
      archiveFilter: null,
    };
    getContext.mockReturnValue(mockContext);
    getFiltersFromURL.mockReturnValue({});
    // Restore updateFilter — earlier tests (setDropdownValue, DOM Integration)
    // overwrite actions.updateFilter with a local jest.fn(), which breaks
    // mockUpdateFilter tracking in these tests.
    actions.updateFilter = mockUpdateFilter;
    // Reset shared allFilters between tests
    Object.keys(state.allFilters).forEach((k) => delete state.allFilters[k]);
  });

  afterEach(() => {
    jest.clearAllMocks();
    mockInitFacets.mockClear();
    mockUpdateFilter.mockClear();
  });

  describe('initCheckboxFacet', () => {
    it('should call initFacets', () => {
      actions.initCheckboxFacet();
      expect(mockInitFacets).toHaveBeenCalled();
    });

    it('should not call updateFilter when archiveFilter is not set', () => {
      actions.initCheckboxFacet();
      expect(mockUpdateFilter).not.toHaveBeenCalled();
    });

    it('should seed archive term into store when archiveFilter is set', () => {
      mockContext.archiveFilter = 'my-category';
      actions.initCheckboxFacet();
      expect(mockUpdateFilter).toHaveBeenCalledWith({
        filterName: 'category_name',
        value: ['my-category'],
      });
    });

    it('should accumulate archive term alongside existing store values', () => {
      mockContext.archiveFilter = 'my-category';
      state.allFilters['category_name'] = ['other-term'];
      actions.initCheckboxFacet();
      expect(mockUpdateFilter).toHaveBeenCalledWith({
        filterName: 'category_name',
        value: ['other-term', 'my-category'],
      });
    });

    it('should not duplicate archive term if already present in store', () => {
      mockContext.archiveFilter = 'my-category';
      state.allFilters['category_name'] = ['my-category'];
      actions.initCheckboxFacet();
      expect(mockUpdateFilter).not.toHaveBeenCalled();
    });
  });
});

describe('setCheckboxValue', () => {
  it('should update filters correctly when checkbox is checked', () => {
    const { preventDefault, mockEvent, actions, setCheckboxValue, state } =
      setupMocks(false);

    // Prepopulate state with an existing value
    state.allFilters.category = ['existing-value'];

    // Update mockEvent to simulate a new value being checked
    mockEvent.target.value = 'new-value';

    // Execute
    setCheckboxValue(mockEvent);

    // Assert
    expect(preventDefault).toHaveBeenCalled();
    expect(actions.updateFilter).toHaveBeenCalledWith({
      filterName: 'category',
      value: ['existing-value', 'new-value'],
    });
  });

  it('should update filters correctly when checkbox is unchecked', () => {
    const { preventDefault, mockEvent, actions, setCheckboxValue, state } =
      setupMocks(false);

    // Prepopulate state with existing values
    state.allFilters.category = ['existing-value', 'new-value'];

    // Update mockEvent to simulate a value being unchecked
    mockEvent.target.value = 'new-value';
    mockEvent.target.checked = false;

    // Execute
    setCheckboxValue(mockEvent);

    // Assert
    expect(preventDefault).toHaveBeenCalled();
    expect(actions.updateFilter).toHaveBeenCalledWith({
      filterName: 'category',
      value: ['existing-value'],
    });
  });
});

describe('autoReload', () => {
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

describe('DOM Integration', () => {
  beforeEach(() => {
    document.body.innerHTML = `
      <select
        class="wpengine-smart-dropdown-facet__field"
        name="category"
        data-dropdown-name="category">
        <option value="">Please select...</option>
        <option value="option1">Option 1</option>
        <option value="option2">Option 2</option>
      </select>
    `;
  });

  afterEach(() => {
    document.body.innerHTML = '';
  });

  it('should handle dropdown change events', () => {
    // Setup
    const dropdown = document.querySelector(
      '.wpengine-smart-dropdown-facet__field'
    );
    expect(dropdown).not.toBeNull();
    actions.updateFilter = jest.fn();

    // Create and dispatch change event
    const changeEvent = new Event('change');
    Object.defineProperty(changeEvent, 'target', {
      value: {
        name: 'category',
        value: 'option1',
      },
      enumerable: true,
    });

    // Execute
    actions.setDropdownValue(changeEvent);

    // Assert
    expect(actions.updateFilter).toHaveBeenCalledWith({
      filterName: 'category',
      value: 'option1',
    });
  });
});

describe('Hierarchical Collapse', () => {
  beforeEach(() => {
    // Setup hierarchical DOM structure
    document.body.innerHTML = `
      <div data-wp-interactive="wpengine-smart-search-facets__store">
        <div class="wpengine-smart-checkbox-facet__field" data-term-id="1" data-has-children="true">
          <button class="term-expand-toggle" data-term-id="1" aria-expanded="false">
            <span class="expand-icon"></span>
          </button>
          <input type="checkbox" value="parent" />
          Parent
        </div>
        <div class="wpengine-smart-checkbox-facet__field term-collapsed" data-term-id="2" data-parent-id="1">
          <span class="term-spacer"></span>
          <input type="checkbox" value="child" />
          Child
        </div>
        <div class="wpengine-smart-checkbox-facet__field term-collapsed" data-term-id="3" data-parent-id="2" data-has-children="true">
          <button class="term-expand-toggle" data-term-id="3" aria-expanded="false">
            <span class="expand-icon"></span>
          </button>
          <input type="checkbox" value="grandchild-parent" />
          Grandchild Parent
        </div>
        <div class="wpengine-smart-checkbox-facet__field term-collapsed" data-term-id="4" data-parent-id="3">
          <span class="term-spacer"></span>
          <input type="checkbox" value="grandchild" />
          Grandchild
        </div>
      </div>
    `;
  });

  afterEach(() => {
    document.body.innerHTML = '';
  });

  it('should toggle term expansion on click', () => {
    const mockContext = {
      expandedTerms: { 1: false },
    };
    getContext.mockReturnValue(mockContext);

    const button = document.querySelector(
      '[data-term-id="1"].term-expand-toggle'
    );
    const mockEvent = {
      preventDefault: jest.fn(),
      stopPropagation: jest.fn(),
      target: button,
    };

    // Execute
    actions.toggleTermExpansion(mockEvent);

    // Assert
    expect(mockEvent.preventDefault).toHaveBeenCalled();
    expect(mockEvent.stopPropagation).toHaveBeenCalled();
    expect(mockContext.expandedTerms['1']).toBe(true);
    expect(button.getAttribute('aria-expanded')).toBe('true');

    const child = document.querySelector('[data-term-id="2"]');
    expect(child.classList.contains('term-expanded')).toBe(true);
    expect(child.classList.contains('term-collapsed')).toBe(false);
  });

  it('should collapse children when parent is collapsed', () => {
    const mockContext = {
      expandedTerms: { 1: true, 2: false, 3: false },
    };
    getContext.mockReturnValue(mockContext);

    // Expand first
    const child = document.querySelector('[data-term-id="2"]');
    child.classList.add('term-expanded');
    child.classList.remove('term-collapsed');

    const button = document.querySelector(
      '[data-term-id="1"].term-expand-toggle'
    );
    button.setAttribute('aria-expanded', 'true');

    const mockEvent = {
      preventDefault: jest.fn(),
      stopPropagation: jest.fn(),
      target: button,
    };

    // Execute - collapse
    actions.toggleTermExpansion(mockEvent);

    // Assert
    expect(mockContext.expandedTerms['1']).toBe(false);
    expect(child.classList.contains('term-collapsed')).toBe(true);
    expect(child.classList.contains('term-expanded')).toBe(false);
  });

  it('should recursively collapse grandchildren', () => {
    const mockContext = {
      expandedTerms: { 1: true, 2: true, 3: true },
    };
    getContext.mockReturnValue(mockContext);

    // Setup expanded state
    const child = document.querySelector('[data-term-id="2"]');
    child.classList.add('term-expanded');
    child.classList.remove('term-collapsed');

    const grandchildParent = document.querySelector('[data-term-id="3"]');
    grandchildParent.classList.add('term-expanded');
    grandchildParent.classList.remove('term-collapsed');

    const grandchild = document.querySelector('[data-term-id="4"]');
    grandchild.classList.add('term-expanded');
    grandchild.classList.remove('term-collapsed');

    const button = document.querySelector(
      '[data-term-id="1"].term-expand-toggle'
    );
    button.setAttribute('aria-expanded', 'true');

    const mockEvent = {
      preventDefault: jest.fn(),
      stopPropagation: jest.fn(),
      target: button,
    };

    // Execute - collapse parent
    actions.toggleTermExpansion(mockEvent);

    // Assert - all descendants should be collapsed
    expect(mockContext.expandedTerms['1']).toBe(false);
    expect(mockContext.expandedTerms['2']).toBe(false);
    expect(mockContext.expandedTerms['3']).toBe(false);
    expect(grandchild.classList.contains('term-collapsed')).toBe(true);
  });
});
