<?php

namespace Jundayw\Tokenizer;

use DateInterval;
use DateTime;
use Exception;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Str;
use Jundayw\Tokenizer\Contracts\Tokenable;
use Jundayw\Tokenizer\Contracts\Authorizable;
use Jundayw\Tokenizer\Facades\Token;

trait HasTokenizable
{
    /**
     * The current access token for the authentication user.
     *
     * @var Authorizable|null
     */
    protected ?Authorizable $accessToken = null;

    /**
     * Set the current access token for the user.
     *
     * @param Authorizable $accessToken
     *
     * @return static
     */
    public function withAccessToken(Authorizable $accessToken): static
    {
        $this->accessToken = $accessToken;

        return $this;
    }

    /**
     * Get the current access token being used by the user.
     *
     * @return Authorizable|null
     */
    public function token(): ?Authorizable
    {
        return $this->accessToken;
    }

    /**
     * Determine if the current API token has a given scope.
     *
     * @param string $scope
     *
     * @return bool
     */
    public function tokenCan(string $scope): bool
    {
        return $this->token()?->can($scope) ?? false;
    }

    /**
     * Get the access tokens that belong to model.
     *
     * @return MorphMany
     */
    public function tokens(): MorphMany
    {
        return $this->morphMany(Tokenizer::authorizableModel(), 'tokenable');
    }

    /**
     * Create a new personal access token for the user.
     *
     * @param string $name
     * @param string $scene
     * @param array  $scopes
     *
     * @return Tokenable
     */
    public function createToken(string $name, string $scene = 'default', array $scopes = []): Tokenable
    {
        $token = $this->tokens()->make([
            'scene'                      => $scene,
            'name'                       => $name,
            // 'access_token'               => $accessToken,
            // 'refresh_token'              => $refreshToken,
            'scopes'                     => $this->getScopes($scopes),
            'access_token_expire_at'     => $this->getDateTimeAt(config('tokenizer.ttl', 7200)),
            'refresh_token_available_at' => $this->getDateTimeAt(config('tokenizer.refresh_nbf', 7200)),
            'refresh_token_expire_at'    => $this->getDateTimeAt(config('tokenizer.refresh_ttl', 'P15D')),
        ]);

        return Token::buildTokens($token, $this);
    }

    /**
     * Return an array of scopes associated with the token.
     *
     * @return string[]
     */
    final public function getScopes(array $scopes): array
    {
        if (in_array('*', $scopes) || in_array('*', $this->abilities())) {
            return ['*'];
        }

        return array_merge($scopes, $this->abilities());
    }

    /**
     * Get a DateTime object after a specified duration.
     *
     * @param string|int $duration ISO 8601 duration string or integer number of seconds
     * @param int        $default  Default seconds to use if string parsing fails, default is 7200 (2 hours)
     *
     * @return DateTime The calculated DateTime object
     */
    final public function getDateTimeAt(string|int $duration = 0, int $default = 7200): DateTime
    {
        if (is_string($duration)) {
            try {
                return now()->add(new DateInterval($duration))->toDateTime();
            } catch (Exception $e) {
                return now()->addSeconds($default)->toDateTime();
            }
        }

        return now()->addSeconds($duration)->toDateTime();
    }

    /**
     * Get the abilities that the user did have.
     *
     * @return array
     */
    public function abilities(): array
    {
        return [];
    }

    /**
     * Return the identifier for the `sub` claim.
     *
     * @return string|int
     */
    public function getJWTIdentifier(): int|string
    {
        return $this->getKey();
    }

    /**
     * Return the issuer for the `iss` claim.
     *
     * @return string
     */
    public function getJWTIssuer(): string
    {
        return str_replace('\\', '.', get_class($this));
    }

    /**
     * Return the unique token ID for the `jti` claim.
     *
     * @return string
     */
    public function getJWTId(): string
    {
        return Str::uuid()->toString();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims(): array
    {
        return [];
    }
}
