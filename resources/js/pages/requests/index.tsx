import { Head, Link } from '@inertiajs/react';
import { PackageSearch, Plus } from 'lucide-react';
import type { CSSProperties } from 'react';
import Heading from '@/components/heading';
import { RequestStatusBadge } from '@/components/request-status-badge';
import { Button } from '@/components/ui/button';
import { useTrans } from '@/hooks/use-trans';
import { formatDate } from '@/lib/dates';
import { create, index, show } from '@/routes/requests';
import type { RequestListItem } from '@/types';

export default function RequestsIndex({
    requests,
}: {
    requests: RequestListItem[];
}) {
    const { t, locale } = useTrans();

    return (
        <>
            <Head title={t('My requests')} />
            <div className="mx-auto w-full max-w-3xl space-y-6 p-4 sm:p-6">
                <div className="flex flex-wrap items-start justify-between gap-4">
                    <Heading
                        title={t('My requests')}
                        description={t(
                            'Items you’ve asked us to find for you.',
                        )}
                    />
                    <Button asChild>
                        <Link href={create()}>
                            <Plus /> {t('Request an item')}
                        </Link>
                    </Button>
                </div>

                {requests.length === 0 ? (
                    <div className="enter rounded-xl border border-dashed p-10 text-center">
                        <PackageSearch className="mx-auto mb-3 size-8 text-muted-foreground motion-safe:animate-bounce" />
                        <p className="font-medium">
                            {t('Can’t find what you want in the shop?')}
                        </p>
                        <p className="mt-1 text-sm text-muted-foreground">
                            {t(
                                'Tell us what you’re looking for, pay a small deposit, and we’ll find it in the UK for you.',
                            )}
                        </p>
                    </div>
                ) : (
                    <ul className="divide-y rounded-xl border">
                        {requests.map((r, i) => (
                            <li
                                key={r.id}
                                className="enter"
                                style={{ '--i': i } as CSSProperties}
                            >
                                <Link
                                    href={show(r.id)}
                                    prefetch
                                    className="flex flex-wrap items-center justify-between gap-3 p-4 transition-colors hover:bg-muted/50"
                                >
                                    <div className="min-w-0">
                                        <p className="font-medium">
                                            {r.description}
                                        </p>
                                        <p className="text-sm text-muted-foreground">
                                            {r.reference} · {r.category} ·{' '}
                                            {t('Grade :grade', {
                                                grade: r.condition,
                                            })}{' '}
                                            · {formatDate(r.created_at, locale)}
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
                )}
            </div>
        </>
    );
}

RequestsIndex.layout = {
    breadcrumbs: [{ title: 'My requests', href: index() }],
};
