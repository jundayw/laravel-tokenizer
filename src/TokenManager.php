<?php

namespace Jundayw\Tokenizer;

use Closure;
use InvalidArgumentException;
use Jundayw\Tokenizer\Contracts\Tokenable;
use Jundayw\Tokenizer\Tokens\HashHmacToken;
use Jundayw\Tokenizer\Tokens\JsonWebToken;

class TokenManager
{
    /**
     * The registered custom driver creators.
     *
     * @var array
     */
    protected array $customCreators = [];

    /**
     * The array of created "drivers".
     *
     * @var array
     */
    protected array $drivers = [];

    /**
     * Get a token driver instance.
     *
     * @param string|null $name
     *
     * @return Tokenable
     */
    public function driver(?string $name = null): Tokenable
    {
        $name = $name ?: $this->getDefaultDriver();

        return $this->drivers[$name] ??= $this->resolve($name);
    }

    /**
     * Get the default authentication driver name.
     *
     * @return string
     */
    public function getDefaultDriver(): string
    {
        return config('tokenizer.default.driver', 'hash');
    }

    /**
     * Get the driver configuration.
     *
     * @param string $name
     *
     * @return array|null
     */
    protected function getConfig(string $name): ?array
    {
        return config("tokenizer.drivers.{$name}");
    }

    /**
     * Resolve the given token driver instance.
     *
     * If no driver name is provided, the default driver will be used.
     *
     * @param string $driver
     *
     * @return Tokenable
     */
    protected function resolve(string $driver): Tokenable
    {
        $config = $this->getConfig($driver);

        if (is_null($config)) {
            throw new InvalidArgumentException("Token driver [{$driver}] is not defined.");
        }

        if (array_key_exists($driver, $this->customCreators)) {
            return call_user_func($this->customCreators[$driver], $config);
        }

        $driverMethod = 'create' . ucfirst($driver) . 'TokenDriver';

        if (method_exists($this, $driverMethod)) {
            return call_user_func([$this, $driverMethod], $driver, $config);
        }

        throw new InvalidArgumentException("Token driver [{$driver}] is not defined.");
    }

    /**
     * Create a hash token based token driver.
     *
     * @param string $name
     * @param array  $config
     *
     * @return Tokenable
     */
    public function createHashTokenDriver(string $name, array $config): Tokenable
    {
        return new HashHmacToken($name, $config);
    }

    /**
     * Create a jwt token based token driver.
     *
     * @param string $name
     * @param array  $config
     *
     * @return Tokenable
     */
    public function createJwtTokenDriver(string $name, array $config): Tokenable
    {
        return new JsonWebToken($name, $config);
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
        return call_user_func_array([$this->driver(), $method], $parameters);
    }
}
