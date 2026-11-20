import { Form, Head, Link } from '@inertiajs/react';
import { ChevronLeft } from 'lucide-react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { formatDate } from '@/lib/dates';
import { index, withdraw } from '@/routes/supplier/offers';

type OfferDetail = {
    id: number;
    reference: string;
    title: string;
    brand: string | null;
    model: string | null;
    serial_number: string | null;
    description: string | null;
    category: string;
    condition: string;
    status: 'submitted' | 'accepted' | 'declined' | 'withdrawn';
    status_label: string;
    asking_price: string;
    agreed_price: string | null;
    decline_reason: string | null;
    reviewed_at: string | null;
    created_at: string | null;
    photos: { thumb: string; full: string }[];
    can_withdraw: boolean;
};

export default function SupplierOfferShow({ offer }: { offer: OfferDetail }) {
    const details: [string, string | null][] = [
        ['Category', offer.category],
        ['Condition', offer.condition],
        ['Brand', offer.brand],
        ['Model', offer.model],
        ['Serial / IMEI', offer.serial_number],
        ['Your price', offer.asking_price],
        ['Agreed price', offer.agreed_price],
        ['Offered', formatDate(offer.created_at)],
    ];

    return (
        <>
            <Head title={`Offer ${offer.reference}`} />
            <div className="mx-auto w-full max-w-3xl space-y-6 p-4 sm:p-6">
                <Link
                    href={index()}
                    className="inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground"
                >
                    <ChevronLeft className="size-4" /> My offers
                </Link>

                <div className="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <p className="text-sm text-muted-foreground">
                            {offer.reference}
                        </p>
                        <h1 className="text-2xl font-semibold">
                            {offer.title}
                        </h1>
                    </div>
                    <Badge
                        variant={
                            offer.status === 'accepted'
                                ? 'default'
                                : 'secondary'
                        }
                    >
                        {offer.status_label}
                    </Badge>
                </div>

                {offer.status === 'accepted' && (
                    <div className="rounded-xl border border-brand-green/40 bg-brand-green/10 p-4 text-sm">
                        We&rsquo;re buying this for {offer.agreed_price}.
                        We&rsquo;ll be in touch to arrange payment and
                        collection.
                    </div>
                )}
                {offer.status === 'declined' && (
                    <div className="rounded-xl border border-brand-red/40 bg-brand-red/10 p-4 text-sm">
                        Declined {formatDate(offer.reviewed_at)}:{' '}
                        {offer.decline_reason}
                    </div>
                )}

                {offer.photos.length > 0 && (
                    <div className="flex flex-wrap gap-2">
                        {offer.photos.map((photo, i) => (
                            <a
                                key={photo.thumb}
                                href={photo.full}
                                target="_blank"
                                rel="noreferrer"
                                className="size-24 overflow-hidden rounded-lg border"
                            >
                                <img
                                    src={photo.thumb}
                                    alt={`Photo ${i + 1}`}
                                    className="size-full object-cover"
                                />
                            </a>
                        ))}
                    </div>
                )}

                <dl className="divide-y rounded-xl border text-sm">
                    {details
                        .filter(([, value]) => value)
                        .map(([label, value]) => (
                            <div
                                key={label}
                                className="grid grid-cols-3 gap-4 px-4 py-2.5"
                            >
                                <dt className="text-muted-foreground">
                                    {label}
                                </dt>
                                <dd className="col-span-2">{value}</dd>
                            </div>
                        ))}
                </dl>

                {offer.description && (
                    <p className="text-sm whitespace-pre-line">
                        {offer.description}
                    </p>
                )}

                {offer.can_withdraw && (
                    <Form action={withdraw(offer.id)}>
                        {({ processing }) => (
                            <Button variant="outline" disabled={processing}>
                                Withdraw offer
                            </Button>
                        )}
                    </Form>
                )}
            </div>
        </>
    );
}

SupplierOfferShow.layout = {
    breadcrumbs: [{ title: 'My offers', href: index() }],
};
