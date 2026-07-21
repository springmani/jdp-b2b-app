/**
 * ChatKit Utilities
 * Shared functions for building ChatKit options and initializing the widget
 */

/**
 * ChatKit configuration object
 */
export interface ChatkitConfig {
  colorScheme?: 'light' | 'dark';
  borderRadius?: 'sharp' | 'medium' | 'round';
  density?: 'compact' | 'normal' | 'spacious';
  greeting?: string;
  placeholder?: string;
  attachmentsEnabled?: boolean;
}

/**
 * WordPress ChatKit settings from localized script
 */
export interface WpeChatKitSettings {
  nonce: string;
  restUrl: string;
  isEnabled?: boolean;
}

/**
 * ChatKit API options
 */
interface ChatkitApiOptions {
  getClientSecret: (currentClientSecret?: string) => Promise<string>;
}

/**
 * ChatKit theme options
 */
interface ChatkitThemeOptions {
  colorScheme?: 'light' | 'dark';
  radius?: 'sharp' | 'medium' | 'round';
  density?: 'compact' | 'normal' | 'spacious';
}

/**
 * ChatKit composer options
 */
interface ChatkitComposerOptions {
  placeholder: string;
  attachments?: {
    enabled: boolean;
  };
}

/**
 * ChatKit start screen options
 */
interface ChatkitStartScreenOptions {
  greeting: string;
}

/**
 * Complete ChatKit options object
 */
export interface ChatkitOptions {
  api: ChatkitApiOptions;
  theme?: ChatkitThemeOptions;
  composer?: ChatkitComposerOptions;
  startScreen?: ChatkitStartScreenOptions;
}

/**
 * ChatKit custom element interface
 */
export interface ChatkitElement extends HTMLElement {
  setOptions: (options: ChatkitOptions) => void;
}

/**
 * Build ChatKit options from attributes/context
 *
 * @param config - Configuration object with theme and composer settings
 * @param settings - wpeChatKitSettings global object
 * @return ChatKit options object
 */
export function buildChatkitOptions(
  config: ChatkitConfig,
  settings: WpeChatKitSettings
): ChatkitOptions {
  const options: ChatkitOptions = {
    api: {
      async getClientSecret(currentClientSecret?: string): Promise<string> {
        try {
          let endpoint: string;
          let body: string | null;

          if (!currentClientSecret) {
            // Initial session creation
            endpoint = settings.restUrl + 'start';
            body = null;
          } else {
            // Session refresh
            endpoint = settings.restUrl + 'refresh';
            body = JSON.stringify({ currentClientSecret });
          }

          const response = await fetch(endpoint, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-WP-Nonce': settings.nonce,
            },
            body,
          });

          if (!response.ok) {
            const error = await response.json();
            throw new Error(error.message || 'Failed to get client secret');
          }

          const data = await response.json();
          return data.client_secret;
        } catch (error) {
          console.error('ChatKit token error:', error);
          throw error;
        }
      },
    },
  };

  // Add theme configuration
  const themeOptions: ChatkitThemeOptions = {};
  if (config.colorScheme && config.colorScheme !== 'light') {
    themeOptions.colorScheme = config.colorScheme;
  }
  if (config.borderRadius && config.borderRadius !== 'medium') {
    themeOptions.radius = config.borderRadius;
  }
  if (config.density && config.density !== 'normal') {
    themeOptions.density = config.density;
  }
  if (Object.keys(themeOptions).length > 0) {
    options.theme = themeOptions;
  }

  // Add composer configuration
  const composerOptions: ChatkitComposerOptions = {
    placeholder: config.placeholder || 'Type your message...',
  };
  if (typeof config.attachmentsEnabled !== 'undefined') {
    composerOptions.attachments = {
      enabled: config.attachmentsEnabled,
    };
  }
  options.composer = composerOptions;

  // Add start screen configuration
  if (config.greeting) {
    options.startScreen = {
      greeting: config.greeting,
    };
  }

  return options;
}

/**
 * Wait for ChatKit custom element to be defined
 *
 * @param callback - Function to call when ChatKit is ready
 */
export function waitForChatKit(callback: () => void): void {
  if (
    typeof customElements !== 'undefined' &&
    customElements.get('openai-chatkit')
  ) {
    callback();
  } else {
    setTimeout(() => waitForChatKit(callback), 100);
  }
}

/**
 * Initialize ChatKit element with options
 *
 * @param chatkitElement - The ChatKit custom element
 * @param options - ChatKit options object
 * @param maxRetries - Maximum number of retries (default: 20)
 */
export function initializeChatkitElement(
  chatkitElement: ChatkitElement,
  options: ChatkitOptions,
  maxRetries: number = 20
): void {
  let retries = 0;

  const trySetOptions = (): void => {
    if (typeof chatkitElement.setOptions === 'function') {
      try {
        chatkitElement.setOptions(options);
      } catch (error) {
        console.error('ChatKit configuration error:', error);
      }
    } else if (retries < maxRetries) {
      retries++;
      setTimeout(trySetOptions, 100);
    }
  };

  customElements.whenDefined('openai-chatkit').then(trySetOptions);
}
