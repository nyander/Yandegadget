import { Head, Link, useForm } from '@inertiajs/react';
import {
    AlertTriangle,
    CheckCircle2,
    ChevronDown,
    ChevronLeft,
    ImageOff,
} from 'lucide-react';
import { useState } from 'react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { formatMoney } from '@/lib/money';
import { cn } from '@/lib/utils';
import { index, store } from '@/routes/staff/sell';
import type { PaymentMethodOption, Price, SellableUnit } from '@/types';

type Props = {
    unit: SellableUnit & {
        condition_notes: string | null;
        serial_number: string | null;
    };
    pricing: {
        listed: Price;
        floor: Price;
        floor_percent: number;
        currency: string;
        rate: { value: string; effective_at: string } | null;
        reservation: {
            kind: 'request' | 'online';
            reference: string;
            customer: string;
            deposit: Price;
            paid_in_full: boolean;
            awaiting_payment: boolean;
            collect_by: string | null;
        } | null;
        prefill?: Price;
    } | null;
    problem: string | null;
    paymentMethods: PaymentMethodOption[];
    today: string;
    earliest: string;
};

export default function SellCreate({
    unit,
    pricing,
    problem,
    paymentMethods,
    today,
    earliest,
}: Props) {
    const [showMore, setShowMore] = useState(false);
    const form = useForm({
        price: pricing
            ? ((pricing.prefill ?? pricing.listed).amount / 100).toFixed(2)
            : '',
        payment_method: 'cash',
        payment_reference: '',
        customer_name: '',
        customer_phone: '',
        notes: '',
        sold_on: today,
    });

    const price = Number.parseFloat(form.data.price);
    const priceMinor = Number.isFinite(price) ? Math.round(price * 100) : null;
    const belowFloor =
        pricing !== null &&
        priceMinor !== null &&
        priceMinor < pricing.floor.amount;
    const discount =
        pricing && priceMinor !== null && pricing.listed.amount > 0
            ? Math.round((1 - priceMinor / pricing.listed.amount) * 100)
            : null;
    const method = paymentMethods.find(
        (m) => m.value === form.data.payment_method,
    );

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        form.post(store.url(unit.id));
    };

    return (
        <>
            <Head title={`Sell ${unit.reference}`} />
            <div className="mx-auto w-full max-w-3xl space-y-6 p-4 sm:p-6">
                <Link
                    href={index()}
                    className="inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground"
                >
                    <ChevronLeft className="size-4" /> Find another item
                </Link>

                <div className="flex gap-4 rounded-xl border p-4">
                    <div className="size-20 shrink-0 overflow-hidden rounded-lg bg-muted">
                        {unit.image ? (
                            <img
                                src={unit.image}
                                alt=""
                                className="size-full object-cover"
                            />
                        ) : (
                            <div className="flex size-full items-center justify-center text-muted-foreground">
                                <ImageOff className="size-6" />
                            </div>
                        )}
                    </div>
                    <div className="min-w-0">
                        <h1 className="text-xl font-semibold">
                            {unit.product}
                        </h1>
                        <p className="text-sm text-muted-foreground">
                            {unit.reference} · Grade {unit.condition.grade} (
                            {unit.condition.name})
                            {unit.serial_number && ` · ${unit.serial_number}`}
                        </p>
                        {unit.condition_notes && (
                            <p className="mt-1 text-sm">
                                {unit.condition_notes}
                            </p>
                        )}
                    </div>
                </div>

                {!pricing ? (
                    <div className="flex gap-3 rounded-xl border border-brand-red/40 bg-brand-red/10 p-4 text-sm">
                        <AlertTriangle className="size-5 shrink-0 text-brand-red" />
                        <p>{problem}</p>
                    </div>
                ) : (
                    <form onSubmit={submit} className="space-y-6">
                        {pricing.reservation &&
                            pricing.reservation.awaiting_payment && (
                                <div className="rounded-xl border border-brand-red/40 bg-brand-red/10 p-4 text-sm">
                                    <p className="font-medium">
                                        Being paid for online by{' '}
                                        {pricing.reservation.customer} (
                                        {pricing.reservation.reference})
                                    </p>
                                    <p className="mt-1">
                                        Don&rsquo;t sell it yet. If the payment
                                        doesn&rsquo;t go through, it&rsquo;s
                                        back on sale within half an hour.
                                    </p>
                                </div>
                            )}
                        {pricing.reservation &&
                            !pricing.reservation.awaiting_payment && (
                                <div className="rounded-xl border border-brand-gold/50 bg-brand-gold/10 p-4 text-sm">
                                    <p className="font-medium">
                                        Reserved for{' '}
                                        {pricing.reservation.customer} (
                                        {pricing.reservation.kind === 'online'
                                            ? 'online reservation'
                                            : 'request'}{' '}
                                        {pricing.reservation.reference})
                                    </p>
                                    <p className="mt-1">
                                        {pricing.reservation.paid_in_full
                                            ? `Paid in full online: ${pricing.reservation.deposit.formatted}. Nothing to collect; just hand it over.`
                                            : `They’ve paid ${pricing.reservation.deposit.formatted} online.${priceMinor !== null ? ` Collect ${formatMoney(Math.max(0, priceMinor - pricing.reservation.deposit.amount) / 100, pricing.currency)} in store.` : ''}`}
                                    </p>
                                </div>
                            )}
                        <div className="grid grid-cols-2 gap-3">
                            <div className="rounded-xl bg-muted/60 p-4">
                                <p className="text-sm text-muted-foreground">
                                    Listed price
                                </p>
                                <p className="mt-1 text-2xl font-semibold">
                                    {pricing.listed.formatted}
                                </p>
                            </div>
                            <div className="rounded-xl bg-muted/60 p-4">
                                <p className="text-sm text-muted-foreground">
                                    Lowest you can accept
                                </p>
                                <p className="mt-1 text-2xl font-semibold">
                                    {pricing.floor.formatted}
                                </p>
                                <p className="text-xs text-muted-foreground">
                                    {pricing.floor_percent}% of listed
                                </p>
                            </div>
                        </div>

                        <div className="space-y-2">
                            <Label htmlFor="price" className="text-base">
                                Agreed price ({pricing.currency})
                            </Label>
                            <Input
                                id="price"
                                inputMode="decimal"
                                value={form.data.price}
                                onChange={(e) =>
                                    form.setData(
                                        'price',
                                        e.target.value.replace(/[^\d.]/g, ''),
                                    )
                                }
                                onFocus={(e) => e.target.select()}
                                className={cn(
                                    'h-16 text-3xl font-semibold',
                                    belowFloor &&
                                        'border-brand-red focus-visible:ring-brand-red',
                                )}
                                aria-invalid={belowFloor || undefined}
                                aria-describedby="price-hint"
                            />
                            <p
                                id="price-hint"
                                className={cn(
                                    'flex items-center gap-1.5 text-sm',
                                    belowFloor
                                        ? 'text-brand-red'
                                        : 'text-muted-foreground',
                                )}
                            >
                                {belowFloor ? (
                                    <>
                                        <AlertTriangle className="size-4" />{' '}
                                        Below the lowest price. The sale
                                        won&rsquo;t be accepted.
                                    </>
                                ) : discount !== null && priceMinor !== null ? (
                                    <>
                                        <CheckCircle2 className="size-4 text-brand-green" />
                                        {discount > 0
                                            ? `${discount}% off the listed price`
                                            : discount < 0
                                              ? `${formatMoney((priceMinor - pricing.listed.amount) / 100, pricing.currency)} above the listed price`
                                              : 'Full listed price'}
                                    </>
                                ) : null}
                            </p>
                            <InputError message={form.errors.price} />
                        </div>

                        <fieldset className="space-y-2">
                            <legend className="text-base font-medium">
                                Payment
                            </legend>
                            <div className="grid grid-cols-2 gap-2 sm:grid-cols-4">
                                {paymentMethods.map((m) => (
                                    <button
                                        key={m.value}
                                        type="button"
                                        onClick={() =>
                                            form.setData(
                                                'payment_method',
                                                m.value,
                                            )
                                        }
                                        aria-pressed={
                                            form.data.payment_method === m.value
                                        }
                                        className={cn(
                                            'h-12 rounded-lg border text-sm font-medium transition',
                                            form.data.payment_method === m.value
                                                ? 'border-foreground bg-foreground text-background'
                                                : 'hover:bg-muted',
                                        )}
                                    >
                                        {m.label}
                                    </button>
                                ))}
                            </div>
                            <InputError message={form.errors.payment_method} />
                            {method?.takes_reference && (
                                <div className="space-y-1 pt-2">
                                    <Label htmlFor="payment_reference">
                                        Transaction reference (optional)
                                    </Label>
                                    <Input
                                        id="payment_reference"
                                        value={form.data.payment_reference}
                                        onChange={(e) =>
                                            form.setData(
                                                'payment_reference',
                                                e.target.value,
                                            )
                                        }
                                        placeholder="e.g. MoMo transaction ID"
                                    />
                                    <InputError
                                        message={form.errors.payment_reference}
                                    />
                                </div>
                            )}
                        </fieldset>

                        <div>
                            <button
                                type="button"
                                onClick={() => setShowMore((v) => !v)}
                                className="flex items-center gap-1 text-sm font-medium text-muted-foreground hover:text-foreground"
                                aria-expanded={showMore}
                            >
                                <ChevronDown
                                    className={cn(
                                        'size-4 transition',
                                        showMore && 'rotate-180',
                                    )}
                                />
                                Customer, date and notes
                            </button>
                            {showMore && (
                                <div className="mt-3 grid gap-4 sm:grid-cols-2">
                                    <div className="space-y-1">
                                        <Label htmlFor="customer_name">
                                            Customer name
                                        </Label>
                                        <Input
                                            id="customer_name"
                                            value={form.data.customer_name}
                                            onChange={(e) =>
                                                form.setData(
                                                    'customer_name',
                                                    e.target.value,
                                                )
                                            }
                                        />
                                        <InputError
                                            message={form.errors.customer_name}
                                        />
                                    </div>
                                    <div className="space-y-1">
                                        <Label htmlFor="customer_phone">
                                            Customer phone
                                        </Label>
                                        <Input
                                            id="customer_phone"
                                            type="tel"
                                            value={form.data.customer_phone}
                                            onChange={(e) =>
                                                form.setData(
                                                    'customer_phone',
                                                    e.target.value,
                                                )
                                            }
                                        />
                                        <InputError
                                            message={form.errors.customer_phone}
                                        />
                                    </div>
                                    <div className="space-y-1">
                                        <Label htmlFor="sold_on">
                                            Date of sale
                                        </Label>
                                        <Input
                                            id="sold_on"
                                            type="date"
                                            min={earliest}
                                            max={today}
                                            value={form.data.sold_on}
                                            onChange={(e) =>
                                                form.setData(
                                                    'sold_on',
                                                    e.target.value,
                                                )
                                            }
                                        />
                                        <InputError
                                            message={form.errors.sold_on}
                                        />
                                    </div>
                                    <div className="space-y-1 sm:col-span-2">
                                        <Label htmlFor="notes">Notes</Label>
                                        <textarea
                                            id="notes"
                                            rows={2}
                                            value={form.data.notes}
                                            onChange={(e) =>
                                                form.setData(
                                                    'notes',
                                                    e.target.value,
                                                )
                                            }
                                            className="w-full rounded-md border bg-transparent px-3 py-2 text-sm shadow-xs focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                                        />
                                        <InputError
                                            message={form.errors.notes}
                                        />
                                    </div>
                                </div>
                            )}
                        </div>

                        <Button
                            type="submit"
                            size="lg"
                            className="h-14 w-full bg-brand-green text-base text-white hover:bg-brand-green/90"
                            disabled={
                                form.processing ||
                                belowFloor ||
                                priceMinor === null
                            }
                        >
                            {form.processing && <Spinner />}
                            Record sale
                            {priceMinor !== null &&
                                ` · ${formatMoney(priceMinor / 100, pricing.currency)}`}
                        </Button>
                    </form>
                )}
            </div>
        </>
    );
}

SellCreate.layout = {
    breadcrumbs: [{ title: 'Sell', href: index() }],
};
