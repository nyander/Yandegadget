<?php

namespace App\Console\Commands;

use App\Services\ProductRequestService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('requests:release-uncollected')]
#[Description('Put reserved items back on sale when the customer hasn\'t collected them in time')]
class ReleaseUncollectedRequests extends Command
{
    public function handle(ProductRequestService $requests): int
    {
        $released = $requests->releaseUncollected();

        $this->info("Released {$released} uncollected ".str('item')->plural($released).'.');

        return self::SUCCESS;
    }
}
