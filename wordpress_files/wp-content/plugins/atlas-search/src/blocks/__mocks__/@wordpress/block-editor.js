import React from 'react';
import PropTypes from 'prop-types';

export const useBlockProps = jest
  .fn()
  .mockReturnValue({ className: 'test-block-class' });

export const InspectorControls = ({ children }) => <div>{children}</div>;

InspectorControls.propTypes = {
  children: PropTypes.node.isRequired,
};

export const PlainText = ({ value, onChange, ...props }) => (
  <input {...props} value={value} onChange={(e) => onChange(e.target.value)} />
);
export const BlockEdit = (
  { attributes, setAttributes, ...props } // Mock BlockEdit
) => (
  <div {...props} data-testid="block-edit">
    <div>Block Edit Content (Mocked)</div>
    {/* Render InspectorControls if namespace matches */}
    {attributes.namespace === 'wpengine-smart-search/recommendations' && (
      <InspectorControls>{/* Your mocked components here */}</InspectorControls>
    )}
  </div>
);

PlainText.propTypes = {
  value: PropTypes.string.isRequired,
  onChange: PropTypes.func.isRequired,
};

BlockEdit.propTypes = {
  attributes: PropTypes.shape({
    namespace: PropTypes.string,
  }).isRequired,
  setAttributes: PropTypes.func.isRequired,
};
