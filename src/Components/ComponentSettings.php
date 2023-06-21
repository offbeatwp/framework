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

    /**
     * Returns the value of the component setting and casts it to a boolean.
     * @param string $index The index of the value to retrieve.
     * @param bool $default Value to return if the value does not exist or is non-scalar.
     * @return bool
     */
    public function getBool(string $index, bool $default = false): bool
    {
        $value = $this->get($index);
        return is_scalar($value) ? (bool)$value : $default;
    }

    /**
     * Returns the value of the component setting and casts it to a string.
     * @param string $index
     * @param string $default Value to return if the value does not exist or is non-scalar.
     * @return string
     */
    public function getString(string $index, string $default = ''): string
    {
        $value = $this->get($index);
        return is_scalar($value) ? (string)$value : $default;
    }

    /**
     * Returns the value of the component setting and casts it to a float.
     * @param string $index
     * @param float $default Value to return if the value does not exist or is non-numeric.
     * @return float
     */
    public function getFloat(string $index, float $default = 0): float
    {
        $value = $this->get($index);
        return is_numeric($value) ? (float)$value : $default;
    }

    /**
     * Returns the value of the component setting and casts it to an int.
     * @param string $index
     * @param int $default Value to return if the value does not exist or is non-numeric.
     * @return int
     */
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