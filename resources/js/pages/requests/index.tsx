import { Head, Link } from '@inertiajs/react';
import { PackageSearch, Plus } from 'lucide-react';
import Heading from '@/components/heading';
import { RequestStatusBadge } from '@/components/request-status-badge';
import { Button } from '@/components/ui/button';
import { formatDate } from '@/lib/dates';
import { create, index, show } from '@/routes/requests';
import type { RequestListItem } from '@/types';

export default function RequestsIndex({
    requests,
}: {
    requests: RequestListItem[];
}) {
    return (
        <>
            <Head title="My requests" />
            <div className="mx-auto w-full max-w-3xl space-y-6 p-4 sm:p-6">
                <div className="flex flex-wrap items-start justify-between gap-4">
                    <Heading
                        title="My requests"
                        description="Items you've asked us to find for you."
                    />
                    <Button asChild>
                        <Link href={create()}>
                            <Plus /> Request an item
                        </Link>
                    </Button>
                </div>

                {requests.length === 0 ? (
                    <div className="rounded-xl border border-dashed p-10 text-center">
                        <PackageSearch className="mx-auto mb-3 size-8 text-muted-foreground" />
                        <p className="font-medium">
                            Can&rsquo;t find what you want in the shop?
                        </p>
                        <p className="mt-1 text-sm text-muted-foreground">
                            Tell us what you&rsquo;re looking for, pay a small
                            deposit, and we&rsquo;ll find it in the UK for you.
                        </p>
                    </div>
                ) : (
                    <ul className="divide-y rounded-xl border">
                        {requests.map((r) => (
                            <li key={r.id}>
                                <Link
                                    href={show(r.id)}
                                    className="flex flex-wrap items-center justify-between gap-3 p-4 hover:bg-muted/50"
                                >
                                    <div className="min-w-0">
                                        <p className="font-medium">
                                            {r.description}
                                        </p>
                                        <p className="text-sm text-muted-foreground">
                                            {r.reference} · {r.category} · Grade{' '}
                                            {r.condition} ·{' '}
                                            {formatDate(r.created_at)}
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
