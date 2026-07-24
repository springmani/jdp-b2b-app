import { useState } from '@wordpress/element';
import { useBlockProps } from '@wordpress/block-editor';

const Edit = ({ attributes, setAttributes }) => {
  const {
    minPrice = 7000,
    maxPrice = 12000,
    startPrice = 7000,
    endPrice = 12000,
    step = 1,
  } = attributes;
  const [minValue, setMinValue] = useState(startPrice);
  const [maxValue, setMaxValue] = useState(endPrice);

  return (
    <div {...useBlockProps()}>
      <label>
        Price Range: ${minValue} - ${maxValue}
      </label>
      <div className="slider-container">
        <input
          type="range"
          min={minPrice}
          max={maxPrice}
          step={step}
          value={minValue}
          onChange={(e) => setMinValue(parseInt(e.target.value))}
        />
        <input
          type="range"
          min={minPrice}
          max={maxPrice}
          step={step}
          value={maxValue}
          onChange={(e) => setMaxValue(parseInt(e.target.value))}
        />
      </div>
    </div>
  );
};

export default Edit;
