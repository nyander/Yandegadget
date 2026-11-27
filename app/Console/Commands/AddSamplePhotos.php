<?php

namespace App\Console\Commands;

use Database\Seeders\Support\SamplePhotos;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('demo:photos')]
#[Description('Add clearly-labelled sample photos to products and items that have none (local demo data only)')]
class AddSamplePhotos extends Command
{
    public function handle(): int
    {
        if (app()->isProduction()) {
            $this->error('Sample photos are for local demo data. Upload real photos in the admin instead.');

            return self::FAILURE;
        }

        $added = SamplePhotos::addMissing();

        $this->info("Added sample photos to {$added['products']} products and {$added['items']} items.");

        return self::SUCCESS;
    }
}
