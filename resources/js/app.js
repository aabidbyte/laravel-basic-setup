/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allow your team to quickly build robust real-time web applications.
 */

import './echo';

/**
 * Alpine.js Plugins
 *
 * Provides focus trap and anchor utilities for better accessibility.
 * Since Alpine.js is included with Livewire 4, we register the plugins
 * when Alpine is initialized.
 */
import anchor from '@alpinejs/anchor';
import focus from '@alpinejs/focus';

import uiStore from './alpine/store/ui';
import searchStore from './alpine/store/search';

// Register Alpine plugins when initialized
document.addEventListener('alpine:init', () => {
    window.Alpine.plugin(anchor);
    window.Alpine.plugin(focus);
    window.Alpine.store('ui', uiStore);
    window.Alpine.store('search', searchStore);
});

// Also register immediately if Alpine is already available
// (handles cases where this script loads after Alpine)
if (window.Alpine) {
    window.Alpine.plugin(anchor);
    window.Alpine.plugin(focus);
    window.Alpine.store('ui', uiStore);
    window.Alpine.store('search', searchStore);
}
