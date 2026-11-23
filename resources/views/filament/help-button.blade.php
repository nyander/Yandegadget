{{-- Help menu next to the notifications bell: tour of this page, the guide, and the tips toggle. --}}
@php($hasTour = app(\App\Support\AdminTours::class)->forRoute(request()->route()?->getName()) !== null)
<x-filament::dropdown placement="bottom-end" width="xs" id="yg-help-menu">
    <x-slot name="trigger">
        <x-filament::icon-button icon="heroicon-o-lifebuoy" color="gray" size="lg" label="Help" tooltip="Help" />
    </x-slot>

    <x-filament::dropdown.list>
        @if ($hasTour)
            <x-filament::dropdown.list.item icon="heroicon-o-play-circle" tag="button" x-on:click="close(); window.YandeTour?.start()">
                Show me around this page
            </x-filament::dropdown.list.item>
        @endif
        <x-filament::dropdown.list.item icon="heroicon-o-book-open" tag="a" :href="app(\App\Support\HelpCentre::class)->url()">
            Read the guide
        </x-filament::dropdown.list.item>
    </x-filament::dropdown.list>

    <x-filament::dropdown.list>
        <div
            class="yg-tips-toggle"
            x-data="{ on: window.YandeTour ? window.YandeTour.tipsEnabled() : true }"
        >
            <span>
                <span class="yg-tips-toggle__label">Tips on each page</span>
                <span class="yg-tips-toggle__hint" x-text="on ? 'A short tour the first time you open a page' : 'Off. Turn on to see every tour again'"></span>
            </span>
            <button
                type="button"
                role="switch"
                class="yg-switch"
                x-bind:aria-checked="on.toString()"
                x-bind:class="{ 'is-on': on }"
                x-on:click="on = ! on; window.YandeTour?.setTips(on)"
                aria-label="Tips on each page"
            ><span></span></button>
        </div>
    </x-filament::dropdown.list>
</x-filament::dropdown>
