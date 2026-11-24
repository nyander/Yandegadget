<?php

namespace App\Console\Commands;

use App\Services\ProductRequestService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('requests:release-uncollected')]
#[Description('Remind customers whose reserved item is due soon, and put items back on sale when they haven\'t been collected in time')]
class ReleaseUncollectedRequests extends Command
{
    public function handle(ProductRequestService $requests): int
    {
        $reminded = $requests->remindUncollected();
        $released = $requests->releaseUncollected();

        $this->info("Reminded {$reminded} ".str('customer')->plural($reminded).'.');
        $this->info("Released {$released} uncollected ".str('item')->plural($released).'.');

        return self::SUCCESS;
    }
}
