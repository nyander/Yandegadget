<x-filament-widgets::widget>
    <x-filament::section
        data-tour="getting-started"
        heading="Getting started"
        :description="$done.' of '.count($steps).' done. Each step links to the page and to the guide.'"
        icon="heroicon-o-rocket-launch"
    >
        <x-slot name="afterHeader">
            <x-filament::link tag="button" wire:click="dismiss" color="gray" size="sm">
                Hide this list
            </x-filament::link>
        </x-slot>

        <div class="yg-progress" role="progressbar" aria-valuemin="0" aria-valuemax="{{ count($steps) }}" aria-valuenow="{{ $done }}">
            <span style="width: {{ round($done / max(count($steps), 1) * 100) }}%"></span>
        </div>

        <ul class="yg-checklist">
            @foreach ($steps as $step)
                <li @class(['is-done' => $step['done']])>
                    @if ($step['done'])
                        <x-filament::icon icon="heroicon-s-check-circle" class="yg-checklist__icon is-done" />
                    @else
                        <x-filament::icon icon="heroicon-o-stop-circle" class="yg-checklist__icon is-todo" />
                    @endif
                    <div class="yg-checklist__body">
                        <div class="yg-checklist__title">{{ $step['title'] }}</div>
                        @unless ($step['done'])
                            <div class="yg-checklist__text">{{ $step['text'] }}</div>
                            <div class="yg-checklist__links">
                                <a href="{{ $step['url'] }}">Open</a>
                                <a href="{{ $step['help'] }}">How?</a>
                                @if ($step['manual'])
                                    <button type="button" wire:click="markDone('{{ $step['key'] }}')">Mark as done</button>
                                @endif
                            </div>
                        @endunless
                    </div>
                </li>
            @endforeach
        </ul>
    </x-filament::section>
</x-filament-widgets::widget>
