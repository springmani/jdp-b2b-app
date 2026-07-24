/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-i18n/
 */
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';
import {
  PanelBody,
  SelectControl,
  TextControl,
  Notice,
  ToggleControl,
} from '@wordpress/components';
import { useEffect, useRef, useState } from '@wordpress/element';
import PropTypes from 'prop-types';
import clsx from '../utils/clsx';
import {
  buildChatkitOptions,
  waitForChatKit,
  initializeChatkitElement,
} from './chatkit-utils';

/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#edit
 *
 * @return {Element} Element to render.
 */
export function Edit({ attributes, setAttributes, clientId }) {
  const {
    displayMode,
    fabPosition,
    customHeight,
    className,
    colorScheme,
    borderRadius,
    density,
    greeting,
    placeholder,
    attachmentsEnabled,
  } = attributes;

  const containerRef = useRef(null);
  const chatkitElementRef = useRef(null);
  const oldChatkitRef = useRef(null);

  // Debounced state for greeting and placeholder to prevent cursor jumping
  const [debouncedGreeting, setDebouncedGreeting] = useState(greeting);
  const [debouncedPlaceholder, setDebouncedPlaceholder] = useState(placeholder);

  // Debounce greeting updates
  useEffect(() => {
    const timer = setTimeout(() => {
      setDebouncedGreeting(greeting);
    }, 1500);

    return () => clearTimeout(timer);
  }, [greeting]);

  // Debounce placeholder updates
  useEffect(() => {
    const timer = setTimeout(() => {
      setDebouncedPlaceholder(placeholder);
    }, 1500);

    return () => clearTimeout(timer);
  }, [placeholder]);

  const classes = clsx(
    'wpengine-chatkit-block',
    className,
    { ...useBlockProps() }.className
  );

  // Create and configure ChatKit element
  useEffect(() => {
    // Capture the current container ref value for use in cleanup
    const container = containerRef.current;

    if (!container || typeof window.wpeChatKitSettings === 'undefined') {
      return;
    }

    let chatkit = null;

    // If there's an existing ChatKit element, we'll transition to the new one
    const hasExistingChatkit = chatkitElementRef.current !== null;

    if (hasExistingChatkit) {
      // Store reference to old element for crossfade
      oldChatkitRef.current = chatkitElementRef.current;

      // Start fading out the old element
      if (oldChatkitRef.current) {
        oldChatkitRef.current.style.transition = 'opacity 0.5s ease-in-out';
        oldChatkitRef.current.style.position = 'absolute';
        oldChatkitRef.current.style.top = '0';
        oldChatkitRef.current.style.left = '0';
        oldChatkitRef.current.style.width = '100%';
        oldChatkitRef.current.style.height = '100%';
      }
    }

    // Wait for ChatKit to be loaded
    waitForChatKit(() => {
      // Don't proceed if component unmounted
      if (!container) return;

      // Create ChatKit element
      chatkit = document.createElement('openai-chatkit');
      chatkit.id = `wpengine-chatkit-editor-${clientId}`;
      chatkitElementRef.current = chatkit;

      // Build options from attributes
      const config = {
        colorScheme,
        borderRadius,
        density,
        greeting: debouncedGreeting,
        placeholder: debouncedPlaceholder,
        attachmentsEnabled,
      };
      const options = buildChatkitOptions(config, window.wpeChatKitSettings);

      // Initialize the new element
      initializeChatkitElement(chatkit, options);

      // Start with opacity 0 if transitioning
      if (hasExistingChatkit) {
        chatkit.style.opacity = '0';
        chatkit.style.transition = 'opacity 0.3s ease-in-out';
      }

      container.appendChild(chatkit);

      // Trigger crossfade animation
      requestAnimationFrame(() => {
        requestAnimationFrame(() => {
          // Fade in new element
          if (chatkit) {
            chatkit.style.opacity = '1';
          }

          // Fade out old element
          if (oldChatkitRef.current) {
            oldChatkitRef.current.style.opacity = '0';
          }

          // Remove old element after transition completes
          setTimeout(() => {
            if (oldChatkitRef.current && oldChatkitRef.current.parentNode) {
              oldChatkitRef.current.remove();
              oldChatkitRef.current = null;
            }
          }, 300);
        });
      });
    });

    // Cleanup function - React will call this when component unmounts
    return () => {
      // Only clean up if component is unmounting, not on every re-render
      // Use the captured container value instead of containerRef.current
      if (chatkit && chatkit.parentNode && !container) {
        chatkit.remove();
      }
    };
  }, [
    clientId,
    displayMode,
    colorScheme,
    borderRadius,
    density,
    debouncedGreeting,
    debouncedPlaceholder,
    attachmentsEnabled,
  ]);

  const displayModeOptions = [
    { label: __('Inline Element', 'wpengine-smart-search'), value: 'inline' },
    {
      label: __('Floating Action Button (FAB)', 'wpengine-smart-search'),
      value: 'fab',
    },
  ];

  const fabPositionOptions = [
    {
      label: __('Bottom Right', 'wpengine-smart-search'),
      value: 'bottom-right',
    },
    { label: __('Bottom Left', 'wpengine-smart-search'), value: 'bottom-left' },
  ];

  const colorSchemeOptions = [
    { label: __('Light', 'wpengine-smart-search'), value: 'light' },
    { label: __('Dark', 'wpengine-smart-search'), value: 'dark' },
  ];

  const borderRadiusOptions = [
    { label: __('Sharp', 'wpengine-smart-search'), value: 'sharp' },
    { label: __('Medium', 'wpengine-smart-search'), value: 'medium' },
    { label: __('Round', 'wpengine-smart-search'), value: 'round' },
  ];

  const densityOptions = [
    { label: __('Compact', 'wpengine-smart-search'), value: 'compact' },
    { label: __('Normal', 'wpengine-smart-search'), value: 'normal' },
    { label: __('Spacious', 'wpengine-smart-search'), value: 'spacious' },
  ];

  return (
    <>
      <InspectorControls key="inspector">
        <PanelBody title={__('ChatKit Settings', 'wpengine-smart-search')}>
          <SelectControl
            label={__('Display Mode', 'wpengine-smart-search')}
            value={displayMode}
            options={displayModeOptions}
            onChange={(value) => setAttributes({ displayMode: value })}
            help={__(
              'Choose how ChatKit should be displayed on the page',
              'wpengine-smart-search'
            )}
            __nextHasNoMarginBottom
          />

          {displayMode === 'fab' && (
            <SelectControl
              label={__('FAB Position', 'wpengine-smart-search')}
              value={fabPosition}
              options={fabPositionOptions}
              onChange={(value) => setAttributes({ fabPosition: value })}
              help={__(
                'Position of the floating action button',
                'wpengine-smart-search'
              )}
              __nextHasNoMarginBottom
            />
          )}

          {displayMode === 'inline' && (
            <TextControl
              label={__('Custom Height', 'wpengine-smart-search')}
              value={customHeight}
              onChange={(value) => setAttributes({ customHeight: value })}
              help={__(
                'Set custom height (e.g., 600px, 80vh)',
                'wpengine-smart-search'
              )}
              __nextHasNoMarginBottom
            />
          )}
        </PanelBody>

        <PanelBody
          title={__('Theme Settings', 'wpengine-smart-search')}
          initialOpen={false}
        >
          <SelectControl
            label={__('Color Scheme', 'wpengine-smart-search')}
            value={colorScheme}
            options={colorSchemeOptions}
            onChange={(value) => setAttributes({ colorScheme: value })}
            help={__(
              'Choose between light and dark theme',
              'wpengine-smart-search'
            )}
            __nextHasNoMarginBottom
          />

          <SelectControl
            label={__('Border Radius', 'wpengine-smart-search')}
            value={borderRadius}
            options={borderRadiusOptions}
            onChange={(value) => setAttributes({ borderRadius: value })}
            help={__('Roundness of UI elements', 'wpengine-smart-search')}
            __nextHasNoMarginBottom
          />

          <SelectControl
            label={__('Density', 'wpengine-smart-search')}
            value={density}
            options={densityOptions}
            onChange={(value) => setAttributes({ density: value })}
            help={__('Spacing between elements', 'wpengine-smart-search')}
            __nextHasNoMarginBottom
          />

          <TextControl
            label={__('Greeting Message', 'wpengine-smart-search')}
            value={greeting}
            onChange={(value) => setAttributes({ greeting: value })}
            help={__(
              'Welcome message shown on start screen',
              'wpengine-smart-search'
            )}
            __nextHasNoMarginBottom
          />

          <TextControl
            label={__('Input Placeholder', 'wpengine-smart-search')}
            value={placeholder}
            onChange={(value) => setAttributes({ placeholder: value })}
            help={__(
              'Placeholder text in the message input',
              'wpengine-smart-search'
            )}
            __nextHasNoMarginBottom
          />

          <ToggleControl
            label={__('Enable Attachments', 'wpengine-smart-search')}
            checked={attachmentsEnabled}
            onChange={(value) => setAttributes({ attachmentsEnabled: value })}
            help={__(
              'Allow users to attach files to messages',
              'wpengine-smart-search'
            )}
            __nextHasNoMarginBottom
          />
        </PanelBody>
      </InspectorControls>

      <div {...useBlockProps()} className={classes}>
        {typeof window.wpeChatKitSettings === 'undefined' ||
        !window.wpeChatKitSettings.isEnabled ? (
          <div className="wpengine-chatkit-block__preview">
            <Notice status="warning" isDismissible={false}>
              <p>
                <strong>
                  {__('ChatKit Not Configured', 'wpengine-smart-search')}
                </strong>
              </p>
              <p>
                {__(
                  'ChatKit must be enabled and configured in the plugin settings before you can use this block.',
                  'wpengine-smart-search'
                )}
              </p>
            </Notice>
          </div>
        ) : displayMode === 'inline' ? (
          <div
            className="wpengine-chatkit-block__editor-wrapper"
            style={{ position: 'relative', height: customHeight }}
          >
            <div
              ref={containerRef}
              className="wpengine-chatkit-block__container"
              style={{ height: customHeight, pointerEvents: 'none' }}
            />
            <div
              className="wpengine-chatkit-block__overlay"
              style={{
                position: 'absolute',
                top: 0,
                left: 0,
                right: 0,
                bottom: 0,
                cursor: 'pointer',
                zIndex: 1,
              }}
            />
          </div>
        ) : (
          <div className="wpengine-chatkit-block__preview">
            <Notice status="info" isDismissible={false}>
              <p>
                {__(
                  'FAB mode will display as a floating button in the',
                  'wpengine-smart-search'
                )}{' '}
                <strong>
                  {fabPosition === 'bottom-right'
                    ? __('bottom right', 'wpengine-smart-search')
                    : __('bottom left', 'wpengine-smart-search')}
                </strong>{' '}
                {__('corner on the frontend.', 'wpengine-smart-search')}
              </p>
            </Notice>
            <div
              className="wpengine-chatkit-block__editor-wrapper"
              style={{
                position: 'relative',
                height: '500px',
                marginTop: '20px',
              }}
            >
              <div
                ref={containerRef}
                className="wpengine-chatkit-block__container"
                style={{ height: '500px', pointerEvents: 'none' }}
              />
              <div
                className="wpengine-chatkit-block__overlay"
                style={{
                  position: 'absolute',
                  top: 0,
                  left: 0,
                  right: 0,
                  bottom: 0,
                  cursor: 'pointer',
                  zIndex: 1,
                }}
              />
            </div>
          </div>
        )}
      </div>
    </>
  );
}

Edit.propTypes = {
  attributes: PropTypes.shape({
    displayMode: PropTypes.string,
    fabPosition: PropTypes.string,
    customHeight: PropTypes.string,
    className: PropTypes.string,
    colorScheme: PropTypes.string,
    borderRadius: PropTypes.string,
    density: PropTypes.string,
    greeting: PropTypes.string,
    placeholder: PropTypes.string,
    attachmentsEnabled: PropTypes.bool,
  }).isRequired,
  setAttributes: PropTypes.func.isRequired,
  clientId: PropTypes.string.isRequired,
};

export default Edit;
