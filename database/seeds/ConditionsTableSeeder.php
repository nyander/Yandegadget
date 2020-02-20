<?php

use Illuminate\Database\Seeder;
use App\Condition;

class ConditionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Condition::truncate();
        Condition::create([ 'details' => 'A', 'explanation'=> 'Mint Condition: Product in mint condition with original package and all origina accessories', 'deposit'=>'80']);
        Condition::create([ 'details' => 'B', 'explanation'=>'Good Condition: Porudct is in good condition with all essential accesssories. No packaging', 'deposit'=>'70']);
        Condition::create([ 'details' => 'C', 'explanation'=> 'Working Condition: Product in poor condition with all essential accessories. No pakcaging', 'deposit'=>'50']);      
    }
}
