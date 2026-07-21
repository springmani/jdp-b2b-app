import React from 'react';
import { render, screen, fireEvent } from '@testing-library/react';
import '@testing-library/jest-dom/extend-expect';
import Edit from './edit';
import nock from 'nock';

describe('Range Slider Edit Component', () => {
  beforeEach(() => {
    global.window.wpApiSettings = {
      nonce: 'mocked_nonce_value',
      root: 'http://localhost/wp-json/',
    };
    const api = nock(window.wpApiSettings.root + 'wpengine-smart-search/v1');

    api
      .get('/range-data-sources')
      .reply(200, [{ name: 'Product Price', slug: 'price' }]);
  });
  afterEach(() => {
    nock.cleanAll();
  });
  it('should render range slider options correctly', async () => {
    render(
      <Edit
        attributes={{ selectedDataSource: 'price' }}
        setAttributes={jest.fn()}
      />
    );
    await screen.findByText('Product Price');
    const dataSourceType = screen.getByTestId('data-source');
    expect(dataSourceType).toBeInTheDocument();
    expect(screen.getByText('Product Price')).toBeInTheDocument();
  });
  it('should render min and max value input boxes correctly', async () => {
    render(
      <Edit
        attributes={{
          selectedDataSource: 'price',
          range: { min: 0, max: 100 },
        }}
        setAttributes={jest.fn()}
      />
    );
    const minValueInput = screen.getByLabelText('Min Value');
    const maxValueInput = screen.getByLabelText('Max Value');
    expect(minValueInput).toBeInTheDocument();
    expect(maxValueInput).toBeInTheDocument();
    expect(minValueInput).toHaveValue('0');
    expect(maxValueInput).toHaveValue('100');
  });

  it('should update min value correctly', async () => {
    const setAttributes = jest.fn();
    render(
      <Edit
        attributes={{
          selectedDataSource: 'price',
          range: { min: 0, max: 100 },
        }}
        setAttributes={setAttributes}
      />
    );
    const minValueInput = await screen.findByLabelText('Min Value');
    fireEvent.change(minValueInput, { target: { value: '10' } });
    expect(minValueInput).toHaveValue('10');
    expect(setAttributes).toHaveBeenCalledWith(
      expect.objectContaining({ range: expect.objectContaining({ min: 10 }) })
    );
  });

  it('should update max value correctly', async () => {
    const setAttributes = jest.fn();
    render(
      <Edit
        attributes={{
          selectedDataSource: 'price',
          range: { min: 0, max: 100 },
        }}
        setAttributes={setAttributes}
      />
    );
    const maxValueInput = await screen.findByLabelText('Max Value');
    fireEvent.change(maxValueInput, { target: { value: '90' } });
    expect(maxValueInput).toHaveValue('90');
    expect(setAttributes).toHaveBeenCalledWith(
      expect.objectContaining({ range: expect.objectContaining({ max: 90 }) })
    );
  });

  it('should show error notice when min value is greater than max value', async () => {
    render(
      <Edit
        attributes={{
          selectedDataSource: 'price',
          range: { min: 100, max: 50 },
        }}
        setAttributes={jest.fn()}
      />
    );
    const errorNotice = await screen.findByText(
      'Min value cannot be greater than Max value.'
    );
    expect(errorNotice).toBeInTheDocument();
  });
  it('should toggle the autoReload visibility with ToggleControl', async () => {
    const setAttributes = jest.fn();
    render(
      <Edit attributes={{ autoReload: true }} setAttributes={setAttributes} />
    );
    const toggle = await screen.findByRole('checkbox');
    expect(toggle).toBeInTheDocument();
    expect(toggle).toBeChecked();

    fireEvent.click(toggle);
    expect(setAttributes).toHaveBeenCalledWith({ autoReload: false });
  });
});
