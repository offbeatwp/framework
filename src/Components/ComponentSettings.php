<?php

namespace OffbeatWP\Components;

use Illuminate\Support\Collection;

#[\AllowDynamicProperties]
final class ComponentSettings
{
    private array $defaultValues;

    /**
     * @param object $args
     * @param mixed[] $defaultValues
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
     * @deprecated Returns the value of the component setting and casts it to an array.
     * @param string $index
     * @return string[]
     */
    public function getArray(string $index): array
    {
        return (array)$this->get($index);
    }

    /**
     * Return the value form a url param.
     * If the url param does not exist, the component setting value is returned instead.
     * @param string $index
     * @return mixed
     */
    public function getUrlParam(string $index)
    {
        return $_GET[$index] ?? $this->get($index);
    }

    /**
     * Convert the given keys to URL parameters.<br>
     * Values will be url-encoded.
     * @param string[] $keys
     * @return string
     */
    public function toUrlParams(array $keys): string
    {
        $parameters = [];

        foreach ($keys as $key) {
            $item = $this->get($key);

            $str = '';
            if (is_scalar($item)) {
                $str = urlencode($item);
            } elseif (is_array($item)) {
                $str = implode(',', array_map('urlencode', $item));
            } elseif ($item instanceof Collection) {
                $str = $item->map('urlencode')->implode(',');
            } elseif ($item !== null) {
                trigger_error($key . ' could not be parsed to URL param!', E_USER_WARNING);
            }

            if ($str) {
                $parameters[] = $key . '=' . $str;
            }
        }

        return implode('&', $parameters);
    }

    public function only(iterable $keys): array
    {
        $new = [];

        foreach ($keys as $key) {
            $value = $this->get('key');

            if ($value !== null) {
                $new[$key] = $value;
            }
        }

        return $new;
    }
}