<?php

namespace Jundayw\Tokenizer\Guards;

use Illuminate\Auth\GuardHelpers;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Traits\Macroable;
use Jundayw\Tokenizer\Contracts\Auth\Grant;
use Jundayw\Tokenizer\Contracts\Auth\SupportsBasicAuth;
use Jundayw\Tokenizer\Contracts\Auth\SupportsTokenAuth;

class TokenizerGuard implements Guard, SupportsBasicAuth, SupportsTokenAuth
{
    use GuardHelpers, Macroable;

    public function __construct(
        protected Grant $grant,
        protected Request $request,
        UserProvider $provider
    ) {
        $this->provider = $provider;
    }

    /**
     * Get the currently authenticated user.
     *
     * @return Authenticatable|null
     */
    public function user(): ?Authenticatable
    {
        // If we've already retrieved the user for the current request we can just
        // return it back immediately. We do not want to fetch the user data on
        // every call to this method because that would be tremendously slow.
        if (!is_null($this->user)) {
            return $this->user;
        }

        return $this->user = call_user_func(
            $this->grant, $this->request, $this->getProvider()
        );
    }

    /**
     * Validate a user's credentials.
     *
     * @param array $credentials
     *
     * @return bool
     */
    public function validate(array $credentials = []): bool
    {
        return !is_null((new static(
            $this->grant, $credentials['request'], $this->getProvider()
        ))->user());
    }

    /**
     * Set the current request instance.
     *
     * @param Request $request
     *
     * @return static
     */
    public function setRequest(Request $request): static
    {
        $this->request = $request;

        return $this;
    }
}
