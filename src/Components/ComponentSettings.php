<?php

namespace OffbeatWP\Components;

#[\AllowDynamicProperties]
final class ComponentSettings
{
    private array $defaultValues;

    /**
     * @param object $args
     * @param array $defaultValues
     */
    public function __construct($args, array $defaultValues = [])
    {
        foreach (get_object_vars($args) as $key => $value) {
            $this->$key = $value;
        }

        $this->defaultValues = $defaultValues;
    }

    /**
     * Set a value manually.
     * @param string $index
     * @param mixed $value
     */
    public function set(string $index, $value): void
    {
        $this->$index = $value;
    }

    /**
     * Returns the value of the component setting or the default value of the setting if it does not exist.
     * @param string $index The index of the value to retrieve.
     * @return mixed
     */
    public function get(string $index)
    {
        if (property_exists($this, $index)) {
            return $this->$index;
        }

        return $this->defaultValues[$index] ?? null;
    }

    /** Returns the value of the component setting and parses it to a boolean. */
    public function getBool(string $index, bool $default = false): bool
    {
        $value = $this->get($index);
        return is_scalar($value) ? (bool)$value : $default;
    }

    /** Returns the value of the component setting and parses it to a string. */
    public function getString(string $index, string $default = ''): string
    {
        $value = $this->get($index);
        return is_scalar($value) ? (string)$value : $default;
    }

    /** Returns the value of the component setting and parses it to a float. */
    public function getFloat(string $index, float $default = 0): float
    {
        $value = $this->get($index);
        return is_numeric($value) ? (float)$value : $default;
    }

    /** Returns the value of the component setting and parses it to an int. */
    public function getInt(string $index, int $default = 0): int
    {
        $value = $this->get($index);
        return is_numeric($value) ? (int)$value : $default;
    }

    /**
     * Return the value form a url param.
     * If the url param does not exist, the component setting value is returned instead.
     * @param string $index
     * @return scalar|array|null
     */
    public function getUrlParam(string $index)
    {
        return $_GET[$index] ?? $this->get($index);
    }
}