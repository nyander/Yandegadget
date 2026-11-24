{{-- Admin scripts at the end of every page: tours (App\Support\AdminTours) and live updates (App\Support\LiveUpdates). --}}
@php($tour = app(\App\Support\AdminTours::class)->forRoute(request()->route()?->getName()))
@if ($tour)
    <script type="application/json" id="yg-tour-data">@json($tour, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT)</script>
@endif
@vite('resources/js/admin/tour.ts')
@if (\App\Support\LiveUpdates::enabled())
    {{-- Where the browser connects for live updates. Public values only (no secret). --}}
    <meta name="yg-reverb" content="{{ json_encode(\App\Support\LiveUpdates::clientConfig()) }}">
    @vite('resources/js/admin/live.ts')
@endif
