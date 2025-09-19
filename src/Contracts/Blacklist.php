<?php

namespace Jundayw\Tokenizer\Contracts;

use Illuminate\Contracts\Cache\Repository;

interface Blacklist extends Repository
{
    /**
     * Check if blacklist functionality is enabled.
     *
     * @return bool True if blacklist is enabled, false otherwise.
     */
    public function isBlacklistEnabled(): bool;

    /**
     * Enable or disable blacklist functionality.
     *
     * @param bool $blacklistEnabled
     *
     * @return static Returns the current instance for method chaining.
     */
    public function setBlacklistEnabled(bool $blacklistEnabled): static;
}
