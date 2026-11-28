import { Form, Head, Link } from '@inertiajs/react';
import { ChevronLeft, Smartphone } from 'lucide-react';
import { useState } from 'react';
import { Button } from '@/components/ui/button';
import { useTrans } from '@/hooks/use-trans';
import { cn } from '@/lib/utils';
import { index, store } from '@/routes/reservations';
import { show as shopShow } from '@/routes/shop';
import type { Price } from '@/types';

type Props = {
    item: {
        reference: string;
        name: string;
        slug: string;
        grade: string;
        condition: string;
        image: string | null;
    };
    price: Price;
    deposit: Price;
    depositPercent: number;
    days: number;
};

/** Choose how much to pay online, then go to mobile money / card checkout. */
export default function ReservationCreate({
    item,
    price,
    deposit,
    depositPercent,
    days,
}: Props) {
    const { t } = useTrans();
    const [option, setOption] = useState<'full' | 'deposit'>('full');
    const options = [
        {
            value: 'full' as const,
            title: t('Pay in full'),
            amount: price.formatted,
            text: t(
                'Pay :price now. Nothing to pay in store; just collect it.',
                {
                    price: price.formatted,
                },
            ),
        },
        {
            value: 'deposit' as const,
            title: t('Pay a deposit'),
            amount: deposit.formatted,
            text: t(
                'Pay :percent% now (:deposit). Pay the rest in store, where the final price can be agreed.',
                { percent: depositPercent, deposit: deposit.formatted },
            ),
        },
    ];

    return (
        <>
            <Head title={t('Reserve :name', { name: item.name })} />
            <div className="mx-auto w-full max-w-2xl space-y-6 p-4 sm:p-6">
                <Link
                    href={shopShow(item.slug)}
                    className="inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground"
                >
                    <ChevronLeft className="size-4" /> {item.name}
                </Link>

                <div className="enter flex items-center gap-4 rounded-xl border p-4">
                    {item.image && (
                        <img
                            src={item.image}
                            alt=""
                            className="size-20 shrink-0 rounded-lg border object-cover"
                        />
                    )}
                    <div className="min-w-0">
                        <h1 className="text-xl font-semibold tracking-tight">
                            {t('Reserve :name', { name: item.name })}
                        </h1>
                        <p className="text-sm text-muted-foreground">
                            {t('Grade :grade · :condition · Ref :reference', {
                                grade: item.grade,
                                condition: item.condition,
                                reference: item.reference,
                            })}
                        </p>
                    </div>
                </div>

                <Form
                    {...store.form({
                        product: item.slug,
                        reference: item.reference,
                    })}
                >
                    {({ processing }) => (
                        <div className="space-y-4">
                            <fieldset className="space-y-3">
                                <legend className="mb-2 font-medium">
                                    {t('How would you like to pay?')}
                                </legend>
                                {options.map((o) => (
                                    <label
                                        key={o.value}
                                        className={cn(
                                            'flex cursor-pointer items-start gap-3 rounded-xl border p-4 transition',
                                            option === o.value
                                                ? 'border-brand-green bg-brand-green/5 ring-1 ring-brand-green'
                                                : 'hover:border-foreground/20',
                                        )}
                                    >
                                        <input
                                            type="radio"
                                            name="payment_option"
                                            value={o.value}
                                            checked={option === o.value}
                                            onChange={() => setOption(o.value)}
                                            className="mt-1 accent-brand-green"
                                        />
                                        <span className="min-w-0 flex-1">
                                            <span className="flex items-baseline justify-between gap-3">
                                                <span className="font-medium">
                                                    {o.title}
                                                </span>
                                                <span className="text-lg font-semibold">
                                                    {o.amount}
                                                </span>
                                            </span>
                                            <span className="mt-1 block text-sm text-muted-foreground">
                                                {o.text}
                                            </span>
                                        </span>
                                    </label>
                                ))}
                            </fieldset>

                            <div className="rounded-xl bg-muted/60 p-4 text-sm">
                                <p>
                                    {t(
                                        'Once paid, it’s kept for you for :days days at 27 Ayawaso, Legon Boundary, Accra. If you can’t collect it in time, you get everything back.',
                                        { days },
                                    )}
                                </p>
                            </div>

                            <Button
                                type="submit"
                                size="lg"
                                className="w-full"
                                disabled={processing}
                            >
                                <Smartphone className="size-4" />
                                {t('Pay :amount with mobile money or card', {
                                    amount:
                                        option === 'full'
                                            ? price.formatted
                                            : deposit.formatted,
                                })}
                            </Button>
                            <p className="text-center text-xs text-muted-foreground">
                                {t(
                                    'MTN MoMo, Telecel Cash and AirtelTigo Money, or a bank card. The item is held for you for 30 minutes while you pay.',
                                )}
                            </p>
                        </div>
                    )}
                </Form>
            </div>
        </>
    );
}

ReservationCreate.layout = {
    breadcrumbs: [{ title: 'My reservations', href: index() }],
};
