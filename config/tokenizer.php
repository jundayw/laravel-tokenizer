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

        'driver' => env('TOKEN_DRIVER', 'hash'),

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
    ],

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

            'secret_key' => env('TOKEN_HASH_SECRET_KEY', env('APP_KEY')),
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
    | Database Connection
    |--------------------------------------------------------------------------
    |
    | Here you may specify which database connection should be used by the
    | token storage. If left null, the default database connection will
    | be utilized for storing and retrieving authentication tokens.
    |
    */

    'connection' => env('TOKEN_CONNECTION'),

    /*
    |--------------------------------------------------------------------------
    | Token Storage Table
    |--------------------------------------------------------------------------
    |
    | This option defines the database table used to store authentication
    | tokens. You may change it to any table name that fits your application's
    | requirements. The default value is "auth_tokens".
    |
    */

    'table' => env('TOKEN_TABLE', 'auth_tokens'),

];
