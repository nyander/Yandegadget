import { Head, Link } from '@inertiajs/react';
import { ShoppingBag } from 'lucide-react';
import type { CSSProperties } from 'react';
import Heading from '@/components/heading';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { useLiveRefresh } from '@/hooks/use-live-refresh';
import { useTrans } from '@/hooks/use-trans';
import { formatDate } from '@/lib/dates';
import { reservationStatusLabels } from '@/lib/reservation-status';
import { index, show } from '@/routes/reservations';
import { index as shopIndex } from '@/routes/shop';
import type { ReservationDetail } from '@/types';

export default function ReservationsIndex({
    reservations,
}: {
    reservations: ReservationDetail[];
}) {
    useLiveRefresh(15, ['reservations'], {
        channel: 'me',
        topics: ['reservations', 'payments'],
    });
    const { t, locale } = useTrans();

    return (
        <>
            <Head title={t('My reservations')} />
            <div className="mx-auto w-full max-w-3xl space-y-6 p-4 sm:p-6">
                <Heading
                    title={t('My reservations')}
                    description={t(
                        'Items you’ve reserved and paid for online, to collect in store.',
                    )}
                />

                {reservations.length === 0 ? (
                    <div className="rounded-xl border border-dashed p-8 text-center">
                        <ShoppingBag className="mx-auto size-8 text-muted-foreground" />
                        <p className="mt-3 text-muted-foreground">
                            {t(
                                'Nothing reserved yet. Find something in the shop and reserve it with mobile money or card.',
                            )}
                        </p>
                        <Button asChild className="mt-4">
                            <Link href={shopIndex()}>
                                {t('Browse the shop')}
                            </Link>
                        </Button>
                    </div>
                ) : (
                    <ul className="divide-y rounded-xl border">
                        {reservations.map((r, i) => (
                            <li
                                key={r.id}
                                className="enter"
                                style={{ '--i': i } as CSSProperties}
                            >
                                <Link
                                    href={show(r.id)}
                                    className="flex items-center gap-4 p-4 transition-colors hover:bg-muted/50"
                                >
                                    {r.item.image && (
                                        <img
                                            src={r.item.image}
                                            alt=""
                                            className="size-12 rounded-md border object-cover"
                                        />
                                    )}
                                    <div className="min-w-0 flex-1">
                                        <p className="font-medium">
                                            {r.item.name}
                                        </p>
                                        <p className="text-sm text-muted-foreground">
                                            {r.reference} ·{' '}
                                            {r.status === 'reserved'
                                                ? t('Collect by :date', {
                                                      date:
                                                          formatDate(
                                                              r.collect_by,
                                                              locale,
                                                          ) ?? '',
                                                  })
                                                : formatDate(
                                                      r.created_at,
                                                      locale,
                                                  )}
                                        </p>
                                    </div>
                                    <Badge
                                        variant={
                                            r.status === 'cancelled'
                                                ? 'destructive'
                                                : 'secondary'
                                        }
                                    >
                                        {t(reservationStatusLabels[r.status])}
                                    </Badge>
                                </Link>
                            </li>
                        ))}
                    </ul>
                )}
            </div>
        </>
    );
}

ReservationsIndex.layout = {
    breadcrumbs: [{ title: 'My reservations', href: index() }],
};
