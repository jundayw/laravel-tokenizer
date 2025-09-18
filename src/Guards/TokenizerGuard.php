<?php

namespace Jundayw\Tokenizer\Guards;

use Illuminate\Auth\Events\Authenticated;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\GuardHelpers;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Factory as Auth;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Http\Request;
use Illuminate\Support\Traits\Macroable;
use Jundayw\Tokenizer\Contracts\Auth\Grant;
use Jundayw\Tokenizer\Contracts\Auth\SupportsTokenAuth;
use Jundayw\Tokenizer\Contracts\Tokenable;
use Jundayw\Tokenizer\Contracts\Tokenizable;
use Jundayw\Tokenizer\Events\TokenAuthenticated;

class TokenizerGuard implements Guard, SupportsTokenAuth
{
    use GuardHelpers, TokenAuthHelpers, Helpers, Macroable;

    public function __construct(
        protected string $name,
        protected Repository $config,
        protected Auth $auth,
        public Grant $grant,
        protected Request $request,
        UserProvider $provider
    ) {
        $this->provider = $provider;
    }

    /**
     * Get the currently authenticated user.
     *
     * @return Authenticatable|Tokenizable|null
     */
    public function user(): Authenticatable|Tokenizable|null
    {
        // If we've already retrieved the user for the current request we can just
        // return it back immediately. We do not want to fetch the user data on
        // every call to this method because that would be tremendously slow.
        if ($this->hasUser()) {
            return $this->user;
        }

        if (empty($token = $this->getAccessTokenFromRequest($this->getRequest()))) {
            return null;
        }

        $token = $this->getTokenable()->setAccessToken($token)->getAccessToken();

        $authorizable = $this->getGrant()
            ->getAuthorizable()
            ->findAccessToken($token);

        if (is_null($authorizable) ||
            !$this->isValidAuthenticationToken($authorizable, $tokenizable = $authorizable->getRelation('tokenable')) ||
            !$this->supportsTokens($tokenizable)) {
            return null;
        }

        $tokenizable = tap($tokenizable, static fn(Tokenizable $tokenizable) => $tokenizable->withAccessToken($authorizable));

        $this->usingAuthorizable($authorizable)->fireAuthenticatedEvent($tokenizable);

        return $this->setUser($tokenizable)->user;
    }

    /**
     * Validate a user's credentials.
     *
     * @param array $credentials
     *
     * @return bool
     */
    public function validate(array $credentials = []): bool
    {
        return !is_null($this->provider->retrieveByCredentials($credentials));
    }

    /**
     * Build an access token and refresh token pair from given values.
     *
     * @param string $name
     * @param string $platform
     * @param array  $scopes
     *
     * @return Tokenable|null
     */
    public function createToken(string $name, string $platform = 'default', array $scopes = []): ?Tokenable
    {
        if (is_null($this->getUser())) {
            return null;
        }
        return $this->getGrant()->createToken($name, $platform, $scopes);
    }

    /**
     * Revoke (invalidate) the both access and refresh tokens by access token.
     *
     * @return bool
     */
    public function revokeToken(): bool
    {
        if (is_null($this->getUser())) {
            return false;
        }
        return $this->forgetUser()->getGrant()->revokeToken();
    }

    /**
     * Refresh the access and refresh tokens using the current refresh token.
     *
     * @return Tokenable|null
     */
    public function refreshToken(): ?Tokenable
    {
        if (empty($token = $this->getRefreshTokenFromRequest($this->getRequest()))) {
            return null;
        }

        $token = $this->getTokenable()->setRefreshToken($token)->getRefreshToken();

        $authorizable = $this->getGrant()
            ->getAuthorizable()
            ->findRefreshToken($token);

        if (is_null($authorizable) ||
            !$this->isValidAuthenticationToken($authorizable, $tokenizable = $authorizable->getRelation('tokenable')) ||
            !$this->supportsTokens($tokenizable)) {
            return null;
        }

        $this->usingTokenizable($tokenizable)->usingAuthorizable($authorizable)->fireAuthenticatedEvent($tokenizable);

        return $this->getGrant()->refreshToken();
    }

    /**
     * Fire the authenticated event.
     *
     * @param Authenticatable $user
     *
     * @return void
     */
    protected function fireAuthenticatedEvent(Authenticatable $user): void
    {
        event(new TokenAuthenticated($this->getAuthorizable(), $user, $this->getTokenable()));
        event(new Authenticated($this->name, $user));
    }

    /**
     * Fire the login event.
     *
     * @param Authenticatable $user
     *
     * @return void
     */
    protected function fireLoginEvent(Authenticatable $user): void
    {
        event(new Login($this->name, $user, false));
    }

    /**
     * Fire the logout event.
     *
     * @param Authenticatable $user
     *
     * @return void
     */
    protected function fireLogoutEvent(Authenticatable $user): void
    {
        event(new Logout($this->name, $user));
    }

    /**
     * Return the currently cached user.
     *
     * @return Authenticatable|Tokenizable|null
     */
    public function getUser(): Authenticatable|Tokenizable|null
    {
        if ($this->hasUser()) {
            return $this->user;
        }

        return null;
    }

    /**
     * Set the current user.
     *
     * @param Authenticatable $user
     *
     * @return $this
     */
    public function setUser(Authenticatable $user): static
    {
        $this->user = $user;

        return $this->usingTokenizable($this->user);
    }

    /**
     * Get the current request instance.
     *
     * @return Request
     */
    public function getRequest(): Request
    {
        return $this->request;
    }

    /**
     * Set the current request instance.
     *
     * @param Request $request
     *
     * @return static
     */
    public function setRequest(Request $request): static
    {
        $this->request = $request;

        return $this;
    }
}
