<?php

namespace Jundayw\Tokenizer\Tokens;

use Closure;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Support\Str;
use Jundayw\Tokenizer\Contracts\Authorizable;
use Jundayw\Tokenizer\Contracts\Tokenable;
use Jundayw\Tokenizer\Contracts\Tokenizable;
use Jundayw\Tokenizer\Tokenizer;
use Symfony\Component\HttpFoundation\Cookie;

abstract class Token implements Tokenable
{
    public string        $name;
    protected Repository $config;
    protected ?string    $accessToken  = null;
    protected ?string    $refreshToken = null;
    protected string     $expiresIn;

    /**
     * Get the name of the instance.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Set the name of the instance.
     *
     * @param string $name
     *
     * @return static
     */
    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Get the configuration repository instance.
     *
     * @return Repository
     */
    public function getConfig(): Repository
    {
        return $this->config;
    }

    /**
     * Set the configuration repository instance.
     *
     * @param Repository $config
     *
     * @return static
     */
    public function setConfig(Repository $config): static
    {
        $this->config = $config;
        return $this;
    }

    /**
     * Validate the token using a validator.
     *
     * @param string $token
     *
     * @return bool
     */
    abstract public function validate(string $token): bool;

    /**
     * Get the current access token value.
     *
     * @return string|null
     */
    public function getAccessToken(): ?string
    {
        return hash('sha256', $this->accessToken);
    }

    /**
     * Set the raw access token value before hashing.
     *
     * @param string $token
     *
     * @return static
     */
    public function setAccessToken(string $token): static
    {
        $this->accessToken = $token;
        return $this;
    }

    /**
     * Get the current refresh token value.
     *
     * @return string|null
     */
    public function getRefreshToken(): ?string
    {
        return hash('sha512', $this->refreshToken);
    }

    /**
     * Set the raw refresh token value before hashing.
     *
     * @param string $token
     *
     * @return static
     */
    public function setRefreshToken(string $token): static
    {
        $this->refreshToken = $token;
        return $this;
    }

    /**
     * Get the number of seconds until the access token expires.
     *
     * @return string
     */
    public function getExpiresIn(): string
    {
        return $this->expiresIn;
    }

    /**
     * Generate a new access token.
     *
     * This token is typically short-lived and is used to authenticate API requests.
     *
     * @param Authorizable $authorizable
     * @param Tokenizable  $tokenizable
     *
     * @return string
     */
    abstract protected function generateAccessToken(Authorizable $authorizable, Tokenizable $tokenizable): string;

    /**
     * Generate a new refresh token.
     *
     * Refresh tokens are long-lived and used to obtain new access tokens.
     *
     * @param Authorizable $authorizable
     * @param Tokenizable  $tokenizable
     *
     * @return string
     */
    abstract protected function generateRefreshToken(Authorizable $authorizable, Tokenizable $tokenizable): string;

    /**
     * Generate a unique access token for the given authorizable and tokenizable.
     *
     * @param Authorizable $authorizable
     * @param Tokenizable  $tokenizable
     *
     * @return string
     */
    protected function generateUniqueAccessToken(Authorizable $authorizable, Tokenizable $tokenizable): string
    {
        return $authorizable->newQuery()
            ->where('access_token', $token = $this->generateAccessToken($authorizable, $tokenizable))
            ->exists() ? $this->generateUniqueAccessToken($authorizable, $tokenizable) : $token;
    }

    /**
     * Generate a unique refresh token for the given authorizable and tokenizable.
     *
     * @param Authorizable $authorizable
     * @param Tokenizable  $tokenizable
     *
     * @return string
     */
    protected function generateUniqueRefreshToken(Authorizable $authorizable, Tokenizable $tokenizable): string
    {
        return $authorizable->newQuery()
            ->where('refresh_token', $token = $this->generateRefreshToken($authorizable, $tokenizable))
            ->exists() ? $this->generateUniqueRefreshToken($authorizable, $tokenizable) : $token;
    }

    /**
     * Build an access token and refresh token pair from given values.
     *
     * @param Authorizable $authorizable
     * @param Tokenizable  $tokenizable
     *
     * @return Tokenable
     */
    public function buildTokens(Authorizable $authorizable, Tokenizable $tokenizable): Tokenable
    {
        $this->accessToken  = $this->generateUniqueAccessToken($authorizable, $tokenizable);
        $this->refreshToken = $this->generateUniqueRefreshToken($authorizable, $tokenizable);
        $this->expiresIn    = $authorizable->getAttribute('access_token_expire_at')->toIso8601ZuluString();

        return $this;
    }

    /**
     * Returns the identifier as a RFC 9562/4122 case-insensitive string.
     *
     * @see     https://datatracker.ietf.org/doc/html/rfc9562/#section-4
     *
     * @example 09748193-048a-4bfb-b825-8528cf74fdc1 (len=36)
     * @return string
     */
    public function ulid(): string
    {
        return Str::ulid()->toRfc4122();
    }

    /**
     * Create a new cookie token.
     *
     * @return Cookie
     */
    public function getCookie(): Cookie
    {
        return new Cookie(
            name: Tokenizer::cookie(),
            value: $this->toJson(),
            expire: $this->getExpiresIn(),
            path: config('session.path'),
            domain: config('session.domain'),
            secure: config('session.secure'),
            httpOnly: true,
            raw: false,
            sameSite: config('session.same_site') ?? null
        );
    }

    /**
     * Add a cookie to the response.
     *
     * @param Cookie|null $cookie
     *
     * @return static
     */
    public function withCookie(Cookie $cookie = null): static
    {
        app('cookie')->queue($cookie ?? $this->getCookie());

        return $this;
    }

    /**
     * Determine the token type for the current driver.
     *
     * If the driver name matches the default driver defined in the
     * tokenizer configuration, the token type will be returned as
     * "Bearer". Otherwise, the driver name itself will be used.
     *
     * @return string
     */
    protected function getTokenType(): string
    {
        return with(
            value: $this->getName() === config('tokenizer.default.driver'),
            callback: fn(bool $default) => $default ? 'Bearer' : $this->getName()
        );
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    #[\Override]
    public function toArray(): array
    {
        if (is_callable($tokenable = Tokenizer::tokenable())) {
            return Closure::bind($tokenable, $this, static::class)($this);
        }

        return [
            "access_token"  => $this->accessToken,
            "token_type"    => $this->getTokenType(),
            "expires_in"    => $this->getExpiresIn(),
            "refresh_token" => $this->refreshToken,
        ];
    }

    /**
     * Convert the object to its JSON representation.
     *
     * @param int $options
     *
     * @return string
     */
    #[\Override]
    public function toJson($options = 0): string
    {
        return json_encode($this->toArray(), $options);
    }
}
