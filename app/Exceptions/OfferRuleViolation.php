<?php

namespace App\Exceptions;

use DomainException;

/**
 * A supplier offer action that breaks a business rule. The message is safe to show.
 */
class OfferRuleViolation extends DomainException {}
