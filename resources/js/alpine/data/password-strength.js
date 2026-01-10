export default function passwordStrength(targetId, translations = {}) {
    return {
        targetId: targetId,
        translations: translations,
        password: '',
        requirements: {
            length: false,
            lowercase: false,
            uppercase: false,
            number: false,
            symbol: false,
        },
        score: 0,
        checkInterval: null,
        inputListener: null,
        changeListener: null,

        init() {
            // Get the target input element
            const getTargetElement = () => document.getElementById(this.targetId);
            
            // Create bound event handlers so we can remove them later
            this.inputListener = (event) => {
                if (event.target.id === this.targetId) {
                    this.checkStrength(event.target.value);
                }
            };

            this.changeListener = (event) => {
                if (event.target.id === this.targetId) {
                    this.checkStrength(event.target.value);
                }
            };

            // Add event listeners
            window.addEventListener('input', this.inputListener);
            window.addEventListener('change', this.changeListener);

            // Use polling to catch value changes from Livewire
            // This is the most reliable approach for Livewire wire:model updates
            this.checkInterval = setInterval(() => {
                const target = getTargetElement();
                if (target && target.value !== this.password) {
                    this.checkStrength(target.value);
                }
            }, 100); // 100ms polling for responsive updates

            // Initial check
            this.$nextTick(() => {
                const target = getTargetElement();
                if (target && target.value) {
                    this.checkStrength(target.value);
                }
            });
        },

        checkStrength(val) {
            this.password = val;
            this.requirements.length = val.length >= 8;
            this.requirements.lowercase = /[a-z]/.test(val);
            this.requirements.uppercase = /[A-Z]/.test(val);
            this.requirements.number = /[0-9]/.test(val);
            this.requirements.symbol = /[^A-Za-z0-9]/.test(val);

            let s = 0;
            if (this.requirements.length) s++;
            if (this.requirements.lowercase && this.requirements.uppercase) s++;
            if (this.requirements.number) s++;
            if (this.requirements.symbol) s++;
            this.score = s;
        },

        get label() {
            if (this.password.length === 0) return '';
            if (this.score <= 2) return this.translations.weak || 'Weak';
            if (this.score <= 3) return this.translations.good || 'Good';
            return this.translations.strong || 'Strong';
        },

        get color() {
            if (this.score <= 2) return 'bg-error';
            if (this.score <= 3) return 'bg-warning';
            return 'bg-success';
        },

        get textColor() {
            if (this.score <= 2) return 'text-error';
            if (this.score <= 3) return 'text-warning';
            return 'text-success';
        },

        destroy() {
            // Clean up event listeners
            if (this.inputListener) {
                window.removeEventListener('input', this.inputListener);
            }
            if (this.changeListener) {
                window.removeEventListener('change', this.changeListener);
            }
            
            // Clear the polling interval
            if (this.checkInterval) {
                clearInterval(this.checkInterval);
            }
        },
    };
}
