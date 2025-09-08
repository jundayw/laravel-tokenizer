<?php

namespace Jundayw\Tokenizer\Facades;

use Illuminate\Support\Facades\Facade;
use Jundayw\Tokenizer\Contracts\Authorizable;
use Jundayw\Tokenizer\Contracts\Tokenable;
use Jundayw\Tokenizer\Contracts\Tokenizable;
use Jundayw\Tokenizer\Tokens\HashHmacToken;
use Jundayw\Tokenizer\Tokens\Token as TokenFactory;
use Jundayw\Tokenizer\TokenManager;

/**
 * @method static string getDefaultDriver()
 * @method static Tokenable resolve(string $driver = null)
 * @method static TokenManager extend(string $driver, \Closure $callback)
 * @method static Tokenable buildTokens(Authorizable $authorizable, Tokenizable $tokenizable)
 * @method static Tokenable refreshTokens(string $refreshToken = null)
 * @method static bool revokeToken(string $accessToken = null)
 *
 * @see TokenManager
 * @see TokenFactory
 * @see HashHmacToken
 */
class Token extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return TokenManager::class;
    }
}
