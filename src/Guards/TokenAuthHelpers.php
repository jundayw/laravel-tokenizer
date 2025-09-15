<?php

namespace Jundayw\Tokenizer\Guards;

use Illuminate\Contracts\Auth\Authenticatable;

trait TokenAuthHelpers
{
    /**
     * Log the given user ID into the application without maintaining session state.
     *
     * @param mixed $id
     *
     * @return static|null
     */
    public function onceUsingId(mixed $id): static|null
    {
        if (!is_null($user = $this->provider->retrieveById($id))) {
            return $this->setUser($user);
        }

        return null;
    }

    /**
     * Log a user into the application without maintaining session state.
     *
     * @param array $credentials
     *
     * @return static|null
     */
    public function once(array $credentials = []): static|null
    {
        if (!is_null($user = $this->provider->retrieveByCredentials($credentials))) {
            return $this->setUser($user);
        }

        return null;
    }

    /**
     * Attempt to authenticate a user using the given credentials.
     *
     * @param array $credentials
     *
     * @return static|null
     */
    public function attempt(array $credentials = []): static|null
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
     * @return static|null
     */
    public function loginUsingId(mixed $id): static|null
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
     * @return static|null
     */
    public function login(Authenticatable $user): static|null
    {
        $this->setUser($user)->fireLoginEvent($user);
        return $this;
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
