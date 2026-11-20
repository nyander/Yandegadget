<?php

namespace App\Http\Controllers;

use App\Services\ShopSettings;
use Carbon\CarbonImmutable;
use Inertia\Inertia;
use Inertia\Response;

class LegalController extends Controller
{
    public function __construct(private readonly ShopSettings $settings) {}

    public function privacy(): Response
    {
        return Inertia::render('legal/privacy', $this->props());
    }

    public function terms(): Response
    {
        return Inertia::render('legal/terms', $this->props());
    }

    /**
     * @return array<string, mixed>
     */
    private function props(): array
    {
        return [
            'contact' => config('shop.contact'),
            'dpcRegistration' => config('shop.dpc_registration'),
            'cancellationFeePercent' => $this->settings->cancellationFeePercent(),
            'collectionDays' => $this->settings->collectionDays(),
            'updatedOn' => CarbonImmutable::parse((string) config('shop.legal_updated_on'))->format('j F Y'),
        ];
    }
}
