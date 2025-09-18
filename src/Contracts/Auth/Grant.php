<?php

namespace Jundayw\Tokenizer\Contracts\Auth;

use Illuminate\Http\Request;
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
     * Check if blacklist functionality is enabled.
     *
     * @return bool True if blacklist is enabled, false otherwise.
     */
    public function isBlacklistEnabled(): bool;

    /**
     * Enable or disable blacklist functionality.
     *
     * @param bool $blacklistEnabled
     *
     * @return static Returns the current instance for method chaining.
     */
    public function setBlacklistEnabled(bool $blacklistEnabled): static;

    /**
     * Check if whitelist functionality is enabled.
     *
     * @return bool True if whitelist is enabled, false otherwise.
     */
    public function isWhitelistEnabled(): bool;

    /**
     * Enable or disable whitelist functionality.
     *
     * @param bool $whitelistEnabled
     *
     * @return static Returns the current instance for method chaining.
     */
    public function setWhitelistEnabled(bool $whitelistEnabled): static;

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
     * Get the Tokenable instance.
     *
     * @return Tokenable
     */
    public function getTokenable(): Tokenable;

    /**
     * Set the Tokenable instance.
     *
     * @param Tokenable $tokenable
     *
     * @return static
     */
    public function setTokenable(Tokenable $tokenable): static;

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
    public function setTokenizable(Tokenizable $tokenizable): static;

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

    /**
     * Get the current HTTP request instance.
     *
     * @return Request
     */
    public function getRequest(): Request;

    /**
     * Set the current HTTP request instance.
     *
     * @param Request $request
     *
     * @return static Returns the current instance for method chaining.
     */
    public function setRequest(Request $request): static;
}
