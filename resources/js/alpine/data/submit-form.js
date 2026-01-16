/**
 * Submit Form Alpine Component
 *
 * CSP-safe component for disabling submit buttons during form submission.
 * Adds 'form-submitting' class to form, CSS handles disabling children.
 *
 * Usage:
 *   <form x-data="submitForm">
 *     <button type="submit">Submit</button>
 *   </form>
 *
 * CSS will automatically disable all buttons/links inside via:
 *   .form-submitting button, .form-submitting a { pointer-events: none; opacity: 0.5; }
 */
export default function submitForm() {
    return {
        init() {
            this.$el.addEventListener('submit', () => {
                this.$el.classList.add('form-submitting');
            });
        },
    };
}

// Register component when Alpine is ready
document.addEventListener('alpine:init', () => {
    window.Alpine.data('submitForm', submitForm);
});

// Also register immediately if Alpine is already available
if (window.Alpine) {
    window.Alpine.data('submitForm', submitForm);
}
