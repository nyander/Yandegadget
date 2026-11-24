{{-- Shop settings → Change history (App\Filament\Pages\ShopSettings::changes()). --}}
<div data-tour="setting-changes">
@if ($changes === [])
    <p class="yg-table__empty">No changes yet. When someone saves a different value, it shows here.</p>
@else
    <div class="yg-panel">
        <table class="yg-table">
            <thead>
                <tr>
                    <th scope="col">When</th>
                    <th scope="col">Who</th>
                    <th scope="col">Setting</th>
                    <th scope="col">From</th>
                    <th scope="col">To</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($changes as $change)
                    <tr>
                        <th scope="row">{{ $change['when']->format('j M Y, H:i') }}</th>
                        <td>{{ $change['who'] }}</td>
                        <td>{{ $change['what'] }}</td>
                        <td>{{ $change['from'] }}</td>
                        <td><strong>{{ $change['to'] }}</strong></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
</div>
