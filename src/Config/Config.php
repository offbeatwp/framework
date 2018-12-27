<?php
namespace OffbeatWP\Config;

class Config {
    private $app;
    protected $config = null;

    public function __construct($app) {
        $this->app = $app;

        if (is_null($this->config)) {
            $this->loadConfig();
        }
    }

    private function loadConfig() {
        $configFiles = glob($this->app->configPath() . '/*.php');

        foreach ($configFiles as $configFile) {
            $this->set(basename($configFile, '.php'), require $configFile);
        }
    }

    public function get($key, $default = null) {
        $return = $default;

        if (isset($this->config[$key])) {
            $return = $this->config[$key];
        } elseif (strpos($key, '.') !== false) {
            $config = $this->config;
            
            foreach (explode('.', $key) as $var) {
                if (isset($config[$var])) {
                    $config = $config[$var];
                } else {
                    return null;
                }
            }

            $return = $config;
        }

        if (is_array($return)) {
            $return = collect($return);
        }

        return $return;
    }

    public function set($key, $value) {
        $this->config[$key] = $value;

        return $value;
    }

    public function all() {
        return $this->config;
    }
}