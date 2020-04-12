<?php

use Illuminate\Database\Seeder;
use App\Condition;
use App\Category;
use App\Supplier;
use App\Product;
use App\User;

class ProductTableSeeder extends Seeder
{
    
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Product::truncate();
        $supplier = Supplier::select('id')->where('id', '1')->value('id');
        $categories = Category::select('id')->where('id', '1')->value('id');
        $condition = Condition::select('id')->where('id', '1')->value('id');
        $user = User::select('id')->where('id', '1')->value('id');
        Product::create(['name' => 'Product 1',
                         'user_id' => $user,   
                         'type' => $categories,
                         'cost' => '25',
                         'supplier' => $supplier,
                         'purchase_Date' => '18/04/2020',
                         'condition' => $condition,
                         'selling_Price' => '50' ,
                         'featured' => 'true',
                         'thumbnail_path' => 'LG TB-1586622730.jpg'
                         ]); 
        $supplier2 = Supplier::select('id')->where('id', '2')->value('id');
        $categories2 = Category::select('id')->where('id', '2')->value('id');
        $condition2 = Condition::select('id')->where('id', '2')->value('id');               
        Product::create(['name' => 'Product 2',
                         'user_id' => $user,
                         'type' => $categories2,
                         'cost' => '25',
                         'supplier' =>  $supplier2,
                         'purchase_Date' => '18/04/2020',
                         'condition' => $condition2,
                         'selling_Price' => '50',
                         'featured' => 'true',
                         'thumbnail_path' => 'Iphone-1586622759.jpg'                         
                         ]); 
        $supplier3 = Supplier::select('id')->where('id', '3')->value('id');
        $categories3 = Category::select('id')->where('id', '3')->value('id');
        $condition3 = Condition::select('id')->where('id', '3')->value('id');
        Product::create(['name' => 'Product 3',
                         'user_id' => $user,
                         'type' => $categories3,
                         'cost' => '25',
                         'supplier' => $supplier3,
                         'purchase_Date' => '18/04/2020',
                         'condition' =>  $condition3,
                         'selling_Price' => '50',
                         'featured' => 'true',
                         'thumbnail_path' => 'Macbook-1586622814.jpg'                            
                         ]);

    }
}
