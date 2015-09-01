<?php

namespace Ob\Di;

class Container
{
    /**
     * Container parameters.
     *
     * @var array
     */
    private $parameters = [];

    /**
     * A registry of callables to create services.
     *
     * @var array
     */
    private $callables = [];

    /**
     * A registry of service factories.
     *
     * @var array
     */
    private $factories = [];

    /**
     * A registry of service instance.
     *
     * @var array
     */
    private $instances = [];

    /**
     * @param array $parameters An associative array of parameters (i.e. ['name' => $value])
     */
    public function __construct($parameters = [])
    {
        $this->parameters = $parameters;
    }

    /**
     * Set a container parameter.
     *
     * @param string $name  The parameter name
     * @param mixed  $value The parameter value
     */
    public function setParam($name, $value)
    {
        $this->parameters[$name] = $value;
    }

    /**
     * Retrieve a container parameter.
     *
     * @param string $name The parameter name
     *
     * @return mixed
     */
    public function getParam($name)
    {
        $param = $this->parameters[$name];

        if (is_callable($param)) {
            return $param();
        }

        return $param;
    }

    /**
     * Return whether a container parameter exists or not.
     *
     * @param string $name The parameter name
     *
     * @return bool
     */
    public function hasParam($name)
    {
        return isset($this->parameters[$name]);
    }

    /**
     * Unset a container parameter.
     *
     * @param string $name The parameter name
     */
    public function unsetParam($name)
    {
        unset($this->parameters[$name]);
    }

    /**
     * Register a function as a parameter.
     *
     * @param string   $name     The parameter name
     * @param callable $callable A callable that returns a value
     */
    public function protect($name, $callable)
    {
        $this->parameters[$name] = $callable;
    }

    /**
     * Register a callable to create a service by name.
     *
     * @param string   $name     The service name
     * @param callable $callable A callable that returns an object.
     */
    public function set($name, $callable)
    {
        $this->callables[$name] = $callable;
        unset($this->instances[$name]);
    }

    /**
     * Register a callable as a factory service.
     *
     * @param string   $name     The service name
     * @param callable $callable A callable that returns an object.
     */
    public function factory($name, $callable)
    {
        $this->factories[$name] = $callable;
    }

    /**
     * Get a service by name.
     *
     * @param string $name The service name
     *
     * @return mixed
     */
    public function get($name)
    {
        if (isset($this->factories[$name])) {
            return call_user_func($this->factories[$name], $this);
        }

        if (!isset($this->instances[$name])) {
            $this->instances[$name] = call_user_func($this->callables[$name], $this);
        }

        return $this->instances[$name];
    }

    /**
     * Return whether a service exists or not.
     *
     * @param string $name The service name
     *
     * @return bool
     */
    public function has($name)
    {
        return isset($this->factories[$name]) || isset($this->callables[$name]);
    }

    /**
     * Get the anonymous function used to create a service.
     *
     * @param string $name The service name
     *
     * @return callable
     */
    public function raw($name)
    {
        return $this->callables[$name];
    }

    /**
     * Extends a service definition.
     *
     * @param string   $name     The service name
     * @param callable $callable A callable that extends the service definition
     */
    public function extend($name, $callable)
    {
        $service = $this->callables[$name];

        $extended = function ($c) use ($callable, $service) {
            return $callable($service($c), $c);
        };

        $this->set($name, $extended);
    }
}
