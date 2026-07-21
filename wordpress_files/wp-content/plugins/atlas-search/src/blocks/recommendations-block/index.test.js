import React from 'react';
import { render, screen, fireEvent } from '@testing-library/react';
import '@testing-library/jest-dom/extend-expect';
import { withQueryLoopControlsFactory } from './index';
import { select } from '@wordpress/data';

jest.mock('@wordpress/i18n', () => ({
  __: jest.fn((str) => str),
}));

jest.mock('@wordpress/blocks', () => ({
  registerBlockVariation: jest.fn(),
}));

jest.mock('@wordpress/hooks', () => ({
  addFilter: jest.fn(),
}));

jest.mock('@wordpress/data', () => ({
  select: jest.fn(),
}));

jest.mock('./index', () => ({
  ...jest.requireActual('./index'),
  isSingular: jest.fn(),
}));

describe('withQueryLoopControlsFactory', () => {
  it('should be defined', () => {
    expect(withQueryLoopControlsFactory).toBeDefined();
  });

  it('should render Recommendations Block', () => {
    const BlockEdit = () => <div>Block Edit Content</div>;
    const EnhancedBlockEdit = withQueryLoopControlsFactory()(BlockEdit);

    render(
      <EnhancedBlockEdit
        attributes={{
          namespace: 'wpengine-smart-search/recommendations',
          postLimit: 5,
          recommendationsType: 'trending',
        }}
        setAttributes={jest.fn()}
      />
    );

    expect(
      screen.getByRole('heading', { name: 'Recommendation Settings' })
    ).toBeInTheDocument();
    expect(screen.getByRole('slider', { name: 'Count' })).toBeInTheDocument();
    expect(
      screen.getByRole('combobox', { name: 'RecommendationsType' })
    ).toBeInTheDocument();
  });

  it('should call setAttributes when changing Post Limit', () => {
    const setAttributes = jest.fn();
    const BlockEdit = () => <div>Block Edit Content</div>;
    const EnhancedBlockEdit = withQueryLoopControlsFactory()(BlockEdit);

    render(
      <EnhancedBlockEdit
        attributes={{
          namespace: 'wpengine-smart-search/recommendations',
          postLimit: 5,
          recommendationsType: 'trending',
        }}
        setAttributes={setAttributes}
      />
    );

    fireEvent.change(screen.getByRole('slider', { name: 'Count' }), {
      target: { value: '10' },
    });

    expect(setAttributes).toHaveBeenCalledWith({
      postLimit: 10,
      query: { perPage: 10 },
    });
  });

  it('should call setAttributes when changing Recommendations Type', () => {
    select.mockImplementation((store) => {
      if (store === 'core/edit-site') {
        return {
          getEditedPostType: () => 'wp_template',
          getEditedPostId: () => 'postType//single-post',
        };
      }
      return {};
    });
    const setAttributes = jest.fn();
    const BlockEdit = () => <div>Block Edit Content</div>;
    const EnhancedBlockEdit = withQueryLoopControlsFactory()(BlockEdit);

    render(
      <EnhancedBlockEdit
        attributes={{
          namespace: 'wpengine-smart-search/recommendations',
          postLimit: 5,
          recommendationsType: 'trending',
        }}
        setAttributes={setAttributes}
      />
    );

    fireEvent.change(
      screen.getByRole('combobox', { name: 'RecommendationsType' }),
      { target: { value: 'related' } }
    );

    expect(setAttributes).toHaveBeenCalledWith(
      expect.objectContaining({
        recommendationsType: 'related',
      })
    );
  });
});

it('should show both Trending and Related options when single post', () => {
  select.mockImplementation((store) => {
    if (store === 'core/edit-site') {
      return {
        getEditedPostType: () => 'wp_template',
        getEditedPostId: () => 'postType//single-post',
      };
    }
    return {};
  });
  const setAttributes = jest.fn();
  const BlockEdit = () => <div>Block Edit Content</div>;
  const EnhancedBlockEdit = withQueryLoopControlsFactory()(BlockEdit);

  render(
    <EnhancedBlockEdit
      attributes={{
        namespace: 'wpengine-smart-search/recommendations',
        postLimit: 5,
        recommendationsType: 'trending',
      }}
      setAttributes={setAttributes}
    />
  );

  const options = screen.getAllByRole('option').map((opt) => opt.textContent);
  expect(options).toEqual(
    expect.arrayContaining(['Trending Posts', 'Related Posts'])
  );
});

it('should show just Trending not single post', () => {
  select.mockImplementation((store) => {
    if (store === 'core/edit-site') {
      return {
        getEditedPostType: () => 'wp_template',
        getEditedPostId: () => 'postType//home',
      };
    }
    return {};
  });
  const setAttributes = jest.fn();
  const BlockEdit = () => <div>Block Edit Content</div>;
  const EnhancedBlockEdit = withQueryLoopControlsFactory()(BlockEdit);

  render(
    <EnhancedBlockEdit
      attributes={{
        namespace: 'wpengine-smart-search/recommendations',
        postLimit: 5,
        recommendationsType: 'trending',
      }}
      setAttributes={setAttributes}
    />
  );

  const options = screen.getAllByRole('option').map((opt) => opt.textContent);
  expect(options).toEqual(expect.arrayContaining(['Trending Posts']));
});
