<?php

namespace Jundayw\Tokenizer\Guards;

use Illuminate\Auth\Events\Authenticated;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\GuardHelpers;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Traits\Macroable;
use Jundayw\Tokenizer\Contracts\Auth\Grant;
use Jundayw\Tokenizer\Contracts\Auth\SupportsTokenAuth;
use Jundayw\Tokenizer\Contracts\Tokenizable;

class TokenizerGuard implements Guard, SupportsTokenAuth
{
    use GuardHelpers, TokenAuthHelpers, Macroable;

    public function __construct(
        protected string $name,
        protected Grant $grant,
        protected Request $request,
        UserProvider $provider
    ) {
        $this->provider = $provider;
    }

    /**
     * Get the currently authenticated user.
     *
     * @return Authenticatable|Tokenizable|null
     */
    public function user(): Authenticatable|Tokenizable|null
    {
        // If we've already retrieved the user for the current request we can just
        // return it back immediately. We do not want to fetch the user data on
        // every call to this method because that would be tremendously slow.
        if ($this->hasUser()) {
            return $this->user;
        }

        $this->user = call_user_func(
            $this->grant, $this->request, $this->getProvider()
        );

        if ($this->user instanceof Authenticatable) {
            $this->fireAuthenticatedEvent($this->user);
        }

        return $this->user;
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
        if ($this->guest()) {
            return false;
        }

        if (is_null($user = $this->provider->retrieveByCredentials($credentials))) {
            return false;
        }

        if (get_class($this->user) !== get_class($user)) {
            return false;
        }

        return $this->user->getAuthIdentifier() === $user->getAuthIdentifier();
    }

    /**
     * Fire the authenticated event.
     *
     * @param Authenticatable $user
     *
     * @return void
     */
    protected function fireAuthenticatedEvent(Authenticatable $user): void
    {
        event(new Authenticated($this->name, $user));
    }

    /**
     * Fire the login event.
     *
     * @param Authenticatable $user
     *
     * @return void
     */
    protected function fireLoginEvent(Authenticatable $user): void
    {
        event(new Login($this->name, $user, false));
    }

    /**
     * Fire the logout event.
     *
     * @param Authenticatable $user
     *
     * @return void
     */
    protected function fireLogoutEvent(Authenticatable $user): void
    {
        event(new Logout($this->name, $user));
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
