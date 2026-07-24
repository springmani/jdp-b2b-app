const baseConfig = require('@wordpress/scripts/config/jest-unit.config.js');

module.exports = {
  ...baseConfig,
  preset: null,
  setupFilesAfterEnv: ['./jest.setup.js'],
  testPathIgnorePatterns: ['/node_modules/', '<rootDir>/tests'],
  testEnvironment: 'jsdom',
  transformIgnorePatterns: [
    '/node_modules/(?!(@wordpress/.*|uuid|is-plain-obj)/)', // Include @wordpress, uuid and is-plain-obj
  ],
  transform: {
    '^.+\\.(js|jsx|ts|tsx)$': [
      'babel-jest',
      { configFile: './babel.config.js' }, // Explicit Babel config path
    ],
  },
  moduleNameMapper: {
    '^react$': require.resolve('../../node_modules/react'),
    '\\.svg$': '<rootDir>//__mocks__/@wordpress/svg.js',
    '\\.(gif|jpg|jpeg|png)$': '<rootDir>/tests/__mocks__/image.js',
    '\\.(css|scss)$': 'identity-obj-proxy',
  },
  coverageReporters: ['clover', 'html', 'text'],
  coverageDirectory: '../../coverage/blocks',
};
