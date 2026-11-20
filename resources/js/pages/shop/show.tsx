import { Head, Link } from '@inertiajs/react';
import { ChevronLeft, ImageOff, MapPin } from 'lucide-react';
import { useState } from 'react';
import { Badge } from '@/components/ui/badge';
import { cn } from '@/lib/utils';
import { index as shopIndex } from '@/routes/shop';
import type { ProductDetail, ShopImage } from '@/types';

type Props = {
    product: ProductDetail;
    rateUpdatedAt: string | null;
};

export default function ShopShow({ product, rateUpdatedAt }: Props) {
    const [active, setActive] = useState(0);
    const image: ShopImage | undefined = product.images[active];
    const specs = Object.entries(product.specs);
    const summary = (
        product.description ??
        `${product.name}, second-hand from the UK, at Yande Gadgets in Legon, Accra.`
    ).slice(0, 160);

    return (
        <>
            <Head title={product.name}>
                <meta
                    head-key="description"
                    name="description"
                    content={summary}
                />
                <meta
                    head-key="og:title"
                    property="og:title"
                    content={product.name}
                />
                <meta
                    head-key="og:description"
                    property="og:description"
                    content={summary}
                />
                {product.images[0] && (
                    <meta
                        head-key="og:image"
                        property="og:image"
                        content={product.images[0].large}
                    />
                )}
            </Head>

            <div className="mx-auto max-w-7xl px-4 py-6 sm:px-6">
                <Link
                    href={shopIndex({
                        query: { category: product.category.slug },
                    })}
                    className="inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground"
                >
                    <ChevronLeft className="size-4" /> {product.category.name}
                </Link>

                <div className="mt-4 grid gap-8 lg:grid-cols-2">
                    <div>
                        <div className="aspect-square overflow-hidden rounded-xl border bg-muted">
                            {image ? (
                                <img
                                    src={image.large}
                                    alt={image.alt || product.name}
                                    className="size-full object-contain"
                                />
                            ) : (
                                <div className="flex size-full items-center justify-center text-muted-foreground">
                                    <ImageOff className="size-10" aria-hidden />
                                    <span className="sr-only">
                                        No photo yet
                                    </span>
                                </div>
                            )}
                        </div>
                        {product.images.length > 1 && (
                            <div className="mt-3 flex gap-2 overflow-x-auto">
                                {product.images.map((img, i) => (
                                    <button
                                        key={img.thumb}
                                        type="button"
                                        onClick={() => setActive(i)}
                                        className={cn(
                                            'size-16 shrink-0 overflow-hidden rounded-md border-2',
                                            i === active
                                                ? 'border-foreground'
                                                : 'border-transparent opacity-70 hover:opacity-100',
                                        )}
                                        aria-label={`Show photo ${i + 1}`}
                                        aria-pressed={i === active}
                                    >
                                        <img
                                            src={img.thumb}
                                            alt=""
                                            className="size-full object-cover"
                                        />
                                    </button>
                                ))}
                            </div>
                        )}
                    </div>

                    <div>
                        {product.brand && (
                            <p className="text-sm text-muted-foreground">
                                {product.brand}
                            </p>
                        )}
                        <h1 className="text-2xl font-semibold tracking-tight sm:text-3xl">
                            {product.name}
                        </h1>
                        {product.description && (
                            <p className="mt-4 whitespace-pre-line text-muted-foreground">
                                {product.description}
                            </p>
                        )}

                        <h2 className="mt-8 font-semibold">
                            {product.units.length === 1
                                ? '1 available'
                                : `${product.units.length} available`}
                        </h2>
                        <p className="text-sm text-muted-foreground">
                            Each item is graded on its own. Prices are in cedis
                            at today&rsquo;s rate
                            {rateUpdatedAt
                                ? ` (updated ${new Date(rateUpdatedAt).toLocaleDateString('en-GB', { day: 'numeric', month: 'short' })})`
                                : ''}
                            ; the final price is agreed in store.
                        </p>

                        <ul className="mt-4 space-y-3">
                            {product.units.map((unit) => (
                                <li
                                    key={unit.reference}
                                    className="rounded-xl border p-4"
                                >
                                    <div className="flex items-start justify-between gap-4">
                                        <div>
                                            <div className="flex items-center gap-2">
                                                <span className="flex size-7 items-center justify-center rounded-md bg-muted text-sm font-semibold">
                                                    {unit.condition.grade}
                                                </span>
                                                <span className="font-medium">
                                                    {unit.condition.name}
                                                </span>
                                                {unit.status === 'reserved' && (
                                                    <Badge variant="secondary">
                                                        Reserved
                                                    </Badge>
                                                )}
                                            </div>
                                            {unit.condition.description && (
                                                <p className="mt-2 text-sm text-muted-foreground">
                                                    {unit.condition.description}
                                                </p>
                                            )}
                                            {unit.condition_notes && (
                                                <p className="mt-1 text-sm">
                                                    {unit.condition_notes}
                                                </p>
                                            )}
                                        </div>
                                        <p className="shrink-0 text-lg font-semibold">
                                            {unit.price.formatted}
                                        </p>
                                    </div>
                                    {unit.photos.length > 0 && (
                                        <div className="mt-3 flex gap-2 overflow-x-auto">
                                            {unit.photos.map((photo) => (
                                                <a
                                                    key={photo.thumb}
                                                    href={photo.large}
                                                    target="_blank"
                                                    rel="noreferrer"
                                                    className="size-14 shrink-0 overflow-hidden rounded-md border"
                                                >
                                                    <img
                                                        src={photo.thumb}
                                                        alt={`${unit.condition.name} item photo`}
                                                        className="size-full object-cover"
                                                    />
                                                </a>
                                            ))}
                                        </div>
                                    )}
                                    <p className="mt-3 text-xs text-muted-foreground">
                                        Ref {unit.reference}
                                    </p>
                                </li>
                            ))}
                        </ul>

                        <div className="mt-6 flex gap-3 rounded-xl bg-muted/60 p-4 text-sm">
                            <MapPin
                                className="mt-0.5 size-4 shrink-0 text-brand-green"
                                aria-hidden
                            />
                            <p>
                                Come and see it at 27 Ayawaso, Legon Boundary,
                                Accra. Quote the reference so we can have it
                                ready.
                            </p>
                        </div>

                        {specs.length > 0 && (
                            <>
                                <h2 className="mt-8 font-semibold">
                                    Specifications
                                </h2>
                                <dl className="mt-3 divide-y rounded-xl border text-sm">
                                    {specs.map(([key, value]) => (
                                        <div
                                            key={key}
                                            className="grid grid-cols-3 gap-4 px-4 py-2.5"
                                        >
                                            <dt className="text-muted-foreground">
                                                {key}
                                            </dt>
                                            <dd className="col-span-2">
                                                {value}
                                            </dd>
                                        </div>
                                    ))}
                                </dl>
                            </>
                        )}
                    </div>
                </div>
            </div>
        </>
    );
}
