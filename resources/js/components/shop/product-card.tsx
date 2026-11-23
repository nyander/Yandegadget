import { Link } from '@inertiajs/react';
import { ImageOff } from 'lucide-react';
import type { CSSProperties } from 'react';
import { Badge } from '@/components/ui/badge';
import { useTrans } from '@/hooks/use-trans';
import { show } from '@/routes/shop';
import type { ProductCard as ProductCardType } from '@/types';

export function ProductCard({
    product,
    index = 0,
}: {
    product: ProductCardType;
    index?: number;
}) {
    const { t } = useTrans();
    const reservedOnly =
        product.in_store_count === 0 && product.reserved_count > 0;

    return (
        <Link
            href={show(product.slug)}
            prefetch
            style={{ '--i': index } as CSSProperties}
            className="enter group flex flex-col overflow-hidden rounded-xl border bg-card transition duration-300 hover:-translate-y-1 hover:shadow-lg focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none active:translate-y-0 active:scale-[0.99] motion-reduce:hover:translate-y-0"
        >
            <div className="relative aspect-square overflow-hidden bg-muted">
                {product.image ? (
                    <img
                        src={product.image.thumb}
                        alt={product.image.alt || product.name}
                        loading="lazy"
                        className="size-full object-cover transition duration-500 group-hover:scale-105 motion-reduce:group-hover:scale-100"
                    />
                ) : (
                    <div className="flex size-full items-center justify-center text-muted-foreground">
                        <ImageOff className="size-8" aria-hidden />
                        <span className="sr-only">{t('No photo yet')}</span>
                    </div>
                )}
                {reservedOnly && (
                    <Badge
                        className="absolute top-2 left-2"
                        variant="secondary"
                    >
                        {t('Reserved')}
                    </Badge>
                )}
            </div>
            <div className="flex flex-1 flex-col gap-1 p-3">
                <p className="text-xs text-muted-foreground">
                    {product.brand ?? product.category.name}
                </p>
                <p className="line-clamp-2 leading-snug font-medium transition-colors group-hover:text-brand-green">
                    {product.name}
                </p>
                <div className="mt-auto flex items-end justify-between gap-2 pt-2">
                    {product.from_price ? (
                        <p className="font-semibold">
                            <span className="text-xs font-normal text-muted-foreground">
                                {t('from')}{' '}
                            </span>
                            {product.from_price.formatted}
                        </p>
                    ) : (
                        <p className="text-sm text-muted-foreground">
                            {t('Ask in store')}
                        </p>
                    )}
                    {product.in_store_count > 1 && (
                        <span className="text-xs text-muted-foreground">
                            {t(':count available', {
                                count: product.in_store_count,
                            })}
                        </span>
                    )}
                </div>
            </div>
        </Link>
    );
}

export function ProductGrid({ products }: { products: ProductCardType[] }) {
    return (
        <div className="grid grid-cols-2 gap-3 sm:gap-4 md:grid-cols-3 lg:grid-cols-4">
            {products.map((product, index) => (
                <ProductCard key={product.id} product={product} index={index} />
            ))}
        </div>
    );
}
