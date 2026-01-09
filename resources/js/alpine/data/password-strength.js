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

        init() {
            // Listen for input events on the window, filtering by targetId
            window.addEventListener('input', (event) => {
                if (event.target.id === this.targetId) {
                    this.checkStrength(event.target.value);
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
    };
}
