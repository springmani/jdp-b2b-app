/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-i18n/
 */
import { useBlockProps } from '@wordpress/block-editor';
import { withInstanceId } from '@wordpress/compose';
import ServerSideRender from '@wordpress/server-side-render';
import { useSelect } from '@wordpress/data';
import { useEffect } from 'react';
import { isEqual } from 'lodash';

/**
 * React hook that is used to mark the block wrapper element.
 * It provides all the necessary props like the class name.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-block-editor/#useblockprops
 */

/**
 * Internal dependencies
 */

/**
 * Lets webpack process CSS, SASS or SCSS files referenced in JavaScript files.
 * Those files can contain any CSS code that gets applied to the editor.
 *
 * @see https://www.npmjs.com/package/@wordpress/scripts#using-css
 */

/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#edit
 *
 * @return {Element} Element to render.
 */

const Edit = ({ name, attributes, setAttributes }) => {
  const { label } = attributes;

  const collectSources = (blocks) => {
    const facetTypes = [
      'wpengine-smart-search/filter-facet',
      'wpengine-smart-search/range-facet',
    ];
    const sources = {};
    const traverseBlocks = (blocks) => {
      blocks.forEach((block) => {
        if (
          facetTypes.includes(block.name) &&
          block.attributes.selectedDataSource
        ) {
          sources[block.attributes.selectedDataSource] = {
            label: block.attributes?.label,
          };
        }
        traverseBlocks(block.innerBlocks);
      });
    };
    traverseBlocks(blocks);
    return sources;
  };

  const selectedDataSources = useSelect((select) => {
    const { getBlocks } = select('core/block-editor');
    return collectSources(getBlocks());
  }, []);

  useEffect(() => {
    if (selectedDataSources.length === 0) {
      return;
    }
    if (!isEqual(attributes.selectedDataSources, selectedDataSources)) {
      setAttributes({ selectedDataSources });
    }
  }, [selectedDataSources, setAttributes, attributes.selectedDataSources]);

  return (
    <>
      <div {...useBlockProps()}>
        <textarea
          className="block-editor-plain-text"
          style={{
            backgroundColor: 'transparent',
            overflow: 'hidden',
            overflowWrap: 'break-word',
            resize: 'horizontal',
            height: '26px',
          }}
          rows={1}
          value={label}
          onChange={(e) =>
            setAttributes({ ...attributes, label: e.target.value })
          }
        />
        <ServerSideRender
          block={name}
          attributes={{
            ...attributes,
            hasLabel: false,
            selectedDataSources,
          }}
          urlQueryArgs={Object.entries(selectedDataSources).reduce(
            (acc, [key, item]) => {
              acc[key] = 'sample_' + key;
              return acc;
            },
            {}
          )}
        />
      </div>
    </>
  );
};

export default withInstanceId(Edit);
