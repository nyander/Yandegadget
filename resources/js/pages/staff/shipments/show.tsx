import { Head, Link } from '@inertiajs/react';
import { ChevronLeft, PartyPopper } from 'lucide-react';
import { CheckInCard } from '@/components/staff/check-in-card';
import { formatDate } from '@/lib/dates';
import { index } from '@/routes/staff/shipments';
import type { ShipmentDetail, ShipmentItemRow } from '@/types';

type Props = {
    shipment: ShipmentDetail;
    items: ShipmentItemRow[];
};

export default function StaffShipmentShow({ shipment, items }: Props) {
    const remaining = shipment.items_count - shipment.checked_count;
    const done = shipment.status === 'received';

    return (
        <>
            <Head title={`Shipment ${shipment.reference}`} />
            <div className="mx-auto w-full max-w-4xl space-y-6 p-4 sm:p-6">
                <Link
                    href={index()}
                    className="inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground"
                >
                    <ChevronLeft className="size-4" /> All shipments
                </Link>

                <div className="sticky top-0 z-10 -mx-4 border-b bg-background/95 px-4 py-3 backdrop-blur sm:-mx-6 sm:px-6">
                    <div className="flex flex-wrap items-baseline justify-between gap-2">
                        <h1 className="text-2xl font-semibold">
                            {shipment.reference}
                        </h1>
                        <p className="text-sm font-medium tabular-nums">
                            {shipment.checked_count} of {shipment.items_count}{' '}
                            checked in
                        </p>
                    </div>
                    <p className="text-sm text-muted-foreground">
                        {shipment.carrier ?? 'Carrier not set'}
                        {shipment.tracking_number &&
                            ` · Tracking ${shipment.tracking_number}`}
                        {shipment.dispatched_on &&
                            ` · Dispatched ${formatDate(shipment.dispatched_on)}`}
                        {shipment.tracking_url && (
                            <>
                                {' · '}
                                <a
                                    href={shipment.tracking_url}
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    className="font-medium text-brand-green underline-offset-4 hover:underline"
                                >
                                    Track
                                </a>
                            </>
                        )}
                    </p>
                    <div className="mt-2 h-2 overflow-hidden rounded-full bg-muted">
                        <div
                            className="h-full rounded-full bg-brand-green transition-all"
                            style={{
                                width: `${shipment.items_count ? (shipment.checked_count / shipment.items_count) * 100 : 0}%`,
                            }}
                        />
                    </div>
                </div>

                {shipment.notes && (
                    <p className="rounded-lg bg-muted/60 p-3 text-sm">
                        {shipment.notes}
                    </p>
                )}

                {done && (
                    <div className="flex items-center gap-3 rounded-xl border border-brand-green/40 bg-brand-green/10 p-4">
                        <PartyPopper className="size-5 text-brand-green" />
                        <p className="text-sm">
                            Every item is checked in. The admin has been told,
                            and items that arrived are now in the shop.
                        </p>
                    </div>
                )}

                {!done && remaining > 0 && (
                    <p className="text-sm text-muted-foreground">
                        Open each box, check the item against its grade, then
                        tap the result. Add photos of any damage.
                    </p>
                )}

                <ul className="space-y-3">
                    {items.map((item) => (
                        <CheckInCard
                            key={item.id}
                            shipmentId={shipment.id}
                            item={item}
                        />
                    ))}
                </ul>
            </div>
        </>
    );
}

StaffShipmentShow.layout = {
    breadcrumbs: [{ title: 'Shipments', href: index() }],
};
