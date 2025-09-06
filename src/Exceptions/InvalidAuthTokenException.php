<?php

namespace Jundayw\Tokenizer\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Throwable;

class InvalidAuthTokenException extends AuthorizationException
{
    public function __construct($message = 'Invalid token provided.', $code = 403, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
