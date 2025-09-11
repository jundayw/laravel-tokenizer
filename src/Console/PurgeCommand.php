<?php

namespace Jundayw\Tokenizer\Console;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Jundayw\Tokenizer\Contracts\Authorizable;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'tokenizer:purge')]
class PurgeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tokenizer:purge
                            {--r|revoked : Only purge revoked tokens and authentication codes}
                            {--e|expired : Only purge expired tokens and authentication codes}
                            {--t|hours=168 : The number of hours to retain expired tokens}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Purge revoked and / or expired tokens and authentication codes';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(): void
    {
        $revoked = $this->option('revoked') || !$this->option('expired');

        $expired = $this->option('expired') || !$this->option('revoked')
            ? now()->subHours($this->option('hours'))
            : false;

        tap(app(Authorizable::class), fn(Model $authorization) => $authorization
            ->newQuery()
            ->when($revoked, fn(Builder $query) => $query->withTrashed()->whereNotNull('deleted_at'))
            ->when($expired, fn(Builder $query) => $query->orWhere('refresh_token_expire_at', '<', $expired))
            ->cursor()
            ->each(function (Authorizable $authorizable) {
                $authorizable->forceDelete();
            })
        );

        $this->components->info(sprintf('Purged %s.', implode(' and ', array_filter([
            $revoked ? 'revoked items' : null,
            $expired ? "items expired for more than {$expired->longAbsoluteDiffForHumans()}" : null,
        ]))));
    }
}
