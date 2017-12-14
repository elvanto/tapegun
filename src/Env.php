<?php

namespace Tapegun;

class Env
{
    /**
     * @var array
     */
    private $env = [];

    /**
     * @var string[]
     */
    private $checkedKeys = [];

    /**
     * Environment constructor.
     *
     * @param array $env
     */
    function __construct(array $env)
    {
        $this->env = $env;
    }

    /**
     * Returns a new environment after merging values from another.
     *
     * @param Env $env
     * @return Env
     */
    public function merge(Env $env)
    {
        return new self(array_merge($this->env, $env->env));
    }

    /**
     * Returns the value of an environment variable.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        return $this->env[$key] ?? $default;
    }

    /**
     * Sets the value of an environment variable.
     *
     * @param string $key
     * @param mixed $value
     */
    public function set(string $key, $value)
    {
        $this->env[$key] = $value;
    }

    /**
     * Replaces all string instances of environment variable names with
     * their respective values.
     *
     * @param string $message
     * @return string
     */
    public function resolve(string $message)
    {
        return preg_replace_callback('/\{\{(.*?)\}\}/', function ($matches) {
            if (in_array($matches[1], $this->checkedKeys)) {
                throw new \Exception('Circular dependency detected in environment.');
            }

            $this->checkedKeys[] = $matches[1];
            $value = $this->get($matches[1]);

            if (is_string($value)) {
                $value = $this->resolve($value);
            }

            array_pop($this->checkedKeys);
            return $value;
        }, $message);
    }
}