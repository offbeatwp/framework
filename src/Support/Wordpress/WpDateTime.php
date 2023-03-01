<?php

namespace OffbeatWP\Support\Wordpress;

use DateTime;
use DateTimeZone;
use Exception;
use InvalidArgumentException;
use OffbeatWP\Support\Traits\WpDateTimeTrait;

/**
 * An extension of the DateTime class.<br>
 * When instantiated, it will default to using the format and timezone defined by the WordPress blog.<br>
 */
final class WpDateTime extends DateTime
{
    use WpDateTimeTrait;

    public function __construct(string $datetime = 'now', ?DateTimeZone $timezone = null)
    {
        parent::__construct($datetime, $timezone ?: wp_timezone());
    }

    public static function createFromFormat(string $format, string $datetime, ?DateTimeZone $timezone = null): WpDateTime
    {
        $output = DateTime::createFromFormat($format, $datetime, $timezone);
        if (!$output) {
            throw new Exception(reset(DateTime::getLastErrors()['errors']));
        }

        return new WpDateTime($output->format('Y-m-d H:i:s.u'));
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
            throw new InvalidArgumentException('Invalid DateTime modifier: ' . $modifier);
        }

        return $result;
    }
}