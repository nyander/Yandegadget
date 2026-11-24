{{-- A "?" that explains a report line on hover, tap or keyboard focus (App\Support\ReportGlossary). --}}
@props(['label'])
@php
    $entry = app(\App\Support\ReportGlossary::class)->explain($label);
@endphp
@if ($entry)
    @php
        $tip = '<div class="yg-explain__tip">'
            .'<p><strong>What it is</strong>'.e($entry['what']).'</p>'
            .'<p><strong>Where it comes from</strong>'.e($entry['from']).'</p>'
            .'<p><strong>Why it matters</strong>'.e($entry['impact']).'</p>'
            .'</div>';
    @endphp
    <button
        type="button"
        class="yg-explain"
        x-data
        x-tooltip="{ content: @js($tip), theme: $store.theme, allowHTML: true, maxWidth: 340, placement: 'top' }"
        x-on:click.stop.prevent
    >
        <x-filament::icon icon="heroicon-m-question-mark-circle" class="yg-explain__icon" />
        <span class="yg-sr-only">About {{ $label }}: {{ $entry['what'] }} {{ $entry['from'] }} {{ $entry['impact'] }}</span>
    </button>
@endif
