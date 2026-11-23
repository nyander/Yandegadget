import { Head, Link, usePage } from '@inertiajs/react';
import {
    ArrowRight,
    Banknote,
    Handshake,
    PackageSearch,
    Plus,
    Settings,
    ShieldCheck,
    Store,
    Truck,
} from 'lucide-react';
import type { CSSProperties, ComponentType, ReactNode } from 'react';
import { RequestStatusBadge } from '@/components/request-status-badge';
import { useTrans } from '@/hooks/use-trans';
import { formatDate } from '@/lib/dates';
import { dashboard, home } from '@/routes';
import { edit as profile } from '@/routes/profile';
import {
    create as createRequest,
    index as myRequests,
    show as showRequest,
} from '@/routes/requests';
import { index as staffSell } from '@/routes/staff/sell';
import { index as staffShipments } from '@/routes/staff/shipments';
import { index as supplierOffers } from '@/routes/supplier/offers';
import type { RequestListItem } from '@/types';

type Props = {
    recentRequests: RequestListItem[];
    activeRequests: number;
    readyRequests: number;
};

type Shortcut = {
    title: string;
    text: string;
    href: string;
    icon: ComponentType<{ className?: string }>;
    external?: boolean;
    badge?: ReactNode;
};

export default function Dashboard({
    recentRequests,
    activeRequests,
    readyRequests,
}: Props) {
    const { auth } = usePage().props;
    const { t, locale } = useTrans();
    const can = (permission: string) => auth.permissions.includes(permission);
    const isCustomer = can('requests.create') && !can('requests.manage');
    const firstName = auth.user?.name?.split(' ')[0] ?? '';

    const shortcuts: Shortcut[] = [
        ...(isCustomer
            ? [
                  {
                      title: t('My requests'),
                      text:
                          activeRequests > 0
                              ? t(':count in progress', {
                                    count: activeRequests,
                                })
                              : t('Items you’ve asked us to find'),
                      href: myRequests.url(),
                      icon: PackageSearch,
                      badge:
                          readyRequests > 0 ? (
                              <span className="rounded-full bg-brand-green px-2 py-0.5 text-xs font-medium text-white motion-safe:animate-pulse">
                                  {t(':count ready', { count: readyRequests })}
                              </span>
                          ) : null,
                  },
                  {
                      title: t('Request an item'),
                      text: t('Tell us what you want and we’ll find it'),
                      href: createRequest.url(),
                      icon: Plus,
                  },
              ]
            : []),
        ...(can('sales.record')
            ? [
                  {
                      title: 'Sell',
                      text: 'Record a sale in store',
                      href: staffSell.url(),
                      icon: Banknote,
                  },
              ]
            : []),
        ...(can('shipments.receive')
            ? [
                  {
                      title: 'Shipments',
                      text: 'Check in items from the UK',
                      href: staffShipments.url(),
                      icon: Truck,
                  },
              ]
            : []),
        ...(can('supplier-offers.submit')
            ? [
                  {
                      title: 'My offers',
                      text: 'Items you’ve offered to the shop',
                      href: supplierOffers.url(),
                      icon: Handshake,
                  },
              ]
            : []),
        ...(can('catalogue.manage')
            ? [
                  {
                      title: 'Back office',
                      text: 'Stock, sales, reports and settings',
                      href: '/admin',
                      icon: ShieldCheck,
                      external: true,
                  },
              ]
            : []),
        {
            title: t('Browse the shop'),
            text: t('See what’s in store in Accra'),
            href: home.url(),
            icon: Store,
        },
        {
            title: t('Settings'),
            text: t('Your details, password and sign-in'),
            href: profile.url(),
            icon: Settings,
        },
    ];

    return (
        <>
            <Head title={t('Account')} />
            <div className="mx-auto w-full max-w-4xl space-y-8 p-4 sm:p-6">
                <div className="enter">
                    <h1 className="text-2xl font-semibold tracking-tight">
                        {firstName
                            ? t('Hello, :name', { name: firstName })
                            : t('Hello')}
                    </h1>
                    <p className="mt-1 text-muted-foreground">
                        {t('What would you like to do today?')}
                    </p>
                </div>

                <div className="grid gap-3 sm:grid-cols-2">
                    {shortcuts.map((item, i) => (
                        <ShortcutCard key={item.href} item={item} index={i} />
                    ))}
                </div>

                {isCustomer && recentRequests.length > 0 && (
                    <section
                        className="enter"
                        style={{ '--i': shortcuts.length } as CSSProperties}
                    >
                        <div className="mb-3 flex items-baseline justify-between">
                            <h2 className="font-semibold">
                                {t('Latest requests')}
                            </h2>
                            <Link
                                href={myRequests()}
                                className="text-sm text-muted-foreground transition-colors hover:text-foreground"
                            >
                                {t('See all')}
                            </Link>
                        </div>
                        <ul className="divide-y rounded-xl border">
                            {recentRequests.map((r) => (
                                <li key={r.id}>
                                    <Link
                                        href={showRequest(r.id)}
                                        className="flex flex-wrap items-center justify-between gap-3 p-4 transition-colors hover:bg-muted/50"
                                    >
                                        <div className="min-w-0">
                                            <p className="font-medium">
                                                {r.description}
                                            </p>
                                            <p className="text-sm text-muted-foreground">
                                                {r.reference} ·{' '}
                                                {formatDate(
                                                    r.created_at,
                                                    locale,
                                                )}
                                            </p>
                                        </div>
                                        <RequestStatusBadge
                                            status={r.status}
                                            label={r.status_label}
                                        />
                                    </Link>
                                </li>
                            ))}
                        </ul>
                    </section>
                )}
            </div>
        </>
    );
}

function ShortcutCard({ item, index }: { item: Shortcut; index: number }) {
    const Icon = item.icon;
    const className =
        'enter group flex items-start gap-4 rounded-xl border bg-card p-4 transition duration-300 hover:-translate-y-0.5 hover:border-foreground/20 hover:shadow-md active:scale-[0.99] motion-reduce:hover:translate-y-0';
    const content = (
        <>
            <span className="flex size-10 shrink-0 items-center justify-center rounded-lg bg-brand-green/10 text-brand-green transition-colors duration-300 group-hover:bg-brand-green group-hover:text-white">
                <Icon className="size-5" />
            </span>
            <span className="min-w-0 flex-1">
                <span className="flex flex-wrap items-center gap-2 font-medium">
                    {item.title}
                    {item.badge}
                </span>
                <span className="mt-0.5 block text-sm text-muted-foreground">
                    {item.text}
                </span>
            </span>
            <ArrowRight className="mt-1 size-4 shrink-0 text-muted-foreground opacity-0 transition duration-300 group-hover:translate-x-0.5 group-hover:opacity-100" />
        </>
    );
    const style = { '--i': index } as CSSProperties;

    return item.external ? (
        <a href={item.href} className={className} style={style}>
            {content}
        </a>
    ) : (
        <Link href={item.href} prefetch className={className} style={style}>
            {content}
        </Link>
    );
}

Dashboard.layout = {
    breadcrumbs: [
        {
            title: 'Account',
            href: dashboard(),
        },
    ],
};
