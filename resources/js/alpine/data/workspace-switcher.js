/**
 * Workspace Switcher Alpine Component
 */
(function() {
    function workspaceSwitcher(config = {}) {
        return {
            switcherOpen: false,
            impersonationModalOpen: false,

            init() {
                // Initialization logic if needed
            }
        };
    }

    // Register with Alpine
    const register = () => {
        if (window.Alpine) {
            window.Alpine.data('workspaceSwitcher', workspaceSwitcher);
        }
    };

    if (window.Alpine) {
        register();
    } else {
        document.addEventListener('alpine:init', register);
    }
})();
