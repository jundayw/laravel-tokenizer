<?php

namespace Jundayw\Tokenizer\Grants;

use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Factory;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Http\Request;
use Jundayw\Tokenizer\Contracts\Auth\Grant;

class TokenizerGrant implements Grant
{
    public function __construct(
        protected Factory              $factory,
        protected array                $providers,
        protected EloquentUserProvider $provider,
    )
    {
        //
    }

    public function __invoke(Request $request, UserProvider $provider): ?Authenticatable
    {
        return $provider->retrieveById(1);
    }

    /**
     * Get the user provider used by the guard.
     *
     * @return UserProvider
     */
    public function getProvider(): UserProvider
    {
        return $this->provider;
    }

    /**
     * Set the user provider used by the guard.
     *
     * @param UserProvider $provider
     *
     * @return static
     */
    public function setProvider(UserProvider $provider): static
    {
        $this->provider = $provider;

        return $this;
    }
}
