/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-i18n/
 */
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { withInstanceId } from '@wordpress/compose';
import { useEffect, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import {
  SelectControl,
  PanelBody,
  ToggleControl,
  RangeControl,
  Spinner,
  TextControl,
  FormTokenField,
  Placeholder,
  __experimentalToggleGroupControl as ToggleGroupControl,
  __experimentalToggleGroupControlOption as ToggleGroupControlOption,
} from '@wordpress/components';
import PropTypes from 'prop-types';
import axios from 'axios';
import ServerSideRender from '@wordpress/server-side-render';

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

export function Edit({ attributes, instanceId, setAttributes, name }) {
  const {
    label,
    formId,
    selectedDataSource = '',
    dataSourceType = 'taxonomy',
    excludedTerms = [],
    excludedPostTypes = [],
    selectedType = 'checkbox',
    autoReload = true,
    isSearchable = true,
    isScrollable = true,
    maxHeight = 300,
    filterMode = 'or',
    showHierarchy = false,
    defaultExpandedHierarchy = false,
  } = attributes;

  const [taxonomies, setTaxonomies] = useState([]);
  const [postTypes, setPostTypes] = useState([]);
  const [terms, setTerms] = useState([]);
  useEffect(() => {
    if (!formId) {
      setAttributes({
        formId: `wpengine-smart-dropdown-facet-${instanceId}`,
      });
    }
  }, [formId, setAttributes, instanceId]);

  useEffect(() => {
    const fetchTaxonomies = async () => {
      try {
        const response = await axios.get(
          window.wpApiSettings.root + 'wpengine-smart-search/v1/taxonomies',
          {
            headers: {
              'Content-Type': 'application/json',
              'X-WP-Nonce': window.wpApiSettings.nonce,
            },
          }
        );
        setTaxonomies(response.data);
      } catch (err) {
        // TODO: Handle error
        console.error('Error fetching taxonomies:', err);
      }
    };

    const fetchPostTypes = async () => {
      try {
        const response = await axios.get(
          window.wpApiSettings.root + 'wpengine-smart-search/v1/post-types',
          {
            headers: {
              'Content-Type': 'application/json',
              'X-WP-Nonce': window.wpApiSettings.nonce,
            },
          }
        );
        setPostTypes(response.data);
      } catch (err) {
        // TODO: Handle error
        console.error('Error fetching post types:', err);
      }
    };

    fetchTaxonomies();
    fetchPostTypes();
  }, []);

  // Update label when data source changes
  useEffect(() => {
    if (dataSourceType === 'post_type') {
      // Set label for post type mode
      setAttributes({
        label: __('Post Type', 'wpengine-smart-search'),
      });
    } else if (taxonomies.length > 0 && selectedDataSource) {
      // Set label for taxonomy mode
      const selectedTaxonomy = taxonomies.find(
        (tax) => tax.name === selectedDataSource
      );
      if (selectedTaxonomy) {
        setAttributes({
          label: selectedTaxonomy.labels.name || selectedTaxonomy.name,
        });
      }
    }
  }, [selectedDataSource, taxonomies, dataSourceType, setAttributes]);

  // Create options for select control, including an empty option
  const taxonomyOptions = [
    ...(taxonomies || []).map((taxonomy) => ({
      label: taxonomy.labels.name || taxonomy.name,
      value: taxonomy.name,
    })),
  ];

  useEffect(() => {
    const fetchTerms = async () => {
      if (dataSourceType === 'post_type') {
        // Use post types as "terms" for excludedTerms suggestions.
        setTerms(postTypes);
        return;
      }

      // Taxonomy mode - fetch terms for excludedTerms suggestions.
      let dataSource = selectedDataSource;
      if (!dataSource) {
        dataSource = 'category';
        setAttributes({ selectedDataSource: dataSource });
      }
      try {
        const response = await axios.get(
          `${window.wpApiSettings.root}wpengine-smart-search/v1/taxonomies/${dataSource}/terms`,
          {
            headers: {
              'Content-Type': 'application/json',
              'X-WP-Nonce': window.wpApiSettings.nonce,
            },
          }
        );
        setTerms(response.data);
      } catch (err) {
        console.error('Error fetching terms:', err);
      }
    };

    fetchTerms();
  }, [selectedDataSource, dataSourceType, postTypes, setAttributes]);

  return (
    <>
      <InspectorControls key="inspector">
        <PanelBody title="Settings">
          <ToggleGroupControl
            label={__('Filter By', 'wpengine-smart-search')}
            value={dataSourceType}
            onChange={(value) => {
              const updates = { dataSourceType: value };

              // Force radio mode for post types (single-selection only).
              if (value === 'post_type') {
                updates.selectedType = 'radio';
              } else if (selectedType === 'radio') {
                // Reset to checkbox when switching away from post_type
                updates.selectedType = 'checkbox';
              }

              setAttributes(updates);
            }}
            isBlock
          >
            <ToggleGroupControlOption
              value="taxonomy"
              label={__('Taxonomy', 'wpengine-smart-search')}
            />
            <ToggleGroupControlOption
              value="post_type"
              label={__('Post Type', 'wpengine-smart-search')}
            />
          </ToggleGroupControl>
          {dataSourceType === 'taxonomy' && (
            <SelectControl
              key={`taxonomy-select-${instanceId}`}
              label="Select Source"
              data-testid="data-source"
              value={selectedDataSource}
              options={taxonomyOptions}
              onChange={(value) => {
                setAttributes({ selectedDataSource: value });
              }}
              __nextHasNoMarginBottom
            />
          )}
          {dataSourceType === 'post_type' && (
            <p className="components-base-control__help">
              {__(
                'Post types are automatically filtered to show only types with published posts.',
                'wpengine-smart-search'
              )}
            </p>
          )}
          <TextControl
            label={__('Label', 'wpengine-smart-search')}
            value={label}
            onChange={(value) => setAttributes({ label: value })}
            help={__(
              'Customize the label for this filter',
              'wpengine-smart-search'
            )}
          />
          {dataSourceType === 'taxonomy' ? (
            <ToggleGroupControl
              label={__('Filter Type', 'wpengine-smart-search')}
              value={selectedType}
              onChange={(value) => setAttributes({ selectedType: value })}
              isBlock
            >
              <ToggleGroupControlOption
                value="select"
                label={__('Dropdown', 'wpengine-smart-search')}
              />
              <ToggleGroupControlOption
                value="checkbox"
                label={__('Checkbox', 'wpengine-smart-search')}
              />
            </ToggleGroupControl>
          ) : (
            <div>
              <p style={{ marginBottom: '4px', fontWeight: 500 }}>
                {__('Filter Type', 'wpengine-smart-search')}
              </p>
              <p className="components-base-control__help">
                {__(
                  'Post type filters use radio buttons (single selection only)',
                  'wpengine-smart-search'
                )}
              </p>
            </div>
          )}
          <ToggleControl
            label={__('Enable Auto Reload', 'wpengine-smart-search')}
            checked={autoReload}
            onChange={(value) => setAttributes({ autoReload: value })}
            help={__(
              'Automatically reload the page when a filter selection changes',
              'wpengine-smart-search'
            )}
          />
          <SelectControl
            label={__('Match Mode', 'wpengine-smart-search')}
            value={filterMode}
            options={[
              {
                label: __('Match any selected (OR)', 'wpengine-smart-search'),
                value: 'or',
              },
              {
                label: __('Match all selected (AND)', 'wpengine-smart-search'),
                value: 'and',
              },
            ]}
            onChange={(value) => setAttributes({ filterMode: value })}
            help={__(
              'Choose whether posts must match ANY or ALL selected terms in this facet',
              'wpengine-smart-search'
            )}
            __nextHasNoMarginBottom
          />
          {dataSourceType === 'taxonomy' && (
            <>
              <ToggleControl
                label={__('Show Hierarchy', 'wpengine-smart-search')}
                checked={showHierarchy}
                onChange={(value) => setAttributes({ showHierarchy: value })}
                help={__(
                  'Display terms in hierarchical tree structure',
                  'wpengine-smart-search'
                )}
              />
              {showHierarchy && selectedType === 'checkbox' && (
                <ToggleControl
                  label={__(
                    'Expand Hierarchy by Default',
                    'wpengine-smart-search'
                  )}
                  checked={defaultExpandedHierarchy}
                  onChange={(value) =>
                    setAttributes({ defaultExpandedHierarchy: value })
                  }
                  help={__(
                    'Show all child terms expanded on initial load',
                    'wpengine-smart-search'
                  )}
                />
              )}
              <FormTokenField
                label={__('Exclude Terms', 'wpengine-smart-search')}
                value={excludedTerms}
                suggestions={terms.map((t) => t.slug).filter(Boolean)}
                onChange={(slugs) => setAttributes({ excludedTerms: slugs })}
                help={__(
                  'Enter term slugs to hide from display (comma-separated)',
                  'wpengine-smart-search'
                )}
              />
            </>
          )}
          {dataSourceType === 'post_type' && (
            <FormTokenField
              label={__('Exclude Post Types', 'wpengine-smart-search')}
              value={excludedPostTypes}
              suggestions={postTypes.map((pt) => pt.slug).filter(Boolean)}
              onChange={(slugs) => setAttributes({ excludedPostTypes: slugs })}
              help={__(
                'Enter post type slugs to hide from display (comma-separated)',
                'wpengine-smart-search'
              )}
            />
          )}
          {selectedType === 'checkbox' && (
            <>
              <ToggleControl
                label={__('Searchable', 'wpengine-smart-search')}
                checked={isSearchable}
                onChange={(value) => setAttributes({ isSearchable: value })}
                help={__(
                  'Allow users to search through terms',
                  'wpengine-smart-search'
                )}
              />
              <ToggleControl
                label={__('Scrollable', 'wpengine-smart-search')}
                checked={isScrollable}
                onChange={(value) => setAttributes({ isScrollable: value })}
                help={__(
                  'Make the term list scrollable with a max height',
                  'wpengine-smart-search'
                )}
              />
              {isScrollable && (
                <RangeControl
                  label={__('Max Height (px)', 'wpengine-smart-search')}
                  value={maxHeight}
                  onChange={(value) => setAttributes({ maxHeight: value })}
                  min={100}
                  max={800}
                  step={10}
                />
              )}
            </>
          )}
        </PanelBody>
      </InspectorControls>
      <div {...useBlockProps()}>
        <ServerSideRender
          block={name}
          attributes={attributes}
          EmptyResponsePlaceholder={() => (
            <Placeholder
              icon="filter"
              label={__('Filter Facet', 'wpengine-smart-search')}
            >
              {__(
                'No filters available. Check your data source settings.',
                'wpengine-smart-search'
              )}
            </Placeholder>
          )}
          ErrorResponsePlaceholder={({ response }) => (
            <Placeholder
              icon="warning"
              label={__('Filter Facet Error', 'wpengine-smart-search')}
            >
              {response?.message ||
                __('Failed to render preview.', 'wpengine-smart-search')}
            </Placeholder>
          )}
          LoadingResponsePlaceholder={() => (
            <Placeholder
              icon="filter"
              label={__('Loading...', 'wpengine-smart-search')}
            >
              <Spinner />
            </Placeholder>
          )}
        />
      </div>
    </>
  );
}

Edit.propTypes = {
  attributes: PropTypes.shape({
    label: PropTypes.string,
    formId: PropTypes.string,
    className: PropTypes.string,
    hasLabel: PropTypes.bool,
    align: PropTypes.string,
    selectedDataSource: PropTypes.string,
    selectedType: PropTypes.string,
    autoReload: PropTypes.bool,
    isSearchable: PropTypes.bool,
    isScrollable: PropTypes.bool,
    maxHeight: PropTypes.number,
    filterMode: PropTypes.string,
    dataSourceType: PropTypes.string,
    excludedTerms: PropTypes.array,
    excludedPostTypes: PropTypes.array,
    showHierarchy: PropTypes.bool,
    defaultExpandedHierarchy: PropTypes.bool,
  }).isRequired,
  instanceId: PropTypes.number.isRequired,
  setAttributes: PropTypes.func.isRequired,
  name: PropTypes.string.isRequired,
};

export default withInstanceId(Edit);
