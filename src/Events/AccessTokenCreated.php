<?php

namespace Jundayw\Tokenizer\Events;

use Jundayw\Tokenizer\Contracts\Authorizable;
use Jundayw\Tokenizer\Contracts\Tokenable;
use Jundayw\Tokenizer\Contracts\Tokenizable;

class AccessTokenCreated
{
    /**
     * Create a new event instance.
     *
     * @param Authorizable $authorizable
     * @param Tokenizable  $tokenizable
     * @param Tokenable    $tokenable
     */
    public function __construct(
        protected readonly Authorizable $authorizable,
        protected readonly Tokenizable $tokenizable,
        protected readonly Tokenable $tokenable,
    ) {
        //
    }

    /**
     * Get the authorizable entity instance.
     *
     * @return Authorizable
     */
    public function getAuthorizable(): Authorizable
    {
        return $this->authorizable;
    }

    /**
     * Get the tokenizable entity instance.
     *
     * @return Tokenizable
     */
    public function getTokenizable(): Tokenizable
    {
        return $this->tokenizable;
    }

    /**
     * Get the tokenable entity instance.
     *
     * @return Tokenable
     */
    public function getTokenable(): Tokenable
    {
        return $this->tokenable;
    }
}
