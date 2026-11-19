/**
 * Formats a major-unit amount the same way the server does for Ghana.
 */
export function formatMoney(amount: number, currency: string): string {
    return new Intl.NumberFormat('en-GH', {
        style: 'currency',
        currency,
    }).format(amount);
}
