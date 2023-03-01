<?php

namespace OffbeatWP\Support\Wordpress;

use DateTime;
use DateTimeInterface;
use DateTimeZone;
use OffbeatWP\Support\Traits\WpDateTimeTrait;

/**
 * An extension of the DateTime class.<br>
 * When instantiated, it will default to using the format and timezone defined by the WordPress blog.<br>
 * Additionally, the methods called by this class will throw exceptions when invalid data is provided rather than returning false.
 */
final class WpDateTime extends DateTime
{
    use WpDateTimeTrait;

    public function __construct(string $datetime = 'now', ?DateTimeZone $timezone = null)
    {
        parent::__construct($datetime, $timezone ?: wp_timezone());
    }

    public static function createFromInterface(DateTimeInterface $object): WpDateTime
    {
        return new static($object->format('Y-m-d H:i:s.u'), $object->getTimezone() ?: null);
    }

    /**
     * Returns new WpDateTimeImmutable object formatted according to the specified format.<br>
     * Throws exception is no date object can be created.
     */
    public static function createFromFormat(string $format, string $datetime, ?DateTimeZone $timezone = null): WpDateTime
    {
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
     * @return static Returns the DateTime object for method chaining. Throws InvalidArgumentException on failure.
     * @link https://php.net/manual/en/datetime.modify.php
     */
    public function modify($modifier): WpDateTime
    {
        $result = parent::modify($modifier);
        if (!$result) {
            throw static::getLastDateException();
        }

        return $result;
    }
}