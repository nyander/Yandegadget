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

    /*
     * Shown on the privacy policy and terms. The Data Protection Commission
     * registration number appears once the shop has one (Data Protection Act, 2012).
     */
    'contact' => [
        'email' => env('SHOP_CONTACT_EMAIL'),
        'phone' => env('SHOP_CONTACT_PHONE'),
        'address' => '27 Ayawaso, Legon Boundary, Accra, Ghana',
    ],
    'dpc_registration' => env('SHOP_DPC_REGISTRATION'),
    'legal_updated_on' => '2026-11-20',

    /*
    | Languages customers can choose on the shop and their account pages,
    | each named in its own language. English is the source text: the others
    | live in lang/{code}.json, keyed by the English wording.
    */
    'locales' => [
        'en' => 'English',
        'tw' => 'Twi',
        'gaa' => 'Gã',
        'ee' => 'Eʋegbe',
        'fr' => 'Français',
    ],
];
