import React from 'react';
import { __ } from '@wordpress/i18n';
import { registerBlockVariation } from '@wordpress/blocks';
import { addFilter } from '@wordpress/hooks';
import { createHigherOrderComponent } from '@wordpress/compose';
import { InspectorControls } from '@wordpress/block-editor';
import { PanelBody, RangeControl, SelectControl } from '@wordpress/components';
import PropTypes from 'prop-types';
import { select } from '@wordpress/data';
import { ReactComponent as BlockIcon } from './components/block-icon.svg';

const RECOMMENDATIONS_VARIATION = 'wpengine-smart-search/recommendations';

registerBlockVariation('core/query', {
  name: RECOMMENDATIONS_VARIATION,
  title: __('AI-Powered Recommendations', 'wpengine-smart-search'),
  description: __(
    'Fetches recommended posts from WP Engine Smart Search',
    'wpengine-smart-search'
  ),
  icon: BlockIcon,
  attributes: {
    namespace: RECOMMENDATIONS_VARIATION,

    query: {
      postType: 'post',
      offset: 0,
      exclude: [],
      inherit: false,
      perPage: 5,
      sticky: 'exclude',
    },
    postLimit: 5,
    recommendationsType: 'trending',
  },
  allowedControls: ['postType', 'sticky', 'taxQuery'],
  scope: ['inserter'],
  isActive: ['namespace'],
  innerBlocks: [
    [
      'core/post-template',
      { layout: { type: 'grid', columnCount: 3 } },
      [
        ['core/post-featured-image'],
        ['core/post-title', { level: 3, isLink: true }],
      ],
    ],
  ],
});

const addQueryLoopAttributes = (settings, name) => {
  if (name === 'core/query') {
    settings.attributes = {
      ...settings.attributes,
      postLimit: {
        type: 'number',
        default: 5,
      },
      recommendationsType: {
        type: 'string',
        default: 'trending',
      },
    };
  }
  return settings;
};
addFilter(
  'blocks.registerBlockType',
  RECOMMENDATIONS_VARIATION,
  addQueryLoopAttributes
);

export function isSingular() {
  const nonSingularTemplates = new Set([
    'home',
    'front-page',
    'archive',
    'archive-post',
    'search',
    '404',
    'category',
    'tag',
    'author',
    'date',
    'cart',
    'checkout',
    'my-account',
  ]);

  const editor = select('core/edit-site');
  const templateType = editor?.getEditedPostType();
  var templateSlug = editor?.getEditedPostId()?.split('//')[1];
  const postEditor = select('core/editor');

  //this checks if editing posts,pages,cpts directly
  if (!templateType) {
    templateSlug = postEditor?.getCurrentPostType();
  }

  if (templateType && templateType !== 'wp_template') {
    return false;
  }

  return (
    !nonSingularTemplates.has(templateSlug) ||
    templateSlug?.startsWith('single-')
  );
}

export function withQueryLoopControlsFactory() {
  const EnhancedBlockEdit = (BlockEdit) => {
    const WrappedComponent = (props) => {
      if (
        props.attributes.namespace !== 'wpengine-smart-search/recommendations'
      ) {
        return <BlockEdit {...props} />;
      }
      const { attributes, setAttributes } = props;
      const { postLimit, recommendationsType } = attributes;

      const isSinglePost = isSingular();

      const recommendationsOptions = isSinglePost
        ? [
            { label: 'Trending Posts', value: 'trending' },
            { label: 'Related Posts', value: 'related' },
          ]
        : [{ label: 'Trending Posts', value: 'trending' }];

      return (
        <>
          <InspectorControls>
            <PanelBody
              title={__('Recommendation Settings', 'wpengine-smart-search')}
              initialOpen={true}
            >
              <RangeControl
                title="PostLimit"
                label={__('Count', 'wpengine-smart-search')}
                value={postLimit}
                onChange={(newPostLimit) => {
                  setAttributes({
                    postLimit: newPostLimit,
                    query: { ...attributes.query, perPage: newPostLimit },
                  });
                }}
                min={1}
                max={20}
              />
              <SelectControl
                title="RecommendationsType"
                label={__('RecommendationsType', 'wpengine-smart-search')}
                value={recommendationsType}
                options={recommendationsOptions}
                onChange={(newValue) =>
                  setAttributes({ recommendationsType: newValue })
                }
              />
            </PanelBody>
          </InspectorControls>
          <BlockEdit {...props} />
        </>
      );
    };

    WrappedComponent.propTypes = {
      attributes: PropTypes.shape({
        namespace: PropTypes.string.isRequired,
        postLimit: PropTypes.number.isRequired,
        recommendationsType: PropTypes.string.isRequired,
      }).isRequired,
      setAttributes: PropTypes.func.isRequired,
    };
    return WrappedComponent;
  };

  return createHigherOrderComponent(EnhancedBlockEdit, 'withQueryLoopControls');
}

addFilter('editor.BlockEdit', 'core/query', withQueryLoopControlsFactory());
