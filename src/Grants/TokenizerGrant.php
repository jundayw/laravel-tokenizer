<?php

namespace Jundayw\Tokenizer\Grants;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Factory;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Http\Request;
use Jundayw\Tokenizer\Contracts\Auth\Grant;
use Jundayw\Tokenizer\Contracts\Authorizable;
use Jundayw\Tokenizer\Contracts\Tokenizable;
use Jundayw\Tokenizer\Events\TokenAuthenticated;
use Jundayw\Tokenizer\HasTokenizable;
use Jundayw\Tokenizer\Tokenizer;

class TokenizerGrant implements Grant
{
    public function __construct(
        protected Factory $auth,
        protected array $providers,
        protected Request $request,
        protected Authorizable $provider,
    ) {
        //
    }

    /**
     * Retrieve the authenticated user for the incoming request.
     *
     * @param Request      $request
     * @param UserProvider $provider
     *
     * @return Authenticatable|null
     */
    public function __invoke(Request $request, UserProvider $provider): ?Authenticatable
    {
        foreach (config('tokenizer.guards', []) as $guard) {
            if (config("auth.guards.{$guard}.driver") === 'tokenizer') {
                continue;
            }
            if ($tokenable = $this->auth->guard($guard)->user()) {
                return $tokenable;
            }
        }

        if (is_null($token = $this->getTokenForRequest($request))) {
            return null;
        }

        $accessToken = $this->provider->findAccessToken($token);

        if (!$this->isValidAccessToken($accessToken) ||
            !$accessToken->getRelation('tokenable') ||
            !$this->supportsTokens($tokenable = $accessToken->getRelation('tokenable'))) {
            return null;
        }

        $tokenable = tap($tokenable, static fn(Tokenizable $tokenable) => $tokenable->withAccessToken($accessToken));

        if (method_exists($accessToken->getConnection(), 'hasModifiedRecords') &&
            method_exists($accessToken->getConnection(), 'setRecordModificationState')) {
            tap($accessToken->getConnection()->hasModifiedRecords(), function ($hasModifiedRecords) use ($accessToken) {
                $accessToken->forceFill(['last_used_at' => now()])->save();
                $accessToken->getConnection()->setRecordModificationState($hasModifiedRecords);
            });
        } else {
            $accessToken->forceFill(['last_used_at' => now()])->save();
        }

        event(new TokenAuthenticated($accessToken, $tokenable));

        return $tokenable;
    }

    /**
     * Get the token for the current request.
     *
     * @param Request $request
     *
     * @return string|null
     */
    protected function getTokenForRequest(Request $request): ?string
    {
        if (is_callable($accessTokenRetrievalCallback = Tokenizer::accessTokenRetrievalCallback())) {
            return call_user_func($accessTokenRetrievalCallback, $request);
        }

        return $request->bearerToken() ?: null;
    }

    /**
     * Determine if the provided access token is valid.
     *
     * @param Authorizable|null $accessToken
     *
     * @return bool
     */
    protected function isValidAccessToken(Authorizable $accessToken = null): bool
    {
        if (is_null($accessToken) || is_null($tokenable = $accessToken->getRelation('tokenable'))) {
            return false;
        }

        $isValid = $this->hasValidProvider($tokenable);

        if (is_callable($accessTokenAuthenticationCallback = Tokenizer::accessTokenAuthenticationCallback())) {
            $isValid = call_user_func($accessTokenAuthenticationCallback, $accessToken, $isValid);
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
        if (is_null($provider = $this->providers['provider'] ?? null)) {
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

    /**
     * Get the authorizable model used by the token.
     *
     * @return Authorizable
     */
    public function getProvider(): Authorizable
    {
        return $this->provider;
    }

    /**
     * Set the authorizable model used by the token.
     *
     * @param Authorizable $provider
     *
     * @return static
     */
    public function setProvider(Authorizable $provider): static
    {
        $this->provider = $provider;

        return $this;
    }
}
