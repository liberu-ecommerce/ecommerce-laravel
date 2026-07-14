<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * A checkout failure whose message is safe to show the client (empty cart, out of
 * stock, invalid shipping rate, payment declined). The GraphQL layer surfaces these
 * as visible errors; any other exception stays masked as an internal error.
 */
class CheckoutException extends RuntimeException {}
