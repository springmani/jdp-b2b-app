import React from 'react';
import PropTypes from 'prop-types';

export const PanelBody = ({ title, children }) => (
  <div className="panel-body">
    <h2>{title}</h2>
    {children}
  </div>
);

PanelBody.propTypes = {
  title: PropTypes.string.isRequired,
  children: PropTypes.node.isRequired,
};

export const TextControl = ({ value, label, onChange, ...props }) => {
  const [inputValue, setInputValue] = React.useState(value);

  const handleChange = (e) => {
    setInputValue(e.target.value);
    onChange(e.target.value);
  };

  return (
    <label>
      {label}
      <input
        type="text"
        {...props}
        value={inputValue}
        onChange={handleChange}
      />
    </label>
  );
};

TextControl.propTypes = {
  value: PropTypes.string,
  onChange: PropTypes.func.isRequired,
  label: PropTypes.string,
};

export const ToggleControl = ({ checked, onChange, label }) => (
  <label className="toggle-control">
    <input
      type="checkbox"
      checked={checked}
      onChange={() => onChange(!checked)}
    />
    {label}
  </label>
);

ToggleControl.propTypes = {
  checked: PropTypes.bool.isRequired,
  onChange: PropTypes.func.isRequired,
  label: PropTypes.string,
};

export const SelectControl = ({
  value,
  onChange,
  options,
  label,
  ...props
}) => {
  delete props['__nextHasNoMarginBottom'];
  return (
    <label>
      {label}
      <select
        key={Math.random().toString(36).substring(2, 15)}
        {...props}
        value={value}
        onChange={(e) => onChange(e.target.value)}
      >
        {options.map((option, index) => (
          <option key={`${option.value}-${index}`} value={option.value}>
            {option.label}
          </option>
        ))}
      </select>
    </label>
  );
};

SelectControl.propTypes = {
  value: PropTypes.string.isRequired,
  onChange: PropTypes.func.isRequired,
  options: PropTypes.arrayOf(
    PropTypes.shape({
      value: PropTypes.string,
      label: PropTypes.string,
    })
  ).isRequired,
  label: PropTypes.string,
  __nextHasNoMarginBottom: PropTypes.bool,
};

export const RangeControl = ({ value, onChange, label, ...props }) => {
  const input = (
    <input
      type="range"
      {...props}
      value={value}
      onChange={(e) => onChange(parseInt(e.target.value))}
    />
  );

  return label ? (
    <label>
      {label}
      {input}
    </label>
  ) : (
    input
  );
};

RangeControl.propTypes = {
  value: PropTypes.number.isRequired,
  onChange: PropTypes.func.isRequired,
  label: PropTypes.string,
};

export const Notice = ({ status, isDismissible, children }) => (
  <div className={`notice notice-${status}`}>
    {isDismissible && <button className="notice-dismiss">Dismiss</button>}
    {children}
  </div>
);

Notice.propTypes = {
  status: PropTypes.string.isRequired,
  isDismissible: PropTypes.bool,
  children: PropTypes.node.isRequired,
};

export const Spinner = () => <div className="spinner">Loading...</div>;

export const __experimentalToggleGroupControl = ({
  value,
  onChange,
  label,
  children,
}) => (
  <div className="toggle-group-control">
    {label && <label>{label}</label>}
    <div className="toggle-group-control__options">{children}</div>
  </div>
);

__experimentalToggleGroupControl.propTypes = {
  value: PropTypes.string,
  onChange: PropTypes.func,
  label: PropTypes.string,
  children: PropTypes.node,
};

export const __experimentalToggleGroupControlOption = ({ value, label }) => (
  <button type="button" value={value}>
    {label}
  </button>
);

__experimentalToggleGroupControlOption.propTypes = {
  value: PropTypes.string.isRequired,
  label: PropTypes.string.isRequired,
};

export const FormTokenField = ({
  value = [],
  onChange,
  suggestions = [],
  label,
}) => (
  <div className="form-token-field">
    {label && <label>{label}</label>}
    <input
      type="text"
      value={value.join(', ')}
      onChange={(e) => {
        const tokens = e.target.value
          .split(',')
          .map((t) => t.trim())
          .filter(Boolean);
        onChange(tokens);
      }}
      data-suggestions={suggestions.join(',')}
    />
  </div>
);

FormTokenField.propTypes = {
  value: PropTypes.array,
  onChange: PropTypes.func.isRequired,
  suggestions: PropTypes.array,
  label: PropTypes.string,
};

export const Placeholder = ({ icon, label, children }) => (
  <div className="placeholder">
    {icon && <span className={`icon-${icon}`}>{icon}</span>}
    {label && <strong>{label}</strong>}
    {children}
  </div>
);

Placeholder.propTypes = {
  icon: PropTypes.string,
  label: PropTypes.string,
  children: PropTypes.node,
};
