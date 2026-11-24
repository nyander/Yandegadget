import { usePage } from '@inertiajs/react';
import { useCallback } from 'react';

export type Translate = (
    text: string,
    replace?: Record<string, string | number>,
) => string;

/**
 * Translates English wording into the visitor's language, using the
 * lang/{locale}.json strings shared by the server. Missing strings fall
 * back to English. Placeholders look like :name.
 */
export function useTrans(): { t: Translate; locale: string } {
    const { translations, locale } = usePage().props;

    const t = useCallback<Translate>(
        (text, replace) => {
            let line = translations?.[text] || text;

            if (replace) {
                // Longest names first, so :refund doesn't eat the start of :refunded.
                const entries = Object.entries(replace).sort(
                    ([a], [b]) => b.length - a.length,
                );

                for (const [key, value] of entries) {
                    line = line.replaceAll(`:${key}`, String(value));
                }
            }

            return line;
        },
        [translations],
    );

    return { t, locale: locale ?? 'en' };
}

/** BCP 47 tag for dates and numbers; falls back to British English where the browser has no data. */
export function intlLocale(locale: string): string {
    return locale === 'fr' ? 'fr-FR' : 'en-GB';
}

/**
 * Marks English wording defined outside a component (menus, labels in
 * constants) so it's picked up for translation. Translate it with t() when
 * it's shown.
 */
export const tk = (text: string): string => text;
