<?php

namespace Jundayw\Tokenizer\Tokens;

use Jundayw\Tokenizer\Contracts\Authorizable;
use Jundayw\Tokenizer\Contracts\Tokenizable;

class HashHmacToken extends Token
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
    public function generateAccessToken(Authorizable $authorizable, Tokenizable $tokenizable): string
    {
        $data = json_encode([
            'iss' => $authorizable->getAttribute('tokenable_type'),
            'sub' => $authorizable->getAttribute('tokenable_id'),
            'exp' => $authorizable->getAttribute('access_token_expire_at'),
            'iat' => now(),
            'jti' => time(),
        ], JSON_UNESCAPED_UNICODE);
        return hash_hmac('sha256', $data, config('tokenizer.secret'));
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
        $data = json_encode([
            'iss' => $authorizable->getAttribute('tokenable_type'),
            'sub' => $authorizable->getAttribute('tokenable_id'),
            'exp' => $authorizable->getAttribute('access_token_expire_at'),
            'nbf' => $authorizable->getAttribute('refresh_token_available_at'),
            'iat' => now(),
            'jti' => time(),
        ], JSON_UNESCAPED_UNICODE);
        return hash_hmac('sha256', $data, config('tokenizer.secret'));
    }
}
