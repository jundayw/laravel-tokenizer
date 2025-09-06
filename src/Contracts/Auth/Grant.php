<?php

namespace Jundayw\Tokenizer\Contracts\Auth;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Http\Request;
use Jundayw\Tokenizer\Contracts\Authorizable;

interface Grant
{
    /**
     * Retrieve the authenticated user for the incoming request.
     *
     * @param Request      $request
     * @param UserProvider $provider
     *
     * @return Authenticatable|null
     */
    public function __invoke(Request $request, UserProvider $provider): ?Authenticatable;

    /**
     * Get the authorizable model used by the token.
     *
     * @return Authorizable
     */
    public function getProvider(): Authorizable;

    /**
     * Set the authorizable model used by the token.
     *
     * @param Authorizable $provider
     *
     * @return static
     */
    public function setProvider(Authorizable $provider): static;
}
