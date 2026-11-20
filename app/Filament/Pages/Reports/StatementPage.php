<?php

namespace App\Filament\Pages\Reports;

use App\Enums\Permission;
use App\Models\User;
use App\Reports\Statement;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;
use UnitEnum;

/**
 * A financial statement with a date filter and CSV/PDF downloads.
 *
 * @property-read Schema $form
 */
abstract class StatementPage extends Page
{
    protected static string|UnitEnum|null $navigationGroup = 'Reports';

    protected string $view = 'filament.pages.statement';

    /** @var array<string, mixed>|null */
    public ?array $data = [];

    abstract public function statement(): Statement;

    public static function canAccess(): bool
    {
        /** @var User|null $user */
        $user = auth()->user();

        return (bool) $user?->can(Permission::ViewReports->value);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('csv')
                ->label('Download CSV')
                ->icon(Heroicon::OutlinedTableCells)
                ->color('gray')
                ->action(fn () => $this->downloadCsv()),
            Action::make('pdf')
                ->label('Download PDF')
                ->icon(Heroicon::OutlinedDocumentArrowDown)
                ->action(fn () => $this->downloadPdf()),
        ];
    }

    public function downloadCsv(): StreamedResponse
    {
        abort_unless(static::canAccess(), 403);

        $statement = $this->statement();

        return response()->streamDownload(function () use ($statement) {
            $out = fopen('php://output', 'w');

            if ($out === false) {
                return;
            }

            fputcsv($out, [$statement->title, $statement->period, "Amounts in {$statement->currency}"], escape: '');
            fputcsv($out, ['Section', 'Line', 'Amount'], escape: '');

            foreach ($statement->rows() as $row) {
                fputcsv($out, $row, escape: '');
            }

            foreach ($statement->notes as $note) {
                fputcsv($out, ['Note', $note, ''], escape: '');
            }

            fclose($out);
        }, $this->filename($statement, 'csv'), ['Content-Type' => 'text/csv']);
    }

    public function downloadPdf(): StreamedResponse
    {
        abort_unless(static::canAccess(), 403);

        $statement = $this->statement();
        $pdf = Pdf::loadView('reports.pdf', ['statement' => $statement])->output();

        return response()->streamDownload(
            fn () => print ($pdf),
            $this->filename($statement, 'pdf'),
            ['Content-Type' => 'application/pdf'],
        );
    }

    private function filename(Statement $statement, string $extension): string
    {
        return Str::slug("yande-gadgets {$statement->title} {$statement->period}").".{$extension}";
    }
}
