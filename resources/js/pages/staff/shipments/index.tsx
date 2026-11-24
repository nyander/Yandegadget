import { useLiveRefresh } from '@/hooks/use-live-refresh';
import { Head, Link } from '@inertiajs/react';
import { CheckCircle2, PackageOpen, Truck } from 'lucide-react';
import Heading from '@/components/heading';
import { Badge } from '@/components/ui/badge';
import { formatDate, formatDateTime } from '@/lib/dates';
import { index, show } from '@/routes/staff/shipments';
import type { ShipmentSummary } from '@/types';

type Props = {
    inTransit: ShipmentSummary[];
    recentlyReceived: ShipmentSummary[];
};

export default function StaffShipments({ inTransit, recentlyReceived }: Props) {
    useLiveRefresh(10, ['inTransit', 'recentlyReceived']);
    return (
        <>
            <Head title="Shipments" />
            <div className="mx-auto w-full max-w-4xl space-y-8 p-4 sm:p-6">
                <Heading
                    title="Shipments"
                    description="Check each item in as a shipment arrives from the UK."
                />

                <section className="space-y-3">
                    <h2 className="flex items-center gap-2 font-semibold">
                        <Truck className="size-4" /> On the way
                    </h2>
                    {inTransit.length === 0 ? (
                        <div className="rounded-xl border border-dashed p-8 text-center text-muted-foreground">
                            <PackageOpen className="mx-auto mb-2 size-6" />
                            Nothing on the way right now.
                        </div>
                    ) : (
                        <ul className="space-y-3">
                            {inTransit.map((shipment) => (
                                <ShipmentRow
                                    key={shipment.id}
                                    shipment={shipment}
                                />
                            ))}
                        </ul>
                    )}
                </section>

                {recentlyReceived.length > 0 && (
                    <section className="space-y-3">
                        <h2 className="flex items-center gap-2 font-semibold">
                            <CheckCircle2 className="size-4" /> Recently checked
                            in
                        </h2>
                        <ul className="space-y-3">
                            {recentlyReceived.map((shipment) => (
                                <ShipmentRow
                                    key={shipment.id}
                                    shipment={shipment}
                                />
                            ))}
                        </ul>
                    </section>
                )}
            </div>
        </>
    );
}

function ShipmentRow({ shipment }: { shipment: ShipmentSummary }) {
    const done = shipment.status === 'received';
    const percent = shipment.items_count
        ? Math.round((shipment.checked_count / shipment.items_count) * 100)
        : 0;

    return (
        <li>
            <Link
                href={show(shipment.id)}
                className="block rounded-xl border p-4 transition hover:bg-muted/50 focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
            >
                <div className="flex flex-wrap items-center justify-between gap-2">
                    <div className="flex items-center gap-2">
                        <span className="text-lg font-semibold">
                            {shipment.reference}
                        </span>
                        <Badge variant={done ? 'secondary' : 'default'}>
                            {done ? 'Checked in' : 'In transit'}
                        </Badge>
                    </div>
                    <span className="text-sm text-muted-foreground">
                        {done
                            ? `Completed ${formatDateTime(shipment.completed_at)}`
                            : shipment.expected_on
                              ? `Expected ${formatDate(shipment.expected_on)}`
                              : `Dispatched ${formatDate(shipment.dispatched_on)}`}
                    </span>
                </div>
                <p className="mt-1 text-sm text-muted-foreground">
                    {shipment.carrier ?? 'Carrier not set'} · to{' '}
                    {shipment.destination}
                </p>
                <div className="mt-3 flex items-center gap-3">
                    <div
                        className="h-2 flex-1 overflow-hidden rounded-full bg-muted"
                        role="progressbar"
                        aria-valuenow={shipment.checked_count}
                        aria-valuemin={0}
                        aria-valuemax={shipment.items_count}
                        aria-label="Items checked in"
                    >
                        <div
                            className="h-full rounded-full bg-brand-green"
                            style={{ width: `${percent}%` }}
                        />
                    </div>
                    <span className="text-sm tabular-nums">
                        {shipment.checked_count} / {shipment.items_count}
                    </span>
                </div>
            </Link>
        </li>
    );
}

StaffShipments.layout = {
    breadcrumbs: [{ title: 'Shipments', href: index() }],
};
