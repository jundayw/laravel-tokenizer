<?php

namespace Jundayw\Tokenizer;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Str;
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
     * Get the access tokens that belong to model.
     *
     * @return MorphMany
     */
    public function tokens(): MorphMany
    {
        return $this->morphMany(Tokenizer::authorizableModel(), 'tokenable');
    }

    /**
     * Get the abilities that the user did have.
     *
     * @return array
     */
    public function getScopes(): array
    {
        return [];
    }

    /**
     * Return the identifier for the `sub` claim.
     *
     * @return string|int
     */
    public function getJWTIdentifier(): int|string
    {
        return $this->getKey();
    }

    /**
     * Return the issuer for the `iss` claim.
     *
     * @return string
     */
    public function getJWTIssuer(): string
    {
        return str_replace('\\', '.', get_class($this));
    }

    /**
     * Return the unique token ID for the `jti` claim.
     *
     * @return string
     */
    public function getJWTId(): string
    {
        return Str::uuid()->toString();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims(): array
    {
        return [];
    }
}
