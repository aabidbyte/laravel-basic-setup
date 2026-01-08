export default () => ({
    init() {
        const observer = new IntersectionObserver((entries) => {
            if (entries[0].isIntersecting) {
                this.$wire.loadMore();
            }
        }, {
            root: null,
            rootMargin: '1200px',
            threshold: 0
        });

        observer.observe(this.$el);
    }
})
