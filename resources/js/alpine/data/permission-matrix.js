export default (readonly = false) => ({
    toggleBatch(type, key) {
        if (readonly) return;

        const selector = `input[data-${type}='${key}']`;

        // Try scoped search first
        let inputs = this.$el.querySelectorAll(selector);

        // Fallback to global search within container if scoped fails
        // (This addresses issues where Alpine sometimes loses scope context in complex DOMs)
        if (!inputs.length) {
            inputs = document.querySelectorAll(
                `.permission-matrix-container ${selector}`,
            );
        }

        if (!inputs.length) return;

        // Check if all are currently checked
        // Note: inputs is a NodeList, so we convert to Array
        const inputList = Array.from(inputs);
        const allChecked = inputList.every((i) => i.checked);
        const targetState = !allChecked;

        // Toggle each input
        inputList.forEach((input) => {
            if (input.checked !== targetState) {
                input.checked = targetState;
                input.dispatchEvent(new Event('change', { bubbles: true }));
            }
        });
    },
});
