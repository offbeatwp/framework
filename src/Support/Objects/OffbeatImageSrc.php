<?php

namespace OffbeatWP\Support\Objects;

use ArrayIterator;
use IteratorAggregate;

/** @implements IteratorAggregate<int, string|int|bool> */
final class OffbeatImageSrc implements IteratorAggregate
{
    public readonly string $url;
    public readonly int $width;
    public readonly int $height;
    public readonly bool $resized;

    /** @param array{0: string, 1: int, 2: int, 3: bool} $imgData */
    public function __construct(array $imgData)
    {
        $this->url = $imgData[0];
        $this->width = $imgData[1];
        $this->height = $imgData[2];
        $this->resized = $imgData[3];
    }

    /** @return ArrayIterator<int, string|int|bool> */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator([$this->url, $this->width, $this->height, $this->resized]);
    }
}