/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allow your team to quickly build robust real-time web applications.
 */

import "./echo";

// Restore theme after Livewire navigation (fixes theme being removed during morphing)
(function () {
    const restoreTheme = () => {
        const theme =
            localStorage.getItem("theme") ||
            (window.matchMedia("(prefers-color-scheme: dark)").matches
                ? "dark"
                : "light");
        const currentTheme =
            document.documentElement.getAttribute("data-theme");

        if (currentTheme !== theme) {
            document.documentElement.setAttribute("data-theme", theme);
        }
    };

    // Monitor Livewire navigation and restore theme
    if (window.Livewire) {
        // Restore theme immediately after navigation
        document.addEventListener("livewire:navigated", () => {
            restoreTheme();
        });

        // Also restore theme before navigation starts (as a safety measure)
        document.addEventListener("livewire:navigating", () => {
            restoreTheme();
        });
    }
})();
