<?php

namespace App\Policies;

/** The buying list is part of buying stock, so it follows the catalogue permission. */
class BuyingListItemPolicy extends CataloguePolicy {}
