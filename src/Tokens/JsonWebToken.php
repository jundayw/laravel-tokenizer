<?php

namespace Jundayw\Tokenizer\Tokens;

use DomainException;
use Firebase\JWT\BeforeValidException;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\SignatureInvalidException;
use Illuminate\Config\Repository;
use InvalidArgumentException;
use Jundayw\Tokenizer\Contracts\Authorizable;
use Jundayw\Tokenizer\Contracts\Tokenizable;
use Jundayw\Tokenizer\Tokenizer;
use UnexpectedValueException;

class JsonWebToken extends Token
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

        return JWT::encode($payload, $this->getKeyByAlgorithm(), $this->getConfig()->get('algo'));
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

        return JWT::encode($payload, $this->getKeyByAlgorithm(), $this->getConfig()->get('algo'));
    }

    protected function getKeyByAlgorithm(bool $isPrivate = true): string
    {
        if (str_starts_with($this->getConfig()->get('algo'), 'H')) {
            return $this->getConfig()->get('secret_key');
        }

        $file = $isPrivate ? $this->getConfig()->get('private_key') : $this->getConfig()->get('public_key');
        $key  = Tokenizer::keyPath($file);

        return is_file($key) ? file_get_contents($key) : $key;
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
        try {
            JWT::decode($token, new Key(
                $this->getKeyByAlgorithm(false),
                $this->getConfig()->get('algo')
            ));
            return true;
        } catch (InvalidArgumentException $e) {
            // provided key/key-array is empty or malformed.
        } catch (DomainException $e) {
            // provided algorithm is unsupported OR
            // provided key is invalid OR
            // unknown error thrown in openSSL or libsodium OR
            // libsodium is required but not available.
        } catch (SignatureInvalidException $e) {
            // provided JWT signature verification failed.
        } catch (BeforeValidException $e) {
            // provided JWT is trying to be used before "nbf" claim OR
            // provided JWT is trying to be used before "iat" claim.
        } catch (ExpiredException $e) {
            // provided JWT is trying to be used after "exp" claim.
        } catch (UnexpectedValueException $e) {
            // provided JWT is malformed OR
            // provided JWT is missing an algorithm / using an unsupported algorithm OR
            // provided JWT algorithm does not match provided key OR
            // provided key ID in key/key-array is empty or invalid.
        }

        return false;
    }
}
