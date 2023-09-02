// echo.js
import Pusher from 'pusher-js';

import Echo from 'laravel-echo';

window.Pusher = Pusher;

export function initializeEcho(token) {
    return new Echo({
        broadcaster: 'pusher',
        key: import.meta.env.VITE_PUSHER_APP_KEY,
        cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER ?? 'mt1',
        forceTLS: false,
        auth: {
            headers: {
                Authorization: `Bearer ${token}`,
            },
        },
        authEndpoint: 'http://localhost:8000/api/broadcasting/auth',
    });
}


