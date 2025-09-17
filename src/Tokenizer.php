<?php

namespace Jundayw\Tokenizer;

use Jundayw\Tokenizer\Models\Authorization;

final class Tokenizer
{
    /**
     * Indicates whether migrations will be run.
     *
     * @var bool
     */
    protected static bool $runsMigrations = true;

    /**
     * Determines whether migrations should be run.
     *
     * @return bool
     */
    public static function shouldRunMigrations(): bool
    {
        return self::$runsMigrations;
    }

    /**
     * The configuration does not register its migrations.
     *
     * @return void
     */
    public static function ignoreMigrations(): void
    {
        self::$runsMigrations = false;
    }

    /**
     * The storage location of the encryption keys.
     *
     * @var string
     */
    protected static string $keyPath;

    /**
     * Set the storage location of the encryption keys.
     *
     * @param string $path
     *
     * @return void
     */
    public static function loadKeysFrom(string $path): void
    {
        self::$keyPath = $path;
    }

    /**
     * The location of the encryption keys.
     *
     * @param string $file
     *
     * @return string
     */
    public static function keyPath(string $file): string
    {
        $file = ltrim($file, '/\\');

        return self::$keyPath
            ? rtrim(self::$keyPath, '/\\') . DIRECTORY_SEPARATOR . $file
            : storage_path($file);
    }

    /**
     * The name for API token cookies.
     *
     * @var string
     */
    protected static string $cookie = 'tokenizer';

    /**
     * Get or set the name for API token cookies.
     *
     * @param string|null $cookie
     *
     * @return string|self
     */
    public static function cookie(string $cookie = null): self|string
    {
        if (is_null($cookie)) {
            return self::$cookie;
        }

        self::$cookie = $cookie;

        return new self;
    }

    /**
     * The authorizable model class name.
     *
     * @var string
     */
    protected static string $authorizableModel = Authorization::class;

    /**
     * Set the authorizable model class name.
     *
     * @param string $authorizableModel
     *
     * @return void
     */
    public static function useAuthorizableModel(string $authorizableModel): void
    {
        self::$authorizableModel = $authorizableModel;
    }

    /**
     * Get the authorizable model class name.
     *
     * @return string
     */
    public static function authorizableModel(): string
    {
        return self::$authorizableModel;
    }

    /**
     * A callback that can get the token from the request.
     *
     * @var callable|null
     */
    protected static $tokenRetrievalCallback = null;

    /**
     * Specify a callback that should be used to fetch the token from the request.
     *
     * @param callable|null $callback
     *
     * @return void
     */
    public static function useTokenFromRequest(callable $callback = null): void
    {
        self::$tokenRetrievalCallback = $callback;
    }

    /**
     * Get the callback used to retrieve the token from the request.
     *
     * @return callable|null
     */
    public static function tokenRetrievalCallback(): ?callable
    {
        return self::$tokenRetrievalCallback;
    }

    /**
     * A callback that can add to the validation of the token.
     *
     * @var callable|null
     */
    protected static $tokenAuthenticationCallback = null;

    /**
     * Specify a callback that should be used to authenticate token.
     *
     * @param callable|null $callback
     *
     * @return void
     */
    public static function useTokenAuthenticationCallback(callable $callback = null): void
    {
        self::$tokenAuthenticationCallback = $callback;
    }

    /**
     * Get the callback used to validation the token.
     *
     * @return callable|null
     */
    public static function tokenAuthenticationCallback(): ?callable
    {
        return self::$tokenAuthenticationCallback;
    }

    /**
     * Callback used to perform additional validation on the token format.
     *
     * @var callable|null
     */
    protected static $tokenVerificationCallback = null;

    /**
     * Register a callback to be used for token format validation.
     *
     * @param callable|null $callback
     *
     * @return void
     */
    public static function useTokenVerificationCallback(callable $callback = null): void
    {
        self::$tokenVerificationCallback = $callback;
    }

    /**
     * Retrieve the currently registered token validation callback.
     *
     * @return callable|null
     */
    public static function tokenVerificationCallback(): ?callable
    {
        return self::$tokenVerificationCallback;
    }

    /**
     * Callback used to extract a token from a cookie.
     *
     * @var callable|null
     */
    protected static $tokenViaCookieCallback = null;

    /**
     * Register a callback to retrieve a token from a cookie.
     *
     * @param callable|null $callback
     *
     * @return void
     */
    public static function useTokenViaCookieCallback(callable $callback = null): void
    {
        self::$tokenViaCookieCallback = $callback;
    }

    /**
     * Get the currently registered callback for retrieving a token from a cookie.
     *
     * @return callable|null
     */
    public static function tokenViaCookieCallback(): ?callable
    {
        return self::$tokenViaCookieCallback;
    }

    /**
     * Callback used to extract the access token from a cookie.
     *
     * @var callable|null
     */
    protected static $accessTokenViaCookieCallback = null;

    /**
     * Register a callback to retrieve the access token from a cookie.
     *
     * @param callable|null $callback
     *
     * @return void
     */
    public static function useAccessTokenViaCookieCallback(callable $callback = null): void
    {
        self::$accessTokenViaCookieCallback = $callback;
    }

    /**
     * Get the currently registered callback for retrieving the access token from a cookie.
     *
     * @return callable|null
     */
    public static function accessTokenViaCookieCallback(): ?callable
    {
        return self::$accessTokenViaCookieCallback;
    }

    /**
     * Callback used to extract the refresh token from a cookie.
     *
     * @var callable|null
     */
    protected static $refreshTokenViaCookieCallback = null;

    /**
     * Register a callback to retrieve the refresh token from a cookie.
     *
     * @param callable|null $callback
     *
     * @return void
     */
    public static function useRefreshTokenViaCookieCallback(callable $callback = null): void
    {
        self::$refreshTokenViaCookieCallback = $callback;
    }

    /**
     * Get the currently registered callback for retrieving the refresh token from a cookie.
     *
     * @return callable|null
     */
    public static function refreshTokenViaCookieCallback(): ?callable
    {
        return self::$refreshTokenViaCookieCallback;
    }

    /**
     * Callback used to customize token array serialization.
     *
     * @var callable|null
     */
    protected static $tokenable = null;

    /**
     * Register a callback to customize token serialization.
     *
     * @param callable|null $callback
     *
     * @return void
     */
    public static function useTokenable(callable $callback = null): void
    {
        self::$tokenable = $callback;
    }

    /**
     * Get the currently registered callback for token serialization.
     *
     * @return callable|null
     */
    public static function tokenable(): ?callable
    {
        return self::$tokenable;
    }
}
