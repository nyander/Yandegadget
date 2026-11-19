import { Form, Head, Link } from '@inertiajs/react';
import { ImageOff, Receipt, Search } from 'lucide-react';
import Heading from '@/components/heading';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { show as receipt } from '@/routes/staff/sales';
import { create, index } from '@/routes/staff/sell';
import type { Price, SellableUnit } from '@/types';

type Props = {
    search: string;
    units: SellableUnit[];
    today: {
        count: number;
        total: Price | null;
        sales: {
            id: number;
            reference: string;
            product: string;
            price_paid: Price;
            payment_method: string;
            sold_at: string;
            seller: string;
        }[];
    };
};

export default function SellIndex({ search, units, today }: Props) {
    return (
        <>
            <Head title="Sell" />
            <div className="mx-auto grid w-full max-w-6xl gap-8 p-4 sm:p-6 xl:grid-cols-[1fr_20rem]">
                <div className="space-y-5">
                    <Heading
                        title="Sell an item"
                        description="Find the item by its reference (on the label), serial number or name."
                    />

                    <Form
                        action={index()}
                        method="get"
                        className="flex gap-2"
                        options={{ preserveState: true }}
                    >
                        <div className="relative flex-1">
                            <Search className="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground" />
                            <Input
                                name="q"
                                type="search"
                                defaultValue={search}
                                autoFocus
                                placeholder="e.g. YG-000012 or iPhone"
                                aria-label="Find an item"
                                className="h-12 pl-9 text-base"
                            />
                        </div>
                        <Button type="submit" size="lg" className="h-12">
                            Find
                        </Button>
                    </Form>

                    <p className="text-sm text-muted-foreground">
                        {search
                            ? `${units.length} match${units.length === 1 ? '' : 'es'} in store`
                            : 'Recently arrived in store'}
                    </p>

                    {units.length === 0 ? (
                        <div className="rounded-xl border border-dashed p-8 text-center text-muted-foreground">
                            Nothing in store matches that.
                        </div>
                    ) : (
                        <ul className="grid grid-cols-[repeat(auto-fill,minmax(16rem,1fr))] gap-3">
                            {units.map((unit) => (
                                <li key={unit.id}>
                                    <Link
                                        href={create(unit.id)}
                                        className="flex gap-3 rounded-xl border p-3 transition hover:bg-muted/50 focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                                    >
                                        <div className="size-16 shrink-0 overflow-hidden rounded-lg bg-muted">
                                            {unit.image ? (
                                                <img
                                                    src={unit.image}
                                                    alt=""
                                                    className="size-full object-cover"
                                                />
                                            ) : (
                                                <div className="flex size-full items-center justify-center text-muted-foreground">
                                                    <ImageOff className="size-5" />
                                                </div>
                                            )}
                                        </div>
                                        <div className="min-w-0 flex-1">
                                            <p className="truncate font-medium">
                                                {unit.product}
                                            </p>
                                            <p className="text-sm text-muted-foreground">
                                                {unit.reference} · Grade{' '}
                                                {unit.condition.grade}
                                            </p>
                                            <div className="mt-1 flex items-center gap-2">
                                                <span className="font-semibold">
                                                    {unit.listed?.formatted ??
                                                        'No rate set'}
                                                </span>
                                                {unit.status === 'reserved' && (
                                                    <Badge variant="secondary">
                                                        Reserved
                                                    </Badge>
                                                )}
                                            </div>
                                        </div>
                                    </Link>
                                </li>
                            ))}
                        </ul>
                    )}
                </div>

                <aside className="space-y-3 xl:pt-16">
                    <div className="rounded-xl border p-4">
                        <p className="text-sm text-muted-foreground">
                            Today&rsquo;s sales
                        </p>
                        <p className="mt-1 text-2xl font-semibold">
                            {today.total?.formatted ?? '—'}
                        </p>
                        <p className="text-sm text-muted-foreground">
                            {today.count} item{today.count === 1 ? '' : 's'}{' '}
                            sold
                        </p>
                    </div>
                    {today.sales.length > 0 && (
                        <ul className="divide-y rounded-xl border">
                            {today.sales.map((sale) => (
                                <li key={sale.id}>
                                    <Link
                                        href={receipt(sale.id)}
                                        className="flex items-center gap-3 p-3 text-sm hover:bg-muted/50"
                                    >
                                        <Receipt className="size-4 shrink-0 text-muted-foreground" />
                                        <div className="min-w-0 flex-1">
                                            <p className="truncate font-medium">
                                                {sale.product}
                                            </p>
                                            <p className="text-muted-foreground">
                                                {new Date(
                                                    sale.sold_at,
                                                ).toLocaleTimeString('en-GB', {
                                                    hour: '2-digit',
                                                    minute: '2-digit',
                                                })}{' '}
                                                · {sale.payment_method} ·{' '}
                                                {sale.seller}
                                            </p>
                                        </div>
                                        <span className="font-medium">
                                            {sale.price_paid.formatted}
                                        </span>
                                    </Link>
                                </li>
                            ))}
                        </ul>
                    )}
                </aside>
            </div>
        </>
    );
}

SellIndex.layout = {
    breadcrumbs: [{ title: 'Sell', href: index() }],
};
