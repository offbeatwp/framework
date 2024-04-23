<?php

namespace OffbeatWP\Components;

use DateTimeZone;
use Exception;
use JsonSerializable;
use OffbeatWP\Support\Wordpress\WpDateTime;
use stdClass;

final class ComponentSettings implements JsonSerializable
{
    /** @var array<string, scalar|stdClass|mixed[]> */
    private array $attributes;

    public function __construct(object $args)
    {
        $this->attributes = array_filter(get_object_vars($args), fn($v) => $v !== null);
    }

    /**
     * Set a value manually.
     * @param non-empty-string $key
     * @param string|bool|int|float|stdClass|mixed[] $value
     */
    public function set(string $key, string|bool|int|float|stdClass|array $value): void
    {
        $this->attributes[$key] = $key;
    }

    public function unset(string $key): void
    {
        if (array_key_exists($key, $this->attributes)) {
            unset($this->attributes[$key]);
        }
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->attributes);
    }

    /**
     * Returns the value of the component setting or the default value of the setting if it does not exist.
     * @param non-empty-string $key
     * @return scalar|stdClass|mixed[]|null
     */
    public function get(string $key): string|bool|int|float|stdClass|array|null
    {
        if (array_key_exists($key, $this->attributes)) {
            return $this->attributes[$key];
        }

        return $this->defaultValues[$key] ?? null;
    }

    /**
     * Returns the value of the component setting and casts it to a boolean.
     * @param non-empty-string $key The index of the value to retrieve.
     * @param bool $default Value to return if the value does not exist or is non-scalar.
     * @return bool
     */
    public function getBool(string $key, bool $default = false): bool
    {
        $value = $this->get($key);
        return is_scalar($value) ? (bool)$value : $default;
    }

    /**
     * Returns the value of the component setting and casts it to a string.
     * @param non-empty-string $key
     * @param string $default Value to return if the value does not exist or is non-scalar.
     * @return string
     */
    public function getString(string $key, string $default = ''): string
    {
        $value = $this->get($key);
        return is_scalar($value) ? (string)$value : $default;
    }

    /**
     * Returns the value of the component setting and casts it to a float.
     * @param non-empty-string $key
     * @param float $default Value to return if the value does not exist or is non-numeric.
     * @return float
     */
    public function getFloat(string $key, float $default = 0): float
    {
        $value = $this->get($key);
        return is_numeric($value) ? (float)$value : $default;
    }

    /**
     * Returns the value of the component setting and casts it to an int.
     * @param non-empty-string $key
     * @param int $default Value to return if the value does not exist or is non-numeric.
     * @return int
     */
    public function getInt(string $key, int $default = 0): int
    {
        $value = $this->get($key);
        return is_numeric($value) ? (int)$value : $default;
    }

    /**
     * Returns the value of the component setting.<br>
     * Timezone will default to <b>UTC</b> if not specified in the second parameter.<br>
     * Will return <i>NULL</i> if DateTime is not valid.
     */
    public function getDateTime(string $key, ?DateTimeZone $timeZone = null): ?WpDateTime
    {
        $datetime = $this->getString($key);
        if (!$datetime) {
            return null;
        }

        try {
            return WpDateTime::make($datetime, $timeZone ?? new DateTimeZone('UTC'));
        } catch (Exception) {
            return null;
        }
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
            } elseif ($item !== null) {
                $str = implode(',', array_map('urlencode', (array)$item));
            }

            if ($str) {
                $parameters[] = $key . '=' . $str;
            }
        }

        return implode('&', $parameters);
    }

    /** @return array<string, scalar|stdClass|mixed[]> */
    public function jsonSerialize(): array
    {
        return $this->attributes;
    }
}