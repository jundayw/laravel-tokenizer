<?php

namespace Jundayw\Tokenizer;

trait HasApiTokens
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
