<?php

namespace Jundayw\Tokenizer;

use Jundayw\Tokenizer\Models\AuthToken;

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
     * The token model class name.
     *
     * @var string
     */
    protected static string $tokenModel = AuthToken::class;

    /**
     * Set the token model class name.
     *
     * @param string $tokenModel
     *
     * @return void
     */
    public static function useTokenModel(string $tokenModel): void
    {
        self::$tokenModel = $tokenModel;
    }

    /**
     * Get the token model class name.
     *
     * @return string
     */
    public static function tokenModel(): string
    {
        return self::$tokenModel;
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
