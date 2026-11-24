import { Head, useForm, usePage } from '@inertiajs/react';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { update } from '@/routes/supplier/profile';

type SupplierDetails = {
    name: string;
    contact_name: string | null;
    email: string | null;
    phone: string | null;
    address: string | null;
};

export default function SupplierProfile({
    supplier,
    isNew,
}: {
    supplier: SupplierDetails;
    isNew: boolean;
}) {
    const { name: shopName } = usePage().props;
    const form = useForm({
        name: supplier.name ?? '',
        contact_name: supplier.contact_name ?? '',
        email: supplier.email ?? '',
        phone: supplier.phone ?? '',
        address: supplier.address ?? '',
    });

    const field = (
        key: keyof typeof form.data,
        label: string,
        props: React.ComponentProps<typeof Input> = {},
    ) => (
        <div className="space-y-1">
            <Label htmlFor={key}>{label}</Label>
            <Input
                id={key}
                value={form.data[key]}
                onChange={(e) => form.setData(key, e.target.value)}
                {...props}
            />
            <InputError message={form.errors[key]} />
        </div>
    );

    return (
        <>
            <Head title="Your business" />
            <div className="mx-auto w-full max-w-xl space-y-6 p-4 sm:p-6">
                <Heading
                    title={
                        isNew ? 'Tell us about your business' : 'Your business'
                    }
                    description={
                        isNew
                            ? `We need these details before you can offer items to ${shopName}.`
                            : 'The details we use to contact and pay you.'
                    }
                />
                <form
                    className="space-y-4"
                    onSubmit={(e) => {
                        e.preventDefault();
                        form.put(update.url());
                    }}
                >
                    {field('name', 'Business name', { required: true })}
                    {field('contact_name', 'Contact name', { required: true })}
                    {field('email', 'Email', { type: 'email', required: true })}
                    {field('phone', 'Phone', { type: 'tel', required: true })}
                    <div className="space-y-1">
                        <Label htmlFor="address">Address</Label>
                        <textarea
                            id="address"
                            rows={3}
                            value={form.data.address}
                            onChange={(e) =>
                                form.setData('address', e.target.value)
                            }
                            required
                            className="w-full rounded-md border bg-transparent px-3 py-2 text-sm shadow-xs focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                        />
                        <InputError message={form.errors.address} />
                    </div>
                    <Button type="submit" disabled={form.processing}>
                        {form.processing && <Spinner />}
                        {isNew ? 'Save and continue' : 'Save'}
                    </Button>
                </form>
            </div>
        </>
    );
}
