<?php

namespace App\Services\Payments;

use RuntimeException;

/**
 * The payment provider couldn't be reached or refused the request.
 */
class GatewayException extends RuntimeException {}
