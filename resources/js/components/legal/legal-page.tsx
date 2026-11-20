import { Head } from '@inertiajs/react';
import type { ReactNode } from 'react';

export type LegalProps = {
    contact: { email: string | null; phone: string | null; address: string };
    dpcRegistration: string | null;
    updatedOn: string;
};

export function LegalPage({
    title,
    updatedOn,
    children,
}: {
    title: string;
    updatedOn: string;
    children: ReactNode;
}) {
    return (
        <>
            <Head title={title} />
            <article className="mx-auto max-w-3xl px-4 py-12 sm:px-6">
                <h1 className="text-3xl font-semibold tracking-tight">
                    {title}
                </h1>
                <p className="mt-2 text-sm text-muted-foreground">
                    Last updated {updatedOn}
                </p>
                <div className="mt-8 space-y-4 leading-relaxed [&_h2]:mt-10 [&_h2]:text-xl [&_h2]:font-semibold [&_li]:ml-5 [&_li]:list-disc [&_ul]:space-y-1">
                    {children}
                </div>
            </article>
        </>
    );
}

export function ContactLines({ contact }: { contact: LegalProps['contact'] }) {
    return (
        <ul>
            <li>In person: Yande Gadgets, {contact.address}</li>
            {contact.email && (
                <li>
                    Email:{' '}
                    <a className="underline" href={`mailto:${contact.email}`}>
                        {contact.email}
                    </a>
                </li>
            )}
            {contact.phone && <li>Phone or WhatsApp: {contact.phone}</li>}
        </ul>
    );
}
