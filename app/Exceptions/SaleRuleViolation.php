<?php

namespace App\Exceptions;

use DomainException;

/**
 * A sale that breaks a business rule. The message is safe to show to staff.
 */
class SaleRuleViolation extends DomainException {}
