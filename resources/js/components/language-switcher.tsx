import { router, usePage } from '@inertiajs/react';
import { Check, Languages } from 'lucide-react';
import { useState } from 'react';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { useTrans } from '@/hooks/use-trans';
import { cn } from '@/lib/utils';
import { update } from '@/routes/locale';

export function LanguageSwitcher({
    className,
    showName = true,
}: {
    className?: string;
    showName?: boolean;
}) {
    const { locales } = usePage().props;
    const { t, locale } = useTrans();
    const [switching, setSwitching] = useState(false);
    const current = locales.find((l) => l.code === locale) ?? locales[0];

    if (locales.length < 2) {
        return null;
    }

    const choose = (code: string) => {
        if (code === locale) {
            return;
        }

        router.post(
            update.url(),
            { locale: code },
            {
                preserveScroll: true,
                viewTransition: true,
                onStart: () => setSwitching(true),
                onFinish: () => setSwitching(false),
            },
        );
    };

    return (
        <DropdownMenu>
            <DropdownMenuTrigger asChild>
                <Button
                    variant="ghost"
                    size="sm"
                    className={cn('gap-1.5', className)}
                    aria-label={t('Language')}
                    data-test="language-switcher"
                >
                    <Languages
                        className={cn(
                            'transition-transform duration-500',
                            switching && 'rotate-180',
                        )}
                    />
                    {showName && (
                        <span className="hidden sm:inline">
                            {current?.name}
                        </span>
                    )}
                </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end" className="min-w-44">
                <DropdownMenuLabel className="text-xs font-normal text-muted-foreground">
                    {t('Language')}
                </DropdownMenuLabel>
                <DropdownMenuSeparator />
                {locales.map((option) => (
                    <DropdownMenuItem
                        key={option.code}
                        lang={option.code}
                        onSelect={() => choose(option.code)}
                        className="justify-between"
                    >
                        {option.name}
                        {option.code === locale && (
                            <Check className="size-4 text-brand-green" />
                        )}
                    </DropdownMenuItem>
                ))}
            </DropdownMenuContent>
        </DropdownMenu>
    );
}
