<?php

namespace Tapegun;

class Config
{
    /**
     * @var string
     */
    private $cwd;

    /**
     * @var array
     */
    private $config = [];

    /**
     * Builds a configuration from a JSON file.
     *
     * @param string $path
     * @return Config
     */
    public static function fromFilePath(string $path)
    {
        if (!file_exists($path)) {
            throw new \InvalidArgumentException($path . ' does not exist.');
        }

        if (!$data = json_decode(file_get_contents($path), true)) {
            throw new \InvalidArgumentException('Failed to load file.');
        }

        return new static(dirname(realpath($path)), $data);
    }

    /**
     * Config constructor.
     *
     * @param string $cwd
     * @param array  $config
     */
    function __construct(string $cwd, array $config)
    {
        $this->cwd = $cwd;
        $this->config = $config;
    }

    /**
     * Returns the configuration directory.
     *
     * @return string
     */
    public function getCwd()
    {
        return $this->cwd;
    }

    /**
     * Returns the value of a configuration key.
     *
     * @param string $key
     * @param mixed  $default
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        return $this->config[$key] ?? $default;
    }
}