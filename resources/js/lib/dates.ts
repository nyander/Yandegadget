import { intlLocale } from '@/hooks/use-trans';

export function formatDate(value: string | null, locale = 'en'): string | null {
    if (!value) {
        return null;
    }

    return new Date(value).toLocaleDateString(intlLocale(locale), {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    });
}

export function formatDateTime(
    value: string | null,
    locale = 'en',
): string | null {
    if (!value) {
        return null;
    }

    return new Date(value).toLocaleString(intlLocale(locale), {
        day: 'numeric',
        month: 'short',
        hour: '2-digit',
        minute: '2-digit',
    });
}
