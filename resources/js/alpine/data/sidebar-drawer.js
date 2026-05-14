/**
 * Layout sidebar drawer (daisyUI drawer) — checkbox open state for mobile nav.
 */
(function () {
    function sidebarDrawer() {
        return {
            isOpen: false,
        };
    }

    const register = () => {
        if (window.Alpine) {
            window.Alpine.data('sidebarDrawer', sidebarDrawer);
        }
    };

    if (window.Alpine) {
        register();
    } else {
        document.addEventListener('alpine:init', register);
    }
})();
