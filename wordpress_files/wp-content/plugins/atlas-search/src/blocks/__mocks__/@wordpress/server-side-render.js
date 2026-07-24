import React from 'react';

const ServerSideRender = ({ block, attributes }) => {
  return (
    <div data-testid="server-side-render">
      Mocked {block} with attributes: {JSON.stringify(attributes)}
    </div>
  );
};

export default ServerSideRender;
