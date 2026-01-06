/**
 * Dropdown Alpine Component
 * Self-registers as 'dropdown'
 */
export function dropdown() {
    return {
        open: false,
        
        toggle() {
            this.open = !this.open;
        },
        
        close() {
            this.open = false;
        }
    };
}

// Self-register
document.addEventListener('alpine:init', () => {
    window.Alpine.data('dropdown', dropdown);
});
