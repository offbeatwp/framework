<?php

namespace OffbeatWP\Support\Objects;

use ArrayIterator;
use IteratorAggregate;

/** @implements IteratorAggregate<int, string|int|bool> */
final class OffbeatImageSrc implements IteratorAggregate
{
    private string $url;
    private int $width;
    private int $height;
    private bool $resized;

    /** @param string[]|int[]|bool[] $imgData */
    public function __construct(array $imgData)
    {
        $this->url = $imgData[0];
        $this->width = $imgData[1];
        $this->height = $imgData[2];
        $this->resized = $imgData[3];
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getWidth(): int
    {
        return $this->width;
    }

    public function getHeight(): int
    {
        return $this->height;
    }

    public function isResized(): bool
    {
        return $this->resized;
    }

    /** @return ArrayIterator<int, string|int|bool> */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator([$this->url, $this->width, $this->height, $this->resized]);
    }
}