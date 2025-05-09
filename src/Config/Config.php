<?php

namespace OffbeatWP\Config;

use OffbeatWP\Foundation\App;
use OffbeatWP\Helpers\ArrayHelper;

final class Config
{
    private readonly App $app;
    /** @var array<string, mixed[]> */
    private array $config;

    public function __construct(App $app)
    {
        $this->app = $app;
        $envConfigValues = $this->getEnvConfigValues();
        $this->config = [];

        foreach (glob($this->app->configPath() . '/*.php') as $path) {
            $name = basename($path, '.php');
            $this->loadConfig($path, $name, $envConfigValues);
        }
    }

    private function loadConfig(string $path, string $name, array $envConfigValues): void
    {
        $configValues = require $path;

        if (is_array($configValues)) {
            $this->config[$name] = $configValues;
            $this->mergeEnvironmentConfig($name, $configValues);

            if (array_key_exists($name, $envConfigValues) && is_iterable($envConfigValues[$name])) {
                $this->mergeConfigEnvFile($name, $envConfigValues[$name]);
            }
        } else {
            trigger_error('Failed to load config file: ' . $path, E_USER_WARNING);
        }
    }

    /** @return iterable<mixed> */
    private function getEnvConfigValues(): iterable
    {
        $envPath = get_template_directory() . '/env.php';

        if (file_exists($envPath)) {
            $envConfigValues = require $envPath;

            if (is_iterable($envConfigValues)) {
                return $envConfigValues;
            }
        }

        return [];
    }

    /** @return array<string, mixed[]> */
    protected function loadConfigEnvFiles(array $envConfigValues): array
    {
        foreach ($envConfigValues as $envKey => $envValue) {
            $this->mergeConfigEnvFile($envKey, $envValue);
        }

        return $this->config;
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
        $result = ArrayHelper::getValueFromDottedKey($key, $this->config);

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
