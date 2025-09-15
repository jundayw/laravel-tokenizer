<?php

namespace Jundayw\Tokenizer;

use Illuminate\Contracts\Cache\Repository;
use Illuminate\Http\Request;
use Jundayw\Tokenizer\Contracts\Auth\Grant;
use Jundayw\Tokenizer\Contracts\Authorizable;

class TokenizerGrant implements Grant
{
    /**
     * The token.
     *
     * @var string|null
     */
    protected ?string $token = null;

    /**
     * The blacklist flag.
     *
     * @var bool
     */
    protected bool $blacklistEnabled = false;

    /**
     * The whitelist flag.
     *
     * @var bool
     */
    protected bool $whitelistEnabled = false;

    public function __construct(
        protected Authorizable $authorizable,
        protected TokenManager $tokenManager,
        protected Repository $blacklist,
        protected Repository $whitelist,
        protected Request $request,
    ) {
        //
    }

    /**
     * Get the token for the current request.
     *
     * @param Request $request
     *
     * @return string|null
     */
    public function getTokenFromRequest(Request $request): ?string
    {
        if (is_callable($accessTokenRetrievalCallback = Tokenizer::accessTokenRetrievalCallback())) {
            return $this->token = call_user_func($accessTokenRetrievalCallback, $request);
        }

        return $this->token = $request->bearerToken() ?: null;
    }

    /**
     * Get the current token value.
     *
     * @return string|null
     */
    public function getToken(): ?string
    {
        return $this->token;
    }

    /**
     * Set the token value from the given string.
     *
     * @param string $token
     *
     * @return string
     */
    public function fromString(string $token): string
    {
        return $this->token = $token;
    }

    /**
     * Check if blacklist functionality is enabled.
     *
     * @return bool True if blacklist is enabled, false otherwise.
     */
    public function isBlacklistEnabled(): bool
    {
        return $this->blacklistEnabled;
    }

    /**
     * Enable or disable blacklist functionality.
     *
     * @param bool $blacklistEnabled
     *
     * @return static Returns the current instance for method chaining.
     */
    public function setBlacklistEnabled(bool $blacklistEnabled): static
    {
        $this->blacklistEnabled = $blacklistEnabled;
        return $this;
    }

    /**
     * Check if whitelist functionality is enabled.
     *
     * @return bool True if whitelist is enabled, false otherwise.
     */
    public function isWhitelistEnabled(): bool
    {
        return $this->whitelistEnabled;
    }

    /**
     * Enable or disable whitelist functionality.
     *
     * @param bool $whitelistEnabled
     *
     * @return static Returns the current instance for method chaining.
     */
    public function setWhitelistEnabled(bool $whitelistEnabled): static
    {
        $this->whitelistEnabled = $whitelistEnabled;
        return $this;
    }

    /**
     * Get the Authorizable instance associated with this object.
     *
     * @return Authorizable
     */
    public function getAuthorizable(): Authorizable
    {
        return $this->authorizable;
    }

    /**
     * Set the Authorizable instance.
     *
     * @param Authorizable $authorizable
     *
     * @return static Returns the current instance for method chaining.
     */
    public function setAuthorizable(Authorizable $authorizable): static
    {
        $this->authorizable = $authorizable;
        return $this;
    }

    /**
     * Get the TokenManager instance.
     *
     * @return TokenManager
     */
    public function getTokenManager(): TokenManager
    {
        return $this->tokenManager;
    }

    /**
     * Set the TokenManager instance.
     *
     * @param TokenManager $tokenManager
     *
     * @return static Returns the current instance for method chaining.
     */
    public function setTokenManager(TokenManager $tokenManager): static
    {
        $this->tokenManager = $tokenManager;
        return $this;
    }

    /**
     * Get the blacklist repository.
     *
     * @return Repository
     */
    public function getBlacklist(): Repository
    {
        return $this->blacklist;
    }

    /**
     * Set the blacklist repository.
     *
     * @param Repository $blacklist
     *
     * @return static Returns the current instance for method chaining.
     */
    public function setBlacklist(Repository $blacklist): static
    {
        $this->blacklist = $blacklist;
        return $this;
    }

    /**
     * Get the whitelist repository.
     *
     * @return Repository
     */
    public function getWhitelist(): Repository
    {
        return $this->whitelist;
    }

    /**
     * Set the whitelist repository.
     *
     * @param Repository $whitelist
     *
     * @return static Returns the current instance for method chaining.
     */
    public function setWhitelist(Repository $whitelist): static
    {
        $this->whitelist = $whitelist;
        return $this;
    }

    /**
     * Get the current HTTP request instance.
     *
     * @return Request
     */
    public function getRequest(): Request
    {
        return $this->request;
    }

    /**
     * Set the current HTTP request instance.
     *
     * @param Request $request
     *
     * @return static Returns the current instance for method chaining.
     */
    public function setRequest(Request $request): static
    {
        $this->request = $request;
        return $this;
    }
}
