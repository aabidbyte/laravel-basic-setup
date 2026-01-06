/**
 * DataTable Alpine Component - Entry Point
 *
 * This file is loaded only on the app layout (authenticated pages).
 * It registers the dataTable Alpine.js component for DataTable functionality.
 *
 * @see resources/js/alpine/data/datatable.js for component implementation
 */

import { dataTable } from './alpine/data/datatable.js';

// Register DataTable component when Alpine is initialized
document.addEventListener('alpine:init', () => {
    window.Alpine.data('dataTable', dataTable);
});

// Also register immediately if Alpine is already available
// (handles cases where this script loads after Alpine)
if (window.Alpine) {
    window.Alpine.data('dataTable', dataTable);
}
