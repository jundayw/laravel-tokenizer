<?php

namespace Jundayw\Tokenizer\Tokens;

use Firebase\JWT\JWT;
use Jundayw\Tokenizer\Contracts\Authorizable;
use Jundayw\Tokenizer\Contracts\Tokenizable;

class JsonWebToken extends Token
{
    public function __construct(
        protected array $config,
    ) {
        //
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
    public function generateAccessToken(Authorizable $authorizable, Tokenizable $tokenizable): string
    {
        $claims  = $tokenizable->getJWTCustomClaims();
        $payload = $claims + [
                'jti' => $tokenizable->getJWTId(),
                'iss' => $tokenizable->getJWTIssuer(),
                'sub' => $tokenizable->getJWTIdentifier(),
                'aud' => $authorizable->getAttribute('scopes'),
                'exp' => $authorizable->getAttribute('access_token_expire_at')->getTimestamp(),
                'iat' => now()->getTimestamp(),
            ];
        return JWT::encode($payload, $this->getKeyByAlgorithm(), $this->config['algo']);
    }

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
    public function generateRefreshToken(Authorizable $authorizable, Tokenizable $tokenizable): string
    {
        $claims  = $tokenizable->getJWTCustomClaims();
        $payload = $claims + [
                'jti' => $tokenizable->getJWTId(),
                'iss' => $tokenizable->getJWTIssuer(),
                'sub' => $tokenizable->getJWTIdentifier(),
                'exp' => $authorizable->getAttribute('access_token_expire_at')->getTimestamp(),
                'nbf' => $authorizable->getAttribute('refresh_token_available_at')->getTimestamp(),
                'iat' => now()->getTimestamp(),
            ];
        return JWT::encode($payload, $this->getKeyByAlgorithm(), $this->config['algo']);
    }

    protected function getKeyByAlgorithm(bool $isPrivate = true): string
    {
        if (str_starts_with($this->config['algo'], 'H')) {
            return $this->config['secret_key'];
        }

        $key = $isPrivate ? $this->config['private_key'] : $this->config['public_key'];

        return is_file($key) ? file_get_contents($key) : $key;
    }
}
