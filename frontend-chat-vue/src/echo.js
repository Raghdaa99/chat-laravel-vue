import Pusher from 'pusher-js';
import Echo from 'laravel-echo';

window.Pusher = Pusher;
const echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER ?? 'mt1',
    forceTLS: false,
    auth: {
        headers: {
            Authorization: `Bearer ${window.sessionStorage.getItem('TOKEN')}`
        },
    },
    authEndpoint: 'http://localhost:8000/api/broadcasting/auth',
});


export { echo };

