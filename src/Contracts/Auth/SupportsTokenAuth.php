<?php

namespace Jundayw\Tokenizer\Contracts\Auth;

use Illuminate\Contracts\Auth\Authenticatable;

interface SupportsTokenAuth
{
    /**
     * Log the given user ID into the application without maintaining session state.
     *
     * @param mixed $id
     *
     * @return Authenticatable|false
     */
    public function onceUsingId(mixed $id): bool|Authenticatable;

    /**
     * Log a user into the application without maintaining session state.
     *
     * @param array $credentials
     *
     * @return bool
     */
    public function once(array $credentials = []): bool;

    /**
     * Attempt to authenticate a user using the given credentials.
     *
     * @param array $credentials
     *
     * @return bool
     */
    public function attempt(array $credentials = []): bool;

    /**
     * Log the given user ID into the application.
     *
     * @param mixed $id
     *
     * @return Authenticatable|false
     */
    public function loginUsingId(mixed $id): bool|Authenticatable;

    /**
     * Log a user into the application.
     *
     * @param Authenticatable $user
     *
     * @return void
     */
    public function login(Authenticatable $user): void;

    /**
     * Log the user out of the application.
     *
     * @return void
     */
    public function logout(): void;
}
