import { useLiveRefresh } from '@/hooks/use-live-refresh';
import { Form, Head, Link, usePage } from '@inertiajs/react';
import { Check, ChevronLeft, MapPin } from 'lucide-react';
import type { CSSProperties } from 'react';
import AlertError from '@/components/alert-error';
import { RequestStatusBadge } from '@/components/request-status-badge';
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
import { tk, useTrans } from '@/hooks/use-trans';
import type { Translate } from '@/hooks/use-trans';
import { formatDate } from '@/lib/dates';
import { cn } from '@/lib/utils';
import { cancel, index, pay } from '@/routes/requests';
import { show as shopShow } from '@/routes/shop';
import type { RequestDetail } from '@/types';

export default function RequestShow({ request }: { request: RequestDetail }) {
    useLiveRefresh(10, ['request'], {
        channel: 'me',
        topics: ['requests', 'payments'],
    });
    const { errors } = usePage().props as { errors: Record<string, string> };
    const { t, locale } = useTrans();
    const date = (value: string | null) => formatDate(value, locale);
    const steps: { label: string; at: string | null }[] = [
        { label: tk('Request made'), at: request.created_at },
        { label: tk('Deposit paid'), at: request.paid_at },
        { label: tk('Item found'), at: request.sourced_at },
        { label: tk('Ready in store'), at: request.ready_at },
        { label: tk('Collected'), at: request.fulfilled_at },
    ];
    const doneCount = steps.filter((step) => step.at !== null).length;
    const messages = Object.values(errors ?? {});

    return (
        <>
            <Head
                title={t('Request :reference', {
                    reference: request.reference,
                })}
            />
            <div className="mx-auto w-full max-w-3xl space-y-6 p-4 sm:p-6">
                <Link
                    href={index()}
                    className="group inline-flex items-center gap-1 text-sm text-muted-foreground transition-colors hover:text-foreground"
                >
                    <ChevronLeft className="size-4 transition-transform group-hover:-translate-x-0.5" />{' '}
                    {t('My requests')}
                </Link>

                <div className="enter flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <p className="text-sm text-muted-foreground">
                            {request.reference} · {request.category}
                        </p>
                        <h1 className="text-2xl font-semibold">
                            {request.description}
                        </h1>
                        <p className="text-sm text-muted-foreground">
                            {t('Grade :grade (:name) or better', {
                                grade: request.condition.grade,
                                name: request.condition.name,
                            })}
                        </p>
                    </div>
                    <RequestStatusBadge
                        status={request.status}
                        label={request.status_label}
                    />
                </div>

                {messages.length > 0 && <AlertError errors={messages} />}

                {request.status === 'awaiting_deposit' && (
                    <div className="enter space-y-3 rounded-xl border p-4">
                        <p>
                            {t(
                                'Pay the :amount deposit and we’ll start looking.',
                                { amount: request.deposit?.formatted ?? '' },
                            )}
                            {request.payment?.status === 'failed' &&
                                ` ${t('Your last payment didn’t go through.')}`}
                        </p>
                        <div className="flex flex-wrap gap-2">
                            <Form action={pay(request.id)}>
                                {({ processing }) => (
                                    <Button disabled={processing}>
                                        {t('Pay deposit')}
                                    </Button>
                                )}
                            </Form>
                            <CancelRequest request={request} />
                        </div>
                    </div>
                )}

                {request.status === 'ready' && request.item && (
                    <div className="enter flex gap-3 rounded-xl border border-brand-green/40 bg-brand-green/10 p-4">
                        <MapPin className="mt-0.5 size-5 shrink-0 text-brand-green motion-safe:animate-bounce" />
                        <div className="text-sm">
                            <p className="font-medium">
                                {t(
                                    ':product is reserved for you at the Legon store.',
                                    { product: request.item.product },
                                )}
                            </p>
                            <p className="mt-1">
                                {t('Quote reference :reference.', {
                                    reference: request.item.reference,
                                })}
                                {request.item.price &&
                                    ` ${t('Listed at :price.', {
                                        price: request.item.price.formatted,
                                    })}`}
                                {request.deposit &&
                                    ` ${t(
                                        'Your :amount deposit comes off the price.',
                                        { amount: request.deposit.formatted },
                                    )}`}
                            </p>
                            {request.collect_by && (
                                <p className="mt-1 font-medium">
                                    {t(
                                        'Please collect it by :date. After that it goes back on sale.',
                                        {
                                            date:
                                                date(request.collect_by) ?? '',
                                        },
                                    )}
                                </p>
                            )}
                            {request.item.shop_slug && (
                                <Link
                                    href={shopShow(request.item.shop_slug)}
                                    className="mt-2 inline-block font-medium underline"
                                >
                                    {t('See it in the shop')}
                                </Link>
                            )}
                        </div>
                    </div>
                )}

                {request.status === 'cancelled' && (
                    <div className="enter rounded-xl border border-brand-red/40 bg-brand-red/10 p-4 text-sm">
                        <p>
                            {t('Cancelled :date.', {
                                date: date(request.cancelled_at) ?? '',
                            })}{' '}
                            {request.cancel_reason}
                        </p>
                        {request.refunded && (
                            <p className="mt-1">
                                {t(
                                    request.payment?.channel === 'mobile_money'
                                        ? tk(
                                              ':refunded of your :deposit deposit has been refunded to your mobile money wallet.',
                                          )
                                        : tk(
                                              ':refunded of your :deposit deposit has been refunded to your card.',
                                          ),
                                    {
                                        refunded: request.refunded.formatted,
                                        deposit:
                                            request.deposit?.formatted ?? '',
                                    },
                                )}
                            </p>
                        )}
                        {!request.refunded && request.paid_at && (
                            <p className="mt-1">
                                {t(
                                    'Your deposit was kept as the cancellation fee.',
                                )}
                            </p>
                        )}
                    </div>
                )}

                {request.status !== 'cancelled' && (
                    <ol className="space-y-0">
                        {steps.map((step, i) => {
                            const done = step.at !== null;

                            return (
                                <li
                                    key={step.label}
                                    className="enter flex gap-3"
                                    style={{ '--i': i + 1 } as CSSProperties}
                                >
                                    <div className="flex flex-col items-center">
                                        <span
                                            style={
                                                {
                                                    '--i': i + 2,
                                                } as CSSProperties
                                            }
                                            className={cn(
                                                'flex size-7 items-center justify-center rounded-full border text-xs transition-colors duration-500',
                                                done &&
                                                    'enter-pop border-brand-green bg-brand-green text-white',
                                                i === doneCount &&
                                                    'border-brand-green text-brand-green ring-4 ring-brand-green/15 motion-safe:animate-pulse',
                                            )}
                                        >
                                            {done ? (
                                                <Check className="size-4" />
                                            ) : (
                                                i + 1
                                            )}
                                        </span>
                                        {i < steps.length - 1 && (
                                            <span className="relative h-6 w-px overflow-hidden bg-border">
                                                {i < doneCount - 1 && (
                                                    <span
                                                        style={
                                                            {
                                                                '--i': i + 3,
                                                            } as CSSProperties
                                                        }
                                                        className="timeline-fill absolute inset-0 bg-brand-green"
                                                    />
                                                )}
                                            </span>
                                        )}
                                    </div>
                                    <div className="pb-4">
                                        <p
                                            className={cn(
                                                'text-sm font-medium',
                                                !done &&
                                                    'text-muted-foreground',
                                            )}
                                        >
                                            {t(step.label)}
                                        </p>
                                        {step.at && (
                                            <p className="text-xs text-muted-foreground">
                                                {date(step.at)}
                                            </p>
                                        )}
                                    </div>
                                </li>
                            );
                        })}
                    </ol>
                )}

                {request.can_cancel &&
                    request.status !== 'awaiting_deposit' && (
                        <div className="border-t pt-4">
                            <CancelRequest request={request} />
                        </div>
                    )}

                {request.notes && (
                    <div className="text-sm">
                        <p className="font-medium">{t('Your notes')}</p>
                        <p className="text-muted-foreground">{request.notes}</p>
                    </div>
                )}
            </div>
        </>
    );
}

function CancelRequest({ request }: { request: RequestDetail }) {
    const { t } = useTrans();

    if (!request.can_cancel) {
        return null;
    }

    const quote = request.cancel_quote;

    return (
        <Dialog>
            <DialogTrigger asChild>
                <Button variant="ghost">{t('Cancel request')}</Button>
            </DialogTrigger>
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>{t('Cancel this request?')}</DialogTitle>
                    <DialogDescription>
                        {cancelText(quote, t)}
                    </DialogDescription>
                </DialogHeader>
                {quote && (
                    <p className="text-sm text-muted-foreground">
                        {t(
                            'Refunds go back to the card or mobile money wallet you paid with, usually within a few working days.',
                        )}
                    </p>
                )}
                <DialogFooter>
                    <DialogClose asChild>
                        <Button variant="outline">
                            {t('Keep my request')}
                        </Button>
                    </DialogClose>
                    <Form action={cancel(request.id)}>
                        {({ processing }) => (
                            <Button variant="destructive" disabled={processing}>
                                {t('Cancel request')}
                            </Button>
                        )}
                    </Form>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}

function cancelText(quote: RequestDetail['cancel_quote'], t: Translate) {
    if (!quote) {
        return t('You haven’t paid anything, so there’s nothing to refund.');
    }

    if (!quote.kept) {
        return t('We’ll refund your full deposit of :amount.', {
            amount: quote.refund.formatted,
        });
    }

    return t(
        'We’ve already found your item, so we keep :kept (:percent% of the deposit) and refund :refund.',
        {
            kept: quote.kept.formatted,
            percent: quote.fee_percent,
            refund: quote.refund.formatted,
        },
    );
}

RequestShow.layout = {
    breadcrumbs: [{ title: 'My requests', href: index() }],
};
