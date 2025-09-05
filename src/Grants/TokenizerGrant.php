<?php

namespace Jundayw\Tokenizer\Grants;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Factory;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Jundayw\Tokenizer\Contracts\Auth\Grant;
use Jundayw\Tokenizer\Contracts\Tokenable;
use Jundayw\Tokenizer\Contracts\TokenModel;
use Jundayw\Tokenizer\HasTokenization;
use Jundayw\Tokenizer\Tokenizer;

class TokenizerGrant implements Grant
{
    public function __construct(
        protected Factory $auth,
        protected array $providers,
        protected Request $request,
        protected Model|TokenModel $tokenModel,
    ) {
        //
    }

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

        return $provider->retrieveById(1);
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
     * Determine if the tokenable model supports API tokens.
     *
     * @param mixed|null $tokenable
     *
     * @return bool
     */
    protected function supportsTokens(mixed $tokenable = null): bool
    {
        return in_array(HasTokenization::class, class_uses_recursive(
            get_class($tokenable)
        ));
    }

    /**
     * Determine if the provided access token is valid.
     *
     * @param mixed $accessToken
     *
     * @return bool
     */
    protected function isValidAccessToken(TokenModel $accessToken = null): bool
    {
        if (is_null($accessToken) || is_null($accessToken->tokenable)) {
            return false;
        }

        $isValid = $this->hasValidProvider($accessToken->tokenable);

        if (is_callable($accessTokenAuthenticationCallback = Tokenizer::accessTokenAuthenticationCallback())) {
            $isValid = call_user_func($accessTokenAuthenticationCallback, $accessToken, $isValid);
        }

        return $isValid;
    }

    /**
     * Determine if the tokenable model matches the provider's model type.
     *
     * @param Tokenable $tokenable
     *
     * @return bool
     */
    protected function hasValidProvider(Tokenable $tokenable): bool
    {
        if (is_null($provider = $this->providers['provider'] ?? null)) {
            return true;
        }

        $model = config("auth.providers.{$provider}.model");

        return $tokenable instanceof $model;
    }

    /**
     * Get the user provider used by the guard.
     *
     * @return TokenModel
     */
    public function getProvider(): TokenModel
    {
        return $this->tokenModel;
    }

    /**
     * Set the user provider used by the guard.
     *
     * @param TokenModel $tokenModel
     *
     * @return static
     */
    public function setProvider(TokenModel $tokenModel): static
    {
        $this->tokenModel = $tokenModel;

        return $this;
    }
}
