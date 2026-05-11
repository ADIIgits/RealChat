import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

// Use runtime config injected by PHP (window.reverbConfig) so this works
// in any environment without needing different build-time env vars.
const cfg = window.reverbConfig || {};

window.Echo = new Echo({
    broadcaster:       'reverb',
    key:               cfg.key      ?? import.meta.env.VITE_REVERB_APP_KEY,
    wsHost:            cfg.host     ?? window.location.hostname,
    wsPort:            cfg.port     ?? 443,
    wssPort:           cfg.wssPort  ?? 443,
    forceTLS:          cfg.forceTLS ?? (window.location.protocol === 'https:'),
    enabledTransports: ['ws', 'wss'],
    authEndpoint:      '/broadcasting/auth',
});
