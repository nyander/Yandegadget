import { useLiveRefresh } from '@/hooks/use-live-refresh';
import { Form, Head, Link } from '@inertiajs/react';
import { CheckCheck, ChevronLeft, PartyPopper } from 'lucide-react';
import { useState } from 'react';
import { Button } from '@/components/ui/button';
import { CheckInCard } from '@/components/staff/check-in-card';
import { formatDate } from '@/lib/dates';
import { index } from '@/routes/staff/shipments';
import { checkIn as checkInBatch } from '@/routes/staff/shipments/batches';
import type { ShipmentDetail, ShipmentItemRow } from '@/types';

type Props = {
    shipment: ShipmentDetail;
    items: ShipmentItemRow[];
};

export default function StaffShipmentShow({ shipment, items }: Props) {
    useLiveRefresh(5, ['shipment', 'items'], {
        channel: 'store',
        topics: ['shipments', 'stock'],
    });
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
                    {groupItems(items).map((group) =>
                        group.batch ? (
                            <BatchCheckIn
                                key={`batch-${group.batch.id}`}
                                shipmentId={shipment.id}
                                batch={group.batch}
                                items={group.items}
                                open={!done && shipment.status === 'in_transit'}
                            />
                        ) : (
                            group.items.map((item) => (
                                <CheckInCard
                                    key={item.id}
                                    shipmentId={shipment.id}
                                    item={item}
                                />
                            ))
                        ),
                    )}
                </ul>
            </div>
        </>
    );
}

StaffShipmentShow.layout = {
    breadcrumbs: [{ title: 'Shipments', href: index() }],
};

type ItemGroup = {
    batch: { id: number; reference: string } | null;
    items: ShipmentItemRow[];
};

/** Items from the same bulk purchase together; one-off items on their own. */
function groupItems(items: ShipmentItemRow[]): ItemGroup[] {
    const groups: ItemGroup[] = [];
    const byBatch = new Map<number, ItemGroup>();

    for (const item of items) {
        if (!item.batch) {
            groups.push({ batch: null, items: [item] });
            continue;
        }

        let group = byBatch.get(item.batch.id);
        if (!group) {
            group = { batch: item.batch, items: [] };
            byBatch.set(item.batch.id, group);
            groups.push(group);
        }
        group.items.push(item);
    }

    return groups;
}

/**
 * A bulk purchase (e.g. 50 watches) as one card: check them all in as arrived
 * with one tap, or open the list to check damaged or missing ones first.
 */
function BatchCheckIn({
    shipmentId,
    batch,
    items,
    open,
}: {
    shipmentId: number;
    batch: { id: number; reference: string };
    items: ShipmentItemRow[];
    open: boolean;
}) {
    const [expanded, setExpanded] = useState(false);
    const waiting = items.filter((item) => !item.checked).length;
    const first = items[0];

    return (
        <li className="rounded-xl border p-4">
            <div className="flex flex-wrap items-center gap-4">
                {first.image && (
                    <img
                        src={first.image}
                        alt=""
                        className="size-14 rounded-lg border object-cover"
                    />
                )}
                <div className="min-w-0 flex-1">
                    <p className="font-medium">
                        {first.product}{' '}
                        <span className="text-muted-foreground">
                            ×{items.length}
                        </span>
                    </p>
                    <p className="text-sm text-muted-foreground">
                        Bulk {batch.reference} · Grade {first.condition.grade} ·{' '}
                        {waiting === 0 ? 'all checked' : `${waiting} to check`}
                    </p>
                </div>
                {open && waiting > 0 && (
                    <Form
                        {...checkInBatch.form({
                            shipment: shipmentId,
                            batch: batch.id,
                        })}
                        options={{ preserveScroll: true }}
                        onBefore={() =>
                            window.confirm(
                                `Mark all ${waiting} as arrived OK? Check any damaged or missing ones one by one first.`,
                            )
                        }
                    >
                        {({ processing }) => (
                            <Button type="submit" disabled={processing}>
                                <CheckCheck className="size-4" />
                                All {waiting} arrived
                            </Button>
                        )}
                    </Form>
                )}
                <Button
                    variant="outline"
                    onClick={() => setExpanded((value) => !value)}
                >
                    {expanded ? 'Hide items' : 'Check one by one'}
                </Button>
            </div>
            {expanded && (
                <ul className="mt-4 space-y-3">
                    {items.map((item) => (
                        <CheckInCard
                            key={item.id}
                            shipmentId={shipmentId}
                            item={item}
                        />
                    ))}
                </ul>
            )}
        </li>
    );
}
