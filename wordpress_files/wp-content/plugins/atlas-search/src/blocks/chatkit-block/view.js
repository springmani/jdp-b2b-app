/**
 * ChatKit Block - Frontend JavaScript
 * Uses WordPress Interactivity API for initialization and FAB toggle
 */

/* global wpeChatKitSettings */

import { store, getContext } from '@wordpress/interactivity';
import {
  buildChatkitOptions,
  waitForChatKit,
  initializeChatkitElement,
} from './chatkit-utils';

store('wpengine-chatkit-block', {
  actions: {
    /**
     * Initialize ChatKit for this block instance
     */
    init: function () {
      // Check if settings are available
      if (typeof wpeChatKitSettings === 'undefined') {
        console.error('ChatKit settings not found');
        return;
      }

      // Get the context with chatkit configuration and block ID
      const context = getContext();
      const { blockId } = context;

      waitForChatKit(() => {
        // Find the chatkit element by its unique ID
        const chatkitElement = document.getElementById(blockId);
        if (!chatkitElement) return;

        // Build options from context and initialize
        const options = buildChatkitOptions(context, wpeChatKitSettings);
        initializeChatkitElement(chatkitElement, options);
      });
    },

    /**
     * Toggle FAB widget visibility
     */
    toggleFab: function (event) {
      const context = getContext();

      // Toggle state
      context.isOpen = !context.isOpen;

      // Find widget and button from the event target
      const button = event.target.closest('[data-chatkit-toggle]');
      const container = button.closest(
        '.wpengine-chatkit-block__fab-container'
      );
      const widget = container.querySelector('.wpengine-chatkit-block__widget');

      if (!widget || !button) return;

      if (context.isOpen) {
        widget.classList.remove('wpengine-chatkit-block__widget--hidden');
        widget.classList.add('wpengine-chatkit-block__widget--visible');
        button.setAttribute('aria-label', 'Close chat');
      } else {
        widget.classList.remove('wpengine-chatkit-block__widget--visible');
        widget.classList.add('wpengine-chatkit-block__widget--hidden');
        button.setAttribute('aria-label', 'Open chat');
      }
    },
  },
});
