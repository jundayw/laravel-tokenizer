<?php

namespace Jundayw\Tokenizer;

use Closure;
use InvalidArgumentException;
use Jundayw\Tokenizer\Contracts\Tokenable;
use Jundayw\Tokenizer\Tokens\HashHmacToken;

class TokenManager
{
    /**
     * The registered custom driver creators.
     *
     * @var array
     */
    protected array $customCreators = [];

    /**
     * Get the default authentication driver name.
     *
     * @return string
     */
    public function getDefaultDriver(): string
    {
        return config('tokenizer.driver') ?? class_basename(HashHmacToken::class);
    }

    /**
     * Resolve the given token driver instance.
     *
     * If no driver name is provided, the default driver will be used.
     *
     * @param string|null $driver
     *
     * @return Tokenable
     */
    public function resolve(string $driver = null): Tokenable
    {
        $driver = $driver ?: $this->getDefaultDriver();

        if (is_null($driver) || !array_key_exists($driver, $this->customCreators)) {
            throw new InvalidArgumentException("Token driver [{$driver}] is not defined.");
        }

        return call_user_func($this->customCreators[$driver], $this);
    }

    /**
     * Register a custom driver creator Closure.
     *
     * @param string  $driver
     * @param Closure $callback
     *
     * @return static
     */
    public function extend(string $driver, Closure $callback): static
    {
        $this->customCreators[$driver] = $callback;

        return $this;
    }

    /**
     * Dynamically call the default driver instance.
     *
     * @param string $method
     * @param array  $parameters
     *
     * @return mixed
     */
    public function __call(string $method, array $parameters)
    {
        return call_user_func_array([$this->resolve(), $method], $parameters);
    }
}
