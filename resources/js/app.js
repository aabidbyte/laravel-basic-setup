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

/**
 * Alpine.js DataTable Component
 *
 * Provides frontend state management for DataTable components.
 */
import { dataTable } from "./alpine-components/datatable.js";

// Register Focus plugin and DataTable component when Alpine is initialized
document.addEventListener("alpine:init", () => {
    window.Alpine.plugin(focus);
    window.Alpine.data("dataTable", dataTable);
});

// Also register immediately if Alpine is already available
// (handles cases where this script loads after Alpine)
if (window.Alpine) {
    window.Alpine.plugin(focus);
    window.Alpine.data("dataTable", dataTable);
}
