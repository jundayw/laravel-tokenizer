<?php

namespace Jundayw\Tokenizer;

use Illuminate\Config\Repository;
use Illuminate\Contracts\Auth\Factory;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Jundayw\Tokenizer\Contracts\Auth\Grant;
use Jundayw\Tokenizer\Contracts\Authorizable;
use Jundayw\Tokenizer\Contracts\Blacklist;
use Jundayw\Tokenizer\Contracts\Whitelist;
use Jundayw\Tokenizer\Events\AccessTokenCreated;
use Jundayw\Tokenizer\Events\AccessTokenRevoked;
use Jundayw\Tokenizer\Events\AccessTokenRefreshed;
use Jundayw\Tokenizer\Guards\TokenizerGuard;
use Jundayw\Tokenizer\Middleware\CheckForAnyScope;
use Jundayw\Tokenizer\Middleware\CheckScopes;
use Jundayw\Tokenizer\Repositories\BlacklistRepository;
use Jundayw\Tokenizer\Repositories\WhitelistRepository;

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

        Tokenizer::loadKeysFrom(config('tokenizer.key_path'));

        $this->registerTokenViaCookie();

        $this->registerAuthRepository();
        $this->registerTokenProvider();
        $this->registerStorageProvider();

        $this->registerGrantProvider();

        $this->aliasMiddleware([
            'scopes' => CheckScopes::class,
            'scope'  => CheckForAnyScope::class,
        ]);
    }

    /**
     * Register the default callbacks for extracting tokens from cookies.
     *
     * This method sets up three callbacks in the Tokenizer:
     *
     * 1. `tokenViaCookieCallback`:
     *    - Parses the raw cookie JSON string into an associative array.
     *    - Returns null if the cookie is empty or invalid.
     *
     * 2. `accessTokenViaCookieCallback`:
     *    - Retrieves the `access_token` value from the decoded cookie array.
     *    - Returns null if the key does not exist.
     *
     * 3. `refreshTokenViaCookieCallback`:
     *    - Retrieves the `refresh_token` value from the decoded cookie array.
     *    - Returns null if the key does not exist.
     *
     * @return void
     */
    protected function registerTokenViaCookie(): void
    {
        Tokenizer::useTokenViaCookieCallback(function ($cookie) {
            try {
                return json_decode($cookie ?: '', true);
            } catch (\Throwable $e) {
                return null;
            }
        });
        Tokenizer::useAccessTokenViaCookieCallback(fn($cookie) => $cookie['access_token'] ?? null);
        Tokenizer::useRefreshTokenViaCookieCallback(fn($cookie) => $cookie['refresh_token'] ?? null);
    }

    /**
     * Register the bindings for the Auth repository.
     *
     * @return void
     */
    protected function registerAuthRepository(): void
    {
        $this->app->singleton(Authorizable::class, static function ($app) {
            return $app->make(Tokenizer::authorizableModel());
        });
    }

    /**
     * Register the bindings for the Token provider.
     *
     * @return void
     */
    protected function registerTokenProvider(): void
    {
        $this->app->singleton(TokenManager::class, static fn() => new TokenManager());
    }

    /**
     * Register the bindings for the Storage provider.
     *
     * @return void
     */
    protected function registerStorageProvider(): void
    {
        $blacklist = config('cache.stores.blacklist', []);
        $whitelist = config('cache.stores.whitelist', []);
        $driver    = config('tokenizer.cache.driver');
        $default   = config("cache.stores.{$driver}", []);
        $prefix    = config('tokenizer.cache.prefix');
        $prefix    = trim($prefix, ':');
        config([
            'cache.stores.blacklist' => $blacklist ?: $default + ['prefix' => $prefix . ':blacklist'],
            'cache.stores.whitelist' => $whitelist ?: $default + ['prefix' => $prefix . ':whitelist'],
        ]);

        $this->app->singleton(Blacklist::class, static function ($app) {
            return new BlacklistRepository($app['cache']->store('blacklist')->getStore());
        });
        $this->app->singleton(Whitelist::class, static function ($app) {
            return new WhitelistRepository($app['cache']->store('whitelist')->getStore());
        });
    }

    /**
     * Register the bindings for the grant provider.
     *
     * @return void
     */
    protected function registerGrantProvider(): void
    {
        $this->app->singleton(Grant::class, static function ($app) {
            return tap(new TokenizerGrant(
                $app[Authorizable::class],
                $app[TokenManager::class],
                $app[Blacklist::class],
                $app[Whitelist::class],
                $app['request'],
            ), static function (Grant $grant) use ($app) {
                $grant
                    ->setBlacklistEnabled(config('tokenizer.cache.blacklist_enabled', false))
                    ->setWhitelistEnabled(config('tokenizer.cache.whitelist_enabled', false));
                $app->refresh('request', $grant, 'setRequest');
            });
        });
    }

    /**
     * Register the middleware.
     *
     * @param array $middlewares
     *
     * @return void
     * @deprecated
     *
     */
    protected function addMiddlewareAlias(array $middlewares = []): void
    {
        $router = $this->app['router'];
        $method = method_exists($router, 'aliasMiddleware') ? 'aliasMiddleware' : 'middleware';

        array_walk($middlewares, static fn(string $class, string $name) => [$router, $method]($name, $class));
    }

    /**
     * Register the middleware.
     *
     * @param array $middlewares
     *
     * @return void
     */
    protected function aliasMiddleware(array $middlewares = []): void
    {
        array_walk($middlewares, static fn(string $class, string $name, Router $router) => [
            $router, method_exists($router, 'aliasMiddleware') ? 'aliasMiddleware' : 'middleware',
        ]($name, $class), $this->app['router']);
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
        $this->registerListeners();
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
            Console\SecretCommand::class,
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
                return tap($this->makeGuard($auth, $name, $config), function (Guard $guard) {
                    app()->refresh('request', $guard, 'setRequest');
                });
            });
        });
    }

    /**
     * Make an instance of the token guard.
     *
     * @param Factory $auth
     * @param string  $name
     * @param array   $config
     *
     * @return TokenizerGuard
     */
    protected function makeGuard(Factory $auth, string $name, array $config): TokenizerGuard
    {
        $tokenManagement = config('tokenizer.token_management');
        $tokenManagement = array_filter($tokenManagement, static fn($key) => !array_key_exists($key, $config), ARRAY_FILTER_USE_KEY);

        return new TokenizerGuard(
            $name,
            new Repository($config + $tokenManagement),
            $auth,
            $this->app[Grant::class],
            $this->app['request'],
            $auth->createUserProvider($config['provider'] ?? null),
        );
    }

    /**
     * Registering event listeners
     *
     * @return void
     */
    protected function registerListeners(): void
    {
        Event::listen(AccessTokenCreated::class, Listeners\AccessTokenCreated::class);
        Event::listen(AccessTokenRevoked::class, Listeners\AccessTokenRevoked::class);
        Event::listen(AccessTokenRefreshed::class, Listeners\RefreshTokenCreated::class);
    }
}
