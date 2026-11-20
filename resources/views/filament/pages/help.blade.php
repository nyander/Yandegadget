@php /** @var \App\Support\HelpCentre $help */ @endphp
<x-filament-panels::page>
    <div class="yg-help">
        <nav class="yg-help__nav" aria-label="Guide topics">
            @foreach ($help->grouped() as $group => $topics)
                <p class="yg-help__group">{{ $group }}</p>
                <ul>
                    @foreach ($topics as $slug => $item)
                        <li>
                            <a href="{{ $help->url($slug) }}" @class(['is-active' => $topic === $slug]) wire:navigate>{{ $item['title'] }}</a>
                        </li>
                    @endforeach
                </ul>
            @endforeach
        </nav>

        <div class="yg-help__main">
            @if ($topic)
                <x-filament::section>
                    <article class="yg-prose">
                        {{ $help->render($topic) }}
                    </article>
                    @if ($next = $help->next($topic))
                        <p class="yg-help__next">
                            Next: <a href="{{ $help->url($next) }}" wire:navigate>{{ $help->topic($next)['title'] }} →</a>
                        </p>
                    @endif
                </x-filament::section>
            @else
                <x-filament::section>
                    <p class="yg-help__intro">Short guides to every part of the back office. New here? Start with <a href="{{ $help->url('welcome') }}" wire:navigate>How the app works</a>, then <a href="{{ $help->url('first-week') }}" wire:navigate>Getting set up</a>.</p>
                </x-filament::section>
                @foreach ($help->grouped() as $group => $topics)
                    <h2 class="yg-help__heading">{{ $group }}</h2>
                    <div class="yg-help__cards">
                        @foreach ($topics as $slug => $item)
                            <a href="{{ $help->url($slug) }}" class="yg-help__card" wire:navigate>
                                <span class="yg-help__card-title">{{ $item['title'] }}</span>
                                <span class="yg-help__card-text">{{ $item['summary'] }}</span>
                            </a>
                        @endforeach
                    </div>
                @endforeach
            @endif
        </div>
    </div>
</x-filament-panels::page>
