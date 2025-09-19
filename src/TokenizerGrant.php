<?php

namespace Jundayw\Tokenizer;

use DateInterval;
use DateTime;
use Exception;
use Jundayw\Tokenizer\Contracts\Auth\Grant;
use Jundayw\Tokenizer\Contracts\Auth\SupportsTokenAuth;
use Jundayw\Tokenizer\Contracts\Authorizable;
use Jundayw\Tokenizer\Contracts\Blacklist;
use Jundayw\Tokenizer\Contracts\Tokenable;
use Jundayw\Tokenizer\Contracts\Tokenizable;
use Jundayw\Tokenizer\Contracts\Whitelist;
use Jundayw\Tokenizer\Events\AccessTokenCreated;
use Jundayw\Tokenizer\Events\AccessTokenRefreshing;
use Jundayw\Tokenizer\Events\AccessTokenRevoked;
use Jundayw\Tokenizer\Events\AccessTokenRefreshed;

class TokenizerGrant implements Grant
{
    /**
     * The token.
     *
     * @var string|null
     */
    protected ?string $token = null;

    /**
     * The Guard instance.
     *
     * @var SupportsTokenAuth
     */
    protected SupportsTokenAuth $guard;

    /**
     * The Tokenable instance.
     *
     * @var Tokenable|null
     */
    public ?Tokenable $tokenable = null;

    /**
     * The Tokenizable instance.
     *
     * @var Tokenizable|null
     */
    public ?Tokenizable $tokenizable = null;

    public function __construct(
        protected Authorizable $authorizable,
        protected TokenManager $tokenManager,
        protected Blacklist $blacklist,
        protected Whitelist $whitelist,
    ) {
        //
    }

    /**
     * Create a new access token for the user.
     *
     * @param string $name
     * @param string $platform
     * @param array  $scopes
     *
     * @return Tokenable|null
     */
    public function createToken(string $name, string $platform = 'default', array $scopes = []): ?Tokenable
    {
        if (is_null($tokenizable = $this->getTokenizable())) {
            return null;
        }

        $ttl          = config('tokenizer.ttl', 7200);
        $refreshNbf   = config('tokenizer.refresh_nbf', 3600);
        $refreshTtl   = config('tokenizer.refresh_ttl', 'P15D');
        $authorizable = $tokenizable->tokens()->make([
            'name'                       => $name,
            'platform'                   => $platform,
            'scopes'                     => $this->getScopes($scopes),
            'access_token_expire_at'     => $this->getDateTimeAt($ttl),
            'refresh_token_available_at' => $this->getDateTimeAt($refreshNbf),
            'refresh_token_expire_at'    => $this->getDateTimeAt($refreshTtl),
        ]);
        $tokenable    = $this->getTokenable()->buildTokens($authorizable, $tokenizable);

        return tap($tokenable, function (Tokenable $tokenable) use ($authorizable, $tokenizable) {
            if ($authorizable->fill([
                'token_driver'  => $tokenable->getName(),
                'access_token'  => $tokenable->getAccessToken(),
                'refresh_token' => $tokenable->getRefreshToken(),
            ])->save()) {
                event(new AccessTokenCreated($this->getGuard()->getConfig(), $authorizable, $tokenizable, $tokenable));
            }
        });
    }

    /**
     * Revoke (invalidate) the both access and refresh tokens by access token.
     *
     * @return bool
     */
    public function revokeToken(): bool
    {
        if (!$this->getAuthorizable()->exists) {
            return false;
        }

        if ($this->getAuthorizable()->delete()) {
            event(new AccessTokenRevoked($this->getAuthorizable(), $this->getTokenizable(), $this->getTokenable()));
            return true;
        }

        return false;
    }

    /**
     * Refresh the access and refresh tokens using the current refresh token.
     *
     * @return Tokenable|null
     */
    public function refreshToken(): ?Tokenable
    {
        $authorizable = $this->getAuthorizable();
        $tokenizable  = $this->getTokenizable();

        if (!$this->getAuthorizable()->exists || is_null($tokenizable)) {
            return null;
        }

        $ttl          = config('tokenizer.ttl', 7200);
        $refreshNbf   = config('tokenizer.refresh_nbf', 3600);
        $refreshTtl   = config('tokenizer.refresh_ttl', 'P15D');
        $authorizable = $authorizable->fill([
            'access_token_expire_at'     => $this->getDateTimeAt($ttl),
            'refresh_token_available_at' => $this->getDateTimeAt($refreshNbf),
            'refresh_token_expire_at'    => $this->getDateTimeAt($refreshTtl),
        ]);
        $tokenable    = $this->getTokenable()->buildTokens($authorizable, $tokenizable);

        return tap($tokenable, function (Tokenable $tokenable) use ($authorizable, $tokenizable) {
            $originals = array_map(fn($instance) => clone $instance, [$authorizable, $tokenizable, $tokenable]);
            if ($authorizable->fill([
                'access_token'  => $tokenable->getAccessToken(),
                'refresh_token' => $tokenable->getRefreshToken(),
            ])->save()) {
                event(new AccessTokenRefreshing(...$originals));
                event(new AccessTokenRefreshed($authorizable, $tokenizable, $tokenable));
            }
        });
    }

    /**
     * Return an array of scopes associated with the token.
     *
     * @return string[]
     */
    final public function getScopes(array $scopes): array
    {
        if (in_array('*', $scopes) || in_array('*', $this->getTokenizable()->getScopes())) {
            return ['*'];
        }

        return array_merge($scopes, $this->getTokenizable()->getScopes());
    }

    /**
     * Get a DateTime object after a specified duration.
     *
     * @param string|int $duration ISO 8601 duration string or integer number of seconds
     * @param int        $default  Default seconds to use if string parsing fails, default is 7200 (2 hours)
     *
     * @return DateTime The calculated DateTime object
     */
    final public function getDateTimeAt(string|int $duration = 0, int $default = 7200): DateTime
    {
        if (is_string($duration)) {
            try {
                return now()->add(new DateInterval($duration))->toDateTime();
            } catch (Exception $e) {
                return now()->addSeconds($default)->toDateTime();
            }
        }

        return now()->addSeconds($duration)->toDateTime();
    }

    /**
     * Set the token value from the given string.
     *
     * @param string|null $token
     *
     * @return string
     */
    public function fromString(string $token = null): string
    {
        return $this->token = $token;
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
     * Get the current guard instance.
     *
     * @return SupportsTokenAuth
     */
    public function getGuard(): SupportsTokenAuth
    {
        return $this->guard;
    }

    /**
     * Set the guard instance to be used.
     *
     * @param SupportsTokenAuth $guard
     *
     * @return static
     */
    public function usingGuard(SupportsTokenAuth $guard): static
    {
        $this->guard = $guard;

        return $this;
    }

    /**
     * Get the Tokenable instance.
     *
     * @return Tokenable
     */
    public function getTokenable(): Tokenable
    {
        return $this->tokenable ?? $this->usingTokenable($this->tokenable)->tokenable;
    }

    /**
     * Set the Tokenable instance.
     *
     * @param Tokenable|string|null $tokenable
     *
     * @return static
     */
    public function usingTokenable(Tokenable|string $tokenable = null): static
    {
        if (is_string($tokenable) || is_null($tokenable)) {
            $tokenable = $this->getTokenManager()->driver($tokenable);
        }

        $this->tokenable = $tokenable;
        return $this;
    }

    /**
     * Get the Tokenizable instance.
     *
     * @return Tokenizable|null
     */
    public function getTokenizable(): ?Tokenizable
    {
        return $this->tokenizable;
    }

    /**
     * Set the Tokenizable instance.
     *
     * @param Tokenizable $tokenizable
     *
     * @return static
     */
    public function usingTokenizable(Tokenizable $tokenizable): static
    {
        $this->tokenizable = $tokenizable;
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
     * @return Blacklist
     */
    public function getBlacklist(): Blacklist
    {
        return $this->blacklist;
    }

    /**
     * Set the blacklist repository.
     *
     * @param Blacklist $blacklist
     *
     * @return static Returns the current instance for method chaining.
     */
    public function setBlacklist(Blacklist $blacklist): static
    {
        $this->blacklist = $blacklist;
        return $this;
    }

    /**
     * Get the whitelist repository.
     *
     * @return Whitelist
     */
    public function getWhitelist(): Whitelist
    {
        return $this->whitelist;
    }

    /**
     * Set the whitelist repository.
     *
     * @param Whitelist $whitelist
     *
     * @return static Returns the current instance for method chaining.
     */
    public function setWhitelist(Whitelist $whitelist): static
    {
        $this->whitelist = $whitelist;
        return $this;
    }
}
