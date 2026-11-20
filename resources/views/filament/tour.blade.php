{{-- Tour steps for this page (App\Support\AdminTours), read by resources/js/admin/tour.ts. --}}
@php($tour = app(\App\Support\AdminTours::class)->forRoute(request()->route()?->getName()))
@if ($tour)
    <script type="application/json" id="yg-tour-data">@json($tour, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT)</script>
@endif
@vite('resources/js/admin/tour.ts')
