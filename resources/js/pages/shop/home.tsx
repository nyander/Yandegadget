import { Head, Link } from '@inertiajs/react';
import { ArrowRight, BadgeCheck, Handshake, Plane } from 'lucide-react';
import { ProductGrid } from '@/components/shop/product-card';
import { Button } from '@/components/ui/button';
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
        title: 'Sourced in the UK',
        text: 'Every item is bought and checked in the UK, then shipped to Accra.',
    },
    {
        icon: BadgeCheck,
        title: 'Honest condition grades',
        text: 'Each item is graded on its own, with notes on any wear.',
    },
    {
        icon: Handshake,
        title: 'Price agreed in store',
        text: 'See what is in stock online, then agree the final price with us in person.',
    },
];

export default function Home({ featured, newArrivals, categories }: Props) {
    const hasStock = featured.length + newArrivals.length > 0;

    return (
        <>
            <Head title="Second-hand electronics in Accra" />

            <section className="border-b bg-muted/40">
                <div className="mx-auto max-w-7xl px-4 py-12 sm:px-6 md:py-20">
                    <p className="text-sm font-medium text-brand-green">
                        Legon Boundary, Accra · since 2011
                    </p>
                    <h1 className="mt-3 max-w-2xl text-3xl font-semibold tracking-tight sm:text-5xl">
                        Quality UK electronics, in store in Accra.
                    </h1>
                    <p className="mt-4 max-w-xl text-muted-foreground sm:text-lg">
                        Phones, laptops, TVs, gaming and more. Browse what has
                        arrived, then come in and make it yours.
                    </p>
                    <div className="mt-8 flex flex-wrap gap-3">
                        <Button size="lg" asChild>
                            <Link href={shopIndex()}>
                                Browse the shop <ArrowRight />
                            </Link>
                        </Button>
                        <Button size="lg" variant="outline" asChild>
                            <Link href={createRequest()}>
                                Can&rsquo;t find it? Request it
                            </Link>
                        </Button>
                    </div>
                </div>
            </section>

            {categories.length > 0 && (
                <section className="mx-auto max-w-7xl px-4 pt-10 sm:px-6">
                    <div className="flex gap-2 overflow-x-auto pb-2">
                        {categories.map((category) => (
                            <Button
                                key={category.slug}
                                variant="outline"
                                size="sm"
                                className="shrink-0 rounded-full"
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
                    <h2 className="mb-4 text-xl font-semibold">Featured</h2>
                    <ProductGrid products={featured} />
                </section>
            )}

            {newArrivals.length > 0 && (
                <section className="mx-auto max-w-7xl px-4 pt-10 sm:px-6">
                    <div className="mb-4 flex items-baseline justify-between">
                        <h2 className="text-xl font-semibold">Just arrived</h2>
                        <Link
                            href={shopIndex()}
                            className="text-sm font-medium text-muted-foreground hover:text-foreground"
                        >
                            See everything
                        </Link>
                    </div>
                    <ProductGrid products={newArrivals} />
                </section>
            )}

            {!hasStock && (
                <section className="mx-auto max-w-7xl px-4 pt-10 sm:px-6">
                    <div className="rounded-xl border border-dashed p-10 text-center text-muted-foreground">
                        New stock is on its way from the UK. Check back soon.
                    </div>
                </section>
            )}

            <section className="mx-auto grid max-w-7xl gap-6 px-4 py-14 sm:px-6 md:grid-cols-3">
                {promises.map(({ icon: Icon, title, text }) => (
                    <div key={title} className="flex gap-3">
                        <Icon
                            className="mt-0.5 size-5 shrink-0 text-brand-green"
                            aria-hidden
                        />
                        <div>
                            <p className="font-medium">{title}</p>
                            <p className="mt-1 text-sm text-muted-foreground">
                                {text}
                            </p>
                        </div>
                    </div>
                ))}
            </section>
        </>
    );
}
