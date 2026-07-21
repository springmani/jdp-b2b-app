/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-i18n/
 */
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { withInstanceId } from '@wordpress/compose';
import ServerSideRender from '@wordpress/server-side-render';
import { useEffect, useState } from '@wordpress/element';
import {
  SelectControl,
  PanelBody,
  TextControl,
  Notice,
  ToggleControl,
} from '@wordpress/components';
import PropTypes from 'prop-types';
import axios from 'axios';

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

const Edit = ({ name, instanceId, attributes, setAttributes }) => {
  const { label, selectedDataSource, autoReload } = attributes;
  const [rangeSliderSources, setRangeSliderSources] = useState([]);

  useEffect(() => {
    setAttributes({
      selectedDataSource: selectedDataSource,
      autoReload: autoReload,
    });
  }, [setAttributes, instanceId, selectedDataSource, autoReload]);

  useEffect(() => {
    const fetchRangeDataSources = async () => {
      try {
        const { data } = await axios.get(
          `${window.wpApiSettings.root}wpengine-smart-search/v1/range-data-sources`,
          {
            headers: {
              'Content-Type': 'application/json',
              'X-WP-Nonce': window.wpApiSettings.nonce,
            },
          }
        );
        setRangeSliderSources(data);

        if (data.length > 0 && data[0]?.slug !== selectedDataSource) {
          setAttributes({
            selectedDataSource: data[0].slug,
          });
        }
      } catch (err) {
        // TODO: Handle error
        console.error('Error fetching range facet data sources:', err);
      }
    };

    fetchRangeDataSources();
  }, [selectedDataSource, setAttributes]);

  const rangeSliderOptions = [
    ...(rangeSliderSources || []).map((sliderSource) => ({
      label: sliderSource.name,
      value: sliderSource.slug,
    })),
  ];

  return (
    <>
      <InspectorControls key="inspector">
        <PanelBody title="Settings">
          <SelectControl
            label="Select Source"
            data-testid="data-source"
            value={selectedDataSource}
            options={rangeSliderOptions}
            onChange={(value) => {
              setAttributes({ selectedDataSource: value });
            }}
            __nextHasNoMarginBottom
          />
          {attributes.range?.min > attributes.range?.max && (
            <Notice status="error" isDismissible={false}>
              Min value cannot be greater than Max value.
            </Notice>
          )}
          <TextControl
            label="Min Value"
            value={String(attributes.range?.min || 0)}
            onChange={(value) => {
              const intValue = parseInt(value || '0', 10);
              if (isNaN(intValue)) return;
              setAttributes({
                range: { ...attributes.range, from: intValue, min: intValue },
              });
            }}
          />
          <TextControl
            label="Max Value"
            value={String(attributes.range?.max || 0)}
            onChange={(value) => {
              const intValue = parseInt(value || '0', 10);
              if (isNaN(intValue)) return;
              setAttributes({
                range: { ...attributes.range, to: intValue, max: intValue },
              });
            }}
          />
          <ToggleControl
            label={'Enable Auto Reload'}
            checked={autoReload}
            onChange={(value) => setAttributes({ autoReload: value })}
          />
        </PanelBody>
      </InspectorControls>
      <div className="test" {...useBlockProps()}>
        <textarea
          className="block-editor-plain-text  wpengine-smart-search-slider-facet__label"
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
          }}
        />
      </div>
    </>
  );
};

Edit.propTypes = {
  attributes: PropTypes.shape({
    label: PropTypes.string,
    className: PropTypes.string,
    hasLabel: PropTypes.bool,
    align: PropTypes.string,
    range: PropTypes.object,
    selectedDataSource: PropTypes.string,
    autoReload: PropTypes.bool,
  }).isRequired,
  instanceId: PropTypes.number.isRequired,
  setAttributes: PropTypes.func.isRequired,
};

export default withInstanceId(Edit);
