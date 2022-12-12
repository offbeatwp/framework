<?php

namespace OffbeatWP\Support\Traits;

use DateTimeInterface;
use DateTimeZone;
use TypeError;

trait WpDateTimeTrait
{
    public static function now(?DateTimeZone $timezone = null): self
    {
        return new self('now', $timezone);
    }

    /**
     * Will attempt create a WpDateTime object from the passed variable.
     * @param non-empty-string|DateTimeInterface $datetime
     * @param DateTimeZone|null $timezone
     * @return self
     */
    public static function make($datetime, ?DateTimeZone $timezone = null): self
    {
        if ($datetime) {
            if (is_string($datetime)) {
                return new self($datetime, $timezone);
            }

            return new self($datetime->format('Y-m-d H:i:s.u'), $datetime->getTimezone());
        }

        throw new TypeError('WpDateTime::make expects a non-empty-string or DateTimeInterface as argument.');
    }

    /**
     * Retrieves the date, in localized format. Defaults to the format defined in the blog settings.
     * <br>Note that utilises the wp_date method. As such, the output is affected by the <b>wp_date</b> filter but NOT the <b>date_i18n</b> filter.
     * @param string $format Format in which to retrieve the date.
     * @return string
     */
    public function i18n(string $format = ''): string
    {
        return wp_date($format ?: $this->getWpDateFormat(), $this->getTimestamp());
    }

    public function setYear(int $year): self
    {
        return $this->setDate($year, $this->getMonth(), $this->getDay());
    }

    /**
     * @param int $month Numeric representation of a month from 1 through 12.
     * @return $this
     */
    public function setMonth(int $month): self
    {
        return $this->setDate($this->getYear(), $month, $this->getDay());
    }

    /**
     * @param int $day Numeric representation of a day from 1 through 31.
     * @return $this
     */
    public function setDay(int $day): self
    {
        return $this->setDate($this->getYear(), $this->getMonth(), $day);
    }

    /**
     * @param int $hour 24-hour format of an hour. 0 through 23.
     * @return $this
     */
    public function setHour(int $hour): self
    {
        return $this->setTime($hour, $this->getMinute(), $this->getSecond(), $this->getMicro());
    }

    /**
     * @param int $minute 0 through 59.
     * @return $this
     */
    public function setMinute(int $minute): self
    {
        return $this->setTime($this->getHour(), $minute, $this->getSecond(), $this->getMicro());
    }

    /**
     * @param int $second 0 through 59.
     * @return $this
     */
    public function setSecond(int $second): self
    {
        return $this->setTime($this->getHour(), $this->getMinute(), $second, $this->getMicro());
    }

    /**
     * @param int $micro 0 through 999999.
     * @return $this
     */
    public function setMicro(int $micro): self
    {
        return $this->setTime($this->getHour(), $this->getMinute(), $this->getSecond(), $micro);
    }

    /** Set the time to the start of the day. (00:00:00) */
    public function startOfDay(): self
    {
        return $this->setTime(0, 0);
    }

    /** Set the time to the end of the day. (23:59:59) */
    public function endOfDay(): self
    {
        return $this->setTime(23, 59, 59, 999999);
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

    /** @return int<28, 31> The number of days in the given month. 28 through 31 */
    public function getDaysInMonth(): int
    {
        return (int)$this->format('t');
    }

    /** @return int<0, 365> The day of the year starting from zero. 0 through 365 */
    public function getDayOfYear(): int
    {
        return (int)$this->format('z');
    }

    /** @return int<1, 53> ISO 8601 week number of year, weeks starting on Monday. 1 through 53 */
    public function getWeekOfYear(): int
    {
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

    public function __toString(): string
    {
        return $this->format('Y-m-d H:i:s');
    }
}