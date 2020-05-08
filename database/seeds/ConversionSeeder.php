<?php

use Illuminate\Database\Seeder;
use App\Conversion;

class ConversionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Conversion::truncate();
        Conversion::create(['rate'=> '7']);
    }
}
