import { useForm } from '@inertiajs/react';
import {
    AlertTriangle,
    Camera,
    CheckCircle2,
    ImageOff,
    SearchX,
} from 'lucide-react';
import { useId, useState } from 'react';
import { toast } from 'sonner';
import InputError from '@/components/input-error';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { formatDateTime } from '@/lib/dates';
import { cn } from '@/lib/utils';
import { checkIn } from '@/routes/staff/shipments/items';
import type { ReceiptOutcomeValue, ShipmentItemRow } from '@/types';

type Props = {
    shipmentId: number;
    item: ShipmentItemRow;
};

export function CheckInCard({ shipmentId, item }: Props) {
    return (
        <li
            className={cn(
                'rounded-xl border p-4',
                item.checked && 'bg-muted/40',
            )}
        >
            <div className="flex gap-4">
                <div className="size-16 shrink-0 overflow-hidden rounded-lg bg-muted sm:size-20">
                    {item.image ? (
                        <img
                            src={item.image}
                            alt=""
                            className="size-full object-cover"
                        />
                    ) : (
                        <div className="flex size-full items-center justify-center text-muted-foreground">
                            <ImageOff className="size-5" aria-hidden />
                        </div>
                    )}
                </div>
                <div className="min-w-0 flex-1">
                    <p className="font-medium">{item.product}</p>
                    <p className="text-sm text-muted-foreground">
                        {item.reference} · Grade {item.condition.grade} (
                        {item.condition.name})
                        {item.serial_number && ` · ${item.serial_number}`}
                    </p>
                    {item.condition_notes && (
                        <p className="mt-1 text-sm">{item.condition_notes}</p>
                    )}
                </div>
            </div>

            {item.checked ? (
                <CheckedSummary item={item} />
            ) : (
                <CheckInForm shipmentId={shipmentId} item={item} />
            )}
        </li>
    );
}

const outcomeStyles: Record<ReceiptOutcomeValue, string> = {
    received: 'bg-brand-green text-white',
    damaged: 'bg-brand-gold text-black',
    missing: 'bg-brand-red text-white',
};

function CheckedSummary({ item }: { item: ShipmentItemRow }) {
    return (
        <div className="mt-3 space-y-2 border-t pt-3 text-sm">
            <div className="flex flex-wrap items-center gap-2">
                {item.outcome && (
                    <Badge className={outcomeStyles[item.outcome]}>
                        {item.outcome_label}
                    </Badge>
                )}
                <span className="text-muted-foreground">
                    {item.checked_by} · {formatDateTime(item.checked_at)}
                </span>
            </div>
            {item.notes && <p>{item.notes}</p>}
            {item.photos.length > 0 && (
                <div className="flex gap-2">
                    {item.photos.map((photo) => (
                        <a
                            key={photo.thumb}
                            href={photo.full}
                            target="_blank"
                            rel="noreferrer"
                            className="size-14 overflow-hidden rounded-md border"
                        >
                            <img
                                src={photo.thumb}
                                alt="Arrival photo"
                                className="size-full object-cover"
                            />
                        </a>
                    ))}
                </div>
            )}
        </div>
    );
}

function CheckInForm({ shipmentId, item }: Props) {
    const id = useId();
    const [problem, setProblem] = useState<'damaged' | 'missing' | null>(null);
    const form = useForm<{
        outcome: ReceiptOutcomeValue;
        notes: string;
        photos: File[];
    }>({ outcome: 'received', notes: '', photos: [] });

    const submit = (outcome: ReceiptOutcomeValue) => {
        form.transform((data) => ({ ...data, outcome }));
        form.post(checkIn.url({ shipment: shipmentId, item: item.id }), {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () =>
                toast.success(`${item.reference} checked in`, {
                    description: item.product,
                }),
        });
    };

    return (
        <div className="mt-4 space-y-3 border-t pt-4">
            <div>
                <Label
                    htmlFor={`${id}-photos`}
                    className="inline-flex cursor-pointer items-center gap-2 rounded-md border px-3 py-2 text-sm font-medium hover:bg-muted"
                >
                    <Camera className="size-4" />
                    {form.data.photos.length > 0
                        ? `${form.data.photos.length} photo(s) added`
                        : 'Add photos'}
                </Label>
                <input
                    id={`${id}-photos`}
                    type="file"
                    accept="image/*"
                    capture="environment"
                    multiple
                    className="sr-only"
                    onChange={(e) =>
                        form.setData(
                            'photos',
                            Array.from(e.target.files ?? []).slice(0, 5),
                        )
                    }
                />
                <InputError message={form.errors.photos} className="mt-1" />
                {Object.entries(form.errors)
                    .filter(([key]) => key.startsWith('photos.'))
                    .slice(0, 1)
                    .map(([key, message]) => (
                        <InputError key={key} message={message} />
                    ))}
            </div>

            {problem && (
                <div className="space-y-1">
                    <Label htmlFor={`${id}-notes`}>
                        {problem === 'damaged'
                            ? 'What is damaged?'
                            : 'What happened?'}
                    </Label>
                    <textarea
                        id={`${id}-notes`}
                        value={form.data.notes}
                        onChange={(e) => form.setData('notes', e.target.value)}
                        rows={3}
                        className="w-full rounded-md border bg-transparent px-3 py-2 text-sm shadow-xs focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                        placeholder={
                            problem === 'damaged'
                                ? 'e.g. Cracked screen corner, box crushed'
                                : 'e.g. Not in the box when opened'
                        }
                    />
                    <InputError message={form.errors.notes} />
                </div>
            )}

            <InputError message={form.errors.outcome} />

            {problem ? (
                <div className="flex flex-wrap gap-2">
                    <Button
                        size="lg"
                        variant={
                            problem === 'missing' ? 'destructive' : 'default'
                        }
                        disabled={form.processing}
                        onClick={() => submit(problem)}
                    >
                        {form.processing && <Spinner />}
                        Save as {problem}
                    </Button>
                    <Button
                        size="lg"
                        variant="ghost"
                        disabled={form.processing}
                        onClick={() => {
                            setProblem(null);
                            form.clearErrors();
                        }}
                    >
                        Cancel
                    </Button>
                </div>
            ) : (
                <div className="grid gap-2 sm:grid-cols-3">
                    <Button
                        size="lg"
                        className="h-12 bg-brand-green text-white hover:bg-brand-green/90"
                        disabled={form.processing}
                        onClick={() => submit('received')}
                    >
                        {form.processing ? <Spinner /> : <CheckCircle2 />}
                        Arrived OK
                    </Button>
                    <Button
                        size="lg"
                        variant="outline"
                        className="h-12"
                        disabled={form.processing}
                        onClick={() => setProblem('damaged')}
                    >
                        <AlertTriangle /> Damaged
                    </Button>
                    <Button
                        size="lg"
                        variant="outline"
                        className="h-12"
                        disabled={form.processing}
                        onClick={() => setProblem('missing')}
                    >
                        <SearchX /> Missing
                    </Button>
                </div>
            )}
        </div>
    );
}
