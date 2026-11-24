import { usePoll } from '@inertiajs/react';

/**
 * Keeps a page up to date without a refresh: every few seconds, quietly
 * reloads just the listed props, so changes other people make (an item
 * checked in, a request found, a sale) appear by themselves. Slows right
 * down while the tab is in the background, and leaves anything being typed
 * alone, since only server data is reloaded.
 */
export function useLiveRefresh(seconds: number, only: string[]): void {
    usePoll(seconds * 1000, { only }, { background: 'throttle', mode: 'rest' });
}
