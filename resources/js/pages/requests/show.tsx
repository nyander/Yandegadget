import { Form, Head, Link, usePage } from '@inertiajs/react';
import { Check, ChevronLeft, MapPin } from 'lucide-react';
import AlertError from '@/components/alert-error';
import { RequestStatusBadge } from '@/components/request-status-badge';
import { Button } from '@/components/ui/button';
import { formatDate } from '@/lib/dates';
import { cn } from '@/lib/utils';
import { cancel, index, pay } from '@/routes/requests';
import { show as shopShow } from '@/routes/shop';
import type { RequestDetail } from '@/types';

export default function RequestShow({ request }: { request: RequestDetail }) {
    const { errors } = usePage().props as { errors: Record<string, string> };
    const steps: { label: string; at: string | null }[] = [
        { label: 'Request made', at: request.created_at },
        { label: 'Deposit paid', at: request.paid_at },
        { label: 'Item found', at: request.sourced_at },
        { label: 'Ready in store', at: request.ready_at },
        { label: 'Collected', at: request.fulfilled_at },
    ];
    const messages = Object.values(errors ?? {});

    return (
        <>
            <Head title={`Request ${request.reference}`} />
            <div className="mx-auto w-full max-w-3xl space-y-6 p-4 sm:p-6">
                <Link
                    href={index()}
                    className="inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground"
                >
                    <ChevronLeft className="size-4" /> My requests
                </Link>

                <div className="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <p className="text-sm text-muted-foreground">
                            {request.reference} · {request.category}
                        </p>
                        <h1 className="text-2xl font-semibold">
                            {request.description}
                        </h1>
                        <p className="text-sm text-muted-foreground">
                            Grade {request.condition.grade} (
                            {request.condition.name}) or better
                        </p>
                    </div>
                    <RequestStatusBadge
                        status={request.status}
                        label={request.status_label}
                    />
                </div>

                {messages.length > 0 && <AlertError errors={messages} />}

                {request.status === 'awaiting_deposit' && (
                    <div className="space-y-3 rounded-xl border p-4">
                        <p>
                            Pay the {request.deposit?.formatted ?? ''} deposit
                            and we&rsquo;ll start looking.
                            {request.payment?.status === 'failed' &&
                                ' Your last payment didn’t go through.'}
                        </p>
                        <div className="flex flex-wrap gap-2">
                            <Form action={pay(request.id)}>
                                {({ processing }) => (
                                    <Button disabled={processing}>
                                        Pay deposit
                                    </Button>
                                )}
                            </Form>
                            {request.can_cancel && (
                                <Form action={cancel(request.id)}>
                                    {({ processing }) => (
                                        <Button
                                            variant="ghost"
                                            disabled={processing}
                                        >
                                            Cancel request
                                        </Button>
                                    )}
                                </Form>
                            )}
                        </div>
                    </div>
                )}

                {request.status === 'ready' && request.item && (
                    <div className="flex gap-3 rounded-xl border border-brand-green/40 bg-brand-green/10 p-4">
                        <MapPin className="mt-0.5 size-5 shrink-0 text-brand-green" />
                        <div className="text-sm">
                            <p className="font-medium">
                                {request.item.product} is reserved for you at
                                the Legon store.
                            </p>
                            <p className="mt-1">
                                Quote reference {request.item.reference}.
                                {request.item.price &&
                                    ` Listed at ${request.item.price.formatted}`}
                                {request.deposit &&
                                    `; your ${request.deposit.formatted} deposit comes off the price.`}
                            </p>
                            {request.item.shop_slug && (
                                <Link
                                    href={shopShow(request.item.shop_slug)}
                                    className="mt-2 inline-block font-medium underline"
                                >
                                    See it in the shop
                                </Link>
                            )}
                        </div>
                    </div>
                )}

                {request.status === 'cancelled' && (
                    <div className="rounded-xl border border-brand-red/40 bg-brand-red/10 p-4 text-sm">
                        <p>
                            Cancelled {formatDate(request.cancelled_at)}.{' '}
                            {request.cancel_reason}
                        </p>
                        {request.payment?.refunded && (
                            <p className="mt-1">
                                Your deposit of {request.deposit?.formatted} has
                                been refunded to your{' '}
                                {request.payment.channel === 'mobile_money'
                                    ? 'mobile money wallet'
                                    : 'card'}
                                .
                            </p>
                        )}
                    </div>
                )}

                {request.status !== 'cancelled' && (
                    <ol className="space-y-0">
                        {steps.map((step, i) => {
                            const done = step.at !== null;

                            return (
                                <li key={step.label} className="flex gap-3">
                                    <div className="flex flex-col items-center">
                                        <span
                                            className={cn(
                                                'flex size-7 items-center justify-center rounded-full border text-xs',
                                                done &&
                                                    'border-brand-green bg-brand-green text-white',
                                            )}
                                        >
                                            {done ? (
                                                <Check className="size-4" />
                                            ) : (
                                                i + 1
                                            )}
                                        </span>
                                        {i < steps.length - 1 && (
                                            <span className="h-6 w-px bg-border" />
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
                                            {step.label}
                                        </p>
                                        {step.at && (
                                            <p className="text-xs text-muted-foreground">
                                                {formatDate(step.at)}
                                            </p>
                                        )}
                                    </div>
                                </li>
                            );
                        })}
                    </ol>
                )}

                {request.notes && (
                    <div className="text-sm">
                        <p className="font-medium">Your notes</p>
                        <p className="text-muted-foreground">{request.notes}</p>
                    </div>
                )}
            </div>
        </>
    );
}

RequestShow.layout = {
    breadcrumbs: [{ title: 'My requests', href: index() }],
};
