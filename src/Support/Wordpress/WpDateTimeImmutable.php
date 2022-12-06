<?php

namespace OffbeatWP\Support\Wordpress;

use DateTimeImmutable;
use DateTimeZone;
use OffbeatWP\Support\Traits\WpDateTimeTrait;

/**
 * An extension of the DateTimeImmutable class.<br>
 * When instantiated, it will default to using the format and timezone defined by the WordPress blog.<br>
 */
final class WpDateTimeImmutable extends DateTimeImmutable
{
    use WpDateTimeTrait;

    public function __construct(string $datetime = 'now', ?DateTimeZone $timezone = null)
    {
        parent::__construct($datetime, $timezone ?: wp_timezone());
    }
}