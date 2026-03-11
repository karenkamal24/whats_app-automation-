<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8" />
    <meta name="application-name" content="{{ config('app.name') }}" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>{{ config('app.name') }}</title>

    <style>
        [x-cloak] { display: none !important; }
    </style>

    @filamentStyles
    @vite('resources/css/app.css')
</head>

<body class="antialiased">

{{ $slot }}

@filamentScripts
@vite('resources/js/app.js')

<script src="https://cdnjs.cloudflare.com/ajax/libs/pusher/8.4.0-rc2/pusher.min.js"></script>

<script>
(function () {
    if (typeof Pusher === 'undefined') return;

    const pusherClient = new Pusher('{{ config('broadcasting.connections.reverb.key') }}', {
        wsHost:             '{{ config('broadcasting.connections.reverb.options.host', 'localhost') }}',
        wsPort:             {{ config('broadcasting.connections.reverb.options.port', 8080) }},
        wssPort:            {{ config('broadcasting.connections.reverb.options.port', 8080) }},
        forceTLS:           false,
        enabledTransports:  ['ws'],
        cluster:            'mt1',
        disableStats:       true,
    });

    let activeChannelName = null;

    window.Echo = {
        socketId() {
            return pusherClient.connection.socket_id ?? null;
        },

        channel(name) {
           
            if (activeChannelName && activeChannelName !== name) {
                pusherClient.unsubscribe(activeChannelName);
            }
            activeChannelName = name;

            const ch = pusherClient.subscribe(name);


            ch.unbind_all();

            return {
                listen(event, cb) {
                    const eventName = event.startsWith('.') ? event.slice(1) : event;
                    ch.bind(eventName, cb);
                    return this;
                }
            };
        },

        leaveChannel(name) {
            pusherClient.unsubscribe(name);
            if (activeChannelName === name) activeChannelName = null;
        },

        leave(name) {
            pusherClient.unsubscribe(name);
            if (activeChannelName === name) activeChannelName = null;
        },
    };

    pusherClient.connection.bind('connected', () => {
        console.log('✅ Reverb connected | socket_id:', pusherClient.connection.socket_id);
    });

    pusherClient.connection.bind('error', (err) => {
        console.error('❌ Reverb error:', err);
    });

})();
</script>

</body>
</html>
