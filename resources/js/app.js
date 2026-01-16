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

import passwordStrength from './alpine/data/password-strength';
import InfiniteScroll from './alpine/data/infinite-scroll';
import permissionMatrix from './alpine/data/permission-matrix';
import confirmModal from './alpine/data/confirm-modal';
import submitForm from './alpine/data/submit-form';

// Register Alpine plugins when initialized
document.addEventListener('alpine:init', () => {
    window.Alpine.plugin(anchor);
    window.Alpine.plugin(focus);

    window.Alpine.data('passwordStrength', passwordStrength);
    window.Alpine.data('infiniteScroll', InfiniteScroll);
    window.Alpine.data('permissionMatrix', permissionMatrix);
    window.Alpine.data('confirmModal', confirmModal);
    window.Alpine.data('submitForm', submitForm);
});

// Also register immediately if Alpine is already available
// (handles cases where this script loads after Alpine)
if (window.Alpine) {
    window.Alpine.plugin(anchor);
    window.Alpine.plugin(focus);
    window.Alpine.data('passwordStrength', passwordStrength);
    window.Alpine.data('infiniteScroll', InfiniteScroll);
    window.Alpine.data('permissionMatrix', permissionMatrix);
    window.Alpine.data('confirmModal', confirmModal);
    window.Alpine.data('submitForm', submitForm);
}
