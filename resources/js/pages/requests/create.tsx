import { Head, useForm } from '@inertiajs/react';
import { AlertTriangle, ShieldCheck } from 'lucide-react';
import type { CSSProperties } from 'react';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { useTrans } from '@/hooks/use-trans';
import { cn } from '@/lib/utils';
import { index, store } from '@/routes/requests';
import type { ConditionOption } from '@/types';

type Props = {
    categories: { id: number; name: string }[];
    conditions: ConditionOption[];
    depositsAvailable: boolean;
};

export default function RequestCreate({
    categories,
    conditions,
    depositsAvailable,
}: Props) {
    const { t } = useTrans();
    const form = useForm({
        description: '',
        category_id: '',
        condition_id: conditions[0] ? String(conditions[0].id) : '',
        notes: '',
    });
    const selected = conditions.find(
        (c) => String(c.id) === form.data.condition_id,
    );

    return (
        <>
            <Head title={t('Request an item')} />
            <div className="mx-auto w-full max-w-2xl space-y-6 p-4 sm:p-6">
                <div className="enter">
                    <Heading
                        title={t('Request an item')}
                        description={t(
                            'Tell us what you want. We’ll look for it in the UK and let you know when it’s in the Accra store.',
                        )}
                    />
                </div>

                {!depositsAvailable && (
                    <div className="enter flex gap-3 rounded-xl border border-brand-gold/50 bg-brand-gold/10 p-4 text-sm">
                        <AlertTriangle className="size-5 shrink-0" />
                        <p>
                            {t(
                                'Deposits can’t be taken online right now. Please try again later or visit the store.',
                            )}
                        </p>
                    </div>
                )}

                <form
                    className="enter space-y-6"
                    style={{ '--i': 1 } as CSSProperties}
                    onSubmit={(e) => {
                        e.preventDefault();
                        form.post(store.url());
                    }}
                >
                    <div className="space-y-2">
                        <Label htmlFor="description">
                            {t('What are you looking for?')}
                        </Label>
                        <Input
                            id="description"
                            value={form.data.description}
                            onChange={(e) =>
                                form.setData('description', e.target.value)
                            }
                            placeholder={t(
                                'e.g. iPhone 14 Pro 256GB, any colour',
                            )}
                            maxLength={255}
                            required
                        />
                        <InputError message={form.errors.description} />
                    </div>

                    <div className="space-y-2">
                        <Label htmlFor="category_id">{t('Category')}</Label>
                        <select
                            id="category_id"
                            value={form.data.category_id}
                            onChange={(e) =>
                                form.setData('category_id', e.target.value)
                            }
                            required
                            className="h-9 w-full rounded-md border bg-transparent px-3 text-sm shadow-xs focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                        >
                            <option value="" disabled>
                                {t('Choose a category')}
                            </option>
                            {categories.map((c) => (
                                <option key={c.id} value={c.id}>
                                    {c.name}
                                </option>
                            ))}
                        </select>
                        <InputError message={form.errors.category_id} />
                    </div>

                    <fieldset className="space-y-2">
                        <legend className="text-sm font-medium">
                            {t('Lowest condition you’d accept')}
                        </legend>
                        <div className="grid gap-2">
                            {conditions.map((c) => {
                                const active =
                                    form.data.condition_id === String(c.id);

                                return (
                                    <label
                                        key={c.id}
                                        className={cn(
                                            'flex cursor-pointer items-start gap-3 rounded-xl border p-4 transition duration-200 active:scale-[0.99]',
                                            active
                                                ? 'border-foreground bg-muted/30 ring-1 ring-foreground'
                                                : 'hover:bg-muted/50',
                                        )}
                                    >
                                        <input
                                            type="radio"
                                            name="condition_id"
                                            value={c.id}
                                            checked={active}
                                            onChange={() =>
                                                form.setData(
                                                    'condition_id',
                                                    String(c.id),
                                                )
                                            }
                                            className="mt-1"
                                        />
                                        <div className="flex-1">
                                            <p className="font-medium">
                                                {t('Grade :grade', {
                                                    grade: c.grade,
                                                })}{' '}
                                                – {c.name}
                                            </p>
                                            {c.description && (
                                                <p className="text-sm text-muted-foreground">
                                                    {c.description}
                                                </p>
                                            )}
                                        </div>
                                        <p className="text-sm font-semibold whitespace-nowrap">
                                            {c.deposit?.formatted ?? '—'}
                                            <span className="block text-xs font-normal text-muted-foreground">
                                                {t('deposit')}
                                            </span>
                                        </p>
                                    </label>
                                );
                            })}
                        </div>
                        <InputError message={form.errors.condition_id} />
                    </fieldset>

                    <div className="space-y-2">
                        <Label htmlFor="notes">
                            {t('Anything else? (optional)')}
                        </Label>
                        <textarea
                            id="notes"
                            rows={3}
                            value={form.data.notes}
                            onChange={(e) =>
                                form.setData('notes', e.target.value)
                            }
                            placeholder={t(
                                'Colour, storage, budget, when you need it…',
                            )}
                            className="w-full rounded-md border bg-transparent px-3 py-2 text-sm shadow-xs focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                        />
                        <InputError message={form.errors.notes} />
                    </div>

                    <div className="flex gap-3 rounded-xl bg-muted/60 p-4 text-sm">
                        <ShieldCheck className="size-5 shrink-0 text-brand-green" />
                        <p>
                            {t(
                                'You’ll pay the deposit securely with mobile money or card through Paystack. It counts towards the price when you collect the item, and it’s refunded if we can’t find one.',
                            )}
                        </p>
                    </div>

                    <Button
                        type="submit"
                        size="lg"
                        className="w-full"
                        disabled={form.processing || !depositsAvailable}
                    >
                        {form.processing && <Spinner />}
                        {selected?.deposit
                            ? t('Continue to pay :amount', {
                                  amount: selected.deposit.formatted,
                              })
                            : t('Continue to pay')}
                    </Button>
                </form>
            </div>
        </>
    );
}

RequestCreate.layout = {
    breadcrumbs: [{ title: 'My requests', href: index() }],
};
