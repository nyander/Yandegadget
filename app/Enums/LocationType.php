<?php

namespace App\Enums;

enum LocationType: string
{
    /** A shop that sells to customers, e.g. the Legon store. */
    case Store = 'store';

    /** Where stock is bought and shipped from, e.g. the UK. */
    case Buying = 'buying';
}
