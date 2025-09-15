<?php

namespace Jundayw\Tokenizer\Contracts;

use Illuminate\Database\Eloquent\Relations\MorphMany;

interface Tokenizable
{
    /**
     * Set the current access token for the user.
     *
     * @param Authorizable $accessToken
     *
     * @return static
     */
    public function withAccessToken(Authorizable $accessToken): static;

    /**
     * Get the current access token being used by the user.
     *
     * @return Authorizable|null
     */
    public function token(): ?Authorizable;

    /**
     * Determine if the current API token has a given scope.
     *
     * @param string $scope
     *
     * @return bool
     */
    public function tokenCan(string $scope): bool;

    /**
     * Get the access tokens that belong to model.
     *
     * @return MorphMany
     */
    public function tokens(): MorphMany;

    /**
     * Get the abilities that the user did have.
     *
     * @return array
     */
    public function getScopes(): array;

    /**
     * Return the identifier for the `sub` claim.
     *
     * @return string|int
     */
    public function getJWTIdentifier(): int|string;

    /**
     * Return the issuer for the `iss` claim.
     *
     * @return string
     */
    public function getJWTIssuer(): string;

    /**
     * Return the unique token ID for the `jti` claim.
     *
     * @return string
     */
    public function getJWTId(): string;

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims(): array;
}
