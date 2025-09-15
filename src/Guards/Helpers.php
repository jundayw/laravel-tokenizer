<?php

namespace Jundayw\Tokenizer\Guards;

use Illuminate\Contracts\Cache\Repository;
use Jundayw\Tokenizer\Contracts\Auth\Grant;
use Jundayw\Tokenizer\Contracts\Authorizable;
use Jundayw\Tokenizer\Contracts\Tokenable;
use Jundayw\Tokenizer\Contracts\Tokenizable;
use Jundayw\Tokenizer\HasTokenizable;
use Jundayw\Tokenizer\Tokenizer;
use Jundayw\Tokenizer\TokenManager;

trait Helpers
{
    protected ?Tokenable $tokenable = null;

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
        if (is_null($this->tokenable)) {
            $this->tokenable = $this->getTokenManager()->driver();
        }
        return $this->tokenable;
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
        $this->tokenable = $this->getTokenManager()->driver($name);
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
     * Determine if the provided access token is valid.
     *
     * @param Authorizable|null $authorizable
     * @param Tokenizable|null  $tokenizable
     *
     * @return bool
     */
    protected function isValidAccessToken(Authorizable $authorizable = null, Tokenizable $tokenizable = null): bool
    {
        if (is_null($authorizable) || is_null($tokenizable)) {
            return false;
        }

        $isValid = $this->hasValidProvider($tokenizable);

        if (is_callable($accessTokenAuthenticationCallback = Tokenizer::accessTokenAuthenticationCallback())) {
            $isValid = call_user_func($accessTokenAuthenticationCallback, $authorizable, $tokenizable, $isValid);
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
