import { Head, Link, usePage } from '@inertiajs/react';
import { ChevronLeft, ImageOff, MapPin } from 'lucide-react';
import { useState } from 'react';
import type { CSSProperties } from 'react';
import { Badge } from '@/components/ui/badge';
import { intlLocale, useTrans } from '@/hooks/use-trans';
import { cn } from '@/lib/utils';
import { index as shopIndex } from '@/routes/shop';
import type { ProductDetail, ShopImage } from '@/types';

type Props = {
    product: ProductDetail;
    rateUpdatedAt: string | null;
};

export default function ShopShow({ product, rateUpdatedAt }: Props) {
    const { name: shopName } = usePage().props;
    const { t, locale } = useTrans();
    const [active, setActive] = useState(0);
    const image: ShopImage | undefined = product.images[active];
    const specs = Object.entries(product.specs);
    const summary = (
        product.description ??
        `${product.name}, second-hand from the UK, at ${shopName} in Legon, Accra.`
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
                    className="group inline-flex items-center gap-1 text-sm text-muted-foreground transition-colors hover:text-foreground"
                >
                    <ChevronLeft className="size-4 transition-transform group-hover:-translate-x-0.5" />{' '}
                    {product.category.name}
                </Link>

                <div className="mt-4 grid gap-8 lg:grid-cols-2">
                    <div className="enter">
                        <div className="aspect-square overflow-hidden rounded-xl border bg-muted">
                            {image ? (
                                <img
                                    key={image.large}
                                    src={image.large}
                                    alt={image.alt || product.name}
                                    className="size-full animate-in object-contain duration-300 fade-in zoom-in-[0.98]"
                                />
                            ) : (
                                <div className="flex size-full items-center justify-center text-muted-foreground">
                                    <ImageOff className="size-10" aria-hidden />
                                    <span className="sr-only">
                                        {t('No photo yet')}
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
                                            'size-16 shrink-0 overflow-hidden rounded-md border-2 transition duration-200 active:scale-95',
                                            i === active
                                                ? 'border-foreground'
                                                : 'border-transparent opacity-70 hover:opacity-100',
                                        )}
                                        aria-label={t('Show photo :number', {
                                            number: i + 1,
                                        })}
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

                    <div
                        className="enter"
                        style={{ '--i': 1 } as CSSProperties}
                    >
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
                                ? t('1 available')
                                : t(':count available', {
                                      count: product.units.length,
                                  })}
                        </h2>
                        <p className="text-sm text-muted-foreground">
                            {t(
                                'Each item is graded on its own. Prices are in cedis at today’s rate; the final price is agreed in store.',
                            )}
                            {rateUpdatedAt &&
                                ` ${t('Rate updated :date.', {
                                    date: new Date(
                                        rateUpdatedAt,
                                    ).toLocaleDateString(intlLocale(locale), {
                                        day: 'numeric',
                                        month: 'short',
                                    }),
                                })}`}
                        </p>

                        <ul className="mt-4 space-y-3">
                            {product.units.map((unit, i) => (
                                <li
                                    key={unit.reference}
                                    style={{ '--i': i + 2 } as CSSProperties}
                                    className="enter rounded-xl border p-4 transition-shadow duration-300 hover:shadow-md"
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
                                                        {t('Reserved')}
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
                                                    className="size-14 shrink-0 overflow-hidden rounded-md border transition hover:opacity-90 active:scale-95"
                                                >
                                                    <img
                                                        src={photo.thumb}
                                                        alt={t(
                                                            ':condition item photo',
                                                            {
                                                                condition:
                                                                    unit
                                                                        .condition
                                                                        .name,
                                                            },
                                                        )}
                                                        className="size-full object-cover"
                                                    />
                                                </a>
                                            ))}
                                        </div>
                                    )}
                                    <p className="mt-3 text-xs text-muted-foreground">
                                        {t('Ref :reference', {
                                            reference: unit.reference,
                                        })}
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
                                {t(
                                    'Come and see it at 27 Ayawaso, Legon Boundary, Accra. Quote the reference so we can have it ready.',
                                )}
                            </p>
                        </div>

                        {specs.length > 0 && (
                            <>
                                <h2 className="mt-8 font-semibold">
                                    {t('Specifications')}
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
