<?php

namespace OffbeatWP\Content\Common;

use Exception;
use OffbeatWP\Exceptions\OffbeatModelNotFoundException;
use OffbeatWP\Support\Wordpress\WpDateTime;
use OffbeatWP\Support\Wordpress\WpDateTimeImmutable;

abstract class AbstractOffbeatModel
{
    /** @return positive-int */
    abstract public function getId(): int;

    /** @return mixed[] */
    abstract public function getMetas(): array;

    /**
     * @param string $key
     * @param string|int|float|bool|mixed[]|null $defaultValue
     * @param bool $single
     * @return mixed
     */
    private function getRawMetaValue(string $key, string|int|float|bool|array|null $defaultValue, bool $single = true): mixed
    {
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
    final public function getMetaValue(string $key)
    {
        return $this->getRawMetaValue($key, null);
    }

    /**
     * Check if a meta value exists at all.
     * @return bool True if the meta key exists, regardless of it's value. False if the meta key does not exist.
     */
    final public function hasMeta(string $key): bool
    {
        return array_key_exists($key, $this->getMetas());
    }

    /**
     * Retrieve a meta value as a string.<br>
     * If the meta value does not exist then an <b>empty string</b> is returned.
     */
    final public function getMetaString(string $key): string
    {
        return (string)$this->getRawMetaValue($key, '');
    }

    /**
     * Retrieve a meta value as an integer.<br>
     * If the meta value does not exist then <b>0</b> is returned.
     */
    final public function getMetaInt(string $key): int
    {
        return (int)$this->getRawMetaValue($key, 0);
    }

    /**
     * Retrieve a meta value as a floating point number.<br>
     * If the meta value does not exist then <b>0</b> is returned.
     */
    final public function getMetaFloat(string $key): float
    {
        return (float)$this->getRawMetaValue($key, 0);
    }

    /**
     * Retrieve a meta value as a localised formatted date string.
     * @param string $key Meta key.
     * @param string $format The date format. If not specified, will default to the date_format WordPress option.
     * @return string <b>Formatted date string</b> if the meta key exists and is a valid date. Otherwise, an <b>empty string</b> is returned.
     */
    final public function getMetaDate(string $key, string $format = ''): string
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
    final public function getMetaDateTime(string $key): ?WpDateTime
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
     */
    final public function getMetaDateTimeImmuteable(string $key): ?WpDateTimeImmutable
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
    final public function getMetaBool(string $key): bool
    {
        return (bool)$this->getRawMetaValue($key, false);
    }

    /**
     * Retrieve a meta value as an array.<br>
     * If the meta value does not exist then <b>an empty array</b> is returned.
     * @return mixed[]
     */
    final public function getMetaArray(string $key, bool $single = true): array
    {
        $value = $this->getRawMetaValue($key, [], $single);
        return ($single && is_serialized($value)) ? unserialize($value, ['allowed_classes' => false]) : (array)$value;
    }

    final public static function find(int $id): ?static
    {
        return ($id) ? static::query()->findById($id) : null;
    }

    final public static function first(): ?static
    {
        return static::query()->first();
    }

    final public static function findOrFail(int $id): ?static
    {
        $item = static::find($id);
        if (!$item) {
            throw new OffbeatModelNotFoundException('Could not find ' . static::class . ' model with id ' . $id);
        }

        return $item;
    }

    /** Checks if a model with the given ID exists. */
    final public static function exists(int $id): bool
    {
        if ($id <= 0) {
            return false;
        }

        return static::query()->whereIdIn([$id])->exists();
    }

    /** @return \OffbeatWP\Content\Post\PostQueryBuilder<static>|\OffbeatWP\Content\Taxonomy\TermQueryBuilder<static>|\OffbeatWP\Content\User\UserQueryBuilder<static> */
    abstract public static function query();
}