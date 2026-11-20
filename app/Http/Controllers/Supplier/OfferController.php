<?php

namespace App\Http\Controllers\Supplier;

use App\Enums\OfferStatus;
use App\Enums\StockStatus;
use App\Exceptions\OfferRuleViolation;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSupplierOfferRequest;
use App\Models\Category;
use App\Models\Condition;
use App\Models\Supplier;
use App\Models\SupplierOffer;
use App\Models\User;
use App\Services\SupplierOfferService;
use Brick\Math\RoundingMode;
use Brick\Money\Money;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * UK suppliers offering items to the shop.
 */
class OfferController extends Controller
{
    public function __construct(private readonly SupplierOfferService $offers) {}

    public function index(Request $request): Response
    {
        $supplier = $this->supplier($request);
        $offers = $supplier->offers()->with(['condition', 'stockUnit', 'media'])->latest()->get();

        return Inertia::render('supplier/offers/index', [
            'supplier' => $supplier->only(['name']),
            'stats' => [
                'waiting' => $offers->where('status', OfferStatus::Submitted)->count(),
                'bought' => $offers->where('status', OfferStatus::Accepted)->count(),
                'sold' => $offers->filter(fn (SupplierOffer $o) => $o->stockUnit?->status === StockStatus::Sold)->count(),
            ],
            'offers' => $offers->map(fn (SupplierOffer $o) => [
                'id' => $o->id,
                'reference' => $o->reference,
                'title' => $o->title,
                'status' => $o->status->value,
                'status_label' => $o->status->getLabel(),
                'grade' => $o->condition->grade,
                'asking_price' => $o->asking_price->formatToLocale('en_GB'),
                'agreed_price' => $o->agreed_price?->formatToLocale('en_GB'),
                'image' => $o->getFirstMediaUrl('photos', 'thumb') ?: null,
                'created_at' => $o->created_at?->toIso8601String(),
            ])->all(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('supplier/offers/create', [
            'categories' => Category::where('is_active', true)->orderBy('sort_order')->get(['id', 'name']),
            'conditions' => Condition::orderBy('sort_order')->get(['id', 'grade', 'name', 'description']),
            'currency' => (string) config('shop.list_currency'),
        ]);
    }

    public function store(StoreSupplierOfferRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        /** @var list<UploadedFile> $photos */
        $photos = array_values($request->file('photos', []));

        $offer = $this->offers->submit($this->supplier($request), $user, [
            'category_id' => $request->integer('category_id'),
            'condition_id' => $request->integer('condition_id'),
            'title' => $request->string('title')->toString(),
            'brand' => $request->input('brand'),
            'model' => $request->input('model'),
            'serial_number' => $request->input('serial_number'),
            'description' => $request->input('description'),
            'asking_price' => Money::of($request->string('asking_price')->toString(), (string) config('shop.list_currency'), roundingMode: RoundingMode::HalfUp),
        ], $photos);

        return to_route('supplier.offers.show', $offer);
    }

    public function show(SupplierOffer $offer): Response
    {
        Gate::authorize('view', $offer);

        $offer->load(['category', 'condition', 'media']);

        return Inertia::render('supplier/offers/show', [
            'offer' => [
                'id' => $offer->id,
                'reference' => $offer->reference,
                'title' => $offer->title,
                'brand' => $offer->brand,
                'model' => $offer->model,
                'serial_number' => $offer->serial_number,
                'description' => $offer->description,
                'category' => $offer->category->name,
                'condition' => "{$offer->condition->grade} – {$offer->condition->name}",
                'status' => $offer->status->value,
                'status_label' => $offer->status->getLabel(),
                'asking_price' => $offer->asking_price->formatToLocale('en_GB'),
                'agreed_price' => $offer->agreed_price?->formatToLocale('en_GB'),
                'decline_reason' => $offer->decline_reason,
                'reviewed_at' => $offer->reviewed_at?->toIso8601String(),
                'created_at' => $offer->created_at?->toIso8601String(),
                'photos' => $offer->getMedia('photos')->map(fn (Media $m) => ['thumb' => $m->getUrl('thumb'), 'full' => $m->getUrl()])->all(),
                'can_withdraw' => $offer->status === OfferStatus::Submitted,
            ],
        ]);
    }

    public function withdraw(Request $request, SupplierOffer $offer): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        try {
            $this->offers->withdraw($offer, $user);
        } catch (OfferRuleViolation $e) {
            abort(403, $e->getMessage());
        }

        return to_route('supplier.offers.show', $offer);
    }

    private function supplier(Request $request): Supplier
    {
        /** @var User $user */
        $user = $request->user();

        return $user->supplier ?? abort(403);
    }
}
