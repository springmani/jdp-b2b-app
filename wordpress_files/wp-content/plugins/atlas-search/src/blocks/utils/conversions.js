export function toInteger(value, defaultValue = 0) {
  const result = parseInt(value, 10);
  return isNaN(result) ? defaultValue : result;
}

export function applyPrefix(value, prefix) {
  if (value && value.length > 0 && !value.startsWith(prefix)) {
    return `${prefix}${value}`;
  }
  return value;
}
