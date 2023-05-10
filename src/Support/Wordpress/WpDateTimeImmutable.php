<?php

namespace OffbeatWP\Support\Wordpress;

use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use OffbeatWP\Support\Traits\WpDateTimeTrait;
use TypeError;

/**
 * An extension of the DateTimeImmutable class.<br>
 * When instantiated, it will default to using the format and timezone defined by the WordPress blog.<br>
 * Additionally, the methods called by this class will throw exceptions when invalid data is provided rather than returning false.
 */
final class WpDateTimeImmutable extends DateTimeImmutable
{
    use WpDateTimeTrait;

    public function __construct(string $datetime = 'now', ?DateTimeZone $timezone = null)
    {
        if (!$timezone && apply_filters('offbeatwp/date/wpdatetime_defaults_to_wp_timezone', true)) {
            $timezone = wp_timezone();
        }

        parent::__construct($datetime, $timezone);
    }

    public static function createFromInterface(DateTimeInterface $object): WpDateTimeImmutable
    {
        return new static($object->format('Y-m-d H:i:s.u'), $object->getTimezone() ?: null);
    }

    /**
     * Returns new WpDateTimeImmutable object formatted according to the specified format.<br>
     * Throws exception is no date object can be created.
     * @param string $format
     * @param string $datetime
     */
    public static function createFromFormat($format, $datetime, ?DateTimeZone $timezone = null): WpDateTimeImmutable
    {
        if (!is_string($format) || !is_string($datetime)) {
            throw new TypeError('WpDateTimeImmutable::createFromFormat expects the $format and $datetime arguments to be strings.');
        }

        $object = parent::createFromFormat($format, $datetime, $timezone);
        if (!$object) {
            throw static::getLastDateException('Could not create DateTime from format: ');
        }

        return self::createFromInterface($object);
    }

    /**
     * Alters the timestamp
     * @param string $modifier <p>A date/time string. Valid formats are explained in
     * @return static Returns the newly created object. Throws Exception on failure.
     * @link https://secure.php.net/manual/en/datetime.formats.php
     */
    public function modify($modifier): WpDateTimeImmutable
    {
        $result = parent::modify($modifier);
        if ($result === false) {
            throw static::getLastDateException();
        }

        return $result;
    }

//    public function sub(DateInterval $interval): WpDateTimeImmutable
//    {
//        $result = parent::sub($interval);
//        if (!$result) {
//            throw static::getLastDateException();
//        }
//
//        return $result;
//    }
}