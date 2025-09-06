<?php

namespace Jundayw\Tokenizer;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Jundayw\Tokenizer\Contracts\Tokenable;
use Jundayw\Tokenizer\Contracts\Authorizable;

trait HasTokenizable
{
    /**
     * The current access token for the authentication user.
     *
     * @var Authorizable|null
     */
    protected ?Authorizable $accessToken = null;

    /**
     * Get the access tokens that belong to model.
     *
     * @return MorphMany
     */
    public function tokens(): MorphMany
    {
        return $this->morphMany(Tokenizer::authorizableModel(), 'tokenable');
    }

    /**
     * Get the current access token being used by the user.
     *
     * @return Authorizable|null
     */
    public function token(): ?Authorizable
    {
        return $this->accessToken;
    }

    /**
     * Determine if the current API token has a given scope.
     *
     * @param string $scope
     *
     * @return bool
     */
    public function tokenCan(string $scope): bool
    {
        return $this->token()?->can($scope) ?? false;
    }

    /**
     * Create a new personal access token for the user.
     *
     * @param string $name
     * @param array  $scopes
     *
     * @return Tokenable
     */
    public function createToken(string $name, array $scopes = ['*']): Tokenable
    {
        return call_user_func(app(Tokenable::class), $this, $name, $scopes);
    }

    /**
     * Set the current access token for the user.
     *
     * @param Authorizable $accessToken
     *
     * @return static
     */
    public function withAccessToken(Authorizable $accessToken): static
    {
        $this->accessToken = $accessToken;

        return $this;
    }
}
