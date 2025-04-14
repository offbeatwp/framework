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
    private array $config = [];

    public function __construct(App $app)
    {
        $this->app = $app;
        $this->envConfigValues = $this->getEnvConfigValues();

        // Load all configs
        foreach (glob($this->app->configPath() . '/*.php') as $configFile) {
            $configData = require $configFile;

            if (is_array($configData)) {
                $this->config[basename($configFile, '.php')] = $configData;
            }
        }

        $this->config = $this->loadConfigEnvFiles($this->config);
        $this->config = $this->loadConfigEnvs($this->config);
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
     * @param mixed[] $config
     * @return mixed[]
     */
    protected function loadConfigEnvFiles(array $config): array
    {
        foreach ($this->envConfigValues as $envKey => $envValue) {
            $config[$envKey] = $this->loadConfigEnvFile($envKey, $envValue);
        }

        return $config;
    }

    /**
     * @param mixed[] $envValue
     * @return mixed[]
     */
    private function loadConfigEnvFile(string $envKey, array $envValue): array
    {
        return $this->get($envKey) ? ArrayHelper::mergeRecursiveAssoc($envValue, $this->config[$envKey]) : $envValue;
    }

    /**
     * @param mixed[] $config
     * @return mixed[]
     */
    protected function loadConfigEnvs(array $config): array
    {
        foreach ($config as $configKey => $configSet) {
            if (ArrayHelper::isAssoc($configSet)) {
                $config[$configKey] = $this->loadConfigEnv($configSet);
            }
        }

        return $config;
    }

    /**
     * @param mixed[] $configSet
     * @return mixed[]
     */
    private function loadConfigEnv(array $configSet): array
    {
        // Get all settings in 'env' variable
        $envConfigs = $configSet['env'] ?? null;

        if (is_iterable($envConfigs)) {
            $currentEnvironment = defined('WP_ENV') ? WP_ENV : 'dev';
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

        return $configSet;
    }

    /** @return object|\Illuminate\Support\Collection|string|float|int|bool|null|mixed[]|\OffbeatWP\Config\Config */
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
