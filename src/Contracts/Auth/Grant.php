<?php

namespace Jundayw\Tokenizer\Contracts\Auth;

use Illuminate\Contracts\Cache\Repository;
use Illuminate\Http\Request;
use Jundayw\Tokenizer\Contracts\Authorizable;
use Jundayw\Tokenizer\TokenManager;

interface Grant
{
    /**
     * Get the token for the current request.
     *
     * @param Request $request
     *
     * @return string|null
     */
    public function getTokenFromRequest(Request $request): ?string;

    /**
     * Get the current token value.
     *
     * @return string|null
     */
    public function getToken(): ?string;

    /**
     * Set the token value from the given string.
     *
     * @param string $token
     *
     * @return string
     */
    public function fromString(string $token): string;

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
     * @return Repository
     */
    public function getBlacklist(): Repository;

    /**
     * Set the blacklist repository.
     *
     * @param Repository $blacklist
     *
     * @return static Returns the current instance for method chaining.
     */
    public function setBlacklist(Repository $blacklist): static;

    /**
     * Get the whitelist repository.
     *
     * @return Repository
     */
    public function getWhitelist(): Repository;

    /**
     * Set the whitelist repository.
     *
     * @param Repository $whitelist
     *
     * @return static Returns the current instance for method chaining.
     */
    public function setWhitelist(Repository $whitelist): static;

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
