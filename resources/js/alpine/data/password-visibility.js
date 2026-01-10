/**
 * Password Visibility Alpine Component
 * Self-registers as 'passwordVisibility'
 */
export function passwordVisibility() {
    return {
        showPassword: false,

        toggle() {
            this.showPassword = !this.showPassword;
        },

        generate() {
            const length = 16;
            const charset = {
                upper: 'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
                lower: 'abcdefghijklmnopqrstuvwxyz',
                number: '0123456789',
                symbol: '!@#$%^&*()_+~{}[]:;?<>.,/'
            };

            let password = '';
            
            // Ensure at least one of each type
            password += charset.upper[Math.floor(Math.random() * charset.upper.length)];
            password += charset.lower[Math.floor(Math.random() * charset.lower.length)];
            password += charset.number[Math.floor(Math.random() * charset.number.length)];
            password += charset.symbol[Math.floor(Math.random() * charset.symbol.length)];

            // Fill the rest randomly
            const allChars = Object.values(charset).join('');
            for (let i = password.length; i < length; i++) {
                password += allChars[Math.floor(Math.random() * allChars.length)];
            }

            // Shuffle results
            password = password.split('').sort(() => 0.5 - Math.random()).join('');

            // Set value and trigger events for Livewire/Alpine using $refs
            if (this.$refs.input) {
                this.$refs.input.value = password;
                this.$refs.input.dispatchEvent(new Event('input', { bubbles: true }));
            }
            
            // Show password so the user can see it
            this.showPassword = true;

            // Optional: notify user (if Notification exists)
            if (window.NotificationBuilder) {
                window.NotificationBuilder.make()
                    .title('Password Generated')
                    .success()
                    .send();
            }
        }
    };
}

// Self-register
document.addEventListener('alpine:init', () => {
    window.Alpine.data('passwordVisibility', passwordVisibility);
});
