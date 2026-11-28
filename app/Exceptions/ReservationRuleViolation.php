<?php

namespace App\Exceptions;

use RuntimeException;

/** A reservation rule was broken; the message is safe to show the customer or staff. */
class ReservationRuleViolation extends RuntimeException {}
