import { echo, echoIsConfigured } from '@laravel/echo-react';
import { router, usePage, usePoll } from '@inertiajs/react';
import { useEffect } from 'react';

/** Live updates are on when the build has a Reverb key (see app.tsx). */
const LIVE = Boolean(import.meta.env.VITE_REVERB_APP_KEY);

type Live = {
    /** "store" for staff screens, "me" for a customer's or supplier's own things. */
    channel: 'store' | 'me';
    /** Areas that matter to this page, e.g. ["shipments", "stock"] (App\Support\LiveUpdates). */
    topics: string[];
};

/**
 * Keeps a page up to date without a refresh. With live updates (Reverb), the
 * listed props reload the moment something in `topics` changes; it still
 * checks now and then in case the connection drops. Without them, it checks
 * every `seconds`. Only server data reloads, so anything being typed stays.
 */
export function useLiveRefresh(
    seconds: number,
    only: string[],
    live?: Live,
): void {
    const { auth } = usePage().props;
    const listening = LIVE && live !== undefined;

    usePoll(
        (listening ? Math.max(seconds * 6, 60) : seconds) * 1000,
        { only },
        { background: 'throttle', mode: 'rest' },
    );

    const channelName =
        live?.channel === 'me'
            ? auth.user
                ? `App.Models.User.${auth.user.id}`
                : null
            : live
              ? 'store'
              : null;
    const topicsKey = live?.topics.join(',') ?? '';
    const onlyKey = only.join(',');

    useEffect(() => {
        if (!listening || !channelName || !echoIsConfigured()) {
            return;
        }

        const topics = topicsKey.split(',');
        let timer: number | undefined;
        const handler = (event: { topic: string }) => {
            if (!topics.includes(event.topic)) {
                return;
            }

            // Several changes at once reload once.
            window.clearTimeout(timer);
            timer = window.setTimeout(
                () => router.reload({ only: onlyKey.split(',') }),
                250,
            );
        };

        const channel = echo().private(channelName);
        channel.listen('.records.changed', handler);

        return () => {
            window.clearTimeout(timer);
            channel.stopListening('.records.changed', handler);
        };
    }, [listening, channelName, topicsKey, onlyKey]);
}
