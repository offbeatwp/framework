<?php

namespace OffbeatWP\Support\Wordpress;

use DateTimeImmutable;
use OffbeatWP\Support\Traits\WpDateTimeTrait;

/**
 * An extension of the DateTimeImmutable class.<br>
 * When instantiated, it will default to using the format and timezone defined by the WordPress blog.<br>
 * Additionally, the methods called by this class will throw exceptions when invalid data is provided rather than returning false.
 */
final class WpDateTimeImmutable extends DateTimeImmutable
{
    use WpDateTimeTrait;

    /**
     * Alters the timestamp
     * @param string $modifier <p>A date/time string. Valid formats are explained in
     * @return WpDateTimeImmutable Returns the newly created object. Throws Exception on failure.
     * @link https://secure.php.net/manual/en/datetime.formats.php
     */
    public function modify(string $modifier): WpDateTimeImmutable
    {
        $result = parent::modify($modifier);
        if ($result === false) {
            throw static::getMalformedStringException();
        }

        return $result;
    }
}
