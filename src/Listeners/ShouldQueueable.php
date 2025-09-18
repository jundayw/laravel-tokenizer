<?php

namespace Jundayw\Tokenizer\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;

abstract class ShouldQueueable implements ShouldQueue
{
    use SerializesModels;

    /**
     * Get the name of the listener's queue connection.
     *
     * @return string
     */
    public function viaConnection(): string
    {
        return config('tokenizer.queue.connection');
    }

    /**
     * Get the name of the listener's queue.
     *
     * @return string
     */
    public function viaQueue(): string
    {
        return config('tokenizer.queue.queue');
    }

    /**
     * Handle the event.
     *
     * @param $event
     *
     * @return void
     */
    abstract public function handle($event): void;

    /**
     * Determine whether the listener should be queued.
     *
     * @param $event
     *
     * @return bool
     */
    public function shouldQueue($event): bool
    {
        return true;
    }
}
