/**
 * Theme Switcher Alpine Component
 * Self-registers as 'themeSwitcher'
 */
export function themeSwitcher(initialTheme = 'light') {
    return {
        currentTheme: initialTheme,

        toggle(event) {
            const isDark = event.target.checked;
            this.currentTheme = isDark ? 'dark' : 'light';
            this.$refs.themeInput.value = this.currentTheme;
            this.$refs.themeInput.form.submit();
        },
    };
}

// Self-register
document.addEventListener('alpine:init', () => {
    window.Alpine.data('themeSwitcher', themeSwitcher);
});
