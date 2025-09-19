<?php

namespace Jundayw\Tokenizer\Tokens;

use Illuminate\Config\Repository;
use Jundayw\Tokenizer\Contracts\Authorizable;
use Jundayw\Tokenizer\Contracts\Tokenizable;

class HashHmacToken extends Token
{
    public function __construct(string $name, array $config)
    {
        $this->name   = $name;
        $this->config = new Repository($config);
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
    protected function generateAccessToken(Authorizable $authorizable, Tokenizable $tokenizable): string
    {
        return hash_hmac($this->getConfig()->get('algo'), json_encode([
            'jti' => $this->ulid(),
            'iss' => $authorizable->getAttribute('tokenable_type'),
            'sub' => $authorizable->getAttribute('tokenable_id'),
            'exp' => $authorizable->getAttribute('access_token_expire_at'),
            'iat' => now()->getTimestamp(),
        ], JSON_UNESCAPED_UNICODE), $this->getConfig()->get('secret'));
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
    protected function generateRefreshToken(Authorizable $authorizable, Tokenizable $tokenizable): string
    {
        return hash_hmac($this->getConfig()->get('algo'), json_encode([
            'jti' => $this->ulid(),
            'iss' => $authorizable->getAttribute('tokenable_type'),
            'sub' => $authorizable->getAttribute('tokenable_id'),
            'exp' => $authorizable->getAttribute('access_token_expire_at'),
            'nbf' => $authorizable->getAttribute('refresh_token_available_at'),
            'iat' => now()->getTimestamp(),
        ], JSON_UNESCAPED_UNICODE), $this->getConfig()->get('secret'));
    }

    /**
     * Validate the token using a validator.
     *
     * @param string $token
     *
     * @return bool
     */
    public function validate(string $token): bool
    {
        return true;
    }
}
