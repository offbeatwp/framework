<?php

namespace OffbeatWP\Config;

use OffbeatWP\Foundation\App;
use OffbeatWP\Helpers\ArrayHelper;

final class Config
{
    private readonly App $app;
    private array $config;

    public function __construct(App $app)
    {
        $this->app = $app;
        $this->config = $this->loadConfig();
    }

    private function loadConfig(): array
    {
        $config = [];
        $configFiles = glob($this->app->configPath() . '/*.php');

        foreach ($configFiles as $configFile) {
            $configValues = require $configFile;
            $config[basename($configFile, '.php')] = $configValues;
        }

        $config = $this->loadConfigEnvFile($config);
        $config = $this->loadConfigEnv($config);

        return $config;
    }

    protected function loadConfigEnvFile(array $config): array
    {
        $env = get_template_directory() . '/env.php';
        if (file_exists($env)) {
            $configValues = require $env;
            foreach ($configValues as $key => $value) {
                if ($this->get($key)) {
                    $config[$key] = ArrayHelper::mergeRecursiveAssoc($config[$key], $value);
                }
            }
        }

        return $config;
    }

    protected function loadConfigEnv(array $config): array
    {
        foreach ($config as $configKey => $configSet) {
            if (ArrayHelper::isAssoc($configSet)) {
                // Get current environment
                $currentEnvironment = defined('WP_ENV') ? WP_ENV : 'dev';

                // Get all settings in 'env' variable
                $envConfigs = $configSet['env'] ?? null;

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
                $config[$configKey] = $configSet;
            }
        }

        return $config;
    }

    /**
     * @param string $key
     * @return object|\Illuminate\Support\Collection|string|float|int|bool|null|\OffbeatWP\Config\Config
     */
    public function get(string $key)
    {
        $result = ArrayHelper::getValueFromDottedKey($key, $this->config);
        return is_array($result) ? collect($result) : $result;
    }

    /**
     * @deprecated
     * @param array-key $key
     * @param mixed $value
     * @return mixed
     */
    public function set($key, $value)
    {
        $this->config[$key] = $value;

        return $value;
    }

    /** @return mixed[] */
    public function all(): array
    {
        return $this->config;
    }
}
