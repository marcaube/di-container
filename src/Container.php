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
     * A registry of callables used to create services.
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
     * It can ba a value or a callable that returns a value.
     *
     * @param string $name  The parameter name
     * @param mixed  $value The parameter value
     */
    public function setParam($name, $value)
    {
        $this->ensureNameIsString($name);

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
        $this->ensureNameIsString($name);

        $param = $this->parameters[$name];

        if (is_callable($param)) {
            return call_user_func($param);
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
        $this->ensureNameIsString($name);

        return isset($this->parameters[$name]);
    }

    /**
     * Unset a container parameter.
     *
     * @param string $name The parameter name
     */
    public function unsetParam($name)
    {
        $this->ensureNameIsString($name);

        unset($this->parameters[$name]);
    }

    /**
     * Register a callable to create a service by name.
     *
     * @param string   $name     The service name
     * @param callable $callable A callable that returns an object.
     */
    public function set($name, $callable)
    {
        $this->ensureNameIsString($name);

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
        $this->ensureNameIsString($name);

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
        $this->ensureNameIsString($name);

        if (isset($this->factories[$name])) {
            return call_user_func($this->factories[$name], $this);
        }

        if (!isset($this->instances[$name])) {
            $this->instances[$name] = call_user_func($this->raw($name), $this);
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
        $this->ensureNameIsString($name);

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
        $this->ensureNameIsString($name);

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
        $this->ensureNameIsString($name);

        $service = $this->raw($name);

        $extended = function ($c) use ($callable, $service) {
            return $callable($service($c), $c);
        };

        $this->set($name, $extended);
    }

    /**
     * Returns whether or not a service has been initialized.
     *
     * @param string $name The service name
     *
     * @return bool
     */
    public function initialized($name)
    {
        $this->ensureNameIsString($name);

        return isset($this->instances[$name]);
    }

    /**
     * Configure setter injection for a service definition.
     *
     * @param string $name   The service name
     * @param string $method The method to call
     * @param array  $params An array of parameters for the method
     */
    public function call($name, $method, array $params = [])
    {
        $this->extend($name, function ($service) use ($method, $params) {
            call_user_func_array([$service, $method], $params);

            return $service;
        });
    }

    /**
     * Guard clause against non-string service identifier.
     *
     * @param string $name The service name
     */
    private function ensureNameIsString($name)
    {
        if (!is_string($name)) {
            throw new \InvalidArgumentException(sprintf(
                'The name parameter must be of type string, %s given',
                is_object($name) ? get_class($name) : gettype($name)
            ));
        }
    }
}
