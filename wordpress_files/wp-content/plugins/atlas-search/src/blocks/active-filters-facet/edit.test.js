import { render, screen, fireEvent } from '@testing-library/react';
import { useSelect } from '@wordpress/data';
import { useEffect } from 'react';
import Edit from './edit';

jest.mock('@wordpress/data', () => ({
  useSelect: jest.fn(),
}));

jest.mock('react', () => ({
  ...jest.requireActual('react'),
  useEffect: jest.fn(),
}));

describe('Edit Component', () => {
  const mockSetAttributes = jest.fn();

  beforeEach(() => {
    jest.clearAllMocks();
  });

  it('renders correctly with default props', async () => {
    useSelect.mockReturnValue([]);
    render(
      <Edit
        name="test-block"
        attributes={{ label: '', selectedDataSources: [] }}
        setAttributes={mockSetAttributes}
      />
    );

    expect(screen.getByRole('textbox')).toBeInTheDocument();
    expect(screen.getByRole('textbox')).toHaveValue('');
  });

  it('updates attributes when selectedDataSources changes', () => {
    const selectedDataSources = ['source1', 'source2'];
    useSelect.mockReturnValue(selectedDataSources);

    useEffect.mockImplementation((callback, deps) => {
      if (deps[0] === selectedDataSources) {
        callback();
      }
    });

    render(
      <Edit
        name="test-block"
        attributes={{ label: '', selectedDataSources: [] }}
        setAttributes={mockSetAttributes}
      />
    );

    expect(mockSetAttributes).toHaveBeenCalledWith({ selectedDataSources });
  });

  it('updates label attribute when textarea value changes', () => {
    useSelect.mockReturnValue([]);
    render(
      <Edit
        name="test-block"
        attributes={{ label: '', selectedDataSources: [] }}
        setAttributes={mockSetAttributes}
      />
    );

    const textarea = screen.getByRole('textbox');
    fireEvent.change(textarea, { target: { value: 'New Label' } });

    expect(mockSetAttributes).toHaveBeenCalledWith({
      label: 'New Label',
      selectedDataSources: [],
    });
  });

  it('renders ServerSideRender with correct attributes', () => {
    useSelect.mockReturnValue([]);
    render(
      <Edit
        name="test-block"
        attributes={{ label: '', selectedDataSources: [] }}
        setAttributes={mockSetAttributes}
      />
    );

    expect(screen.getByTestId('server-side-render')).toBeInTheDocument();
  });
});
