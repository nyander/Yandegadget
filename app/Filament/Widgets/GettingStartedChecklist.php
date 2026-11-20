<?php

namespace App\Filament\Widgets;

use App\Enums\Permission;
use App\Models\User;
use App\Support\GettingStarted;
use Filament\Widgets\Widget;

/**
 * The owner's setup checklist. Shown until every step is done or it's hidden.
 */
class GettingStartedChecklist extends Widget
{
    protected static ?int $sort = 0;

    protected int|string|array $columnSpan = 'full';

    protected string $view = 'filament.widgets.getting-started';

    protected static bool $isLazy = false;

    public static function canView(): bool
    {
        /** @var User|null $user */
        $user = auth()->user();

        if (! $user?->can(Permission::ManageSettings->value)) {
            return false;
        }

        $checklist = app(GettingStarted::class);

        if ($checklist->isDismissed()) {
            return false;
        }

        return collect($checklist->steps($user))->contains('done', false);
    }

    public function markDone(string $key): void
    {
        abort_unless(static::canView(), 403);

        app(GettingStarted::class)->markDone($key);
    }

    public function dismiss(): void
    {
        abort_unless(static::canView(), 403);

        app(GettingStarted::class)->dismiss();
        $this->redirect(url('/admin'));
    }

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        /** @var User $user */
        $user = auth()->user();
        $steps = app(GettingStarted::class)->steps($user);

        return [
            'steps' => $steps,
            'done' => count(array_filter($steps, fn (array $step) => $step['done'])),
        ];
    }
}
