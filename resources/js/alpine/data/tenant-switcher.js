/**
 * Tenant Switcher Alpine Component
 */
(function() {
    function tenantSwitcher(config = {}) {
        return {
            switcherOpen: false,
            impersonationModalOpen: false,

            openImpersonation() {
                this.impersonationModalOpen = true;
                this.switcherOpen = false;
            },

            init() {
                // Initialization logic if needed
            }
        };
    }

    // Register with Alpine
    const register = () => {
        if (window.Alpine) {
            window.Alpine.data('tenantSwitcher', tenantSwitcher);
        }
    };

    if (window.Alpine) {
        register();
    } else {
        document.addEventListener('alpine:init', register);
    }
})();
