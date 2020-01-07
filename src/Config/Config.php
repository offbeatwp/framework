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
            $configValues = require $configFile;

            if (is_multisite() && isset($configValues['sites']) && isset($configValues['sites'][get_current_blog_id()])) {
                $configValues = array_merge_recursive($configValues, $configValues['sites'][get_current_blog_id()]);
            }

            $this->set(basename($configFile, '.php'), $configValues);
        }

        $this->loadConfigEnv();
    }

    protected function loadConfigEnv()
    {
        $env = get_template_directory() . '/env.php';
        if (file_exists($env)) {
            $configValues = require $env;
            foreach ($configValues as $key => $value) {
                if (!$this->get($key)) {
                    continue;
                }
                $this->config[$key] = array_replace_recursive($this->config[$key], $value);
            }
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