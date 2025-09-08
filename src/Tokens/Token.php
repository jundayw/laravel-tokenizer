<?php

namespace Jundayw\Tokenizer\Tokens;

use Illuminate\Http\Request;
use Jundayw\Tokenizer\Contracts\Authorizable;
use Jundayw\Tokenizer\Contracts\Tokenable;
use Jundayw\Tokenizer\Contracts\Tokenizable;
use Symfony\Component\HttpFoundation\Response;

abstract class Token implements Tokenable
{
    protected ?string $accessToken  = null;
    protected ?string $refreshToken = null;
    protected int     $expiresIn    = 0;

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
    abstract public function generateAccessToken(Authorizable $authorizable, Tokenizable $tokenizable): string;

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
    abstract public function generateRefreshToken(Authorizable $authorizable, Tokenizable $tokenizable): string;

    /**
     * Get the current access token value.
     *
     * @return string|null
     */
    public function getAccessToken(): ?string
    {
        return $this->accessToken;
    }

    /**
     * Get the current refresh token value.
     *
     * @return string|null
     */
    public function getRefreshToken(): ?string
    {
        return $this->refreshToken;
    }

    /**
     * Get the number of seconds until the access token expires.
     *
     * @return int
     */
    public function getExpiresIn(): int
    {
        return $this->expiresIn;
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
        $this->accessToken  = $this->generateAccessToken($authorizable, $tokenizable);
        $this->refreshToken = $this->generateRefreshToken($authorizable, $tokenizable);
        $this->expiresIn    = $authorizable->getAttribute('access_token_expire_at')->diffInSeconds(now());

        return tap($this, static function (Tokenable $tokenable) use ($authorizable) {
            $authorizable->fill([
                'access_token'  => $tokenable->getAccessToken(),
                'refresh_token' => $tokenable->getRefreshToken(),
            ])->save();
        });
    }

    /**
     * Refresh the access and refresh tokens using the current refresh token.
     *
     * @param string|null $refreshToken
     *
     * @return Tokenable
     */
    public function refreshTokens(string $refreshToken = null): Tokenable
    {
        return $this;
    }

    /**
     * Revoke (invalidate) the both access and refresh tokens by access token.
     *
     * @param string|null $accessToken
     *
     * @return bool
     */
    public function revokeToken(string $accessToken = null): bool
    {
        return true;
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            "access_token"  => $this->getAccessToken(),
            "token_type"    => class_basename($this),
            "expires_in"    => $this->getExpiresIn(),
            "refresh_token" => $this->getRefreshToken(),
        ];
    }

    /**
     * Create an HTTP response that represents the object.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function toResponse($request): Response
    {
        return response();
    }
}
