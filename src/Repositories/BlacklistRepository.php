<?php

namespace Jundayw\Tokenizer\Repositories;

use Illuminate\Cache\Repository;
use Jundayw\Tokenizer\Contracts\Blacklist;

class BlacklistRepository extends Repository implements Blacklist
{
    /**
     * The blacklist flag.
     *
     * @var bool
     */
    protected bool $blacklistEnabled = false;

    /**
     * Check if blacklist functionality is enabled.
     *
     * @return bool True if blacklist is enabled, false otherwise.
     */
    public function isBlacklistEnabled(): bool
    {
        return $this->blacklistEnabled;
    }

    /**
     * Enable or disable blacklist functionality.
     *
     * @param bool $blacklistEnabled
     *
     * @return static Returns the current instance for method chaining.
     */
    public function setBlacklistEnabled(bool $blacklistEnabled): static
    {
        $this->blacklistEnabled = $blacklistEnabled;

        return $this;
    }
}
