<?php

namespace Jundayw\Tokenizer\Exceptions;

class AuthenticationException extends \Illuminate\Auth\AuthenticationException
{
    public function __construct($message = 'Unauthenticated.', array|string $guards = [], string $redirectTo = null)
    {
        parent::__construct($message, is_array($guards) ? $guards : [$guards], $redirectTo);

        $this->code = 401;
    }
}
