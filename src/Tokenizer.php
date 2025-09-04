<?php

namespace Jundayw\Tokenizer;

use Jundayw\Tokenizer\Models\AuthToken;

final class Tokenizer
{
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
}
