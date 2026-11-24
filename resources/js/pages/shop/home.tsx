import { Head, Link } from '@inertiajs/react';
import { ArrowRight, BadgeCheck, Handshake, Plane } from 'lucide-react';
import type { CSSProperties } from 'react';
import { ProductGrid } from '@/components/shop/product-card';
import { Button } from '@/components/ui/button';
import { tk, useTrans } from '@/hooks/use-trans';
import { create as createRequest } from '@/routes/requests';
import { index as shopIndex } from '@/routes/shop';
import type { CategoryLink, ProductCard } from '@/types';

type Props = {
    featured: ProductCard[];
    newArrivals: ProductCard[];
    categories: CategoryLink[];
};

const promises = [
    {
        icon: Plane,
        title: tk('Sourced in the UK'),
        text: tk(
            'Every item is bought and checked in the UK, then shipped to Accra.',
        ),
    },
    {
        icon: BadgeCheck,
        title: tk('Honest condition grades'),
        text: tk('Each item is graded on its own, with notes on any wear.'),
    },
    {
        icon: Handshake,
        title: tk('Price agreed in store'),
        text: tk(
            'See what is in stock online, then agree the final price with us in person.',
        ),
    },
];

export default function Home({ featured, newArrivals, categories }: Props) {
    const { t } = useTrans();
    const hasStock = featured.length + newArrivals.length > 0;
    const stagger = (i: number) => ({ '--i': i }) as CSSProperties;

    return (
        <>
            <Head title={t('Second-hand electronics in Accra')} />

            <section className="relative overflow-hidden border-b bg-muted/40">
                <div
                    aria-hidden
                    className="pointer-events-none absolute -top-24 -right-24 size-72 rounded-full bg-brand-gold/20 blur-3xl motion-safe:animate-[drift_14s_ease-in-out_infinite_alternate]"
                />
                <div
                    aria-hidden
                    className="pointer-events-none absolute -bottom-28 left-1/3 size-72 rounded-full bg-brand-green/15 blur-3xl motion-safe:animate-[drift_18s_ease-in-out_infinite_alternate-reverse]"
                />
                <div className="relative mx-auto max-w-7xl px-4 py-12 sm:px-6 md:py-20">
                    <p
                        className="enter text-sm font-medium text-brand-green"
                        style={stagger(0)}
                    >
                        {t('Legon Boundary, Accra · since 2011')}
                    </p>
                    <h1
                        className="enter mt-3 max-w-2xl text-3xl font-semibold tracking-tight sm:text-5xl"
                        style={stagger(1)}
                    >
                        {t('Quality UK electronics, in store in Accra.')}
                    </h1>
                    <p
                        className="enter mt-4 max-w-xl text-muted-foreground sm:text-lg"
                        style={stagger(2)}
                    >
                        {t(
                            'Phones, laptops, TVs, gaming and more. Browse what has arrived, then come in and make it yours.',
                        )}
                    </p>
                    <div
                        className="enter mt-8 flex flex-wrap gap-3"
                        style={stagger(3)}
                    >
                        <Button size="lg" asChild>
                            <Link href={shopIndex()} className="group">
                                {t('Browse the shop')}{' '}
                                <ArrowRight className="transition-transform group-hover:translate-x-1" />
                            </Link>
                        </Button>
                        <Button size="lg" variant="outline" asChild>
                            <Link href={createRequest()}>
                                {t('Can’t find it? Request it')}
                            </Link>
                        </Button>
                    </div>
                </div>
            </section>

            {categories.length > 0 && (
                <section className="mx-auto max-w-7xl px-4 pt-10 sm:px-6">
                    <div className="flex gap-2 overflow-x-auto pb-2">
                        {categories.map((category, i) => (
                            <Button
                                key={category.slug}
                                variant="outline"
                                size="sm"
                                style={stagger(i)}
                                className="enter shrink-0 rounded-full"
                                asChild
                            >
                                <Link
                                    href={shopIndex({
                                        query: { category: category.slug },
                                    })}
                                >
                                    {category.name}
                                    <span className="text-muted-foreground">
                                        {category.count}
                                    </span>
                                </Link>
                            </Button>
                        ))}
                    </div>
                </section>
            )}

            {featured.length > 0 && (
                <section className="mx-auto max-w-7xl px-4 pt-10 sm:px-6">
                    <h2 className="mb-4 text-xl font-semibold">
                        {t('Featured')}
                    </h2>
                    <ProductGrid products={featured} />
                </section>
            )}

            {newArrivals.length > 0 && (
                <section className="mx-auto max-w-7xl px-4 pt-10 sm:px-6">
                    <div className="mb-4 flex items-baseline justify-between">
                        <h2 className="text-xl font-semibold">
                            {t('Just arrived')}
                        </h2>
                        <Link
                            href={shopIndex()}
                            className="text-sm font-medium text-muted-foreground hover:text-foreground"
                        >
                            {t('See everything')}
                        </Link>
                    </div>
                    <ProductGrid products={newArrivals} />
                </section>
            )}

            {!hasStock && (
                <section className="mx-auto max-w-7xl px-4 pt-10 sm:px-6">
                    <div className="rounded-xl border border-dashed p-10 text-center text-muted-foreground">
                        {t(
                            'New stock is on its way from the UK. Check back soon.',
                        )}
                    </div>
                </section>
            )}

            <section className="mx-auto grid max-w-7xl gap-6 px-4 py-14 sm:px-6 md:grid-cols-3">
                {promises.map(({ icon: Icon, title, text }, i) => (
                    <div
                        key={title}
                        className="enter group flex gap-3"
                        style={stagger(i)}
                    >
                        <Icon
                            className="mt-0.5 size-5 shrink-0 text-brand-green transition-transform duration-300 group-hover:scale-110"
                            aria-hidden
                        />
                        <div>
                            <p className="font-medium">{t(title)}</p>
                            <p className="mt-1 text-sm text-muted-foreground">
                                {t(text)}
                            </p>
                        </div>
                    </div>
                ))}
            </section>
        </>
    );
}
