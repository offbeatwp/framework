<?php

namespace OffbeatWP\Config;

use OffbeatWP\Foundation\App;
use OffbeatWP\Helpers\ArrayHelper;

final class Config
{
    private readonly App $app;
    /** @var iterable<mixed> */
    private readonly iterable $envConfigValues;
    /** @var array<string, mixed[]> */
    private array $config;

    public function __construct(App $app)
    {
        $this->app = $app;
        $this->envConfigValues = $this->getEnvConfigValues();
        $this->config = [];

        foreach (glob($this->app->configPath() . '/*.php') as $configFile) {
            $configValues = require $configFile;

            if (is_array($configValues)) {
                $this->config[basename($configFile, '.php')] = $configValues;
            }
        }

        $this->config = $this->loadConfigEnvFiles($this->config);
        $this->config = $this->loadEnvironmentConfig($this->config);
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

    /**
     * @param array<string, mixed[]> $config
     * @return array<string, mixed[]>
     */
    protected function loadConfigEnvFiles(array $config): array
    {
        foreach ($this->envConfigValues as $key => $value) {
            if ($this->get($key)) {
                $config[$key] = ArrayHelper::mergeRecursiveAssoc($config[$key], $value);
            }
        }

        return $config;
    }

    /**
     * @param array<string, mixed[]> $config
     * @return array<string, mixed[]>
     */
    protected function loadEnvironmentConfig(array $config): array
    {
        foreach ($config as $configKey => $configSet) {
            if (ArrayHelper::isAssoc($configSet)) {
                // Get current environment
                $currentEnvironment = defined('WP_ENV') ? WP_ENV : 'dev';

                // Get all settings in 'env' variable
                $environmentConfigs = $configSet['env'] ?? null;

                if ($environmentConfigs) {
                    $explicitEnvironmentConfigs = [];

                    foreach ($environmentConfigs as $environmentKey => $environmentConfig) {
                        $matched = preg_match('/^!(.*)/', $environmentKey, $matches);

                        if ($matched && !in_array($currentEnvironment, explode('|', $matches[1]))) {
                            $configSet = ArrayHelper::mergeRecursiveAssoc($configSet, $environmentConfig);
                        } elseif (!$matched && in_array($currentEnvironment, explode('|', $environmentKey))) {
                            $explicitEnvironmentConfigs[] = $environmentConfig;
                        }
                    }

                    foreach ($explicitEnvironmentConfigs as $explicitEnvironmentConfig) {
                        $configSet = ArrayHelper::mergeRecursiveAssoc($configSet, $explicitEnvironmentConfig);
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
