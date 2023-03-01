<?php

namespace OffbeatWP\Support\Wordpress;

use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use InvalidArgumentException;
use OffbeatWP\Support\Traits\WpDateTimeTrait;

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
        parent::__construct($datetime, $timezone ?: wp_timezone());
    }

    public static function createFromInterface(DateTimeInterface $object): WpDateTimeImmutable
    {
        return new static($object->format('Y-m-d H:i:s.u'), $object->getTimezone() ?: null);
    }

    /**
     * Alters the timestamp
     * @link https://secure.php.net/manual/en/datetimeimmutable.modify.php
     * @param string $modifier <p>A date/time string. Valid formats are explained in
     * {@link https://secure.php.net/manual/en/datetime.formats.php Date and Time Formats}.</p>
     * @return static Returns the newly created object. Throws InvalidArgumentException on failure.
     * Returns the {@link https://secure.php.net/manual/en/class.datetimeimmutable.php DateTimeImmutable} object for method chaining or <b>FALSE</b> on failure.
     */
    public function modify($modifier): WpDateTimeImmutable
    {
        $result = parent::modify($modifier);
        if (!$result) {
            throw new InvalidArgumentException('Invalid DateTime modifier: ' . $modifier);
        }

        return $result;
    }
}