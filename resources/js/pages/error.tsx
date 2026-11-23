import { Head, Link } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { tk, useTrans } from '@/hooks/use-trans';
import { home } from '@/routes';
import { index as shopIndex } from '@/routes/shop';

const messages: Record<number, { title: string; text: string }> = {
    403: {
        title: tk('You can’t open this page'),
        text: tk(
            'Your account doesn’t have access to it. If you think it should, ask the shop.',
        ),
    },
    404: {
        title: tk('We couldn’t find that page'),
        text: tk('The item may have sold, or the link may be out of date.'),
    },
    429: {
        title: tk('Too many tries'),
        text: tk('Please wait a minute and try again.'),
    },
    500: {
        title: tk('Something went wrong on our side'),
        text: tk(
            'We’ve been told about it. Please try again in a few minutes.',
        ),
    },
    503: {
        title: tk('We’ll be right back'),
        text: tk('The site is being updated. Please try again shortly.'),
    },
};

export default function ErrorPage({ status }: { status: number }) {
    const { t } = useTrans();
    const message = messages[status] ?? messages[500];

    return (
        <>
            <Head title={t(message.title)} />
            <div className="enter mx-auto flex max-w-xl flex-col items-center px-4 py-24 text-center">
                <p className="enter-pop text-sm font-semibold text-brand-green">
                    {status}
                </p>
                <h1 className="mt-2 text-3xl font-semibold tracking-tight">
                    {t(message.title)}
                </h1>
                <p className="mt-3 text-muted-foreground">{t(message.text)}</p>
                <div className="mt-8 flex flex-wrap justify-center gap-3">
                    <Button asChild>
                        <Link href={shopIndex()}>{t('Browse the shop')}</Link>
                    </Button>
                    <Button variant="outline" asChild>
                        <Link href={home()}>{t('Home page')}</Link>
                    </Button>
                </div>
            </div>
        </>
    );
}
