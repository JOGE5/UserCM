import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
// Set CSRF token for axios if available in the page
const tokenMeta = document.querySelector('meta[name="csrf-token"]');
if (tokenMeta) {
	window.axios.defaults.headers.common['X-CSRF-TOKEN'] = tokenMeta.getAttribute('content');
}

// Optional: Initialize Laravel Echo (Pusher) for realtime features
// Laravel Echo (Pusher) initialization removed to avoid Vite attempting to
// resolve the `laravel-echo` import at build time when the package is not
// installed. If you want realtime features, install the packages and then
// re-enable Echo initialization.
//
// To enable realtime with laravel-websockets (recommended for local dev):
// 1) Install the JS packages:
//    npm install --save laravel-echo pusher-js
// 2) Install and configure the server side (beyondcode/laravel-websockets):
//    composer require beyondcode/laravel-websockets
//    php artisan vendor:publish --provider="BeyondCode\\LaravelWebSockets\\WebSocketsServiceProvider" --tag="migrations"
//    php artisan migrate
//    php artisan vendor:publish --provider="BeyondCode\\LaravelWebSockets\\WebSocketsServiceProvider" --tag="config"
//    php artisan websockets:serve
// 3) Add websocket env values to your `.env` (see README or earlier assistant message).
// 4) Restore an Echo initialization block similar to the example below
//    (uncomment it after installing the JS packages):
//
// import Echo from 'laravel-echo';
// import Pusher from 'pusher-js';
// window.Pusher = Pusher;
// window.Echo = new Echo({
//     broadcaster: 'pusher',
//     key: import.meta.env.VITE_PUSHER_APP_KEY,
//     cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
//     wsHost: import.meta.env.VITE_PUSHER_HOST || window.location.hostname,
//     wsPort: import.meta.env.VITE_PUSHER_PORT || 6001,
//     forceTLS: (import.meta.env.VITE_PUSHER_SCHEME || 'http') === 'https',
//     enabledTransports: ['ws', 'wss'],
//     auth: { headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') } }
// });

