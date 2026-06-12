import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

const reverbKey = import.meta.env.VITE_REVERB_APP_KEY;

if (reverbKey) {
    window.Echo = new Echo({
        broadcaster: 'reverb',
        key: reverbKey,
        wsHost: import.meta.env.VITE_REVERB_HOST,
        wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
        wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
        forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
        enabledTransports: ['ws', 'wss'],
        activityTimeout: 120000,
        pongTimeout: 30000,
        authorizer: (channel) => ({
            authorize: (socketId, callback) => {
                window.axios
                    .post('/broadcasting/auth', {
                        socket_id: socketId,
                        channel_name: channel.name,
                    })
                    .then((response) => callback(false, response.data))
                    .catch((error) => callback(true, error));
            },
        }),
    });
}

export default window.Echo;
