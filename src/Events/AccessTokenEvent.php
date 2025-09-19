<?php

namespace Jundayw\Tokenizer\Events;

use Illuminate\Queue\SerializesModels;
use Jundayw\Tokenizer\Contracts\Authorizable;
use Jundayw\Tokenizer\Contracts\Tokenable;
use Jundayw\Tokenizer\Contracts\Tokenizable;

class AccessTokenEvent
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param Authorizable $authorizable
     * @param Tokenizable  $tokenizable
     * @param Tokenable    $tokenable
     */
    public function __construct(
        protected Authorizable $authorizable,
        protected Tokenizable $tokenizable,
        protected Tokenable $tokenable,
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
