import { useLiveRefresh } from '@/hooks/use-live-refresh';
import { Head, Link, usePage } from '@inertiajs/react';
import { ImageOff, PackagePlus, Plus } from 'lucide-react';
import Heading from '@/components/heading';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { formatDate } from '@/lib/dates';
import { create, index, show } from '@/routes/supplier/offers';

type OfferRow = {
    id: number;
    reference: string;
    title: string;
    status: 'submitted' | 'accepted' | 'declined' | 'withdrawn';
    status_label: string;
    grade: string;
    asking_price: string;
    agreed_price: string | null;
    image: string | null;
    created_at: string | null;
};

type Props = {
    supplier: { name: string };
    stats: { waiting: number; bought: number; sold: number };
    offers: OfferRow[];
};

export default function SupplierOffers({ supplier, stats, offers }: Props) {
    const { name: shopName } = usePage().props;
    useLiveRefresh(15, ['stats', 'offers'], {
        channel: 'me',
        topics: ['supplier-offers'],
    });
    return (
        <>
            <Head title="My offers" />
            <div className="mx-auto w-full max-w-4xl space-y-6 p-4 sm:p-6">
                <div className="flex flex-wrap items-start justify-between gap-4">
                    <Heading
                        title="My offers"
                        description={`Items ${supplier.name} has offered to ${shopName}.`}
                    />
                    <Button asChild>
                        <Link href={create()}>
                            <Plus /> Offer an item
                        </Link>
                    </Button>
                </div>

                <dl className="grid grid-cols-3 gap-3">
                    {(
                        [
                            ['Waiting for review', stats.waiting],
                            ['Bought by us', stats.bought],
                            ['Sold in Accra', stats.sold],
                        ] as const
                    ).map(([label, value]) => (
                        <div key={label} className="rounded-xl border p-4">
                            <dt className="text-sm text-muted-foreground">
                                {label}
                            </dt>
                            <dd className="mt-1 text-2xl font-semibold">
                                {value}
                            </dd>
                        </div>
                    ))}
                </dl>

                {offers.length === 0 ? (
                    <div className="rounded-xl border border-dashed p-10 text-center">
                        <PackagePlus className="mx-auto mb-3 size-8 text-muted-foreground" />
                        <p className="font-medium">No offers yet</p>
                        <p className="mt-1 text-sm text-muted-foreground">
                            Offer us phones, laptops, TVs and other electronics.
                            Add good photos and an honest grade.
                        </p>
                    </div>
                ) : (
                    <ul className="divide-y rounded-xl border">
                        {offers.map((offer) => (
                            <li key={offer.id}>
                                <Link
                                    href={show(offer.id)}
                                    className="flex items-center gap-4 p-4 hover:bg-muted/50"
                                >
                                    <div className="size-14 shrink-0 overflow-hidden rounded-lg bg-muted">
                                        {offer.image ? (
                                            <img
                                                src={offer.image}
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
                                            {offer.title}
                                        </p>
                                        <p className="text-sm text-muted-foreground">
                                            {offer.reference} · Grade{' '}
                                            {offer.grade} ·{' '}
                                            {formatDate(offer.created_at)}
                                        </p>
                                    </div>
                                    <div className="text-right">
                                        <Badge
                                            variant={
                                                offer.status === 'accepted'
                                                    ? 'default'
                                                    : 'secondary'
                                            }
                                        >
                                            {offer.status_label}
                                        </Badge>
                                        <p className="mt-1 text-sm font-medium">
                                            {offer.agreed_price ??
                                                offer.asking_price}
                                        </p>
                                    </div>
                                </Link>
                            </li>
                        ))}
                    </ul>
                )}
            </div>
        </>
    );
}

SupplierOffers.layout = {
    breadcrumbs: [{ title: 'My offers', href: index() }],
};
