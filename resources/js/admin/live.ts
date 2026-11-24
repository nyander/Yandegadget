/**
 * Live updates in the admin panel (Filament). Listens on the private
 * "backoffice" channel over Reverb; when something changes, the lists and
 * dashboard widgets on this page redraw straight away. If the connection
 * drops, they're refreshed every few seconds instead, until it's back.
 * See App\Support\LiveUpdates.
 */
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

type LivewireComponent = {
    el: HTMLElement;
    $wire: { $refresh: () => Promise<void> };
};
type LivewireGlobal = {
    all: () => LivewireComponent[];
    dispatch: (event: string) => void;
};

declare global {
    interface Window {
        Pusher: typeof Pusher;
        ygLiveReady?: boolean;
    }
}

/** What redraws: tables (lists, and lists inside a record), dashboard widgets, and pages that ask for it. */
const LIVE_PARTS = '.fi-ta, .fi-wi-widget, [data-live-refresh]';

/** While disconnected, how often to refresh instead. */
const FALLBACK_MS = 5000;

function livewire(): LivewireGlobal | undefined {
    return (window as unknown as { Livewire?: LivewireGlobal }).Livewire;
}

let scheduled: number | undefined;

/** Several changes at once (say "Add several" with 20 items) redraw once. */
function refreshSoon(): void {
    window.clearTimeout(scheduled);
    scheduled = window.setTimeout(refreshNow, 250);
}

function refreshNow(): void {
    const lw = livewire();

    if (!lw || document.hidden) {
        return;
    }

    for (const component of lw.all()) {
        const el = component.el;

        if (el.matches(LIVE_PARTS) || el.querySelector(LIVE_PARTS)) {
            void component.$wire.$refresh();
        }
    }

    // The bell may have something new too.
    lw.dispatch('databaseNotificationsSent');
}

function start(): void {
    const meta = document.querySelector<HTMLMetaElement>(
        'meta[name="yg-reverb"]',
    );

    if (!meta?.content || window.ygLiveReady) {
        return;
    }

    window.ygLiveReady = true;
    const config = JSON.parse(meta.content) as {
        key: string;
        host: string;
        port: number;
        scheme: string;
    };

    window.Pusher = Pusher;
    const echo = new Echo({
        broadcaster: 'reverb',
        key: config.key,
        wsHost: config.host,
        wsPort: config.port,
        wssPort: config.port,
        forceTLS: config.scheme === 'https',
        enabledTransports: ['ws', 'wss'],
        authEndpoint: '/broadcasting/auth',
    });

    echo.private('backoffice').listen('.records.changed', refreshSoon);

    // Connection lost: keep things fresh the old way until it's back.
    let fallback: number | undefined;
    const connection = echo.connector.pusher.connection;

    connection.bind('state_change', ({ current }: { current: string }) => {
        window.clearInterval(fallback);

        if (current !== 'connected') {
            fallback = window.setInterval(refreshNow, FALLBACK_MS);
        }
    });

    // Coming back to the tab: catch up on anything missed while away.
    document.addEventListener('visibilitychange', () => {
        if (!document.hidden) {
            refreshSoon();
        }
    });
}

start();
