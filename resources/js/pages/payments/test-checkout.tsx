import { Form, Head } from '@inertiajs/react';
import { CreditCard, FlaskConical, Loader2, Smartphone } from 'lucide-react';
import { useRef, useState } from 'react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { cn } from '@/lib/utils';
import { decide } from '@/routes/payments/test';
import type { Price } from '@/types';

type Props = {
    payment: {
        reference: string;
        amount: Price;
        status: string;
        what: string;
        email: string;
    };
};

const networks = [
    { value: 'mtn', label: 'MTN MoMo', colour: 'bg-yellow-400 text-black' },
    {
        value: 'telecel',
        label: 'Telecel Cash',
        colour: 'bg-red-600 text-white',
    },
    {
        value: 'airteltigo',
        label: 'AirtelTigo Money',
        colour: 'bg-blue-600 text-white',
    },
] as const;

/**
 * Test checkout, used on your computer instead of Paystack (PAYMENTS_DRIVER=fake).
 * It looks and behaves like paying by mobile money, but no money moves.
 */
export default function TestCheckout({ payment }: Props) {
    const [channel, setChannel] = useState<'mobile_money' | 'card'>(
        'mobile_money',
    );
    const [network, setNetwork] = useState<string>('mtn');
    const [phone, setPhone] = useState('024 123 4567');
    const [waiting, setWaiting] = useState(false);
    const outcome = useRef<'approve' | 'decline'>('approve');

    return (
        <>
            <Head title="Pay" />
            <div className="mx-auto w-full max-w-md space-y-5 p-4 sm:p-6">
                <div className="flex items-center gap-2 rounded-lg border border-dashed border-brand-gold bg-brand-gold/10 px-3 py-2 text-xs">
                    <FlaskConical className="size-4 shrink-0" />
                    Test payment: no real money moves. On the live site this is
                    Paystack&rsquo;s checkout.
                </div>

                <div className="rounded-xl border p-5 text-center">
                    <p className="text-sm text-muted-foreground">
                        {payment.what}
                    </p>
                    <p className="mt-1 text-3xl font-semibold tracking-tight">
                        {payment.amount.formatted}
                    </p>
                    <p className="mt-1 text-xs text-muted-foreground">
                        {payment.email}
                    </p>
                </div>

                <div className="grid grid-cols-2 gap-2">
                    {[
                        {
                            value: 'mobile_money' as const,
                            label: 'Mobile money',
                            icon: Smartphone,
                        },
                        {
                            value: 'card' as const,
                            label: 'Card',
                            icon: CreditCard,
                        },
                    ].map((c) => (
                        <button
                            key={c.value}
                            type="button"
                            onClick={() => setChannel(c.value)}
                            className={cn(
                                'flex items-center justify-center gap-2 rounded-lg border p-3 text-sm font-medium transition',
                                channel === c.value
                                    ? 'border-brand-green ring-1 ring-brand-green'
                                    : 'hover:border-foreground/20',
                            )}
                        >
                            <c.icon className="size-4" /> {c.label}
                        </button>
                    ))}
                </div>

                <Form
                    {...decide.form(payment.reference)}
                    transform={(data) => ({
                        ...data,
                        outcome: outcome.current,
                        channel,
                        network,
                        phone,
                    })}
                    onStart={() => setWaiting(true)}
                    onFinish={() => setWaiting(false)}
                >
                    {({ processing }) => (
                        <div className="space-y-4">
                            {channel === 'mobile_money' ? (
                                <>
                                    <div className="grid grid-cols-3 gap-2">
                                        {networks.map((n) => (
                                            <button
                                                key={n.value}
                                                type="button"
                                                onClick={() =>
                                                    setNetwork(n.value)
                                                }
                                                className={cn(
                                                    'rounded-lg px-2 py-3 text-xs font-semibold transition',
                                                    n.colour,
                                                    network === n.value
                                                        ? 'ring-2 ring-foreground ring-offset-2'
                                                        : 'opacity-70 hover:opacity-100',
                                                )}
                                            >
                                                {n.label}
                                            </button>
                                        ))}
                                    </div>
                                    <div className="space-y-2">
                                        <Label htmlFor="phone">
                                            Mobile money number
                                        </Label>
                                        <Input
                                            id="phone"
                                            inputMode="tel"
                                            value={phone}
                                            onChange={(e) =>
                                                setPhone(e.target.value)
                                            }
                                        />
                                    </div>
                                </>
                            ) : (
                                <div className="space-y-2">
                                    <Label htmlFor="card">Card number</Label>
                                    <Input
                                        id="card"
                                        value="4084 0840 8408 4081"
                                        readOnly
                                    />
                                    <p className="text-xs text-muted-foreground">
                                        Paystack&rsquo;s test card. Nothing is
                                        charged.
                                    </p>
                                </div>
                            )}

                            {waiting || processing ? (
                                <div className="flex items-center justify-center gap-2 rounded-lg bg-muted p-4 text-sm">
                                    <Loader2 className="size-4 animate-spin" />
                                    {channel === 'mobile_money'
                                        ? 'Approve the payment on your phone…'
                                        : 'Confirming…'}
                                </div>
                            ) : (
                                <div className="grid gap-2">
                                    <Button
                                        type="submit"
                                        name="outcome"
                                        value="approve"
                                        size="lg"
                                    >
                                        {channel === 'mobile_money'
                                            ? 'Approve on phone'
                                            : 'Pay'}{' '}
                                        {payment.amount.formatted}
                                    </Button>
                                    <Button
                                        type="submit"
                                        name="outcome"
                                        value="decline"
                                        variant="outline"
                                    >
                                        Decline (to test a failed payment)
                                    </Button>
                                </div>
                            )}
                        </div>
                    )}
                </Form>
            </div>
        </>
    );
}
