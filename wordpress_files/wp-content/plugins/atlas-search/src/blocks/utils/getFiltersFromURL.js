export function getFiltersFromURL() {
  const filters = {};
  const urlParams = new URLSearchParams(window.location.search);

  urlParams.forEach((value, key) => {
    if (value.includes(',')) {
      value = value.split(',');
    } else if (value.includes('+')) {
      value = value.split('+');
    }
    filters[key] = value;
  });
  return filters;
}
