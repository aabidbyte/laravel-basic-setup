/**
 * Password Visibility Alpine Component
 * Self-registers as 'passwordVisibility'
 */
export function passwordVisibility() {
    return {
        showPassword: false,
        
        toggle() {
            this.showPassword = !this.showPassword;
        }
    };
}

// Self-register
document.addEventListener('alpine:init', () => {
    window.Alpine.data('passwordVisibility', passwordVisibility);
});
