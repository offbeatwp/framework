<?php

namespace OffbeatWP\Config;

use OffbeatWP\Foundation\App;
use OffbeatWP\Helpers\ArrayHelper;

final class Config
{
    private readonly App $app;
    private readonly string $baseConfigPath;
    private readonly array $envConfigValues;
    /** @var array<string, mixed[]> */
    private array $config;

    public function __construct(App $app)
    {
        $this->app = $app;
        $this->baseConfigPath = $this->app->configPath() . '/';
        $this->envConfigValues = $this->getEnvConfigValues();
        $this->config = [];

        foreach (glob($this->baseConfigPath . '*.php') as $path) {
            $name = basename($path, '.php');
            $this->loadConfig($name);
        }
    }

    /** @return mixed[] */
    private function loadConfig(string $name): array
    {
        if (!array_key_exists($name, $this->config)) {
            $configValues = require $this->baseConfigPath . $name . '.php';

            if (is_array($configValues)) {
                $this->config[$name] = $configValues;
                $this->mergeEnvironmentConfig($name, $configValues);

                if (array_key_exists($name, $this->envConfigValues) && is_iterable($this->envConfigValues[$name])) {
                    $this->mergeConfigEnvFile($name, $this->envConfigValues[$name]);
                }
            } else {
                trigger_error('Failed to load config file: ' . $name, E_USER_WARNING);
                $this->config[$name] = [];
            }
        }

        return $this->config[$name];
    }

    /** @return mixed[] */
    private function getEnvConfigValues(): array
    {
        $envPath = get_template_directory() . '/env.php';

        if (file_exists($envPath)) {
            $envConfigValues = require $envPath;

            if (is_array($envConfigValues)) {
                return $envConfigValues;
            }
        }

        return [];
    }

    private function mergeConfigEnvFile(string $envKey, mixed $envValue): void
    {
        if ($this->get($envKey)) {
            $this->config[$envKey] = ArrayHelper::mergeRecursiveAssoc($this->config[$envKey], $envValue);
        }
    }

    private function mergeEnvironmentConfig(string $key, mixed $originalValue): void
    {
        if (ArrayHelper::isAssoc($originalValue)) {
            // Get current environment
            $currentEnvironment = defined('WP_ENV') ? constant('WP_ENV') : 'dev';

            // Get all settings in 'env' variable
            $environmentConfigs = $originalValue['env'] ?? null;

            if ($environmentConfigs) {
                $explicitEnvironmentConfigs = [];

                foreach ($environmentConfigs as $environmentKey => $environmentConfig) {
                    $matched = preg_match('/^!(.*)/', $environmentKey, $matches);

                    if ($matched && !in_array($currentEnvironment, explode('|', $matches[1]))) {
                        $originalValue = ArrayHelper::mergeRecursiveAssoc($originalValue, $environmentConfig);
                    } elseif (!$matched && in_array($currentEnvironment, explode('|', $environmentKey))) {
                        $explicitEnvironmentConfigs[] = $environmentConfig;
                    }
                }

                foreach ($explicitEnvironmentConfigs as $explicitEnvironmentConfig) {
                    $originalValue = ArrayHelper::mergeRecursiveAssoc($originalValue, $explicitEnvironmentConfig);
                }
            }

            // Set config
            $this->config[$key] = $originalValue;
        }
    }

    /**
     * @param string $key
     * @return object|\Illuminate\Support\Collection|string|float|int|bool|null|mixed[]|\OffbeatWP\Config\Config
     */
    public function get(string $key, bool $collect = true)
    {
        $keys = explode('.', $key);
        if (!$keys[0]) {
            return null;
        }

        $result = ArrayHelper::getValueFromStringArray($keys, $this->config);

        if (is_array($result)) {
            return $collect ? collect($result) : $result;
        }

        return $result;
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
