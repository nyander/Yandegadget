import { Head, Link, router } from '@inertiajs/react';
import { X } from 'lucide-react';
import { ProductGrid } from '@/components/shop/product-card';
import { Button } from '@/components/ui/button';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { cn } from '@/lib/utils';
import { create as createRequest } from '@/routes/requests';
import { index as shopIndex } from '@/routes/shop';
import type { CategoryLink, Paginated, ProductCard, ShopSort } from '@/types';

type Props = {
    products: Paginated<ProductCard>;
    categories: CategoryLink[];
    filters: { category: string | null; q: string; sort: ShopSort };
    activeCategory: CategoryLink | null;
};

const sortLabels: Record<ShopSort, string> = {
    newest: 'Newest',
    price_asc: 'Price: low to high',
    price_desc: 'Price: high to low',
};

export default function ShopIndex({
    products,
    categories,
    filters,
    activeCategory,
}: Props) {
    const query = (overrides: Partial<Props['filters']>) => {
        const next = { ...filters, ...overrides };

        return Object.fromEntries(
            Object.entries(next).filter(
                ([key, value]) =>
                    value !== null &&
                    value !== '' &&
                    !(key === 'sort' && value === 'newest'),
            ),
        ) as Record<string, string>;
    };

    const title = filters.q
        ? `Results for “${filters.q}”`
        : (activeCategory?.name ?? 'Shop all');

    return (
        <>
            <Head title={title} />

            <div className="mx-auto max-w-7xl px-4 py-8 sm:px-6">
                <div className="flex flex-wrap items-end justify-between gap-4">
                    <div>
                        <h1 className="text-2xl font-semibold tracking-tight">
                            {title}
                        </h1>
                        <p className="mt-1 text-sm text-muted-foreground">
                            {products.total === 1
                                ? '1 product in store'
                                : `${products.total} products in store`}
                        </p>
                    </div>

                    <Select
                        value={filters.sort}
                        onValueChange={(sort) =>
                            router.get(
                                shopIndex.url({
                                    query: query({ sort: sort as ShopSort }),
                                }),
                                {},
                                { preserveScroll: true, preserveState: true },
                            )
                        }
                    >
                        <SelectTrigger className="w-48" aria-label="Sort by">
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            {(Object.keys(sortLabels) as ShopSort[]).map(
                                (sort) => (
                                    <SelectItem key={sort} value={sort}>
                                        {sortLabels[sort]}
                                    </SelectItem>
                                ),
                            )}
                        </SelectContent>
                    </Select>
                </div>

                <div className="mt-6 flex gap-2 overflow-x-auto pb-2">
                    <CategoryChip
                        href={shopIndex.url({
                            query: query({ category: null }),
                        })}
                        active={!filters.category}
                        label="All"
                    />
                    {categories.map((category) => (
                        <CategoryChip
                            key={category.slug}
                            href={shopIndex.url({
                                query: query({ category: category.slug }),
                            })}
                            active={filters.category === category.slug}
                            label={category.name}
                        />
                    ))}
                </div>

                {filters.q && (
                    <div className="mt-4">
                        <Button variant="secondary" size="sm" asChild>
                            <Link
                                href={shopIndex.url({
                                    query: query({ q: '' }),
                                })}
                            >
                                Search: {filters.q} <X />
                            </Link>
                        </Button>
                    </div>
                )}

                <div className="mt-6">
                    {products.data.length > 0 ? (
                        <ProductGrid products={products.data} />
                    ) : (
                        <div className="rounded-xl border border-dashed p-10 text-center text-muted-foreground">
                            Nothing matches that right now.{' '}
                            <Link
                                href={createRequest()}
                                className="font-medium text-foreground underline"
                            >
                                Request it
                            </Link>{' '}
                            and we&rsquo;ll find one for you.
                        </div>
                    )}
                </div>

                {products.last_page > 1 && (
                    <nav
                        className="mt-8 flex flex-wrap justify-center gap-1"
                        aria-label="Pagination"
                    >
                        {products.links.map((link, i) =>
                            link.url ? (
                                <Button
                                    key={i}
                                    variant={link.active ? 'default' : 'ghost'}
                                    size="sm"
                                    asChild
                                >
                                    <Link
                                        href={link.url}
                                        preserveScroll
                                        dangerouslySetInnerHTML={{
                                            __html: link.label,
                                        }}
                                    />
                                </Button>
                            ) : (
                                <span
                                    key={i}
                                    className="px-3 py-1.5 text-sm text-muted-foreground"
                                    dangerouslySetInnerHTML={{
                                        __html: link.label,
                                    }}
                                />
                            ),
                        )}
                    </nav>
                )}
            </div>
        </>
    );
}

function CategoryChip({
    href,
    active,
    label,
}: {
    href: string;
    active: boolean;
    label: string;
}) {
    return (
        <Link
            href={href}
            preserveScroll
            className={cn(
                'shrink-0 rounded-full border px-3 py-1.5 text-sm transition',
                active
                    ? 'border-foreground bg-foreground text-background'
                    : 'hover:bg-muted',
            )}
            aria-current={active ? 'page' : undefined}
        >
            {label}
        </Link>
    );
}
