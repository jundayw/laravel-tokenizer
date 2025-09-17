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
    public static string $cookie = 'tokenizer';

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
    protected static $accessTokenRetrievalCallback = null;

    /**
     * Specify a callback that should be used to fetch the access token from the request.
     *
     * @param callable|null $callback
     *
     * @return void
     */
    public static function useAccessTokenFromRequest(callable $callback = null): void
    {
        self::$accessTokenRetrievalCallback = $callback;
    }

    /**
     * Get the callback used to retrieve the access token from the request.
     *
     * @return callable|null
     */
    public static function accessTokenRetrievalCallback(): ?callable
    {
        return self::$accessTokenRetrievalCallback;
    }

    /**
     * A callback that can add to the validation of the access token.
     *
     * @var callable|null
     */
    protected static $accessTokenAuthenticationCallback = null;

    /**
     * Specify a callback that should be used to authenticate access tokens.
     *
     * @param callable|null $callback
     *
     * @return void
     */
    public static function useAuthenticateAccessTokens(callable $callback = null): void
    {
        self::$accessTokenAuthenticationCallback = $callback;
    }

    /**
     * Get the callback used to validation the access token.
     *
     * @return callable|null
     */
    public static function accessTokenAuthenticationCallback(): ?callable
    {
        return self::$accessTokenAuthenticationCallback;
    }
}
