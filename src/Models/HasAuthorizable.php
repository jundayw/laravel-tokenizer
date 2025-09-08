<?php

namespace Jundayw\Tokenizer\Models;

use Illuminate\Database\Eloquent\Relations\MorphTo;
use Jundayw\Tokenizer\Contracts\Authorizable;

trait HasAuthorizable
{
    /**
     * Get the current connection name for the model.
     *
     * @return string|null
     */
    public function getConnectionName(): ?string
    {
        return $this->connection ?? config('tokenizer.connection');
    }

    /**
     * Get the table associated with the model.
     *
     * @return string
     */
    public function getTable(): string
    {
        return $this->table ?? config('tokenizer.table', parent::getTable());
    }

    /**
     * Get the tokenable model that the access token belongs to.
     *
     * @return MorphTo
     */
    public function tokenable(): MorphTo
    {
        return $this->morphTo('tokenable');
    }

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
    public function findToken(string $token): Authorizable
    {
        return $this->newQuery()
            ->with('tokenable')
            ->where('access_token', $token)
            ->where('access_token_expire_at', '>=', now())
            ->first();
    }

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
    public function updateToken(array $credentials = []): Authorizable
    {
        return tap($this, static fn($model) => $model->update($credentials))->refresh();
    }

    /**
     * Determine if the token has a given scope.
     *
     * @param string $scope
     *
     * @return bool
     */
    public function can(string $scope): bool
    {
        if (in_array('*', $this->getAttribute('scopes'))) {
            return true;
        }

        $scopes = [$scope];

        foreach ($scopes as $scope) {
            if (array_key_exists($scope, array_flip($this->getAttribute('scopes')))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine if the token is missing a given scope.
     *
     * @param string $scope
     *
     * @return bool
     */
    public function cant(string $scope): bool
    {
        return !$this->can($scope);
    }
}
