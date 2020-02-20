<?php

use Illuminate\Database\Seeder;
use App\Category;

class CategoriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Category::truncate();
        Category::create([ 'type' => 'Phones']);
        Category::create([ 'type' => 'Appliances']);
        Category::create([ 'type' => 'TV & Audio']);
        Category::create([ 'type' => 'Computing']);
        Category::create([ 'type' => 'Gaming']);
        Category::create([ 'type' => 'Cameras']);
        Category::create([ 'type' => 'Smart Tech']);
    }
}
