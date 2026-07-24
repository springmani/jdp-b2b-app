import React from 'react';
import { render, screen, fireEvent, waitFor } from '@testing-library/react';
import '@testing-library/jest-dom/extend-expect';
import Edit from './edit';
import nock from 'nock';

describe('Filter Facet Edit Component', () => {
  const mockedTaxonomies = [
    {
      name: 'category',
      labels: { name: 'Categories' },
    },
    {
      name: 'post_tag',
      labels: { name: 'Tags' },
    },
  ];

  const mockedPostTypes = [
    {
      slug: 'post',
      name: 'Posts',
      count: 10,
    },
    {
      slug: 'page',
      name: 'Pages',
      count: 5,
    },
  ];

  const mockedTerms = [
    { name: 'Category1', slug: 'category1', count: 5 },
    { name: 'Category2', slug: 'category2', count: 3 },
  ];

  beforeEach(() => {
    global.window.wpApiSettings = {
      nonce: 'mocked_nonce_value',
      root: 'http://localhost/wp-json/',
    };

    const api = nock('http://localhost/wp-json/wpengine-smart-search/v1');

    // Mock all API endpoints that might be called
    api.persist().get('/taxonomies').reply(200, mockedTaxonomies);
    api.persist().get('/post-types').reply(200, mockedPostTypes);
    api.persist().get('/taxonomies/category/terms').reply(200, mockedTerms);
    api.persist().get('/taxonomies/post_tag/terms').reply(200, []);

    // Catch-all for any other taxonomy terms requests
    api
      .persist()
      .get(/\/taxonomies\/.*\/terms/)
      .reply(200, []);
  });

  afterEach(() => {
    nock.cleanAll();
  });

  it('should render data source select control for taxonomy mode', async () => {
    render(
      <Edit
        name="wpengine-smart-search/filter-facet"
        attributes={{
          dataSourceType: 'taxonomy',
          selectedDataSource: 'category',
        }}
        setAttributes={jest.fn()}
      />
    );

    await screen.findByText('Categories');
    const dataSourceSelect = screen.getByTestId('data-source');
    expect(dataSourceSelect).toBeInTheDocument();
    expect(screen.getByText('Categories')).toBeInTheDocument();
    expect(screen.getByText('Tags')).toBeInTheDocument();
  });

  it('should toggle autoReload with ToggleControl', async () => {
    const setAttributes = jest.fn();
    render(
      <Edit
        name="wpengine-smart-search/filter-facet"
        attributes={{ autoReload: true, dataSourceType: 'taxonomy' }}
        setAttributes={setAttributes}
      />
    );

    const toggle = await screen.findByLabelText('Enable Auto Reload');
    expect(toggle).toBeInTheDocument();
    expect(toggle).toBeChecked();

    fireEvent.click(toggle);
    expect(setAttributes).toHaveBeenCalledWith({ autoReload: false });
  });

  it('should update label text via TextControl', async () => {
    const setAttributes = jest.fn();
    render(
      <Edit
        name="wpengine-smart-search/filter-facet"
        attributes={{ label: 'Filter Label', dataSourceType: 'taxonomy' }}
        setAttributes={setAttributes}
      />
    );

    const labelInput = await screen.findByLabelText('Label');
    fireEvent.change(labelInput, { target: { value: 'New Label' } });
    expect(setAttributes).toHaveBeenCalledWith({ label: 'New Label' });
  });

  it('should toggle showHierarchy for taxonomy mode', async () => {
    const setAttributes = jest.fn();
    render(
      <Edit
        name="wpengine-smart-search/filter-facet"
        attributes={{
          dataSourceType: 'taxonomy',
          selectedDataSource: 'category',
          showHierarchy: false,
        }}
        setAttributes={setAttributes}
      />
    );

    const toggle = await screen.findByLabelText('Show Hierarchy');
    expect(toggle).toBeInTheDocument();
    expect(toggle).not.toBeChecked();

    fireEvent.click(toggle);
    expect(setAttributes).toHaveBeenCalledWith({ showHierarchy: true });
  });

  it('should toggle isSearchable for checkbox type', async () => {
    const setAttributes = jest.fn();
    render(
      <Edit
        name="wpengine-smart-search/filter-facet"
        attributes={{
          selectedType: 'checkbox',
          isSearchable: true,
          dataSourceType: 'taxonomy',
        }}
        setAttributes={setAttributes}
      />
    );

    const toggle = await screen.findByLabelText('Searchable');
    expect(toggle).toBeInTheDocument();
    expect(toggle).toBeChecked();

    fireEvent.click(toggle);
    expect(setAttributes).toHaveBeenCalledWith({ isSearchable: false });
  });

  it('should toggle isScrollable for checkbox type', async () => {
    const setAttributes = jest.fn();
    render(
      <Edit
        name="wpengine-smart-search/filter-facet"
        attributes={{
          selectedType: 'checkbox',
          isScrollable: true,
          dataSourceType: 'taxonomy',
        }}
        setAttributes={setAttributes}
      />
    );

    const toggle = await screen.findByLabelText('Scrollable');
    expect(toggle).toBeInTheDocument();
    expect(toggle).toBeChecked();

    fireEvent.click(toggle);
    expect(setAttributes).toHaveBeenCalledWith({ isScrollable: false });
  });

  it('should update maxHeight value', async () => {
    const setAttributes = jest.fn();
    render(
      <Edit
        name="wpengine-smart-search/filter-facet"
        attributes={{
          selectedType: 'checkbox',
          isScrollable: true,
          maxHeight: 300,
          dataSourceType: 'taxonomy',
        }}
        setAttributes={setAttributes}
      />
    );

    const rangeInput = await screen.findByLabelText('Max Height (px)');
    fireEvent.change(rangeInput, { target: { value: '400' } });
    expect(setAttributes).toHaveBeenCalledWith({ maxHeight: 400 });
  });

  it('should update filterMode selection', async () => {
    const setAttributes = jest.fn();
    render(
      <Edit
        name="wpengine-smart-search/filter-facet"
        attributes={{ filterMode: 'or', dataSourceType: 'taxonomy' }}
        setAttributes={setAttributes}
      />
    );

    const matchModeSelect = await screen.findByLabelText('Match Mode');
    fireEvent.change(matchModeSelect, { target: { value: 'and' } });
    expect(setAttributes).toHaveBeenCalledWith({ filterMode: 'and' });
  });

  it('should auto-update label when taxonomy changes', async () => {
    const setAttributes = jest.fn();
    render(
      <Edit
        name="wpengine-smart-search/filter-facet"
        attributes={{
          dataSourceType: 'taxonomy',
          selectedDataSource: 'category',
        }}
        setAttributes={setAttributes}
      />
    );

    await screen.findByText('Categories');

    // Label should be updated to "Categories" based on taxonomy
    expect(setAttributes).toHaveBeenCalledWith({ label: 'Categories' });
  });

  it('should auto-update label to "Post Type" when switching to post_type mode', async () => {
    const setAttributes = jest.fn();
    const { rerender } = render(
      <Edit
        name="wpengine-smart-search/filter-facet"
        attributes={{
          dataSourceType: 'taxonomy',
          selectedDataSource: 'category',
        }}
        setAttributes={setAttributes}
      />
    );

    await screen.findByText('Categories');

    // Switch to post_type mode
    rerender(
      <Edit
        name="wpengine-smart-search/filter-facet"
        attributes={{
          dataSourceType: 'post_type',
        }}
        setAttributes={setAttributes}
      />
    );

    await waitFor(() => {
      const calls = setAttributes.mock.calls;
      const labelCall = calls.find((call) => call[0].label === 'Post Type');
      expect(labelCall).toBeDefined();
    });
  });
});
