<?php

namespace Jundayw\Tokenizer\Guards;

use Illuminate\Contracts\Auth\Authenticatable;
use Jundayw\Tokenizer\Contracts\Tokenizable;

trait TokenAuthHelpers
{
    /**
     * Log the given user ID into the application without maintaining session state.
     *
     * @param mixed $id
     *
     * @return Authenticatable|Tokenizable|null
     */
    public function onceUsingId(mixed $id): Authenticatable|Tokenizable|null
    {
        if (!is_null($user = $this->provider->retrieveById($id))) {
            return tap($user, fn($user) => $this->setUser($user));
        }

        return null;
    }

    /**
     * Log a user into the application without maintaining session state.
     *
     * @param array $credentials
     *
     * @return Authenticatable|Tokenizable|null
     */
    public function once(array $credentials = []): Authenticatable|Tokenizable|null
    {
        if (!is_null($user = $this->provider->retrieveByCredentials($credentials))) {
            return tap($user, fn($user) => $this->setUser($user));
        }

        return null;
    }

    /**
     * Attempt to authenticate a user using the given credentials.
     *
     * @param array $credentials
     *
     * @return Authenticatable|Tokenizable|null
     */
    public function attempt(array $credentials = []): Authenticatable|Tokenizable|null
    {
        if (!is_null($user = $this->provider->retrieveByCredentials($credentials))) {
            return $this->login($user);
        }

        return null;
    }

    /**
     * Log the given user ID into the application.
     *
     * @param mixed $id
     *
     * @return Authenticatable|Tokenizable|null
     */
    public function loginUsingId(mixed $id): Authenticatable|Tokenizable|null
    {
        if (!is_null($user = $this->provider->retrieveById($id))) {
            return $this->login($user);
        }

        return null;
    }

    /**
     * Log a user into the application.
     *
     * @param Authenticatable $user
     *
     * @return Authenticatable|Tokenizable|null
     */
    public function login(Authenticatable $user): Authenticatable|Tokenizable|null
    {
        return tap($user, fn($user) => $this->setUser($user)->fireLoginEvent($user));
    }

    /**
     * Log the user out of the application.
     *
     * @return bool
     */
    public function logout(): bool
    {
        if ($this->guest()) {
            return false;
        }

        $user = $this->user();

        $this->forgetUser()->fireLogoutEvent($user);

        return true;
    }
}
