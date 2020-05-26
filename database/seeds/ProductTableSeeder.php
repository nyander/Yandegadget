<?php

use Illuminate\Database\Seeder;
use App\Condition;
use App\Category;
use App\Supplier;
use App\Product;
use App\User;
use Carbon\Carbon;

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
        $user = User::select('id')->where('id', '1')->value('id');
       
        $condition = Condition::select('id')->where('id', '1')->value('id');        
        $condition2 = Condition::select('id')->where('id', '2')->value('id');  
        $condition3 = Condition::select('id')->where('id', '3')->value('id');

        $supplier = Supplier::select('id')->where('id', '1')->value('id');
        $supplier2 = Supplier::select('id')->where('id', '2')->value('id');
        $supplier3 = Supplier::select('id')->where('id', '3')->value('id');


        $categories = Category::select('id')->where('id', '1')->value('id');
        $categories2 = Category::select('id')->where('id', '2')->value('id');
        $categories3 = Category::select('id')->where('id', '3')->value('id');
        $categories4 = Category::select('id')->where('id', '4')->value('id');
        $categories5 = Category::select('id')->where('id', '5')->value('id');
        $categories6 = Category::select('id')->where('id', '6')->value('id');
        $categories7 = Category::select('id')->where('id', '7')->value('id');

        
        $date1 = Carbon::now()->toDateString();       
        $date2 = Carbon::createFromFormat('d/m/Y', '28/04/2020');        
       

        Product::create(['name' => 'LG TV 2018',
                         'user_id' => $user,   
                         'type' => $categories3,
                         'cost' => '25',
                         'supplier' => $supplier,
                         'purchase_Date' => $date1,
                         'condition' => $condition,
                         'selling_Price' => '50' ,
                         'featured' => 'true',
                         'thumbnail_path' => 'LG TB-1586622730.jpg'
                         ]);       
                            
        Product::create(['name' => 'Iphone X',
                         'user_id' => $user,
                         'type' => $categories,
                         'cost' => '25',
                         'supplier' =>  $supplier2,
                         'purchase_Date' => $date1,
                         'condition' => $condition2,
                         'selling_Price' => '50',
                         'featured' => 'true',
                         'thumbnail_path' => 'Iphone-1586622759.jpg'                         
                         ]); 

         Product::create(['name' => 'Macbook Pro 2018',
                         'user_id' => $user,
                         'type' => $categories4,
                         'cost' => '25',
                         'supplier' => $supplier3,
                         'purchase_Date' => $date1,
                         'condition' =>  $condition3,
                         'selling_Price' => '50',
                         'featured' => 'true',
                         'thumbnail_path' => 'Macbook-1586622814.jpg'                            
                         ]);

        Product::create(['name' => 'Playstation 4',
                         'user_id' => $user,   
                         'type' => $categories5,
                         'cost' => '25',
                         'supplier' => $supplier,
                         'purchase_Date' => $date1,
                         'condition' => $condition,
                         'selling_Price' => '150' ,
                         'featured' => 'true',
                         'thumbnail_path' => 'playstation_4_2020_.jpg',
                         'sold'=>true,
                         'sold_Date'=> $date2,
                         'received'=>true
                         ]); 
                         
        Product::create(['name' => 'Kodak EG 11',
                         'user_id' => $user,   
                         'type' => $categories3,
                         'cost' => '25',
                         'supplier' => $supplier2,
                         'purchase_Date' => $date1,
                         'condition' => $condition,
                         'selling_Price' => '150' ,
                         'featured' => 'true',
                         'thumbnail_path' => '71QN1O337TL._AC_SL1500_-1588269387.jpg',
                         'sold'=>true,
                         'sold_Date'=> $date2,
                         'received'=>true
                         ]); 
        
        
        Product::create(['name' => 'Product 6',
                         'user_id' => $user,   
                         'type' => $categories7,
                         'cost' => '60',
                         'supplier' => $supplier3,
                         'purchase_Date' => $date1,
                         'condition' => $condition3,
                         'selling_Price' => '250' ,
                         'featured' => 'true',
                         'thumbnail_path' => 'LG_watch_urbane_2nd.jpg',
                         'sold'=>true,
                         'sold_Date'=> $date2,
                         'received'=>true
                         ]);                 

    }
}
