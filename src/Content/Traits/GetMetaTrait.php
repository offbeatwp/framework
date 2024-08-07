<?php

namespace OffbeatWP\Content\Traits;

use Exception;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use OffbeatWP\Content\Post\PostModel;
use OffbeatWP\Support\Wordpress\WpDateTime;
use OffbeatWP\Support\Wordpress\WpDateTimeImmutable;

trait GetMetaTrait
{
    /**
     * @param string $key
     * @param scalar|mixed[]|null $defaultValue
     * @param bool $single
     * @return mixed
     * @internal
     */
    private function getRawMetaValue(string $key, $defaultValue, bool $single = true)
    {
        if (array_key_exists($key, $this->metaToUnset)) {
            return $defaultValue;
        }

        if (array_key_exists($key, $this->metaInput)) {
            return $this->metaInput[$key];
        }

        $metas = $this->getMetas();
        if ($metas && array_key_exists($key, $metas) && is_array($metas[$key])) {
            return ($single) ? reset($metas[$key]) : $metas[$key];
        }

        return $defaultValue;
    }

    /**
     * Returns the metaInput value if one with the given key exists.<br>
     * If not, returns the meta value with the given key from the database.<br>
     * If the value isn't in metaInput or the database, <i>null</i> is returned.
     * @param string $key
     * @return mixed
     */
    public function getMetaValue(string $key)
    {
        return $this->getRawMetaValue($key, null);
    }

    /**
     * Check if a meta value exists at all.
     * @return bool True if the meta key exists, regardless of it's value. False if the meta key does not exist.
     */
    public function hasMeta(string $key): bool
    {
        if (array_key_exists($key, $this->metaToUnset)) {
            return false;
        }

        if (array_key_exists($key, $this->metaInput)) {
            return true;
        }

        $metas = $this->getMetas();
        return ($metas && array_key_exists($key, $metas));
    }

    /**
     * Retrieve a meta value as a string.<br>
     * If the meta value does not exist then an <b>empty string</b> is returned.
     */
    public function getMetaString(string $key): string
    {
        return (string)$this->getRawMetaValue($key, '');
    }

    /**
     * Retrieve a meta value as an integer.<br>
     * If the meta value does not exist then <b>0</b> is returned.
     */
    public function getMetaInt(string $key): int
    {
        return (int)$this->getRawMetaValue($key, 0);
    }

    /**
     * Retrieve a meta value as a floating point number.<br>
     * If the meta value does not exist then <b>0</b> is returned.
     */
    public function getMetaFloat(string $key): float
    {
        return (float)$this->getRawMetaValue($key, 0);
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
        return (bool)$this->getRawMetaValue($key, false);
    }

    /**
     * Retrieve a meta value as an array.<br>
     * If the meta value does not exist then <b>an empty array</b> is returned.
     * @return mixed[]
     */
    public function getMetaArray(string $key, bool $single = true): array
    {
        $value = $this->getRawMetaValue($key, [], $single);
        return ($single && is_serialized($value)) ? unserialize($value, ['allowed_classes' => false]) : (array)$value;
    }

    /** @return PostModel[] */
    public function getMetaPostModels(string $key): array
    {
        $models = [];

        foreach ($this->getMetaArray($key) as $id) {
            if ($id) {
                $model = offbeat('post')->get($id);

                if ($model) {
                    $models[] = $model;
                }
            }
        }

        return $models;
    }

    /**
     * @deprecated Use <i>collect($model->getMetas())</i> instead
     * @return Collection<int|string, mixed>
     */
    public function getMetaCollection(string $key): Collection
    {
        return collect($this->getMetaArray($key));
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
