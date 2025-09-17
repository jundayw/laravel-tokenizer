<?php

namespace Jundayw\Tokenizer;

use DateInterval;
use DateTime;
use Exception;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Http\Request;
use Jundayw\Tokenizer\Contracts\Auth\Grant;
use Jundayw\Tokenizer\Contracts\Authorizable;
use Jundayw\Tokenizer\Contracts\Tokenable;
use Jundayw\Tokenizer\Contracts\Tokenizable;

class TokenizerGrant implements Grant
{
    /**
     * The token.
     *
     * @var string|null
     */
    protected ?string $token = null;

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
     * Create a new access token for the user.
     *
     * @param string $name
     * @param string $scene
     * @param array  $scopes
     *
     * @return Tokenable|null
     */
    public function createToken(string $name, string $scene = 'default', array $scopes = []): ?Tokenable
    {
        if (is_null($tokenizable = $this->getTokenizable())) {
            return null;
        }

        $authorizable = $tokenizable->tokens()->make([
            'scene'                      => $scene,
            'name'                       => $name,
            // 'access_token'               => $accessToken,
            // 'refresh_token'              => $refreshToken,
            // 'token_driver'               => $tokenDriver,
            'scopes'                     => $this->getScopes($scopes),
            'access_token_expire_at'     => $this->getDateTimeAt(config('tokenizer.ttl', 7200)),
            'refresh_token_available_at' => $this->getDateTimeAt(config('tokenizer.refresh_nbf', 7200)),
            'refresh_token_expire_at'    => $this->getDateTimeAt(config('tokenizer.refresh_ttl', 'P15D')),
        ]);

        $tokenable = $this->getTokenable()->buildTokens($authorizable, $tokenizable);
        return tap($tokenable, static function (Tokenable $tokenable) use ($authorizable) {
            $authorizable->fill([
                'token_driver'  => $tokenable->getName(),
                'access_token'  => $tokenable->getAccessToken(),
                'refresh_token' => $tokenable->getRefreshToken(),
            ])->save();
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
        return $this->getAuthorizable()->delete() ?? false;
    }

    /**
     * Refresh the access and refresh tokens using the current refresh token.
     *
     * @return Tokenable|null
     */
    public function refreshToken(): ?Tokenable
    {
        $authorizable = $this->getAuthorizable();
        $tokenable    = $this->getTokenizable();

        if (!$this->getAuthorizable()->exists || is_null($tokenable)) {
            return null;
        }

        $authorizable->fill([
            'access_token_expire_at'     => $this->getDateTimeAt(config('tokenizer.ttl', 7200)),
            'refresh_token_available_at' => $this->getDateTimeAt(config('tokenizer.refresh_nbf', 7200)),
            'refresh_token_expire_at'    => $this->getDateTimeAt(config('tokenizer.refresh_ttl', 'P15D')),
        ]);

        $tokenable = $this->getTokenable()->buildTokens($authorizable, $tokenable);
        return tap($tokenable, function (Tokenable $tokenable) use ($authorizable) {
            $authorizable->fill([
                'access_token'  => $tokenable->getAccessToken(),
                'refresh_token' => $tokenable->getRefreshToken(),
            ])->save();
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
     * Get the Tokenable instance.
     *
     * @return Tokenable
     */
    public function getTokenable(): Tokenable
    {
        return $this->tokenable ?? $this->getTokenManager()->driver();
    }

    /**
     * Set the Tokenable instance.
     *
     * @param Tokenable $tokenable
     *
     * @return static
     */
    public function setTokenable(Tokenable $tokenable): static
    {
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
    public function setTokenizable(Tokenizable $tokenizable): static
    {
        $this->tokenizable = $tokenizable;
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
