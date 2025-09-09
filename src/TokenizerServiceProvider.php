<?php

namespace Jundayw\Tokenizer;

use Illuminate\Contracts\Auth\Factory;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use Jundayw\Tokenizer\Contracts\Authorizable;
use Jundayw\Tokenizer\Grants\TokenizerGrant;
use Jundayw\Tokenizer\Guards\TokenizerGuard;
use Jundayw\Tokenizer\Middleware\CheckForAnyScope;
use Jundayw\Tokenizer\Middleware\CheckScopes;

class TokenizerServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        config([
            'auth.guards.tokenizer' => array_merge([
                'driver'   => 'tokenizer',
                'provider' => null,
            ], config('auth.guards.tokenizer', [])),
        ]);
        if (!app()->configurationIsCached()) {
            $this->mergeConfigFrom(__DIR__ . '/../config/tokenizer.php', 'tokenizer');
        }

        $this->app->bind(Authorizable::class, static function ($app) {
            return $app->make(Tokenizer::authorizableModel());
        });
        $this->app->singleton(TokenManager::class, static function ($app) {
            return new TokenManager($app[Authorizable::class]);
        });

        $this->addMiddlewareAlias('scopes', CheckScopes::class);
        $this->addMiddlewareAlias('scope', CheckForAnyScope::class);

        Tokenizer::loadKeysFrom(config('tokenizer.key_path'));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->registerMigrations();
            $this->registerPublishing();
            $this->registerCommands();
        }

        $this->registerGuard();
    }

    /**
     * Register the middleware.
     *
     * @param string $name
     * @param string $class
     *
     * @return mixed
     */
    protected function addMiddlewareAlias(string $name, string $class): mixed
    {
        $router = $this->app['router'];

        if (method_exists($router, 'aliasMiddleware')) {
            return $router->aliasMiddleware($name, $class);
        }

        return $router->middleware($name, $class);
    }

    /**
     * Register the migration file.
     *
     * @return void
     */
    protected function registerMigrations(): void
    {
        if (Tokenizer::shouldRunMigrations()) {
            $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        }
    }

    /**
     * Register the package's publishable resources.
     *
     * @return void
     */
    protected function registerPublishing(): void
    {
        $this->publishes([
            __DIR__ . '/../database/migrations' => database_path('migrations'),
        ], 'tokenizer-migrations');

        $this->publishes([
            __DIR__ . '/../config/tokenizer.php' => config_path('tokenizer.php'),
        ], 'tokenizer-config');
    }

    /**
     * Register the Authing Artisan commands.
     *
     * @return void
     */
    protected function registerCommands(): void
    {
        $this->commands([
            Console\KeysCommand::class,
            Console\PurgeCommand::class,
        ]);
    }

    /**
     * Register the token guard.
     *
     * @return void
     */
    protected function registerGuard(): void
    {
        Auth::resolved(function (Factory $auth) {
            $auth->extend('tokenizer', function ($app, string $name, array $config) use ($auth) {
                return tap($this->makeGuard($auth, $config), function (Guard $guard) {
                    app()->refresh('request', $guard, 'setRequest');
                });
            });
        });
    }

    /**
     * Make an instance of the token guard.
     *
     * @param Factory $auth
     * @param array   $config
     *
     * @return TokenizerGuard
     */
    protected function makeGuard(Factory $auth, array $config): TokenizerGuard
    {
        return new TokenizerGuard(
            new TokenizerGrant(
                $auth,
                $config,
                $this->app['request'],
                $this->app[Authorizable::class]
            ),
            $this->app['request'],
            $auth->createUserProvider($config['provider'] ?? null),
        );
    }
}
