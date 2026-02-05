/**
 * Centralized Search Store
 *
 * Provides reusable search and highlight functionality across the application.
 * Used by: select component, datatable component, and any future search implementations.
 *
 * @module search
 */
export default {
    /**
     * Default configuration for search operations
     */
    config: {
        chunkSize: 50, // Items to process per chunk
        syncThreshold: 100, // Sync vs async filtering threshold
        highlightClass: 'bg-warning/30 rounded',
        caseSensitive: false,
    },

    /**
     * Highlight search terms in HTML content (CSP-safe)
     *
     * Parses HTML, highlights text nodes only, preserving HTML structure.
     * Returns highlighted HTML as a string.
     *
     * **Security Note:** This is safe to use with innerHTML because:
     * - The HTML is already sanitized by server-side rendering (renderColumn)
     * - We only modify TEXT_NODEs, never adding executable code
     * - Plain text input is automatically treated as a single text node
     *
     * **Works for both plain text AND HTML:**
     * - Plain text: "Hello World" → becomes a text node, gets highlighted
     * - HTML: "<span>Hello World</span>" → span preserved, text inside highlighted
     *
     * @param {string} html - HTML or plain text content to highlight
     * @param {string} query - Search query to highlight
     * @returns {string} HTML with highlighted matches
     *
     * @example
     * // Plain text
     * $store.search.highlightHTML('Hello World', 'world')
     * // Returns: 'Hello <mark class="bg-warning/30 rounded">World</mark>'
     *
     * @example
     * // HTML content
     * $store.search.highlightHTML('<span>Hello World</span>', 'world')
     * // Returns: '<span>Hello <mark class="bg-warning/30 rounded">World</mark></span>'
     */
    highlightHTML(html, query) {
        if (!query || !html) {
            return html.toString();
        }

        const queryStr = query.toString().trim();
        const escapedQuery = this.escapeRegex(queryStr);
        const regex = new RegExp('(' + escapedQuery + ')', 'gi');

        // Create a temporary container to parse HTML
        const temp = document.createElement('div');
        temp.innerHTML = html;

        // Recursively highlight text nodes
        const highlightNode = (node) => {
            if (node.nodeType === Node.TEXT_NODE) {
                const text = node.textContent;
                if (regex.test(text)) {
                    const fragment = document.createDocumentFragment();
                    let lastIndex = 0;
                    let match;
                    regex.lastIndex = 0; // Reset regex

                    while ((match = regex.exec(text)) !== null) {
                        // Add text before match
                        if (match.index > lastIndex) {
                            fragment.appendChild(
                                document.createTextNode(
                                    text.substring(lastIndex, match.index),
                                ),
                            );
                        }

                        // Add highlighted match
                        const mark = document.createElement('mark');
                        mark.className = this.config.highlightClass;
                        mark.textContent = match[0];
                        fragment.appendChild(mark);

                        lastIndex = regex.lastIndex;
                    }

                    // Add remaining text
                    if (lastIndex < text.length) {
                        fragment.appendChild(
                            document.createTextNode(text.substring(lastIndex)),
                        );
                    }

                    node.parentNode.replaceChild(fragment, node);
                }
            } else if (node.nodeType === Node.ELEMENT_NODE) {
                // Recursively process child nodes
                Array.from(node.childNodes).forEach(highlightNode);
            }
        };

        highlightNode(temp);
        return temp.innerHTML;
    },

    /**
     * Filter options array with async chunking for large datasets
     *
     * For small lists (<=syncThreshold), filters synchronously.
     * For large lists, uses async chunking with requestIdleCallback to avoid blocking UI.
     *
     * @param {Array<[any, string]>} options - Array of [value, label] pairs
     * @param {string} query - Search query
     * @param {Object} config - Configuration overrides
     * @param {number} config.chunkSize - Items per chunk (default: 50)
     * @param {number} config.syncThreshold - Sync vs async threshold (default: 100)
     * @param {boolean} config.caseSensitive - Case-sensitive search (default: false)
     * @returns {Object} Object with sync results and async promise
     *
     * @example
     * // Synchronous filtering (small list)
     * const result = $store.search.filterOptions(options, 'search');
     * if (result.sync) {
     *     return result.results; // Immediate results
     * }
     *
     * @example
     * // Asynchronous filtering (large list)
     * const result = $store.search.filterOptions(largeOptions, 'search');
     * // result.results contains first chunk immediately
     * // result.promise resolves with all results
     */
    filterOptions(options, query, config = {}) {
        const cfg = { ...this.config, ...config };

        if (!query || !query.trim()) {
            return {
                sync: true,
                results: options,
                promise: Promise.resolve(options),
            };
        }

        const queryLower = cfg.caseSensitive ? query : query.toLowerCase();

        // Small list: filter synchronously
        if (options.length <= cfg.syncThreshold) {
            const results = options.filter(function (option) {
                const label = cfg.caseSensitive
                    ? option[1]
                    : option[1].toString().toLowerCase();
                return label.includes(queryLower);
            });

            return {
                sync: true,
                results: results,
                promise: Promise.resolve(results),
            };
        }

        // Large list: filter asynchronously with chunking
        return this._filterAsync(options, queryLower, cfg);
    },

    /**
     * Internal async filtering with chunking
     * @private
     */
    _filterAsync(options, queryLower, config) {
        const results = [];
        const chunkSize = config.chunkSize;
        let currentIndex = 0;

        // Filter first chunk synchronously for immediate feedback
        const firstChunk = options.slice(0, chunkSize);
        firstChunk.forEach(function (option) {
            const label = config.caseSensitive
                ? option[1]
                : option[1].toString().toLowerCase();
            if (label.includes(queryLower)) {
                results.push(option);
            }
        });
        currentIndex = chunkSize;

        // Return promise for remaining chunks
        const promise = new Promise(function (resolve) {
            const processChunk = function () {
                const endIndex = Math.min(
                    currentIndex + chunkSize,
                    options.length,
                );
                const chunk = options.slice(currentIndex, endIndex);

                chunk.forEach(function (option) {
                    const label = config.caseSensitive
                        ? option[1]
                        : option[1].toString().toLowerCase();
                    if (label.includes(queryLower)) {
                        results.push(option);
                    }
                });

                currentIndex = endIndex;

                if (currentIndex < options.length) {
                    // Continue processing next chunk
                    if (window.requestIdleCallback) {
                        requestIdleCallback(processChunk);
                    } else {
                        setTimeout(processChunk, 0);
                    }
                } else {
                    // Filtering complete
                    resolve(results);
                }
            };

            // Start processing remaining chunks
            if (window.requestIdleCallback) {
                requestIdleCallback(processChunk);
            } else {
                setTimeout(processChunk, 0);
            }
        });

        return {
            sync: false,
            results: results, // First chunk results
            promise: promise, // Promise for all results
        };
    },

    /**
     * Create a reactive search state object
     *
     * Factory function for creating search state with common properties.
     * Useful for components that need search functionality.
     *
     * @returns {Object} Search state object
     *
     * @example
     * // In Alpine component
     * init() {
     *     this.searchState = this.$store.search.createSearchState();
     * }
     */
    createSearchState() {
        return {
            query: '',
            results: [],
            isSearching: false,
            cachedResults: [],
            filteringInProgress: false,
        };
    },

    /**
     * Escape special regex characters in a string
     *
     * @param {string} str - String to escape
     * @returns {string} Escaped string safe for use in RegExp
     *
     * @example
     * $store.search.escapeRegex('test.com')  // Returns: 'test\\.com'
     */
    escapeRegex(str) {
        return str.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    },
};
