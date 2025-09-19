<?php

namespace Jundayw\Tokenizer\Contracts\Auth;

use Jundayw\Tokenizer\Contracts\Authorizable;
use Jundayw\Tokenizer\Contracts\Blacklist;
use Jundayw\Tokenizer\Contracts\Tokenable;
use Jundayw\Tokenizer\Contracts\Tokenizable;
use Jundayw\Tokenizer\Contracts\Whitelist;
use Jundayw\Tokenizer\TokenManager;

interface Grant
{
    /**
     * Create a new access token for the user.
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
     * Set the token value from the given string.
     *
     * @param string|null $token
     *
     * @return string
     */
    public function fromString(string $token = null): string;

    /**
     * Get the current token value.
     *
     * @return string|null
     */
    public function getToken(): ?string;

    /**
     * Get the current guard instance.
     *
     * @return SupportsTokenAuth
     */
    public function getGuard(): SupportsTokenAuth;

    /**
     * Set the guard instance to be used.
     *
     * @param SupportsTokenAuth $guard
     *
     * @return static
     */
    public function usingGuard(SupportsTokenAuth $guard): static;

    /**
     * Get the Tokenable instance.
     *
     * @return Tokenable
     */
    public function getTokenable(): Tokenable;

    /**
     * Set the Tokenable instance.
     *
     * @param Tokenable|string|null $tokenable
     *
     * @return static
     */
    public function usingTokenable(Tokenable|string $tokenable = null): static;

    /**
     * Get the Tokenizable instance.
     *
     * @return Tokenizable|null
     */
    public function getTokenizable(): ?Tokenizable;

    /**
     * Set the Tokenizable instance.
     *
     * @param Tokenizable $tokenizable
     *
     * @return static
     */
    public function usingTokenizable(Tokenizable $tokenizable): static;

    /**
     * Get the Authorizable instance associated with this object.
     *
     * @return Authorizable
     */
    public function getAuthorizable(): Authorizable;

    /**
     * Set the Authorizable instance.
     *
     * @param Authorizable $authorizable
     *
     * @return static Returns the current instance for method chaining.
     */
    public function setAuthorizable(Authorizable $authorizable): static;

    /**
     * Get the TokenManager instance.
     *
     * @return TokenManager
     */
    public function getTokenManager(): TokenManager;

    /**
     * Set the TokenManager instance.
     *
     * @param TokenManager $tokenManager
     *
     * @return static Returns the current instance for method chaining.
     */
    public function setTokenManager(TokenManager $tokenManager): static;

    /**
     * Get the blacklist repository.
     *
     * @return Blacklist
     */
    public function getBlacklist(): Blacklist;

    /**
     * Set the blacklist repository.
     *
     * @param Blacklist $blacklist
     *
     * @return static Returns the current instance for method chaining.
     */
    public function setBlacklist(Blacklist $blacklist): static;

    /**
     * Get the whitelist repository.
     *
     * @return Whitelist
     */
    public function getWhitelist(): Whitelist;

    /**
     * Set the whitelist repository.
     *
     * @param Whitelist $whitelist
     *
     * @return static Returns the current instance for method chaining.
     */
    public function setWhitelist(Whitelist $whitelist): static;
}
