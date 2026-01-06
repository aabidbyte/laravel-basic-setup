/**
 * Two Factor Setup Alpine Component
 * Self-registers as 'twoFactorSetup'
 */
export function twoFactorSetup(config = {}) {
    return {
        modalStateId: config.modalStateId || 'twoFactorSetupModalOpen',
        showVerificationStep: false,
        modalId: 'two-factor-setup',
        modalConfig: config.initialModalConfig || {},
        verificationModalConfig: config.verificationModalConfig || {},

        isOpen: true,

        init() {
            this.$watch('isOpen', (val) => {
                if (!val) {
                    this.closeModal();
                }
            });

            this.$wire.on('show-verification-step', () => {
                this.showVerificationStep = true;
                this.modalConfig = this.verificationModalConfig;
            });

            this.$wire.on('hide-verification-step', () => {
                this.showVerificationStep = false;
                this.modalConfig = config.initialModalConfig;
            });
        },

        closeModal() {
            this.isOpen = false;
            this.$wire.$parent.closeModal();
        },
    };
}

// Self-register
document.addEventListener('alpine:init', () => {
    window.Alpine.data('twoFactorSetup', twoFactorSetup);
});
