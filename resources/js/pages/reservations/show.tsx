import { Form, Head, Link, usePage } from '@inertiajs/react';
import { Check, ChevronLeft, MapPin } from 'lucide-react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { useLiveRefresh } from '@/hooks/use-live-refresh';
import { useTrans } from '@/hooks/use-trans';
import { formatDate } from '@/lib/dates';
import { reservationStatusLabels } from '@/lib/reservation-status';
import { cancel, index, pay } from '@/routes/reservations';
import { show as shopShow } from '@/routes/shop';
import type { ReservationDetail } from '@/types';

type Props = {
    reservation: ReservationDetail;
    canPay: boolean;
    canCancel: boolean;
};

export default function ReservationShow({
    reservation: r,
    canPay,
    canCancel,
}: Props) {
    useLiveRefresh(10, ['reservation', 'canPay', 'canCancel'], {
        channel: 'me',
        topics: ['reservations', 'payments'],
    });
    const { errors } = usePage().props as { errors: Record<string, string> };
    const { t, locale } = useTrans();
    const date = (value: string | null) => formatDate(value, locale) ?? '—';

    return (
        <>
            <Head
                title={t('Reservation :reference', { reference: r.reference })}
            />
            <div className="mx-auto w-full max-w-2xl space-y-6 p-4 sm:p-6">
                <Link
                    href={index()}
                    className="inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground"
                >
                    <ChevronLeft className="size-4" /> {t('My reservations')}
                </Link>

                <div className="enter flex items-center gap-4">
                    {r.item.image && (
                        <img
                            src={r.item.image}
                            alt=""
                            className="size-20 shrink-0 rounded-lg border object-cover"
                        />
                    )}
                    <div className="min-w-0 flex-1">
                        <p className="text-sm text-muted-foreground">
                            {r.reference}
                        </p>
                        <h1 className="text-2xl font-semibold tracking-tight">
                            <Link
                                href={shopShow(r.item.slug)}
                                className="hover:underline"
                            >
                                {r.item.name}
                            </Link>
                        </h1>
                        <p className="text-sm text-muted-foreground">
                            {t('Grade :grade · :condition · Ref :reference', {
                                grade: r.item.grade,
                                condition: r.item.condition,
                                reference: r.item.reference,
                            })}
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
                </div>

                {r.status === 'reserved' && (
                    <div className="enter-pop rounded-xl border border-brand-green/40 bg-brand-green/10 p-5">
                        <p className="flex items-center gap-2 text-lg font-semibold">
                            <Check className="size-5 text-brand-green" />
                            {t('It’s yours. Collect it by :date.', {
                                date: date(r.collect_by),
                            })}
                        </p>
                        <p className="mt-2 text-sm">
                            {r.paid_in_full
                                ? t(
                                      'Paid in full (:amount). Just bring your reference :reference.',
                                      {
                                          amount: r.due.formatted,
                                          reference: r.reference,
                                      },
                                  )
                                : t(
                                      'You’ve paid :paid. Pay the rest in store: :balance (the final price can be agreed there).',
                                      {
                                          paid: r.due.formatted,
                                          balance: r.balance.formatted,
                                      },
                                  )}
                        </p>
                        <p className="mt-3 flex gap-2 text-sm text-muted-foreground">
                            <MapPin className="mt-0.5 size-4 shrink-0" />
                            {t(
                                '27 Ayawaso, Legon Boundary, Accra. If you can’t collect it in time, you get everything back.',
                            )}
                        </p>
                    </div>
                )}

                {r.status === 'awaiting_payment' && (
                    <div className="rounded-xl border border-brand-gold/50 bg-brand-gold/10 p-5 text-sm">
                        <p className="font-medium">
                            {t('Your payment hasn’t gone through yet.')}
                        </p>
                        <p className="mt-1">
                            {t(
                                'The item is held for you until :time. Try again, or choose another way to pay.',
                                {
                                    time: r.hold_expires_at
                                        ? new Date(
                                              r.hold_expires_at,
                                          ).toLocaleTimeString([], {
                                              hour: '2-digit',
                                              minute: '2-digit',
                                          })
                                        : '',
                                },
                            )}
                        </p>
                        {errors.payment && (
                            <p role="alert" className="mt-2 text-brand-red">
                                {errors.payment}
                            </p>
                        )}
                        {canPay && (
                            <Form {...pay.form(r.id)} className="mt-3">
                                {({ processing }) => (
                                    <Button type="submit" disabled={processing}>
                                        {t('Pay :amount', {
                                            amount: r.due.formatted,
                                        })}
                                    </Button>
                                )}
                            </Form>
                        )}
                    </div>
                )}

                {r.status === 'collected' && (
                    <div className="rounded-xl border p-5 text-sm">
                        <p className="font-medium">
                            {t('Collected. Enjoy it!')}
                        </p>
                    </div>
                )}

                {r.status === 'cancelled' && (
                    <div className="rounded-xl border border-brand-red/40 bg-brand-red/10 p-5 text-sm">
                        <p className="font-medium">
                            {t('This reservation was cancelled.')}
                        </p>
                        {r.cancel_reason && (
                            <p className="mt-1">{r.cancel_reason}</p>
                        )}
                        {r.refunded && (
                            <p className="mt-1">
                                {t(
                                    ':amount is being refunded in full to the account you paid from.',
                                    {
                                        amount: r.due.formatted,
                                    },
                                )}
                            </p>
                        )}
                    </div>
                )}

                <dl className="divide-y rounded-xl border text-sm">
                    {[
                        [t('Price'), r.price.formatted],
                        [
                            r.paid_in_full
                                ? t('Paid online')
                                : t('Deposit paid online'),
                            r.paid_at ? r.due.formatted : '—',
                        ],
                        [
                            t('To pay in store'),
                            r.status === 'reserved' ? r.balance.formatted : '—',
                        ],
                        [t('Paid on'), date(r.paid_at)],
                    ].map(([label, value]) => (
                        <div
                            key={label}
                            className="flex justify-between gap-4 px-4 py-3"
                        >
                            <dt className="text-muted-foreground">{label}</dt>
                            <dd className="font-medium">{value}</dd>
                        </div>
                    ))}
                </dl>

                {canCancel && (
                    <Dialog>
                        <DialogTrigger asChild>
                            <Button variant="outline">
                                {t('Cancel reservation')}
                            </Button>
                        </DialogTrigger>
                        <DialogContent>
                            <DialogHeader>
                                <DialogTitle>
                                    {t('Cancel this reservation?')}
                                </DialogTitle>
                                <DialogDescription>
                                    {r.status === 'reserved'
                                        ? t(
                                              'You’ll get all :amount back, and the item goes back on sale.',
                                              {
                                                  amount: r.due.formatted,
                                              },
                                          )
                                        : t('The item goes back on sale.')}
                                </DialogDescription>
                            </DialogHeader>
                            <DialogFooter>
                                <DialogClose asChild>
                                    <Button variant="outline">
                                        {t('Keep it')}
                                    </Button>
                                </DialogClose>
                                <Form {...cancel.form(r.id)}>
                                    {({ processing }) => (
                                        <Button
                                            type="submit"
                                            variant="destructive"
                                            disabled={processing}
                                        >
                                            {t('Cancel reservation')}
                                        </Button>
                                    )}
                                </Form>
                            </DialogFooter>
                        </DialogContent>
                    </Dialog>
                )}
            </div>
        </>
    );
}

ReservationShow.layout = {
    breadcrumbs: [{ title: 'My reservations', href: index() }],
};
