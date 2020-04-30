<?php

use Illuminate\Database\Seeder;
use App\Transaction;

class TransactionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Transaction::truncate();
        Transaction::create([
            'date'=>'2020-04-01',
            'description'=>'Transaction 1',
            'type'=>'1',
            'amount'=>'10'
        ]);
        Transaction::create([
            'date'=>'2020-04-02',
            'description'=>'Transaction 2',
            'type'=>'2',
            'amount'=>'20'
        ]);

        Transaction::create([
            'date'=>'2020-04-03',
            'description'=>'Transaction 3',
            'type'=>'3',
            'amount'=>'30'
        ]);

        Transaction::create([
            'date'=>'2020-04-04',
            'description'=>'Transaction 4',
            'type'=>'4',
            'amount'=>'40'
        ]);

        Transaction::create([
            'date'=>'2020-04-05',
            'description'=>'Transaction 5',
            'type'=>'5',
            'amount'=>'50'
        ]);

        Transaction::create([
            'date'=>'2020-04-06',
            'description'=>'Transaction 6',
            'type'=>'6',
            'amount'=>'60'
        ]);

        Transaction::create([
            'date'=>'2020-04-07',
            'description'=>'Transaction 7',
            'type'=>'7',
            'amount'=>'70'
        ]);

        Transaction::create([
            'date'=>'2020-04-08',
            'description'=>'Transaction 8',
            'type'=>'8',
            'amount'=>'80'
        ]);

        Transaction::create([
            'date'=>'2020-04-09',
            'description'=>'Transaction 9',
            'type'=>'9',
            'amount'=>'90'
        ]);

        Transaction::create([
            'date'=>'2020-04-10',
            'description'=>'Transaction 10',
            'type'=>'10',
            'amount'=>'100'
        ]);

        Transaction::create([
            'date'=>'2020-04-11',
            'description'=>'Transaction 11',
            'type'=>'11',
            'amount'=>'110'
        ]);

        Transaction::create([
            'date'=>'2020-04-12',
            'description'=>'Transaction 12',
            'type'=>'12',
            'amount'=>'120'
        ]);

        Transaction::create([
            'date'=>'2020-04-13',
            'description'=>'Transaction 13',
            'type'=>'13',
            'amount'=>'130'
        ]);

        Transaction::create([
            'date'=>'2020-04-14',
            'description'=>'Transaction 14',
            'type'=>'14',
            'amount'=>'140'
        ]);

        Transaction::create([
            'date'=>'2020-04-15',
            'description'=>'Transaction 15',
            'type'=>'15',
            'amount'=>'150'
        ]);

        Transaction::create([
            'date'=>'2020-04-16',
            'description'=>'Transaction 16',
            'type'=>'16',
            'amount'=>'160'
        ]);

        Transaction::create([
            'date'=>'2020-04-17',
            'description'=>'Transaction 17',
            'type'=>'17',
            'amount'=>'170'
        ]);

        Transaction::create([
            'date'=> '2020-04-18',
            'description'=>'Transaction 18',
            'type'=>'18',
            'amount'=>'180'
        ]);



    }
}
