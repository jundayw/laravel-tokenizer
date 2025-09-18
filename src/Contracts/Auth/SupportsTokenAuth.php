<?php

namespace Jundayw\Tokenizer\Contracts\Auth;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Http\Request;
use Jundayw\Tokenizer\Contracts\Authorizable;
use Jundayw\Tokenizer\Contracts\Tokenable;
use Jundayw\Tokenizer\Contracts\Tokenizable;
use Jundayw\Tokenizer\TokenManager;

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
     * @return static|null
     */
    public function onceUsingId(mixed $id): static|null;

    /**
     * Log a user into the application without maintaining session state.
     *
     * @param array $credentials
     *
     * @return static|null
     */
    public function once(array $credentials = []): static|null;

    /**
     * Attempt to authenticate a user using the given credentials.
     *
     * @param array $credentials
     *
     * @return static|null
     */
    public function attempt(array $credentials = []): static|null;

    /**
     * Log the given user ID into the application.
     *
     * @param mixed $id
     *
     * @return static|null
     */
    public function loginUsingId(mixed $id): static|null;

    /**
     * Log a user into the application.
     *
     * @param Authenticatable $user
     *
     * @return static|null
     */
    public function login(Authenticatable $user): static|null;

    /**
     * Log the user out of the application.
     *
     * @return bool
     */
    public function logout(): bool;

    /**
     * Get the access token for the current request.
     *
     * @param Request $request
     *
     * @return string|null
     */
    public function getAccessTokenFromRequest(Request $request): ?string;

    /**
     * Get the refresh token for the current request.
     *
     * @param Request $request
     *
     * @return string|null
     */
    public function getRefreshTokenFromRequest(Request $request): ?string;

    /**
     * Build an access token and refresh token pair from given values.
     *
     * @param string $name
     * @param string $platform
     * @param array  $scopes
     *
     * @return Tokenable|null
     */
    public function createToken(string $name, string $platform = 'default', array $scopes = []): ?Tokenable;

    /**
     * Revoke (invalidate) the both access and refresh tokens by access token.
     *
     * @return bool
     */
    public function revokeToken(): bool;

    /**
     * Refresh the access and refresh tokens using the current refresh token.
     *
     * @return Tokenable|null
     */
    public function refreshToken(): ?Tokenable;

    /**
     * Get the current grant instance.
     *
     * @return Grant
     */
    public function getGrant(): Grant;

    /**
     * Set the grant instance to be used.
     *
     * @param Grant $grant
     *
     * @return static
     */
    public function usingGrant(Grant $grant): static;

    /**
     * Get the tokenable entity instance.
     *
     * @return Tokenable
     */
    public function getTokenable(): Tokenable;

    /**
     * Specify the tokenable entity to be used.
     *
     * @param string|null $name
     *
     * @return static
     */
    public function usingTokenable(?string $name = null): static;

    /**
     * Get the Authorizable instance associated with this object.
     *
     * @return Authorizable
     */
    public function getAuthorizable(): Authorizable;

    /**
     * Get the TokenManager instance.
     *
     * @return TokenManager
     */
    public function getTokenManager(): TokenManager;

    /**
     * Get the blacklist repository.
     *
     * @return Repository
     */
    public function getBlacklist(): Repository;

    /**
     * Get the whitelist repository.
     *
     * @return Repository
     */
    public function getWhitelist(): Repository;
}
