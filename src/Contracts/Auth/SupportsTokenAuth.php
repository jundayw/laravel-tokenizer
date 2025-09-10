<?php

namespace Jundayw\Tokenizer\Contracts\Auth;

use Illuminate\Contracts\Auth\Authenticatable;
use Jundayw\Tokenizer\Contracts\Tokenizable;

interface SupportsTokenAuth
{
    /**
     * Get the currently authenticated user.
     *
     * @return Authenticatable|Tokenizable|null
     */
    public function user(): Authenticatable|Tokenizable|null;

    /**
     * Log the given user ID into the application without maintaining session state.
     *
     * @param mixed $id
     *
     * @return Authenticatable|Tokenizable|null
     */
    public function onceUsingId(mixed $id): Authenticatable|Tokenizable|null;

    /**
     * Log a user into the application without maintaining session state.
     *
     * @param array $credentials
     *
     * @return Authenticatable|Tokenizable|null
     */
    public function once(array $credentials = []): Authenticatable|Tokenizable|null;

    /**
     * Attempt to authenticate a user using the given credentials.
     *
     * @param array $credentials
     *
     * @return Authenticatable|Tokenizable|null
     */
    public function attempt(array $credentials = []): Authenticatable|Tokenizable|null;

    /**
     * Log the given user ID into the application.
     *
     * @param mixed $id
     *
     * @return Authenticatable|Tokenizable|null
     */
    public function loginUsingId(mixed $id): Authenticatable|Tokenizable|null;

    /**
     * Log a user into the application.
     *
     * @param Authenticatable $user
     *
     * @return Authenticatable|Tokenizable|null
     */
    public function login(Authenticatable $user): Authenticatable|Tokenizable|null;

    /**
     * Log the user out of the application.
     *
     * @return bool
     */
    public function logout(): bool;
}
