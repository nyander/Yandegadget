import { createInertiaApp } from '@inertiajs/react';
import { Toaster } from '@/components/ui/sonner';
import { TooltipProvider } from '@/components/ui/tooltip';
import { initializeTheme } from '@/hooks/use-appearance';
import AppLayout from '@/layouts/app-layout';
import AuthLayout from '@/layouts/auth-layout';
import SettingsLayout from '@/layouts/settings/layout';
import ShopLayout from '@/layouts/shop-layout';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

void createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    layout: (name) => {
        switch (true) {
            case name.startsWith('shop/'):
            case name.startsWith('legal/'):
            case name === 'error':
                return ShopLayout;
            case name.startsWith('auth/'):
                return AuthLayout;
            case name.startsWith('settings/'):
                return [AppLayout, SettingsLayout];
            default:
                return AppLayout;
        }
    },
    strictMode: true,
    withApp(app) {
        return (
            <TooltipProvider delayDuration={0}>
                {app}
                <Toaster />
            </TooltipProvider>
        );
    },
    progress: {
        color: '#006B3F',
        delay: 150,
    },
    defaults: {
        // Fade between pages where the browser supports it. Filters, sorting
        // and other in-page updates keep their place without a transition.
        visitOptions: (_href, options) => ({
            ...options,
            viewTransition:
                options.viewTransition ||
                (options.method === 'get' &&
                    !options.preserveState &&
                    !options.only?.length &&
                    typeof document !== 'undefined' &&
                    'startViewTransition' in document),
        }),
    },
});

// This will set light / dark mode on load...
initializeTheme();
