<?php

namespace Jundayw\Tokenizer\Guards;

use Illuminate\Contracts\Cache\Repository;
use Illuminate\Http\Request;
use Jundayw\Tokenizer\Contracts\Auth\Grant;
use Jundayw\Tokenizer\Contracts\Authorizable;
use Jundayw\Tokenizer\Contracts\Tokenable;
use Jundayw\Tokenizer\Contracts\Tokenizable;
use Jundayw\Tokenizer\HasTokenizable;
use Jundayw\Tokenizer\Tokenizer;
use Jundayw\Tokenizer\TokenManager;

trait Helpers
{
    /**
     * Get the current grant instance.
     *
     * @return Grant
     */
    public function getGrant(): Grant
    {
        return $this->grant;
    }

    /**
     * Set the grant instance to be used.
     *
     * @param Grant $grant
     *
     * @return static
     */
    public function usingGrant(Grant $grant): static
    {
        $this->grant = $grant;
        return $this;
    }

    /**
     * Get the tokenable entity instance.
     *
     * @return Tokenable
     */
    public function getTokenable(): Tokenable
    {
        return $this->getGrant()->getTokenable();
    }

    /**
     * Specify the tokenable entity to be used.
     *
     * @param string|null $name
     *
     * @return static
     */
    public function usingTokenable(?string $name = null): static
    {
        $this->getGrant()->setTokenable($this->getTokenManager()->driver($name));
        return $this;
    }

    /**
     * Get the tokenizable entity instance.
     *
     * @return Tokenizable
     */
    public function getTokenizable(): Tokenizable
    {
        return $this->getGrant()->getTokenizable();
    }

    /**
     * Specify the tokenizable entity to be used.
     *
     * @param Tokenizable $tokenizable
     *
     * @return static
     */
    public function usingTokenizable(Tokenizable $tokenizable): static
    {
        $this->getGrant()->setTokenizable($tokenizable);
        return $this;
    }

    /**
     * Get the Authorizable instance associated with this object.
     *
     * @return Authorizable
     */
    public function getAuthorizable(): Authorizable
    {
        return $this->getGrant()->getAuthorizable();
    }

    /**
     * Set the authorizable instance to be used.
     *
     * @param Authorizable $authorizable
     *
     * @return $this
     */
    public function usingAuthorizable(Authorizable $authorizable): static
    {
        $this->getGrant()->setAuthorizable($authorizable);
        return $this;
    }

    /**
     * Get the TokenManager instance.
     *
     * @return TokenManager
     */
    public function getTokenManager(): TokenManager
    {
        return $this->getGrant()->getTokenManager();
    }

    /**
     * Get the blacklist repository.
     *
     * @return Repository
     */
    public function getBlacklist(): Repository
    {
        return $this->getGrant()->getBlacklist();
    }

    /**
     * Get the whitelist repository.
     *
     * @return Repository
     */
    public function getWhitelist(): Repository
    {
        return $this->getGrant()->getWhitelist();
    }

    /**
     * Get the access token for the current request.
     *
     * @param Request $request
     *
     * @return string|null
     */
    public function getAccessTokenFromRequest(Request $request): ?string
    {
        if (is_callable($tokenRetrievalCallback = Tokenizer::tokenRetrievalCallback())) {
            $token = call_user_func($tokenRetrievalCallback, $request);
        } else {
            $token = $request->bearerToken();

            if (empty($token)) {
                $token = $request->getPassword();
            }

            if (empty($token)) {
                $token = $this->getAccessTokenViaCookie($request);
            }
        }

        return $this->isValidToken($token) ? $token : null;
    }

    /**
     * Get the refresh token for the current request.
     *
     * @param Request $request
     *
     * @return string|null
     */
    public function getRefreshTokenFromRequest(Request $request): ?string
    {
        if (is_callable($tokenRetrievalCallback = Tokenizer::tokenRetrievalCallback())) {
            $token = call_user_func($tokenRetrievalCallback, $request);
        } else {
            $token = $request->bearerToken();

            if (empty($token)) {
                $token = $request->getPassword();
            }

            if (empty($token)) {
                $token = $this->getRefreshTokenViaCookie($request);
            }
        }

        return $this->isValidToken($token) ? $token : null;
    }

    /**
     * Determine if the token is in the correct format.
     *
     * @param string|null $token
     *
     * @return bool
     */
    protected function isValidToken(string $token = null): bool
    {
        if (is_null($token)) {
            return false;
        }

        if (is_callable($tokenVerificationCallback = Tokenizer::tokenVerificationCallback())) {
            return call_user_func($tokenVerificationCallback, $token);
        }

        return true;
    }

    /**
     * Get the token cookie via the incoming request.
     *
     * @param Request $request
     *
     * @return array|string|null
     */
    public function getTokenViaCookie(Request $request): array|string|null
    {
        $cookie = $request->cookie(Tokenizer::cookie());

        if (is_callable($tokenViaCookieCallback = Tokenizer::tokenViaCookieCallback())) {
            return call_user_func($tokenViaCookieCallback, $cookie);
        }

        return $cookie;
    }

    /**
     * Get the access token cookie via the incoming request.
     *
     * @param Request $request
     *
     * @return string|null
     */
    public function getAccessTokenViaCookie(Request $request): ?string
    {
        $cookie = $this->getTokenViaCookie($request);

        if (is_callable($accessTokenViaCookieCallback = Tokenizer::accessTokenViaCookieCallback())) {
            return call_user_func($accessTokenViaCookieCallback, $cookie);
        }

        return $cookie ?? null;
    }

    /**
     * Get the refresh token cookie via the incoming request.
     *
     * @param Request $request
     *
     * @return string|null
     */
    public function getRefreshTokenViaCookie(Request $request): ?string
    {
        $cookie = $this->getTokenViaCookie($request);

        if (is_callable($refreshTokenViaCookieCallback = Tokenizer::refreshTokenViaCookieCallback())) {
            return call_user_func($refreshTokenViaCookieCallback, $cookie);
        }

        return $cookie ?? null;
    }

    /**
     * Determine if the provided token is valid.
     *
     * @param Authorizable|null $authorizable
     * @param Tokenizable|null  $tokenizable
     *
     * @return bool
     */
    protected function isValidAuthenticationToken(Authorizable $authorizable = null, Tokenizable $tokenizable = null): bool
    {
        if (is_null($authorizable) || is_null($tokenizable)) {
            return false;
        }

        $isValid = $this->hasValidProvider($tokenizable);

        if (is_callable($tokenAuthenticationCallback = Tokenizer::tokenAuthenticationCallback())) {
            $isValid = call_user_func($tokenAuthenticationCallback, $authorizable, $tokenizable, $isValid);
        }

        return $isValid;
    }

    /**
     * Determine if the tokenable model matches the provider's model type.
     *
     * @param Tokenizable $tokenable
     *
     * @return bool
     */
    protected function hasValidProvider(Tokenizable $tokenable): bool
    {
        if (is_null($provider = $this->config->get('provider') ?? null)) {
            return true;
        }

        $model = config("auth.providers.{$provider}.model");

        return $tokenable instanceof $model;
    }

    /**
     * Determine if the tokenable model supports API tokens.
     *
     * @param Tokenizable $tokenable
     *
     * @return bool
     */
    protected function supportsTokens(Tokenizable $tokenable): bool
    {
        return in_array(HasTokenizable::class, class_uses_recursive(
            get_class($tokenable)
        ));
    }
}
