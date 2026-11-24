<?php

namespace App\Http\Controllers\Staff;

use App\Enums\ReceiptOutcome;
use App\Enums\ShipmentStatus;
use App\Exceptions\ShipmentRuleViolation;
use App\Http\Controllers\Controller;
use App\Http\Requests\CheckInShipmentItemRequest;
use App\Models\Shipment;
use App\Models\ShipmentItem;
use App\Models\User;
use App\Services\ShipmentService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Store staff checking shipments in, item by item. Tablet-first.
 */
class ShipmentController extends Controller
{
    public function index(Request $request): Response
    {
        /** @var User $user */
        $user = $request->user();

        $base = Shipment::query()
            ->when($user->location_id, fn (Builder $q, int $locationId) => $q->where('destination_location_id', $locationId))
            ->with(['carrier', 'destination'])
            ->withCount(['items', 'items as checked_count' => fn (Builder $q) => $q->whereNotNull('checked_at')]);

        return Inertia::render('staff/shipments/index', [
            'inTransit' => (clone $base)->where('status', ShipmentStatus::InTransit)
                ->orderByRaw('expected_on is null')->orderBy('expected_on')->orderBy('dispatched_at')
                ->get()->map(fn (Shipment $s) => $this->summary($s))->all(),
            'recentlyReceived' => (clone $base)->where('status', ShipmentStatus::Received)
                ->latest('completed_at')->limit(5)
                ->get()->map(fn (Shipment $s) => $this->summary($s))->all(),
        ]);
    }

    public function show(Request $request, Shipment $shipment): Response
    {
        Gate::authorize('receive', $shipment);
        abort_if($shipment->isDraft(), 404);

        /** @var User $user */
        $user = $request->user();
        $user->unreadNotifications()->where('data->shipment_id', $shipment->id)->update(['read_at' => now()]);

        $shipment->load(['carrier', 'destination'])->loadCount(['items', 'items as checked_count' => fn (Builder $q) => $q->whereNotNull('checked_at')]);

        $items = $shipment->items()
            ->with(['stockUnit.product.media', 'stockUnit.condition', 'checker', 'media'])
            ->get()
            ->sortBy(fn (ShipmentItem $item) => [$item->isChecked() ? 1 : 0, $item->stockUnit->product->name])
            ->values();

        return Inertia::render('staff/shipments/show', [
            'shipment' => [
                ...$this->summary($shipment),
                'tracking_number' => $shipment->tracking_number,
                'tracking_url' => $shipment->trackingUrl(),
                'notes' => $shipment->notes,
            ],
            'items' => $items->map(fn (ShipmentItem $item) => [
                'id' => $item->id,
                'reference' => $item->stockUnit->reference,
                'product' => $item->stockUnit->product->name,
                'brand' => $item->stockUnit->product->brand,
                'serial_number' => $item->stockUnit->serial_number,
                'condition' => [
                    'grade' => $item->stockUnit->condition->grade,
                    'name' => $item->stockUnit->condition->name,
                ],
                'condition_notes' => $item->stockUnit->condition_notes,
                'image' => $item->stockUnit->product->getFirstMediaUrl('gallery', 'thumb') ?: null,
                'checked' => $item->isChecked(),
                'outcome' => $item->outcome?->value,
                'outcome_label' => $item->outcome?->getLabel(),
                'notes' => $item->receipt_notes,
                'checked_by' => $item->checker?->name,
                'checked_at' => $item->checked_at?->toIso8601String(),
                'photos' => $item->getMedia('arrival_photos')->map(fn (Media $m) => ['thumb' => $m->getUrl('thumb'), 'full' => $m->getUrl()])->all(),
            ])->all(),
            'outcomes' => collect(ReceiptOutcome::cases())->map(fn (ReceiptOutcome $o) => ['value' => $o->value, 'label' => $o->getLabel()])->all(),
        ]);
    }

    public function checkIn(CheckInShipmentItemRequest $request, Shipment $shipment, ShipmentItem $item, ShipmentService $shipments): RedirectResponse
    {
        Gate::authorize('receive', $shipment);

        /** @var User $user */
        $user = $request->user();

        /** @var list<UploadedFile> $photos */
        $photos = array_values($request->file('photos', []));

        try {
            $shipments->checkIn(
                $item,
                ReceiptOutcome::from($request->string('outcome')->toString()),
                $user,
                $request->string('notes')->trim()->toString() ?: null,
                $photos,
            );
        } catch (ShipmentRuleViolation $e) {
            return back()->withErrors(['outcome' => $e->getMessage()]);
        }

        return back();
    }

    /**
     * @return array<string, mixed>
     */
    private function summary(Shipment $shipment): array
    {
        return [
            'id' => $shipment->id,
            'reference' => $shipment->reference,
            'status' => $shipment->status->value,
            'carrier' => $shipment->carrier?->name,
            'destination' => $shipment->destination->name,
            'dispatched_on' => $shipment->dispatched_on?->toDateString(),
            'expected_on' => $shipment->expected_on?->toDateString(),
            'completed_at' => $shipment->completed_at?->toIso8601String(),
            'items_count' => (int) $shipment->getAttribute('items_count'),
            'checked_count' => (int) $shipment->getAttribute('checked_count'),
        ];
    }
}
