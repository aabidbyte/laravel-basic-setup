/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allow your team to quickly build robust real-time web applications.
 */

import "./echo";

/**
 * Alpine.js Focus Plugin
 *
 * Provides focus trap utilities for better accessibility.
 * Since Alpine.js is included with Livewire 4, we register the plugin
 * when Alpine is initialized.
 */
import focus from "@alpinejs/focus";

// Register Focus plugin when Alpine is initialized
document.addEventListener("alpine:init", () => {
    window.Alpine.plugin(focus);
});

// Also register immediately if Alpine is already available
// (handles cases where this script loads after Alpine)
if (window.Alpine) {
    window.Alpine.plugin(focus);
}
