import { Head, Link, router } from '@inertiajs/react';
import { ChevronLeft, ChevronRight, X } from 'lucide-react';
import { ProductGrid } from '@/components/shop/product-card';
import { Button } from '@/components/ui/button';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { tk, useTrans } from '@/hooks/use-trans';
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
    newest: tk('Newest'),
    price_asc: tk('Price: low to high'),
    price_desc: tk('Price: high to low'),
};

export default function ShopIndex({
    products,
    categories,
    filters,
    activeCategory,
}: Props) {
    const { t } = useTrans();
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
        ? t('Results for “:query”', { query: filters.q })
        : (activeCategory?.name ?? t('Shop all'));

    return (
        <>
            <Head title={title} />

            <div className="mx-auto max-w-7xl px-4 py-8 sm:px-6">
                <div className="flex flex-wrap items-end justify-between gap-4">
                    <div>
                        <h1
                            key={title}
                            className="enter text-2xl font-semibold tracking-tight"
                        >
                            {title}
                        </h1>
                        <p className="mt-1 text-sm text-muted-foreground">
                            {products.total === 1
                                ? t('1 product in store')
                                : t(':count products in store', {
                                      count: products.total,
                                  })}
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
                        <SelectTrigger
                            className="w-48"
                            aria-label={t('Sort by')}
                        >
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            {(Object.keys(sortLabels) as ShopSort[]).map(
                                (sort) => (
                                    <SelectItem key={sort} value={sort}>
                                        {t(sortLabels[sort])}
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
                        label={t('All')}
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
                                {t('Search: :query', { query: filters.q })}{' '}
                                <X />
                            </Link>
                        </Button>
                    </div>
                )}

                <div className="mt-6">
                    {products.data.length > 0 ? (
                        <ProductGrid products={products.data} />
                    ) : (
                        <div className="enter rounded-xl border border-dashed p-10 text-center text-muted-foreground">
                            {t('Nothing matches that right now.')}{' '}
                            <Link
                                href={createRequest()}
                                className="font-medium text-foreground underline"
                            >
                                {t('Request it and we’ll find one for you.')}
                            </Link>
                        </div>
                    )}
                </div>

                {products.last_page > 1 && (
                    <nav
                        className="mt-8 flex flex-wrap justify-center gap-1"
                        aria-label={t('Pagination')}
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
                                        aria-label={pageLabel(link.label, t)}
                                    >
                                        <PageLabel label={link.label} />
                                    </Link>
                                </Button>
                            ) : (
                                <span
                                    key={i}
                                    className="flex items-center px-3 py-1.5 text-sm text-muted-foreground"
                                >
                                    <PageLabel label={link.label} />
                                </span>
                            ),
                        )}
                    </nav>
                )}
            </div>
        </>
    );
}

// Laravel's pagination links say "« Previous" and "Next »"; show arrows instead so they read in any language.
function isPrevious(label: string) {
    return label.includes('&laquo;') || label.includes('«');
}

function isNext(label: string) {
    return label.includes('&raquo;') || label.includes('»');
}

function pageLabel(label: string, t: (text: string) => string) {
    if (isPrevious(label)) {
        return t('Previous page');
    }

    if (isNext(label)) {
        return t('Next page');
    }

    return undefined;
}

function PageLabel({ label }: { label: string }) {
    if (isPrevious(label)) {
        return <ChevronLeft className="size-4" />;
    }

    if (isNext(label)) {
        return <ChevronRight className="size-4" />;
    }

    return <>{label}</>;
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
                'shrink-0 rounded-full border px-3 py-1.5 text-sm transition duration-200 active:scale-95',
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
