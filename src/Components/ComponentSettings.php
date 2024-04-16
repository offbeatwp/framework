<?php

namespace OffbeatWP\Components;

use DateTimeZone;
use Exception;
use JsonSerializable;
use OffbeatWP\Support\Wordpress\WpDateTime;

#[\AllowDynamicProperties]
final class ComponentSettings implements JsonSerializable
{
    private array $defaultValues;
    private array $attributes = [];

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
        $this->attributes[$index] = $value;
    }

    /**
     * Returns the value of the component setting or the default value of the setting if it does not exist.
     * @param string $index The index of the value to retrieve.
     * @return mixed
     */
    public function get(string $index)
    {
        if (array_key_exists($index, $this->attributes)) {
            return $this->attributes[$index];
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
     * Returns the value of the component setting.<br>
     * Timezone will default to <b>UTC</b> if not specified in the second parameter.<br>
     * Will return <i>NULL</i> if DateTime is not valid.
     */
    public function getDateTime(string $index, ?DateTimeZone $timeZone = null): ?WpDateTime
    {
        $datetime = $this->getString($index);
        if (!$datetime) {
            return null;
        }

        try {
            return WpDateTime::make($datetime, $timeZone ?? new DateTimeZone('UTC'));
        } catch (Exception $e) {
            return null;
        }
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
            } elseif ($item !== null) {
                trigger_error($key . ' could not be parsed to URL param!', E_USER_WARNING);
            }

            if ($str) {
                $parameters[] = $key . '=' . $str;
            }
        }

        return implode('&', $parameters);
    }

    /**
     * Return an array with values of the given keys<br>
     * NULL values will not be returned
     * @param string[] $keys
     * @return mixed[]
     */
    public function toArray(iterable $keys): array
    {
        $new = [];

        foreach ($keys as $key) {
            $value = $this->get($key);

            if ($value !== null) {
                $new[$key] = $value;
            }
        }

        return $new;
    }

    public function jsonSerialize(): array
    {
        $output = [];

        foreach ($this->attributes as $key => $v) {
            $value = $this->get($key);

            if ($value !== null) {
                $output[$key] = $value;
            }
        }

        return $output;
    }
}