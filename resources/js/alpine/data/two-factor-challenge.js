/**
 * Two Factor Challenge Alpine Component
 * Self-registers as 'twoFactorChallenge'
 */
export function twoFactorChallenge(config = {}) {
    return {
        showRecoveryInput: config.showRecoveryInput || false,
        code: '',
        recovery_code: '',

        toggleInput() {
            this.showRecoveryInput = !this.showRecoveryInput;
            this.code = '';
            this.recovery_code = '';

            // Replaces $dispatch('clear-2fa-auth-code') if that was custom

            this.$nextTick(() => {
                if (this.showRecoveryInput) {
                    this.$refs.recovery_code?.focus();
                } else {
                    // Fallback to finding code input if $refs not available
                    const codeInput = document.getElementById('code');
                    codeInput?.focus();
                }
            });
        },
    };
}

// Self-register
document.addEventListener('alpine:init', () => {
    window.Alpine.data('twoFactorChallenge', twoFactorChallenge);
});
