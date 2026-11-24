import { Head, Link, usePage } from '@inertiajs/react';
import { Ban, CheckCircle2, Printer } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { formatDateTime } from '@/lib/dates';
import { index } from '@/routes/staff/sell';
import type { SaleReceipt } from '@/types';

export default function SaleShow({ sale }: { sale: SaleReceipt }) {
    const { name: shopName } = usePage().props;
    const rows: [string, string | null][] = [
        ['Item', sale.product],
        ['Reference', sale.unit_reference],
        ['Serial / IMEI', sale.serial_number],
        ['Condition', sale.condition],
        ['Listed price', sale.listed.formatted],
        ['Request', sale.request_reference],
        ['Deposit paid online', sale.deposit_applied?.formatted ?? null],
        [
            'Paid in store',
            sale.deposit_applied ? sale.paid_in_store.formatted : null,
        ],
        ['Payment', sale.payment_method],
        ['Payment reference', sale.payment_reference],
        ['Customer', sale.customer_name],
        ['Customer phone', sale.customer_phone],
        ['Sold by', sale.seller],
        ['Date', formatDateTime(sale.sold_at)],
        ['Notes', sale.notes],
    ];

    return (
        <>
            <Head title={`Sale ${sale.reference}`} />
            <div className="mx-auto w-full max-w-xl space-y-6 p-4 sm:p-6">
                {sale.voided_at ? (
                    <div className="flex gap-3 rounded-xl border border-brand-red/40 bg-brand-red/10 p-4 text-sm">
                        <Ban className="size-5 shrink-0 text-brand-red" />
                        <p>
                            This sale was voided{' '}
                            {formatDateTime(sale.voided_at)}: {sale.void_reason}
                        </p>
                    </div>
                ) : (
                    <div className="flex items-center gap-3 rounded-xl border border-brand-green/40 bg-brand-green/10 p-4 text-sm print:hidden">
                        <CheckCircle2 className="size-5 shrink-0 text-brand-green" />
                        <p>Sale recorded. The item is no longer in the shop.</p>
                    </div>
                )}

                <div className="rounded-xl border p-6">
                    <div className="flex items-start justify-between gap-4">
                        <div>
                            <p className="font-semibold">{shopName}</p>
                            <p className="text-sm whitespace-pre-line text-muted-foreground">
                                {sale.address ?? sale.location}
                            </p>
                        </div>
                        <div className="text-right">
                            <p className="text-sm text-muted-foreground">
                                Receipt
                            </p>
                            <p className="font-semibold">{sale.reference}</p>
                        </div>
                    </div>

                    <dl className="mt-6 divide-y text-sm">
                        {rows
                            .filter(([, value]) => value)
                            .map(([label, value]) => (
                                <div
                                    key={label}
                                    className="grid grid-cols-3 gap-4 py-2"
                                >
                                    <dt className="text-muted-foreground">
                                        {label}
                                    </dt>
                                    <dd className="col-span-2">{value}</dd>
                                </div>
                            ))}
                    </dl>

                    <div className="mt-4 flex items-baseline justify-between border-t pt-4">
                        <span className="font-medium">Total price</span>
                        <span className="text-2xl font-semibold">
                            {sale.price_paid.formatted}
                        </span>
                    </div>
                </div>

                <div className="flex gap-2 print:hidden">
                    <Button variant="outline" onClick={() => window.print()}>
                        <Printer /> Print
                    </Button>
                    <Button asChild>
                        <Link href={index()}>Sell another item</Link>
                    </Button>
                </div>
            </div>
        </>
    );
}

SaleShow.layout = {
    breadcrumbs: [{ title: 'Sell', href: index() }],
};
