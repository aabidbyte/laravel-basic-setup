/**
 * Two Factor Setup Trigger Alpine Component
 * Self-registers as 'twoFactorSetupTrigger'
 */
export function twoFactorSetupTrigger() {
    return {
        showModal: false,

        init() {
            this.$wire.on('open-two-factor-setup-modal', () => {
                this.showModal = true;
            });

            this.$wire.on('close-two-factor-setup-modal', () => {
                this.showModal = false;
            });
        },
    };
}

// Self-register
document.addEventListener('alpine:init', () => {
    window.Alpine.data('twoFactorSetupTrigger', twoFactorSetupTrigger);
});
