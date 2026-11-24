import { Form, Link, usePage } from '@inertiajs/react';
import { Search, ShieldCheck, User } from 'lucide-react';
import type { ReactNode } from 'react';
import AppLogoIcon from '@/components/app-logo-icon';
import { LanguageSwitcher } from '@/components/language-switcher';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { useTrans } from '@/hooks/use-trans';
import { dashboard, home, login, register } from '@/routes';
import { privacy, terms } from '@/routes/legal';
import { create as createRequest } from '@/routes/requests';
import { index as shopIndex } from '@/routes/shop';

const ADMIN_PERMISSIONS = [
    'catalogue.manage',
    'finances.manage',
    'settings.manage',
    'users.manage',
];

export default function ShopLayout({ children }: { children: ReactNode }) {
    const { auth, name } = usePage().props;
    const { t } = useTrans();
    const canUseAdmin = auth.permissions?.some((permission) =>
        ADMIN_PERMISSIONS.includes(permission),
    );
    const currentQuery =
        typeof window !== 'undefined'
            ? (new URLSearchParams(window.location.search).get('q') ?? '')
            : '';

    return (
        <div className="flex min-h-screen flex-col bg-background text-foreground">
            <div className="flex h-1">
                <div className="flex-1 bg-brand-red" />
                <div className="flex-1 bg-brand-gold" />
                <div className="flex-1 bg-brand-green" />
            </div>
            <header className="vt-header sticky top-0 z-30 border-b bg-background/95 backdrop-blur">
                <div className="mx-auto flex max-w-7xl items-center gap-4 px-4 py-3 sm:px-6">
                    <Link
                        href={home()}
                        className="group flex shrink-0 items-center gap-2"
                    >
                        <span className="flex size-8 items-center justify-center rounded-md bg-brand-green text-white transition-transform duration-300 group-hover:-rotate-6">
                            <AppLogoIcon className="size-5 fill-current" />
                        </span>
                        <span className="hidden font-semibold sm:inline">
                            {name}
                        </span>
                    </Link>

                    <Link
                        href={shopIndex()}
                        className="hidden text-sm font-medium text-muted-foreground transition-colors hover:text-foreground md:inline"
                    >
                        {t('Shop all')}
                    </Link>
                    <Link
                        href={createRequest()}
                        className="hidden text-sm font-medium whitespace-nowrap text-muted-foreground transition-colors hover:text-foreground lg:inline"
                    >
                        {t('Request an item')}
                    </Link>

                    <Form
                        action={shopIndex()}
                        method="get"
                        className="relative ml-auto w-full max-w-md"
                        options={{ preserveScroll: false }}
                    >
                        <Search className="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground" />
                        <Input
                            type="search"
                            name="q"
                            defaultValue={currentQuery}
                            placeholder={t('Search phones, laptops, TVs…')}
                            aria-label={t('Search the shop')}
                            className="pl-9 transition-shadow focus-visible:shadow-md"
                        />
                    </Form>

                    <nav className="flex shrink-0 items-center gap-1">
                        <LanguageSwitcher />
                        {auth.user ? (
                            <>
                                {canUseAdmin && (
                                    <Button variant="ghost" size="sm" asChild>
                                        <a href="/admin">
                                            <ShieldCheck />
                                            <span className="hidden sm:inline">
                                                {t('Admin')}
                                            </span>
                                        </a>
                                    </Button>
                                )}
                                <Button variant="ghost" size="sm" asChild>
                                    <Link href={dashboard()}>
                                        <User />
                                        <span className="hidden sm:inline">
                                            {t('Account')}
                                        </span>
                                    </Link>
                                </Button>
                            </>
                        ) : (
                            <>
                                <Button variant="ghost" size="sm" asChild>
                                    <Link href={login()}>{t('Log in')}</Link>
                                </Button>
                                <Button
                                    size="sm"
                                    asChild
                                    className="hidden sm:inline-flex"
                                >
                                    <Link href={register()}>
                                        {t('Register')}
                                    </Link>
                                </Button>
                            </>
                        )}
                    </nav>
                </div>
            </header>

            <main className="flex-1">{children}</main>

            <footer className="border-t bg-muted/40">
                <div className="mx-auto grid max-w-7xl gap-6 px-4 py-10 text-sm text-muted-foreground sm:px-6 md:grid-cols-3">
                    <div>
                        <p className="font-semibold text-foreground">{name}</p>
                        <p className="mt-2">
                            {t(
                                'Quality second-hand electronics from the UK, sold in Accra since 2011.',
                            )}
                        </p>
                    </div>
                    <div>
                        <p className="font-semibold text-foreground">
                            {t('Visit the store')}
                        </p>
                        <p className="mt-2">
                            27 Ayawaso, Legon Boundary
                            <br />
                            Accra, Ghana
                        </p>
                    </div>
                    <div>
                        <p className="font-semibold text-foreground">
                            {t('About our prices')}
                        </p>
                        <p className="mt-2">
                            {t(
                                'Prices are shown in cedis at today’s exchange rate. The final price is agreed with you in store.',
                            )}
                        </p>
                    </div>
                </div>
                <div className="mx-auto flex max-w-7xl flex-wrap gap-x-6 gap-y-2 border-t px-4 py-4 text-xs text-muted-foreground sm:px-6">
                    <span>
                        &copy; {new Date().getFullYear()} {name}
                    </span>
                    <Link href={privacy()} className="hover:text-foreground">
                        {t('Privacy policy')}
                    </Link>
                    <Link href={terms()} className="hover:text-foreground">
                        {t('Terms of use')}
                    </Link>
                </div>
            </footer>
        </div>
    );
}
