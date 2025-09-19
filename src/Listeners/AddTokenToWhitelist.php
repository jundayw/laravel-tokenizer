<?php

namespace Jundayw\Tokenizer\Listeners;

use Jundayw\Tokenizer\Contracts\Whitelist;
use Jundayw\Tokenizer\Events\AccessTokenEvent;

class AddTokenToWhitelist extends ShouldQueueable
{
    public function __construct(
        protected Whitelist $whitelist
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
        $this->whitelist->put(
            $event->getAuthorizable()->getAttribute('access_token'),
            $event->getAuthorizable(),
            $event->getAuthorizable()->getAttribute('access_token_expire_at'),
        );
        $this->whitelist->put(
            $event->getAuthorizable()->getAttribute('refresh_token'),
            $event->getAuthorizable(),
            $event->getAuthorizable()->getAttribute('refresh_token_expire_at'),
        );
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
