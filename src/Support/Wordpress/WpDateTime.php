<?php

namespace OffbeatWP\Support\Wordpress;

use DateTime;
use DateTimeInterface;
use DateTimeZone;
use OffbeatWP\Support\Traits\WpDateTimeTrait;
use TypeError;

/**
 * An extension of the DateTime class.<br>
 * When instantiated, it will default to using the format and timezone defined by the WordPress blog.<br>
 * Additionally, the methods called by this class will throw exceptions when invalid data is provided rather than returning false.
 */
final class WpDateTime extends DateTime
{
    use WpDateTimeTrait;

    public static function createFromInterface(DateTimeInterface $object): WpDateTime
    {
        return new static($object->format('Y-m-d H:i:s.u'), $object->getTimezone() ?: null);
    }

    /**
     * Returns new WpDateTimeImmutable object formatted according to the specified format.<br>
     * Throws exception is no date object can be created.
     * @param string $format
     * @param string $datetime
     */
    public static function createFromFormat($format, $datetime, ?DateTimeZone $timezone = null): WpDateTime
    {
        if (!is_string($format) || !is_string($datetime)) {
            throw new TypeError('WpDateTime::createFromFormat expects the $format and $datetime arguments to be strings.');
        }

        $object = parent::createFromFormat($format, $datetime, $timezone);
        if (!$object) {
            throw static::getLastDateException('Could not create DateTime from format: ');
        }

        return self::createFromInterface($object);
    }

    /**
     * Alter the timestamp of a DateTime object by incrementing or decrementing
     * in a format accepted by strtotime().
     * @param string $modifier A date/time string. Valid formats are explained in <a href="https://secure.php.net/manual/en/datetime.formats.php">Date and Time Formats</a>.
     * @return static Returns the DateTime object for method chaining. Throws Exception on failure.
     * @link https://php.net/manual/en/datetime.modify.php
     */
    public function modify($modifier): WpDateTime
    {
        $result = parent::modify($modifier);
        if ($result === false) {
            throw static::getLastDateException();
        }

        return $result;
    }
}