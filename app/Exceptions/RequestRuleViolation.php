<?php

namespace App\Exceptions;

use DomainException;

/**
 * A request action that breaks a business rule. The message is safe to show.
 */
class RequestRuleViolation extends DomainException {}
