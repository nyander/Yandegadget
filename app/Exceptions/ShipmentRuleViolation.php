<?php

namespace App\Exceptions;

use DomainException;

/**
 * A shipment action that breaks a business rule. The message is safe to show to staff.
 */
class ShipmentRuleViolation extends DomainException {}
