<?php

namespace OffbeatWP\Content\Traits;

use Exception;
use InvalidArgumentException;
use OffbeatWP\Support\Wordpress\WpDateTime;
use OffbeatWP\Support\Wordpress\WpDateTimeImmutable;

trait GetMetaTrait
{
    private function getScalarMeta(string $key, int $filter): null|string|int|float|bool
    {
        return filter_var($this->getMeta($key), $filter);
    }

    /**
     * Retrieve a meta value as a string.<br>
     * If the meta value does not exist then an <b>empty string</b> is returned.
     */
    public function getMetaString(string $key): string
    {
        return (string)$this->getScalarMeta($key, FILTER_DEFAULT);
    }

    /**
     * Retrieve a meta value as an integer.<br>
     * If the meta value does not exist then <b>0</b> is returned.
     */
    public function getMetaInt(string $key): int
    {
        return (int)$this->getScalarMeta($key, FILTER_VALIDATE_INT);
    }

    /**
     * Retrieve a meta value as a floating point number.<br>
     * If the meta value does not exist then <b>0</b> is returned.
     */
    public function getMetaFloat(string $key): float
    {
        return (float)$this->getScalarMeta($key, FILTER_VALIDATE_FLOAT);
    }

    /**
     * Retrieve a meta value as a localised formatted date string.
     * @param string $key Meta key.
     * @param string $format The date format. If not specified, will default to the date_format WordPress option.
     * @return string <b>Formatted date string</b> if the meta key exists and is a valid date. Otherwise, an <b>empty string</b> is returned.
     */
    public function getMetaDate(string $key, string $format = ''): string
    {
        $strDate = strtotime($this->getMetaString($key));

        if ($strDate) {
            $dateFormat = $format ?: get_option('date_format') ?: 'Y-m-d H:i:s';
            return date_i18n($dateFormat, $strDate);
        }

        return '';
    }

    /**
     * Attempt to retrieve a meta value as a WpDateTime object.<br>
     * If no meta exists or if conversion fails, <i>null</i> will be returned.
     * @param string $key Meta key.
     * @return WpDateTime|null
     */
    public function getMetaDateTime(string $key): ?WpDateTime
    {
        $datetime = $this->getMetaString($key);
        if (!$datetime) {
            return null;
        }

        try {
            return WpDateTime::make($datetime);
        } catch (Exception) {
            return null;
        }
    }

    /**
     * Attempt to retrieve a meta value as a WpDateTimeImmuteable object.<br>
     * If no meta exists or if conversion fails, <i>null</i> will be returned.
     * @param string $key Meta key.
     * @return WpDateTimeImmutable|null
     */
    public function getMetaDateTimeImmuteable(string $key): ?WpDateTimeImmutable
    {
        $datetime = $this->getMetaString($key);
        if (!$datetime) {
            return null;
        }

        try {
            return WpDateTimeImmutable::make($datetime);
        } catch (Exception) {
            return null;
        }
    }

    /**
     * Retrieve a meta value as a boolean.<br>
     * If the meta value does not exist then <b>false</b> is returned.
     */
    public function getMetaBool(string $key): bool
    {
        return (bool)$this->getScalarMeta($key, FILTER_VALIDATE_BOOL);
    }

    /**
     * Retrieve a meta value as an array.<br>
     * If the meta value does not exist then <b>an empty array</b> is returned.
     * @return mixed[]
     */
    public function getMetaArray(string $key, bool $single = true): array
    {
        $value = $this->getMeta($key, $single);
        return $single && is_serialized($value) ? unserialize($value, ['allowed_classes' => false]) : (array)$value;
    }

    /**
     * @param string $metaKey
     * @param mixed[] $shape
     * @return mixed[]
     */
    public function getMetaRepeater(string $metaKey, array $shape): array
    {
        $output = [];

        $l = $this->getMetaInt($metaKey);

        for ($i = 0; $i < $l; $i++) {
            $output[$i] = [];

            foreach ($shape as $key => $value) {
                $fullKey = $metaKey . '_' . $i . '_' . $key;
                $output[$i][$key] = is_array($value) ? $this->getMetaRepeater($fullKey, $value) : $this->getMetaX($fullKey, $value);
            }
        }

        return $output;
    }

    /**
     * @param non-empty-string $metaKey
     * @param "string"|"boolean"|"array"|"integer"|"double"|"float"|"datetime" $type
     * @return scalar|WpDateTime|null|mixed[]
     */
    private function getMetaX(string $metaKey, string $type)
    {
        if ($type === 'string') {
            return $this->getMetaString($metaKey);
        }

        if ($type === 'boolean') {
            return $this->getMetaBool($metaKey);
        }

        if ($type === 'array') {
            return $this->getMetaArray($metaKey);
        }

        if ($type === 'integer') {
            return $this->getMetaInt($metaKey);
        }

        if ($type === 'double' || $type === 'float') {
            return $this->getMetaFloat($metaKey);
        }

        if ($type === 'datetime') {
            return $this->getMetaDateTime($metaKey);
        }

        throw new InvalidArgumentException('Invalid type parameter: ' . $type);
    }
}
