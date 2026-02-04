export default {
    isMobile: window.innerWidth < 1024,

    init() {
        // Single global listener for the entire application
        window.addEventListener('resize', () => {
            this.isMobile = window.innerWidth < 1024;
        });
    },

    /**
     * Highlight search terms in text (returns token array for safe rendering)
     * Used by: select component, datatable component
     *
     * @param {string} text - Text to highlight
     * @param {string} query - Search query
     * @returns {Array<{text: string, highlight: boolean}>} Array of text tokens
     */
    highlightText(text, query) {
        if (!query || !text) {
            return [{ text: text.toString(), highlight: false }];
        }

        const textStr = text.toString();
        const queryStr = query.toString().trim();

        // Escape special regex characters
        const escapedQuery = queryStr.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        const regex = new RegExp(`(${escapedQuery})`, 'gi');

        // Split text into tokens (matching and non-matching parts)
        const tokens = [];
        let lastIndex = 0;
        let match;

        while ((match = regex.exec(textStr)) !== null) {
            // Add non-matching text before this match
            if (match.index > lastIndex) {
                tokens.push({
                    text: textStr.substring(lastIndex, match.index),
                    highlight: false,
                });
            }

            // Add the matching text
            tokens.push({
                text: match[0],
                highlight: true,
            });

            lastIndex = regex.lastIndex;
        }

        // Add any remaining non-matching text
        if (lastIndex < textStr.length) {
            tokens.push({
                text: textStr.substring(lastIndex),
                highlight: false,
            });
        }

        return tokens.length > 0 ? tokens : [{ text: textStr, highlight: false }];
    },
};
