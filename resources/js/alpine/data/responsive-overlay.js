/**
 * Alpine.js Data Component: Responsive Overlay
 *
 * Shared modal overlay state and z-index management.
 */

export function responsiveOverlay(openState, initialOpen = false, useParentState = false) {
    const data = {
        zIndex: 9999,
        zIndexStyle: 'z-index: 9999;',

        init() {
            window.uiZIndexStack = window.uiZIndexStack || {
                current: 9999,
                next() {
                    return ++this.current;
                },
            };

            this.$watch(openState, (value) => {
                if (value) {
                    this.zIndex = window.uiZIndexStack.next();
                    this.syncZIndexStyle();
                }
            });

            if (this[openState] || (initialOpen && !useParentState)) {
                this.zIndex = window.uiZIndexStack.next();
                this.syncZIndexStyle();
            }
        },

        syncZIndexStyle() {
            this.zIndexStyle = 'z-index: ' + this.zIndex + ';';
        },
    };

    if (!useParentState && openState) {
        data[openState] = initialOpen;
    }

    return data;
}

const registerResponsiveOverlayComponents = () => {
    window.Alpine.data('responsiveOverlay', responsiveOverlay);
};

document.addEventListener('alpine:init', registerResponsiveOverlayComponents);

if (window.Alpine) {
    registerResponsiveOverlayComponents();
}
