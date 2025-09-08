<?php

namespace Jundayw\Tokenizer\Contracts;

use Illuminate\Database\Eloquent\Relations\MorphTo;

interface Authorizable
{
    /**
     * Get the tokenable model that the access token belongs to.
     *
     * @return MorphTo
     */
    public function tokenable(): MorphTo;

    /**
     * Find a valid token by its plain-text value.
     *
     *  Loads the related `tokenable` model and ensures the token
     *  has not yet expired. Returns null if no valid token exists.
     *
     * @param string $token
     *
     * @return Authorizable
     */
    public function findToken(string $token): Authorizable;

    /**
     *  Update the token with the given attributes and return the fresh model.
     *
     *  Persists the changes and reloads the model from the database
     *  to ensure all attributes are up-to-date.
     *
     * @param array<string, mixed> $credentials
     *
     * @return Authorizable
     */
    public function updateToken(array $credentials = []): Authorizable;

    /**
     * Determine if the token has a given scope.
     *
     * @param string $scope
     *
     * @return bool
     */
    public function can(string $scope): bool;

    /**
     * Determine if the token is missing a given scope.
     *
     * @param string $scope
     *
     * @return bool
     */
    public function cant(string $scope): bool;
}
