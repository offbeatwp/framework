<?php

namespace OffbeatWP\Support\Wordpress;

use DateTime;
use OffbeatWP\Support\Traits\WpDateTimeTrait;

/**
 * An extension of the DateTime class.<br>
 * When instantiated, it will default to using the format and timezone defined by the WordPress blog.<br>
 * Additionally, the methods called by this class will throw exceptions when invalid data is provided rather than returning false.
 */
final class WpDateTime extends DateTime
{
    use WpDateTimeTrait;

    /**
     * Alter the timestamp of a DateTime object by incrementing or decrementing
     * in a format accepted by strtotime().
     * @param string $modifier A date/time string. Valid formats are explained in <a href="https://secure.php.net/manual/en/datetime.formats.php">Date and Time Formats</a>.
     * @return WpDateTime Returns the DateTime object for method chaining. Throws Exception on failure.
     * @link https://php.net/manual/en/datetime.modify.php
     */
    public function modify($modifier): WpDateTime
    {
        $result = parent::modify($modifier);
        if ($result === false) {
            throw static::getMalformedStringException();
        }

        return $result;
    }
}
