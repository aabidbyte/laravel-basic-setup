export default {
    isMobile: window.innerWidth < 1024,

    init() {
        // Single global listener for the entire application
        window.addEventListener('resize', () => {
            this.isMobile = window.innerWidth < 1024;
        });
    },

    colorClass(color, prefix = 'btn-') {
        const normalizedPrefix = prefix === 'loading-' ? 'text-' : prefix;
        const classes = {
            'btn-': {
                primary: 'btn-primary',
                secondary: 'btn-secondary',
                accent: 'btn-accent',
                neutral: 'btn-neutral',
                info: 'btn-info',
                success: 'btn-success',
                warning: 'btn-warning',
                error: 'btn-error',
            },
            'text-': {
                primary: 'text-primary',
                secondary: 'text-secondary',
                accent: 'text-accent',
                neutral: 'text-neutral',
                info: 'text-info',
                success: 'text-success',
                warning: 'text-warning',
                error: 'text-error',
            },
            'badge-': {
                primary: 'badge-primary',
                secondary: 'badge-secondary',
                accent: 'badge-accent',
                neutral: 'badge-neutral',
                info: 'badge-info',
                success: 'badge-success',
                warning: 'badge-warning',
                error: 'badge-error',
            },
            'input-': {
                primary: 'input-primary',
                secondary: 'input-secondary',
                accent: 'input-accent',
                neutral: 'input-neutral',
                info: 'input-info',
                success: 'input-success',
                warning: 'input-warning',
                error: 'input-error',
            },
            'textarea-': {
                primary: 'textarea-primary',
                secondary: 'textarea-secondary',
                accent: 'textarea-accent',
                neutral: 'textarea-neutral',
                info: 'textarea-info',
                success: 'textarea-success',
                warning: 'textarea-warning',
                error: 'textarea-error',
            },
            'checkbox-': {
                primary: 'checkbox-primary',
                secondary: 'checkbox-secondary',
                accent: 'checkbox-accent',
                neutral: 'checkbox-neutral',
                info: 'checkbox-info',
                success: 'checkbox-success',
                warning: 'checkbox-warning',
                error: 'checkbox-error',
            },
            'radio-': {
                primary: 'radio-primary',
                secondary: 'radio-secondary',
                accent: 'radio-accent',
                neutral: 'radio-neutral',
                info: 'radio-info',
                success: 'radio-success',
                warning: 'radio-warning',
                error: 'radio-error',
            },
            'toggle-': {
                primary: 'toggle-primary',
                secondary: 'toggle-secondary',
                accent: 'toggle-accent',
                neutral: 'toggle-neutral',
                info: 'toggle-info',
                success: 'toggle-success',
                warning: 'toggle-warning',
                error: 'toggle-error',
            },
            'range-': {
                primary: 'range-primary',
                secondary: 'range-secondary',
                accent: 'range-accent',
                neutral: 'range-neutral',
                info: 'range-info',
                success: 'range-success',
                warning: 'range-warning',
                error: 'range-error',
            },
            'progress-': {
                primary: 'progress-primary',
                secondary: 'progress-secondary',
                accent: 'progress-accent',
                neutral: 'progress-neutral',
                info: 'progress-info',
                success: 'progress-success',
                warning: 'progress-warning',
                error: 'progress-error',
            },
        };

        return classes[normalizedPrefix]?.[color] || '';
    },
};
