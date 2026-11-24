<x-filament-panels::page>
    <x-filament::section data-tour="dates">
        {{ $this->form }}
    </x-filament::section>

    <x-filament::section>
        @include('reports.statement', ['statement' => $this->statement(), 'explain' => true])
    </x-filament::section>
</x-filament-panels::page>
