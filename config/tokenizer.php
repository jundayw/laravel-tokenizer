<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Token Driver
    |--------------------------------------------------------------------------
    |
    | This option controls the default token driver used by the TokenManager.
    | You may choose between "hash" or "jwt" or register your own driver.
    |
    */

    'default' => [
        'driver' => env('TOKEN_DRIVER', 'jwt'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Token Management
    |--------------------------------------------------------------------------
    |
    | Manage how user authentication tokens are handled across different
    | platform types. These options define whether users can maintain
    | concurrent tokens and how multiple tokens per platform type are treated.
    |
    */

    'token_management' => [

        /*
        |--------------------------------------------------------------------------
        | Enabled
        |--------------------------------------------------------------------------
        |
        | Determines whether token concurrency control is active.
        | When true, rules that enforce token concurrency limits are applied.
        |
        */

        'enabled' => true,

        /*
        |--------------------------------------------------------------------------
        | Allow Multiple Platforms
        |--------------------------------------------------------------------------
        |
        | When true, tokens for different platforms (e.g., 'pc', 'mobile', 'app')
        | can remain valid at the same time.
        | When false, issuing a new token will invalidate all existing tokens
        | regardless of platform.
        |
        |
        */

        'allow_multi_platforms' => true,

        /*
        |--------------------------------------------------------------------------
        | Multi Platform Tokens
        |--------------------------------------------------------------------------
        |
        | A list of platforms that are allowed to have multiple active tokens
        | simultaneously for the same user.
        | Example: ['pc'] allows multiple tokens for PC platforms at the same time.
        |
        */

        'multi_platform_tokens' => [],
    ],

    /*
    |--------------------------------------------------------------------------
    | Access Token Lifetime (TTL)
    |--------------------------------------------------------------------------
    |
    | This value defines the number of seconds an issued access token will
    | remain valid before expiring. Once expired, the token can no longer
    | be used to access protected resources.
    |
    */

    'ttl' => env('TOKEN_TTL', 7200),

    /*
    |--------------------------------------------------------------------------
    | Refresh Token Not-Before Time
    |--------------------------------------------------------------------------
    |
    | This value defines the number of seconds after issuance during which
    | a refresh token cannot be used. This allows enforcing a minimum wait
    | time before a client is allowed to request a new access token using
    | the refresh token.
    |
    */

    'refresh_nbf' => env('TOKEN_REFRESH_NBF', 7200),

    /*
    |--------------------------------------------------------------------------
    | Refresh Token Lifetime
    |--------------------------------------------------------------------------
    |
    | This value controls the total time-to-live of refresh tokens. It may
    | be expressed as a relative interval (e.g. "P15D" for 15 days). Once
    | a refresh token expires, the client must re-authenticate to obtain
    | new tokens.
    |
    */

    'refresh_ttl' => env('TOKEN_REFRESH_TTL', 'P15D'),

    /*
    |--------------------------------------------------------------------------
    | Key Storage Path
    |--------------------------------------------------------------------------
    |
    | The base directory where private and public keys are stored. This path
    | will be prepended to the private_key and public_key file names.
    | You can also use environment variables to configure this path.
    |
    */

    'key_path' => env('TOKEN_KEY_PATH', storage_path('keys')),

    /*
    |--------------------------------------------------------------------------
    | Token Drivers
    |--------------------------------------------------------------------------
    |
    | Here you may configure all the token drivers used by your application.
    | Each driver may have its own set of options passed to its constructor.
    |
    */

    'drivers' => [

        'hash' => [

            /*
            |--------------------------------------------------------------------------
            | Hashing / Signing Algorithm
            |--------------------------------------------------------------------------
            |
            | This value determines the algorithm used to sign or hash tokens.
            | You may adjust this to suit the security requirements of your application.
            |
            | Supported: @link https://php.net/manual/en/function.hash-hmac.php
            |
            */

            'algo' => env('TOKEN_HASH_ALGO', 'sha256'),

            /*
            |--------------------------------------------------------------------------
            | Token Secret Key
            |--------------------------------------------------------------------------
            |
            | This key is used to sign or verify tokens issued by the application.
            | By default, it falls back to the application APP_KEY. You should
            | ensure that this key remains secret and secure at all times.
            |
            */

            'secret_key' => env('TOKEN_SECRET_KEY', env('APP_KEY')),
        ],

        'jwt' => [

            /*
            |--------------------------------------------------------------------------
            | Cryptographic algorithms for signing and verification
            |--------------------------------------------------------------------------
            |
            | This option determines the cryptographic algorithm used for signing
            | and verifying JWT tokens. You may choose from asymmetric algorithms
            | (RS256, RS384, RS512, ES256, ES384, ES512, EdDSA) or symmetric ones
            | (HS256, HS384, HS512).
            |
            | - RS*, ES*, EdDSA: Require both private_key (signing) and public_key
            |   (verification). Recommended for distributed systems.
            |
            | - HS*: Require only secret_key. Simpler but less flexible, recommended
            |   for single-service deployments.
            |
            | Default is HS256 for compatibility, but you should consider stronger
            | algorithms like RS384 or ES384 for better security.
            |
            */

            'algo' => env('TOKEN_ALGO', 'HS256'),

            /*
            |--------------------------------------------------------------------------
            | Private Key
            |--------------------------------------------------------------------------
            |
            | The private key is used to sign tokens when working with asymmetric
            | algorithms such as RS256, RS512, or ES512. This key must be kept
            | strictly confidential and should never be exposed publicly.
            |
            | You may specify the path to a PEM file, or load the key directly
            | from an environment variable or a secure key manager.
            |
            */

            'private_key' => env('TOKEN_PRIVATE_KEY', 'tokenizer-private.key'),

            /*
            |--------------------------------------------------------------------------
            | Public Key
            |--------------------------------------------------------------------------
            |
            | The public key is used to verify tokens that were signed with the
            | corresponding private key when using asymmetric algorithms such
            | as RS256, RS512, or ES512. This key may be safely shared with
            | other services or clients that need to validate tokens.
            |
            | You may specify the path to a PEM file, or load the key directly
            | from an environment variable or a secure key manager.
            |
            */

            'public_key' => env('TOKEN_PUBLIC_KEY', 'tokenizer-public.key'),


            /*
            |--------------------------------------------------------------------------
            | Secret Key
            |--------------------------------------------------------------------------
            |
            | This key is used for signing tokens when using symmetric algorithms
            | such as HMAC (e.g., HS256, HS512). It must be provided as plain text
            | and can be set via environment variables for convenience.
            |
            */

            'secret_key' => env('TOKEN_SECRET_KEY', env('APP_KEY')),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication Guards
    |--------------------------------------------------------------------------
    |
    | Next, you may define every authentication guard for your application.
    | Of course, a great default configuration has been defined for you
    | here which uses session storage and the Eloquent user provider.
    |
    | All authentication drivers have a user provider. This defines how the
    | users are actually retrieved out of your database or other storage
    | mechanisms used by this application to persist your user's data.
    |
    | Supported: "session"
    |
    */

    'guards' => [],

    /*
    |--------------------------------------------------------------------------
    | Database Storage Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure how tokens are persisted in your application's
    | database. This section allows you to define which database connection
    | should be used and the specific table where all issued tokens will be
    | stored. By customizing these settings, you can isolate token storage
    | from your primary application tables if desired.
    |
    */

    'database' => [

        /*
        |--------------------------------------------------------------------------
        | Database Connection
        |--------------------------------------------------------------------------
        |
        | This option specifies the database connection that should be used
        | to store and manage issued tokens. By default, it falls back to
        | the application's main database connection if no specific
        | TOKEN_CONNECTION is defined in the environment file.
        |
        |
        */

        'connection' => env('TOKEN_CONNECTION', env('DB_CONNECTION', 'mysql')),

        /*
        |--------------------------------------------------------------------------
        | Token Storage Table
        |--------------------------------------------------------------------------
        |
        | This table is used to persist all generated tokens and their metadata,
        | including access tokens, refresh tokens, expiration times, and
        | revocation status. You can change this table name if you want
        | to store tokens in a custom table.
        |
        |
        */

        'table' => env('TOKEN_TABLE', 'auth_tokens'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Token Queue Configuration
    |--------------------------------------------------------------------------
    |
    | This section defines the queue connection and queue name used for
    | processing token-related jobs, such as issuing, refreshing, or
    | revoking tokens asynchronously.
    |
    | You can configure the connection to use any queue driver supported
    | by Laravel, e.g., "sync", "database", "redis", "sqs", etc.
    |
    */

    'queue' => [

        /*
        |--------------------------------------------------------------------------
        | Token Queue Connection
        |--------------------------------------------------------------------------
        |
        | The queue connection to use for token jobs.
        |
        | If set to 'sync', jobs will be executed immediately in the current process.
        |
        */

        'connection' => env('TOKEN_QUEUE_CONNECTION', env('QUEUE_CONNECTION', 'sync')),

        /*
        |--------------------------------------------------------------------------
        | Token Queue's Work Queue
        |--------------------------------------------------------------------------
        |
        | The name of the queue where token jobs will be pushed.
        |
        | This is useful for separating token jobs from other application jobs and
        | allows prioritization or dedicated workers.
        |
        */

        'queue' => env('TOKEN_QUEUE', 'default'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Token Cache Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the cache-backed mechanisms used to manage token state,
    | such as blacklist and whitelist lookups.
    | The cache is used as a fast lookup layer to reduce database hits.
    | When both blacklist and whitelist are enabled, blacklist checks take precedence.
    |
    */

    'cache' => [

        /*
        |--------------------------------------------------------------------------
        | Token Cache Store
        |--------------------------------------------------------------------------
        |
        | The cache store to be used for managing token states (blacklist/whitelist).
        | You may specify any of the stores defined in your cache.php configuration
        | file, such as "redis", "memcached", "file", or "database".
        |
        */

        'driver' => env('TOKEN_CACHE_DRIVER', env('CACHE_DRIVER', 'file')),

        /*
        |--------------------------------------------------------------------------
        | Blacklist Feature
        |--------------------------------------------------------------------------
        |
        | Enable or disable the token blacklist feature.
        | When enabled, tokens added to the blacklist will be considered invalid
        | and denied access, regardless of their expiration status.
        |
        */

        'blacklist_enabled' => env('TOKEN_BLACKLIST_ENABLED', true),

        /*
        |--------------------------------------------------------------------------
        | Whitelist Feature
        |--------------------------------------------------------------------------
        |
        | Enable or disable the token whitelist feature.
        | When enabled, tokens added to the whitelist will always be considered
        | valid and granted access, provided they are not expired or revoked in the database.
        |
        */

        'whitelist_enabled' => env('TOKEN_WHITELIST_ENABLED', false),

        /*
        |--------------------------------------------------------------------------
        | Token Cache Key Prefix
        |--------------------------------------------------------------------------
        |
        | This value will be prepended to all token-related cache keys to prevent
        | naming collisions across different applications or environments.
        |
        */

        'prefix' => env('TOKEN_CACHE_PREFIX', 'tokenizer:'),
    ],

];
