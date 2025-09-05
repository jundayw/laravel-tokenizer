<?php

namespace Jundayw\Tokenizer\Contracts;

use Illuminate\Database\Eloquent\Relations\MorphMany;

interface Tokenizable
{
    /**
     * Get the access tokens that belong to model.
     *
     * @return MorphMany
     */
    public function tokens(): MorphMany;

    /**
     * Get the current access token being used by the user.
     *
     * @return TokenModel|null
     */
    public function token(): ?TokenModel;

    /**
     * Determine if the current API token has a given scope.
     *
     * @param string $scope
     *
     * @return bool
     */
    public function tokenCan(string $scope): bool;

    /**
     * Create a new access token for the user.
     *
     * @param string $name
     * @param array  $scopes
     *
     * @return Tokenable
     */
    public function createToken(string $name, array $scopes = ['*']): Tokenable;

    /**
     * Set the current access token for the user.
     *
     * @param TokenModel $accessToken
     *
     * @return static
     */
    public function withAccessToken(TokenModel $accessToken): static;
}
