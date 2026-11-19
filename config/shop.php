<?php

return [
    /*
     * Stock is bought in the UK, so costs and list prices are entered in GBP.
     */
    'list_currency' => env('SHOP_LIST_CURRENCY', 'GBP'),

    /*
     * Customers in Ghana see prices converted at the latest exchange rate.
     */
    'display_currency' => env('SHOP_DISPLAY_CURRENCY', 'GHS'),

    'display_locale' => env('SHOP_DISPLAY_LOCALE', 'en_GH'),

    'per_page' => 12,
];
