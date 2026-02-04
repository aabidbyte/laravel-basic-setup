export default {
    isMobile: window.innerWidth < 1024,

    init() {
        // Single global listener for the entire application
        window.addEventListener('resize', () => {
            this.isMobile = window.innerWidth < 1024;
        });
    },
};
