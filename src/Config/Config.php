<?php
namespace OffbeatWP\Config;

use OffbeatWP\Foundation\App;
use OffbeatWP\Helpers\ArrayHelper;

final class Config {
    private App $app;
    /** @var mixed[]|null */
    protected ?array $config = null;

    public function __construct(App $app) {
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
                if (!$this->get($key)) {
                    continue;
                }
                $this->config[$key] = ArrayHelper::mergeRecursiveAssoc($this->config[$key], $value);
            }
        }
    }

    protected function loadConfigEnv(): void
    {
        foreach ($this->all() as $configKey => $configSet) {
            if (array_is_list($configSet)) {
                continue;
            }

            // Get current environment
            $currentEnvironment = defined('WP_ENV') ? WP_ENV : 'dev';

            // Get all settings in 'env' variable
            $envConfigs = ArrayHelper::getValueFromDottedKey('env', $configSet);

            if ($envConfigs) {
                $explicitEnvConfigs = [];

                foreach ($envConfigs as $envKey => $envConfig) {
                    if (preg_match('/^!(.*)/', $envKey, $matches) && !in_array($currentEnvironment, explode('|', $matches[1]), true)) {
                        $configSet = ArrayHelper::mergeRecursiveAssoc($configSet, $envConfig);
                    } elseif (!preg_match('/^!(.*)/', $envKey, $matches)) {
                        if (in_array($currentEnvironment, explode('|', $envKey), true)) {
                            $explicitEnvConfigs[] = $envConfig;
                        }
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

    /** @return object|mixed[]|float|int|bool|null|\OffbeatWP\Config\Config */
    public function get(string $key)
    {
        return ArrayHelper::getValueFromDottedKey($key, $this->config ?: []);
    }

    /**
     * @param array-key $key
     * @param mixed $value
     */
    public function set($key, $value): void
    {
        $this->config[$key] = $value;
    }

    public function all(): array
    {
        return (array)$this->config;
    }
}