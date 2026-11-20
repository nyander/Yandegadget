{{-- A financial statement table, shared by the admin pages and the PDF. --}}
@php /** @var \App\Reports\Statement $statement */ @endphp
<div class="yg-statement__header">
    <span class="yg-statement__title">{{ $statement->title }}</span>
    <span class="yg-statement__period">{{ $statement->period }} · amounts in {{ $statement->currency }}</span>
</div>
<table class="yg-statement">
    @foreach ($statement->sections as $section)
        @if ($section->isStandaloneTotal())
            <tbody>
                <tr class="yg-statement__grand">
                    <th scope="row">
                        {{ $section->total->label }}
                        @if ($section->total->hint)
                            <span class="yg-statement__hint">{{ $section->total->hint }}</span>
                        @endif
                    </th>
                    <td @class(['yg-neg' => $section->total->isNegative()])>{{ $section->total->formatted() }}</td>
                </tr>
            </tbody>
        @else
            <tbody>
                <tr class="yg-statement__heading">
                    <th scope="rowgroup" colspan="2">{{ $section->title }}</th>
                </tr>
                @forelse ($section->lines as $line)
                    <tr>
                        <th scope="row">
                            {{ $line->label }}
                            @if ($line->hint)
                                <span class="yg-statement__hint">{{ $line->hint }}</span>
                            @endif
                        </th>
                        <td @class(['yg-neg' => $line->isNegative()])>{{ $line->formatted() }}</td>
                    </tr>
                @empty
                    <tr class="yg-statement__empty">
                        <th scope="row">Nothing recorded</th>
                        <td></td>
                    </tr>
                @endforelse
                @if ($section->total)
                    <tr class="yg-statement__total">
                        <th scope="row">{{ $section->total->label }}</th>
                        <td @class(['yg-neg' => $section->total->isNegative()])>{{ $section->total->formatted() }}</td>
                    </tr>
                @endif
            </tbody>
        @endif
    @endforeach
</table>
@foreach ($statement->notes as $note)
    <p class="yg-statement__note">{{ $note }}</p>
@endforeach
