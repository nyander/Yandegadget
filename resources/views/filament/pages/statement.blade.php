<x-filament-panels::page>
    <x-filament::section>
        {{ $this->form }}
    </x-filament::section>

    <x-filament::section>
        @include('reports.statement', ['statement' => $this->statement()])
    </x-filament::section>
</x-filament-panels::page>
