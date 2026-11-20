import { Head, useForm } from '@inertiajs/react';
import { Camera, X } from 'lucide-react';
import { useMemo } from 'react';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { index, store } from '@/routes/supplier/offers';

type Props = {
    categories: { id: number; name: string }[];
    conditions: {
        id: number;
        grade: string;
        name: string;
        description: string | null;
    }[];
    currency: string;
};

const selectClass =
    'h-9 w-full rounded-md border bg-transparent px-3 text-sm shadow-xs focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none';

export default function SupplierOfferCreate({
    categories,
    conditions,
    currency,
}: Props) {
    const form = useForm<{
        title: string;
        category_id: string;
        condition_id: string;
        brand: string;
        model: string;
        serial_number: string;
        description: string;
        asking_price: string;
        photos: File[];
    }>({
        title: '',
        category_id: '',
        condition_id: '',
        brand: '',
        model: '',
        serial_number: '',
        description: '',
        asking_price: '',
        photos: [],
    });

    const previews = useMemo(
        () => form.data.photos.map((file) => URL.createObjectURL(file)),
        [form.data.photos],
    );
    const photoError =
        form.errors.photos ??
        Object.entries(form.errors).find(([key]) =>
            key.startsWith('photos.'),
        )?.[1];

    return (
        <>
            <Head title="Offer an item" />
            <div className="mx-auto w-full max-w-2xl space-y-6 p-4 sm:p-6">
                <Heading
                    title="Offer an item"
                    description="We'll review it and let you know if we'd like to buy it."
                />

                <form
                    className="space-y-5"
                    onSubmit={(e) => {
                        e.preventDefault();
                        form.post(store.url(), { forceFormData: true });
                    }}
                >
                    <div className="space-y-1">
                        <Label htmlFor="title">Item</Label>
                        <Input
                            id="title"
                            value={form.data.title}
                            onChange={(e) =>
                                form.setData('title', e.target.value)
                            }
                            placeholder="e.g. MacBook Air M2 13-inch 256GB"
                            required
                        />
                        <InputError message={form.errors.title} />
                    </div>

                    <div className="grid gap-4 sm:grid-cols-2">
                        <div className="space-y-1">
                            <Label htmlFor="category_id">Category</Label>
                            <select
                                id="category_id"
                                className={selectClass}
                                value={form.data.category_id}
                                onChange={(e) =>
                                    form.setData('category_id', e.target.value)
                                }
                                required
                            >
                                <option value="" disabled>
                                    Choose…
                                </option>
                                {categories.map((c) => (
                                    <option key={c.id} value={c.id}>
                                        {c.name}
                                    </option>
                                ))}
                            </select>
                            <InputError message={form.errors.category_id} />
                        </div>
                        <div className="space-y-1">
                            <Label htmlFor="condition_id">Condition</Label>
                            <select
                                id="condition_id"
                                className={selectClass}
                                value={form.data.condition_id}
                                onChange={(e) =>
                                    form.setData('condition_id', e.target.value)
                                }
                                required
                            >
                                <option value="" disabled>
                                    Choose…
                                </option>
                                {conditions.map((c) => (
                                    <option key={c.id} value={c.id}>
                                        {c.grade} – {c.name}
                                    </option>
                                ))}
                            </select>
                            <InputError message={form.errors.condition_id} />
                        </div>
                        <div className="space-y-1">
                            <Label htmlFor="brand">Brand</Label>
                            <Input
                                id="brand"
                                value={form.data.brand}
                                onChange={(e) =>
                                    form.setData('brand', e.target.value)
                                }
                            />
                        </div>
                        <div className="space-y-1">
                            <Label htmlFor="model">Model</Label>
                            <Input
                                id="model"
                                value={form.data.model}
                                onChange={(e) =>
                                    form.setData('model', e.target.value)
                                }
                            />
                        </div>
                        <div className="space-y-1">
                            <Label htmlFor="serial_number">
                                Serial / IMEI (optional)
                            </Label>
                            <Input
                                id="serial_number"
                                value={form.data.serial_number}
                                onChange={(e) =>
                                    form.setData(
                                        'serial_number',
                                        e.target.value,
                                    )
                                }
                            />
                        </div>
                        <div className="space-y-1">
                            <Label htmlFor="asking_price">
                                Your price ({currency})
                            </Label>
                            <Input
                                id="asking_price"
                                inputMode="decimal"
                                value={form.data.asking_price}
                                onChange={(e) =>
                                    form.setData(
                                        'asking_price',
                                        e.target.value.replace(/[^\d.]/g, ''),
                                    )
                                }
                                required
                            />
                            <InputError message={form.errors.asking_price} />
                        </div>
                    </div>

                    <div className="space-y-1">
                        <Label htmlFor="description">
                            Condition and what&rsquo;s included
                        </Label>
                        <textarea
                            id="description"
                            rows={4}
                            value={form.data.description}
                            onChange={(e) =>
                                form.setData('description', e.target.value)
                            }
                            placeholder="Any marks or faults, battery health, box, charger, accessories…"
                            className="w-full rounded-md border bg-transparent px-3 py-2 text-sm shadow-xs focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                        />
                        <InputError message={form.errors.description} />
                    </div>

                    <div className="space-y-2">
                        <Label>Photos</Label>
                        <div className="flex flex-wrap gap-2">
                            {previews.map((src, i) => (
                                <div
                                    key={src}
                                    className="relative size-20 overflow-hidden rounded-lg border"
                                >
                                    <img
                                        src={src}
                                        alt={`Photo ${i + 1}`}
                                        className="size-full object-cover"
                                    />
                                    <button
                                        type="button"
                                        onClick={() =>
                                            form.setData(
                                                'photos',
                                                form.data.photos.filter(
                                                    (_, j) => j !== i,
                                                ),
                                            )
                                        }
                                        className="absolute top-1 right-1 rounded-full bg-black/60 p-0.5 text-white"
                                        aria-label={`Remove photo ${i + 1}`}
                                    >
                                        <X className="size-3" />
                                    </button>
                                </div>
                            ))}
                            {form.data.photos.length < 8 && (
                                <label className="flex size-20 cursor-pointer flex-col items-center justify-center gap-1 rounded-lg border border-dashed text-xs text-muted-foreground hover:bg-muted/50">
                                    <Camera className="size-5" />
                                    Add
                                    <input
                                        type="file"
                                        accept="image/jpeg,image/png,image/webp"
                                        multiple
                                        className="sr-only"
                                        onChange={(e) => {
                                            form.setData(
                                                'photos',
                                                [
                                                    ...form.data.photos,
                                                    ...Array.from(
                                                        e.target.files ?? [],
                                                    ),
                                                ].slice(0, 8),
                                            );
                                            e.target.value = '';
                                        }}
                                    />
                                </label>
                            )}
                        </div>
                        <p className="text-xs text-muted-foreground">
                            Up to 8 photos. Show the front, back and any marks.
                        </p>
                        <InputError message={photoError} />
                    </div>

                    <Button type="submit" size="lg" disabled={form.processing}>
                        {form.processing && <Spinner />}
                        Send offer
                    </Button>
                </form>
            </div>
        </>
    );
}

SupplierOfferCreate.layout = {
    breadcrumbs: [{ title: 'My offers', href: index() }],
};
