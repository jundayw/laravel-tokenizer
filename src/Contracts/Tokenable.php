<?php

namespace Jundayw\Tokenizer\Contracts;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Responsable;

interface Tokenable extends Arrayable, Responsable
{
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
    public function generateAccessToken(Authorizable $authorizable, Tokenizable $tokenizable): string;

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
    public function generateRefreshToken(Authorizable $authorizable, Tokenizable $tokenizable): string;

    /**
     * Get the current access token value.
     *
     * @return string|null
     */
    public function getAccessToken(): ?string;

    /**
     * Get the current refresh token value.
     *
     * @return string|null
     */
    public function getRefreshToken(): ?string;

    /**
     * Get the number of seconds until the access token expires.
     *
     * @return int
     */
    public function getExpiresIn(): int;

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
     * Refresh the access and refresh tokens using the current refresh token.
     *
     * @param string|null $refreshToken
     *
     * @return Tokenable
     */
    public function refreshTokens(string $refreshToken = null): Tokenable;

    /**
     * Revoke (invalidate) the both access and refresh tokens by access token.
     *
     * @param string|null $accessToken
     *
     * @return bool
     */
    public function revokeToken(string $accessToken = null): bool;
}
