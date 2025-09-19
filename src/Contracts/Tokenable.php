<?php

namespace Jundayw\Tokenizer\Contracts;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Symfony\Component\HttpFoundation\Cookie;

interface Tokenable extends Arrayable, Jsonable
{
    /**
     * Get the name of the instance.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Set the name of the instance.
     *
     * @param string $name
     *
     * @return static
     */
    public function setName(string $name): static;

    /**
     * Get the configuration repository instance.
     *
     * @return Repository
     */
    public function getConfig(): Repository;

    /**
     * Set the configuration repository instance.
     *
     * @param Repository $config
     *
     * @return static
     */
    public function setConfig(Repository $config): static;

    /**
     * Validate the token using a validator.
     *
     * @param string $token
     *
     * @return bool
     */
    public function validate(string $token): bool;

    /**
     * Get the current access token value.
     *
     * @return string|null
     */
    public function getAccessToken(): ?string;

    /**
     * Set the raw access token value before hashing.
     *
     * @param string $token
     *
     * @return static
     */
    public function setAccessToken(string $token): static;

    /**
     * Get the current refresh token value.
     *
     * @return string|null
     */
    public function getRefreshToken(): ?string;

    /**
     * Set the raw refresh token value before hashing.
     *
     * @param string $token
     *
     * @return static
     */
    public function setRefreshToken(string $token): static;

    /**
     * Get the number of seconds until the access token expires.
     *
     * @return string
     */
    public function getExpiresIn(): string;

    /**
     * Build an access token and refresh token pair from given values.
     *
     * @param Authorizable $authorizable
     * @param Tokenizable  $tokenizable
     *
     * @return Tokenable
     */
    public function buildTokens(Authorizable $authorizable, Tokenizable $tokenizable): Tokenable;

    /**
     * Returns the identifier as a RFC 9562/4122 case-insensitive string.
     *
     * @see     https://datatracker.ietf.org/doc/html/rfc9562/#section-4
     *
     * @example 09748193-048a-4bfb-b825-8528cf74fdc1 (len=36)
     * @return string
     */
    public function ulid(): string;

    /**
     * Create a new cookie token.
     *
     * @return Cookie
     */
    public function getCookie(): Cookie;

    /**
     * Add a cookie to the response.
     *
     * @param Cookie|null $cookie
     *
     * @return static
     */
    public function withCookie(Cookie $cookie = null): static;
}
