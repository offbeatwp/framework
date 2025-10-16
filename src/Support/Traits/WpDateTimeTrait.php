<?php

namespace OffbeatWP\Support\Traits;

use DateMalformedStringException;
use DateTimeInterface;
use DateTimeZone;

trait WpDateTimeTrait
{
    /** Return the current DateTime. */
    public static function now(?DateTimeZone $timezone = null): static
    {
        return new static('now', $timezone);
    }

    /**
     * Retrieves the date, in localized format. Defaults to the format defined in the blog settings.
     * <br>Note that utilises the wp_date method. As such, the output is affected by the <b>wp_date</b> filter but NOT the <b>date_i18n</b> filter.
     * @param string $format Format in which to retrieve the date. When omitted uses the configured WordPress date.
     */
    public function i18n(string $format = ''): string
    {
        /** @var string */
        return wp_date($format ?: $this->getWpDateFormat() . ' ' . $this->getWpTimeFormat(), $this->getTimestamp());
    }

    public function setYear(int $year): static
    {
        return $this->setDate($year, $this->getMonth(), $this->getDay());
    }

    /** @param int<1, 12> $month Numeric representation of a month from 1 through 12. */
    public function setMonth(int $month): static
    {
        return $this->setDate($this->getYear(), $month, $this->getDay());
    }

    /** @param int<1, 31> $day Numeric representation of a day from 1 through 31. */
    public function setDay(int $day): static
    {
        return $this->setDate($this->getYear(), $this->getMonth(), $day);
    }

    /** @param int<0, 23> $hour 24-hour format of an hour. 0 through 23. */
    public function setHour(int $hour): static
    {
        return $this->setTime($hour, $this->getMinute(), $this->getSecond(), $this->getMicro());
    }

    /** @param int<0, 59> $minute 0 through 59. */
    public function setMinute(int $minute): static
    {
        return $this->setTime($this->getHour(), $minute, $this->getSecond(), $this->getMicro());
    }

    /** @param int<0, 59> $second 0 through 59. */
    public function setSecond(int $second): static
    {
        return $this->setTime($this->getHour(), $this->getMinute(), $second, $this->getMicro());
    }

    /** @param int $micro 0 through 999999. */
    public function setMicro(int $micro): static
    {
        return $this->setTime($this->getHour(), $this->getMinute(), $this->getSecond(), $micro);
    }

    /** Set the time to the start of the day. (00:00:00) */
    public function startOfDay(): static
    {
        return $this->setTime(0, 0);
    }

    /** Set the time to the end of the day. (23:59:59) */
    public function endOfDay(): static
    {
        return $this->setTime(23, 59, 59, 999999);
    }

    /** Set the date and time to the start of the month. */
    public function startOfMonth(): static
    {
        return $this->setDay(1)->startOfDay();
    }

    /** Set the date and time to the end of the month. */
    public function endOfMonth(): static
    {
        return $this->setDay($this->getDaysInMonth())->endOfDay();
    }

    /**
     * Returns date formatted according to given format.<br>
     * Unlike the DateTime format method, the $format parameter can be omitted to use the format specified by the <b>date_format</b> blog setting.
     * @link https://php.net/manual/en/datetime.format.php
     */
    public function format(string $format = ''): string
    {
        return parent::format($format ?: $this->getWpDateFormat() . ' ' . $this->getWpTimeFormat());
    }

    public function formatDate(): string
    {
        return $this->format($this->getWpDateFormat());
    }

    public function formatTime(): string
    {
        return $this->format($this->getWpTimeFormat());
    }

    private function getWpDateFormat(): string
    {
        return owp_get_option_string('date_format') ?: 'Y-m-d';
    }

    private function getWpTimeFormat(): string
    {
        return owp_get_option_string('time_format') ?: 'H:i:s';
    }

    /** @return int A full numeric representation of a year, at least 4 digits, with - for years BCE. */
    public function getYear(): int
    {
        return (int)$this->format('Y');
    }

    /** @return int<1, 12> Numeric representation of a month from 1 through 12. */
    public function getMonth(): int
    {
        /** @var int<1, 12> */
        return (int)$this->format('n');
    }

    /** @return int<1, 31> Day of the month. 1 through 31. */
    public function getDay(): int
    {
        /** @var int<1, 31> */
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
        /** @var int<0, 59> */
        return (int)$this->format('i');
    }

    /** @return int<0, 59> Seconds. 0 through 59. */
    public function getSecond(): int
    {
        /** @var int<0, 59> */
        return (int)$this->format('s');
    }

    /** @return int<0, 999999> Microseconds. 0 through 999999. */
    public function getMicro(): int
    {
        /** @var int<0, 999999> */
        return (int)$this->format('u');
    }

    /** @return int<28, 31> The number of days in the given month. 28 through 31 */
    public function getDaysInMonth(): int
    {
        return (int)$this->format('t');
    }

    /** @return int<0, 365> The day of the year starting from zero. 0 through 365 */
    public function getDayOfYear(): int
    {
        /** @var int<0, 365> */
        return (int)$this->format('z');
    }

    /** @return int<1, 53> ISO 8601 week number of year, weeks starting on Monday. 1 through 53 */
    public function getWeekOfYear(): int
    {
        /** @var int<1, 53> */
        return (int)$this->format('W');
    }

    public function isLeapYear(): bool
    {
        return (bool)$this->format('L');
    }

    public function isToday(): bool
    {
        $today = self::now();
        return ($today->getYear() === $this->getYear() && $today->getDayOfYear() === $this->getDayOfYear());
    }

    public function isPast(): bool
    {
        return $this < self::now();
    }

    public function isFuture(): bool
    {
        return $this > self::now();
    }

    /** Set the timezone to the local of wp_timezone(). */
    public function setToWpTimezone(): static
    {
        return $this->setTimezone(wp_timezone());
    }

    public function __toString(): string
    {
        return $this->format('Y-m-d H:i:s');
    }

    /** Will attempt create a WpDateTime object from the passed variable. */
    public static function make(string|DateTimeInterface $datetime, ?DateTimeZone $timezone = null): static
    {
        if (is_string($datetime)) {
            return new static($datetime, $timezone);
        }

        return static::createFromInterface($datetime);
    }

    private static function getMalformedStringException(): DateMalformedStringException
    {
        return new DateMalformedStringException('Invalid date modify string');
    }
}
