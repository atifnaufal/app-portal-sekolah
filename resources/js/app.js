import './bootstrap';

import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

if (import.meta.env.VITE_REVERB_APP_KEY) {
    const scheme = import.meta.env.VITE_REVERB_SCHEME
        || (window.location.protocol === 'https:' ? 'https' : 'http');

    window.Echo = new Echo({
        broadcaster: 'reverb',
        key: import.meta.env.VITE_REVERB_APP_KEY,
        wsHost: import.meta.env.VITE_REVERB_HOST || window.location.hostname,
        wsPort: import.meta.env.VITE_REVERB_PORT ?? (scheme === 'http' ? 80 : 443),
        wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
        forceTLS: scheme === 'https',
        enabledTransports: ['ws', 'wss'],
    });
}
