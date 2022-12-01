<?php

namespace OffbeatWP\Support\Wordpress;

use DateTime;
use DateTimeInterface;
use DateTimeZone;
use Exception;
use InvalidArgumentException;
use TypeError;

/**
 * An extension of the DateTime class.<br>
 * When instantiated, it will default to using the format and timezone defined by the WordPress blog.<br>
 */
final class WpDateTime extends DateTime
{
    public function __construct(string $datetime = 'now', ?DateTimeZone $timezone = null)
    {
        parent::__construct($datetime, $timezone ?: wp_timezone());
    }

    public static function now(?DateTimeZone $timezone = null): WpDateTime
    {
        return new WpDateTime('now', $timezone);
    }

    /**
     * @param string|int|DateTimeInterface $datetime
     * @param DateTimeZone|null $timezone
     * @return WpDateTime|null
     */
    public static function make($datetime, ?DateTimeZone $timezone = null): ?WpDateTime
    {
        if (is_int($datetime)) {
            $datetime = '@' . $datetime;
        } elseif ($datetime instanceof DateTimeInterface) {
            $ts = $datetime->getTimestamp();
            if ($ts === false) {
                return null;
            }

            $datetime = '@' . $ts;
        }

        try {
            return new WpDateTime($datetime, $timezone);
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Retrieves the date, in localized format. Defaults to the format defined in the blog settings.
     * @param string $format Format in which to retrieve the date.
     * @return string
     */
    public function i18n(string $format = ''): string
    {
        return wp_date($format ?: $this->getWpDateFormat(), $this->getTimestamp());
    }

    /**
     * Returns date formatted according to given format.<br>
     * Unlike the DateTime format method, the $format parameter can be omitted to use the format specified by the <b>date_format</b> blog setting.
     * @param string $format
     * @return string
     * @link https://php.net/manual/en/datetime.format.php
     */
    public function format($format = ''): string
    {
        return parent::format($format ?: $this->getWpDateFormat());
    }

    private function getWpDateFormat(): string
    {
        return get_option('date_format') ?: 'Y-m-d H:i:s';
    }

    /** @return int A full numeric representation of a year, at least 4 digits, with - for years BCE. */
    public function getYear(): int
    {
        return (int)$this->format('Y');
    }

    /** @return int<1, 12> Numeric representation of a month from 1 through 12. */
    public function getMonth(): int
    {
        return (int)$this->format('n');
    }

    /** @return int<1, 31> Day of the month. 1 through 31. */
    public function getDay(): int
    {
        return (int)$this->format('j');
    }

    /** @return int<0, 23> 24-hour format of an hour. 0 through 23. */
    public function getHour(): int
    {
        return (int)$this->format('G');
    }

    /** @return int<0, 59> Minutes. 0 through 59. */
    public function getMinute(): int
    {
        return (int)$this->format('i');
    }

    /** @return int<0, 59> Seconds. 0 through 59. */
    public function getSecond(): int
    {
        return (int)$this->format('s');
    }

    /** @return int<0, 999999> Microseconds. 0 through 999999. */
    public function getMicro(): int
    {
        return (int)$this->format('u');
    }

    public function setYear(int $year): self
    {
        $this->setDate($year, $this->getMonth(), $this->getDay());
        return $this;
    }

    /**
     * @param int<1, 12> $month Numeric representation of a month from 1 through 12.
     * @return $this
     */
    public function setMonth(int $month): self
    {
        if ($month < 1 || $month > 12) {
            throw new TypeError('SetMonth expects an integer in the 1 ~ 12 range.');
        }

        $this->setDate($this->getYear(), $month, $this->getDay());
        return $this;
    }

    /**
     * @param int<1, 31> $day Numeric representation of a day from 1 through 31.
     * @return $this
     */
    public function setDay(int $day): self
    {
        if ($day < 1 || $day > 31) {
            throw new TypeError('SetDay expects an integer in the 1 ~ 31 range.');
        }

        $this->setDate($this->getYear(), $this->getMonth(), $day);
        return $this;
    }

    /**
     * @param int<0, 23> $hour 24-hour format of an hour. 0 through 23.
     * @return $this
     */
    public function setHour(int $hour): self
    {
        if ($hour < 0 || $hour > 23) {
            throw new TypeError('setHour expects an integer in the 0 ~ 23 range.');
        }

        $this->setTime($hour, $this->getMinute(), $this->getSecond(), $this->getMicro());
        return $this;
    }

    /**
     * @param int<0, 59> $minute 0 through 59.
     * @return $this
     */
    public function setMinute(int $minute): self
    {
        if ($minute < 0 || $minute > 59) {
            throw new TypeError('setMinute expects an integer in the 0 ~ 59 range.');
        }

        $this->setTime($this->getHour(), $minute, $this->getSecond(), $this->getMicro());
        return $this;
    }

    /**
     * @param int<0, 59> $second 0 through 59.
     * @return $this
     */
    public function setSecond(int $second): self
    {
        if ($second < 0 || $second > 59) {
            throw new TypeError('setSecond expects an integer in the 0 ~ 59 range.');
        }

        $this->setTime($this->getHour(), $this->getMinute(), $second, $this->getMicro());
        return $this;
    }

    /**
     * @param int<0, 999999> $micro
     * @return $this
     */
    public function setMicro(int $micro): self
    {
        if ($micro < 0 || $micro > 999999) {
            throw new TypeError('setMicro expects an integer in the 0 ~ 999999 range.');
        }

        $this->setTime($this->getHour(), $this->getMinute(), $this->getSecond(), $micro);
        return $this;
    }

    /** @return int<28, 31> The number of days in the given month. 28 through 31 */
    public function getDaysInMonth(): int
    {
        return (int)$this->format('t');
    }

    /**
     * Alter the timestamp of a DateTime object by incrementing or decrementing in a format accepted by strtotime().
     * <br><br><b>Beware:</b> Unlike DateTime's implementation, modify will throw an <i>InvalidArgumentException</i> if an invalid modifier value is given.
     * @param string $modifier A date/time string. Valid formats are explained in <a href="https://secure.php.net/manual/en/datetime.formats.php">Date and Time Formats</a>.
     * @return WpDateTime Returns the DateTime object for method chaining or FALSE on failure.
     * @throws InvalidArgumentException
     * @link https://php.net/manual/en/datetime.modify.php
     */
    public function modify($modifier)
    {
        $result = parent::modify($modifier);
        if (!$result) {
            throw new InvalidArgumentException('Invalid DateTime modifier: ' . $modifier);
        }

        return $result;
    }
}