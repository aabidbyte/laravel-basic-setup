/**
 * Laravel Echo Configuration
 *
 * Initializes Laravel Echo with Reverb for real-time broadcasting.
 * Provides error handling and graceful degradation when Echo is unavailable.
 */

import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

// Make Pusher available globally for Echo
window.Pusher = Pusher;

const reverbKey = import.meta.env.VITE_REVERB_APP_KEY;
const reverbHost = import.meta.env.VITE_REVERB_HOST;

/**
 * Initialize Laravel Echo with Reverb
 */
function initializeEcho() {
    // Only initialize if required environment variables are present
    if (!reverbKey || !reverbHost) {
        console.warn(
            '[Echo] Reverb configuration missing. Real-time features will be disabled.',
            {
                hasKey: !!reverbKey,
                hasHost: !!reverbHost,
            },
        );
        return;
    }

    try {
        window.Echo = new Echo({
            broadcaster: 'reverb',
            key: reverbKey,
            wsHost: reverbHost,
            wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
            wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
            forceTLS:
                (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
            enabledTransports: ['ws', 'wss'],
        });
    } catch (error) {
        console.error('[Echo] Failed to initialize:', error);
        // Create a mock Echo object to prevent errors in dependent code
        window.Echo = {
            private: () => ({
                listen: () => ({ listen: () => ({}) }),
            }),
        };
    }
}

// Initialize Echo
initializeEcho();
