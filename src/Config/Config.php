<?php
namespace OffbeatWP\Config;

use OffbeatWP\Foundation\App;
use OffbeatWP\Helpers\ArrayHelper;

class Config {
    /** @var App */
    private $app;
    /** @var mixed[]|null */
    protected $config = null;

    /** @param App $app */
    public function __construct($app) {
        $this->app = $app;

        if ($this->config === null) {
            $this->loadConfig();
        }
    }

    private function loadConfig(): void
    {
        $configFiles = glob($this->app->configPath() . '/*.php');

        foreach ($configFiles as $configFile) {
            $configValues = require $configFile;

            $this->set(basename($configFile, '.php'), $configValues);
        }

        $this->loadConfigEnvFile();
        $this->loadConfigEnv();
    }

    protected function loadConfigEnvFile(): void
    {
        $env = get_template_directory() . '/env.php';
        if (file_exists($env)) {
            $configValues = require $env;
            foreach ($configValues as $key => $value) {
                if ($this->get($key)) {
                    $this->config[$key] = ArrayHelper::mergeRecursiveAssoc($this->config[$key], $value);
                }
            }
        }
    }

    protected function loadConfigEnv(): void
    {
        if (is_array($this->all())) {
            foreach ($this->all() as $configKey => $configSet) {
                if (ArrayHelper::isAssoc($configSet)) {
                    // Get current environment
                    $currentEnvironment = defined('WP_ENV') ? WP_ENV : 'dev';

                    // Get all settings in 'env' variable
                    $envConfigs = ArrayHelper::getValueFromDottedKey('env', $configSet);

                    if ($envConfigs) {
                        $explicitEnvConfigs = [];

                        foreach ($envConfigs as $envKey => $envConfig) {
                            $matched = preg_match('/^!(.*)/', $envKey, $matches);

                            if ($matched && !in_array($currentEnvironment, explode('|', $matches[1]))) {
                                $configSet = ArrayHelper::mergeRecursiveAssoc($configSet, $envConfig);
                            } elseif (!$matched && in_array($currentEnvironment, explode('|', $envKey))) {
                                $explicitEnvConfigs[] = $envConfig;
                            }
                        }

                        foreach ($explicitEnvConfigs as $explicitEnvConfig) {
                            $configSet = ArrayHelper::mergeRecursiveAssoc($configSet, $explicitEnvConfig);
                        }
                    }

                    // Set config
                    $this->set($configKey, $configSet);
                }
            }
        }
    }

    /**
     * @param string $key
     * @param null $default Unused
     * @return object|\Illuminate\Support\Collection|string|float|int|bool|null|\OffbeatWP\Config\Config
     */
    public function get(string $key, $default = null)
    {
        $config = $this->config;
        $return = ArrayHelper::getValueFromDottedKey($key, $config ?: []);

        if (is_array($return)) {
            $return = collect($return);
        }

        return $return;
    }

    /**
     * @param array-key $key
     * @param mixed $value
     * @return mixed
     */
    public function set($key, $value) {
        $this->config[$key] = $value;

        return $value;
    }

    /** @return mixed[] */
    public function all()
    {
        return $this->config;
    }
}