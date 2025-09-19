<?php

namespace Jundayw\Tokenizer\Listeners;

use Jundayw\Tokenizer\Contracts\Whitelist;
use Jundayw\Tokenizer\Events\AccessTokenEvent;

class RemoveTokenFromWhitelist extends ShouldQueueable
{
    public function __construct(
        protected Whitelist $whitelist,
    ) {
        //
    }

    /**
     * @inheritdoc
     *
     * @param AccessTokenEvent $event
     *
     * @return void
     */
    public function handle($event): void
    {
        $this->whitelist->forget($event->getAuthorizable()->getAttribute('access_token'));
        $this->whitelist->forget($event->getAuthorizable()->getAttribute('refresh_token'));
    }

    /**
     * Determine whether the listener should be queued.
     *
     * @param $event
     *
     * @return bool
     */
    public function shouldQueue($event): bool
    {
        return $this->whitelist->isWhitelistEnabled();
    }
}
