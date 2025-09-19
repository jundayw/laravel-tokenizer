<?php

namespace Jundayw\Tokenizer\Contracts;

use Illuminate\Contracts\Cache\Repository;

interface Whitelist extends Repository
{
    /**
     * Check if whitelist functionality is enabled.
     *
     * @return bool True if whitelist is enabled, false otherwise.
     */
    public function isWhitelistEnabled(): bool;

    /**
     * Enable or disable whitelist functionality.
     *
     * @param bool $whitelistEnabled
     *
     * @return static Returns the current instance for method chaining.
     */
    public function setWhitelistEnabled(bool $whitelistEnabled): static;
}
