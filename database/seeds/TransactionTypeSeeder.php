<?php

use Illuminate\Database\Seeder;
use App\TransactionType;

class TransactionTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        TransactionType::truncate();

        TransactionType::create(['type'=> 'cash', 'explanation' => '' ]);
        TransactionType::create(['type'=> 'Account Recievable', 'explanation' => '' ]);
        TransactionType::create(['type'=> 'Inventory', 'explanation' => '' ]);
        TransactionType::create(['type'=> 'Prepaid', 'explanation' => '' ]);
        TransactionType::create(['type'=> 'Other Current Assets ', 'explanation' => '' ]);
        TransactionType::create(['type'=> 'Land', 'explanation' => '' ]);
        TransactionType::create(['type'=> 'Building', 'explanation' => '' ]);
        TransactionType::create(['type'=> 'Vehicles', 'explanation' => '' ]);
        TransactionType::create(['type'=> 'Machinery', 'explanation' => '' ]);
        TransactionType::create(['type'=> 'Computer/Software', 'explanation' => '' ]);
        TransactionType::create(['type'=> 'Other Fixed Assets', 'explanation' => '' ]);
        TransactionType::create(['type'=> 'Accounts Payable', 'explanation' => '' ]);
        TransactionType::create(['type'=> 'Overdrafts', 'explanation' => '' ]);
        TransactionType::create(['type'=> 'Current Lease Payaple', 'explanation' => '' ]);
        TransactionType::create(['type'=> 'Customer repayments', 'explanation' => '' ]);
        TransactionType::create(['type'=> 'Other Current Assets', 'explanation' => '' ]);
        TransactionType::create(['type'=> 'Long-Term Depts ', 'explanation' => '' ]);
        TransactionType::create(['type'=> 'Pension Fund Liability', 'explanation' => '' ]);
        TransactionType::create(['type'=> 'Preffered Taxed Liability', 'explanation' => '' ]);
        TransactionType::create(['type'=> 'Other long term liabilities', 'explanation' => '' ]);
        TransactionType::create(['type'=> 'Retained Equity', 'explanation' => '' ]);
        TransactionType::create(['type'=> 'Treasury Stock', 'explanation' => '' ]);
        TransactionType::create(['type'=> 'Common Stock', 'explanation' => '' ]);
        TransactionType::create(['type'=> 'Other Shareholder Equity', 'explanation' => '' ]);
        


    }
}
