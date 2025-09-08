<?php

namespace Jundayw\Tokenizer\Events;

use Jundayw\Tokenizer\Contracts\Authorizable;
use Jundayw\Tokenizer\Contracts\Tokenizable;

class TokenAuthenticated
{
    /**
     * Create a new event instance.
     *
     * @param Authorizable $token
     * @param Tokenizable  $tokenable
     */
    public function __construct(
        protected readonly Authorizable $token,
        protected readonly Tokenizable $tokenable
    ) {
        //
    }

    /**
     * The access token that was authenticated.
     *
     * @return Authorizable
     */
    public function getToken(): Authorizable
    {
        return $this->token;
    }

    /**
     * Get the tokenable model that the access token belongs to.
     *
     * @return Tokenizable
     */
    public function getTokenable(): Tokenizable
    {
        return $this->tokenable;
    }
}
