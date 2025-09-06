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
